<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\PeopleCategory;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;

class PersonController extends Controller
{
    // GET /api/people?search=&category=&position=&status=all|current|former&from=&to=&page=
    public function index(Request $request)
    {
        $search   = trim((string) $request->get('search', ''));
        $category = $request->get('category', '');
        $position = $request->get('position', '');
        $status   = $request->get('status', 'all'); // all, current, former
        $fromDate = $request->get('from', '');
        $toDate   = $request->get('to', '');
        $perPage  = min((int) $request->get('per_page', 10), 50);

        $hasHistoryFilter = $position || $status !== 'all' || $fromDate || $toDate;

        $items = Person::query()
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($category, function ($q) use ($category) {
                $q->whereHas('peopleCategories', fn ($sq) => $sq->where('people_categories.id', $category));
            })
            ->when($hasHistoryFilter, function ($q) use ($position, $status, $fromDate, $toDate) {
                $q->whereHas('histories', function ($hq) use ($position, $status, $fromDate, $toDate) {
                    $hq->when($position, fn ($sq) => $sq->where('position_id', $position))
                        ->when($status === 'current', fn ($sq) => $sq->where('is_current', true))
                        ->when($status === 'former', fn ($sq) => $sq->where('is_current', false))
                        ->when($fromDate, fn ($sq) => $sq->whereDate('from_date', '>=', $fromDate))
                        ->when($toDate, fn ($sq) => $sq->whereDate('from_date', '<=', $toDate));
                });
            })
            ->with([
                'peopleCategories:id,name',
                'histories' => fn ($q) => $q->with('position:id,title')->orderByDesc('is_current')->orderByDesc('from_date'),
                'media',
            ])
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'data' => $items->getCollection()->map(fn ($p) => $this->transformListItem($p)),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'per_page'     => $items->perPage(),
                'total'        => $items->total(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }

    // GET /api/people/filters
    public function filters()
    {
        $categories = Cache::remember('people_categories_list', 86400, function () {
            return PeopleCategory::orderBy('name')->get(['id', 'name']);
        });

        $positions = Cache::remember('positions_list', 86400, function () {
            return Position::orderBy('title')->get(['id', 'title']);
        });

        return response()->json([
            'categories' => $categories,
            'positions'  => $positions,
        ]);
    }

    // GET /api/people/{slug}
   public function show(string $slug)
{
    $person = Person::query()
        ->with([
            'media',
            'peopleCategories:id,name',
            'histories' => fn ($q) => $q
                ->with('position:id,title')
                ->orderByDesc('is_current')
                ->orderByDesc('from_date'),
        ])
        ->where(function ($q) use ($slug) {
            $q->where('slug', $slug)->orWhere('id', $slug);
        })
        ->first();

    if (!$person) {
        return response()->json(['message' => 'Not found'], 404);
    }

    views($person)->record();

    return response()->json([
        'data' => $this->transformShowItem($person),
    ]);
}

    public function creators()
    {
        $creators = Cache::remember('person_contributors', now()->addHour(), function () {
            $causerIds = Activity::query()
                ->where('subject_type', Person::class)
                ->whereNotNull('causer_id')
                ->distinct()
                ->pluck('causer_id');

            return User::query()
                ->whereIn('id', $causerIds)
                ->select(['id', 'name', 'avatar', 'slug', 'email_verified_at', 'last_active_at', 'profession'])
                ->with(['media'])
                ->get()
                ->map(fn ($u) => $this->transformCreator($u));
        });

        return response()->json(['data' => $creators]);
    }

    public function itemCreators(int $id)
    {
        $creators = Cache::remember("person_creators_{$id}", now()->addHour(), function () use ($id) {
            $causerIds = Activity::query()
                ->where('subject_type', Person::class)
                ->where('subject_id', $id)
                ->whereNotNull('causer_id')
                ->distinct()
                ->pluck('causer_id');

            return User::query()
                ->whereIn('id', $causerIds)
                ->select(['id', 'name', 'avatar', 'slug', 'email_verified_at', 'last_active_at', 'profession'])
                ->with(['media'])
                ->get()
                ->map(fn ($u) => $this->transformCreator($u));
        });

        return response()->json(['data' => $creators]);
    }

    public function react(Request $request, int $id)
{
    $request->validate(['type' => 'required|in:like,dislike']);

    if (!auth()->check()) {
        return response()->json([
            'success' => false,
            'message' => 'লগইন করা প্রয়োজন',
        ], 401);
    }

    $person = Person::findOrFail($id);
    $person->react($request->type);
    $person->refresh();

    return response()->json([
        'success'           => true,
        'like_count'        => $person->countReaction('like'),
        'dislike_count'     => $person->countReaction('dislike'),
        'user_has_liked'    => $person->hasReaction('like'),
        'user_has_disliked' => $person->hasReaction('dislike'),
    ]);
}

public function reactionStatus(int $id)
{
    $person = Person::findOrFail($id);

    $userHasLiked    = false;
    $userHasDisliked = false;

    if (auth()->check()) {
        $userHasLiked    = $person->hasReaction('like');
        $userHasDisliked = $person->hasReaction('dislike');
    }

    return response()->json([
        'success'           => true,
        'user_has_liked'    => $userHasLiked,
        'user_has_disliked' => $userHasDisliked,
        'like_count'        => $person->countReaction('like'),
        'dislike_count'     => $person->countReaction('dislike'),
    ]);
}

    private function transformListItem(Person $person): array
    {
        $activeRole = $person->histories->firstWhere('is_current', true);

        return [
            'id'          => $person->id,
            'name'        => $person->name,
            'slug'        => $person->slug,
            'image_url' => $person->getFirstMediaUrl('images', 'thumb')
                ?: $person->getFirstMediaUrl('images')
                ?: $person->getFirstMediaUrl('default', 'thumb')
                ?: $person->getFirstMediaUrl('default'),
            'categories'  => $person->peopleCategories->map(fn ($c) => [
                'id'   => $c->id,
                'name' => $c->name,
            ])->values(),
            'is_current'  => (bool) $activeRole,
            'current_role'=> $activeRole
                ? ($activeRole->position?->title ?? $activeRole->custom_role ?? 'পদবী অজানা')
                : null,
            'role_from_year' => $activeRole?->from_date
                ? $activeRole->from_date->format('Y')
                : null,
        ];
    }

    private function transformShowItem(Person $person): array
{
    $currentRole = $person->histories->firstWhere('is_current', true);

    // Multiple images support
    $images = $person->getMedia('images')->map(function ($media) {
        return [
            'url'     => $media->getUrl(),
            'caption' => $media->getCustomProperty('caption') ?? null,
            'thumb'   => $media->getUrl('thumb'),
        ];
    })->values()->toArray();

    if (empty($images)) {
        $images = $person->getMedia('default')->map(function ($media) {
            return [
                'url'     => $media->getUrl(),
                'caption' => $media->getCustomProperty('caption') ?? null,
                'thumb'   => $media->getUrl('thumb'),
            ];
        })->values()->toArray();
    }

    return [
        'id'            => $person->id,
        'name'          => $person->name,
        'slug'          => $person->slug,
        'bio'           => $person->bio,
        'image_url'     => $images[0]['url'] ?? null,
        'images'        => $images,
        'date_of_birth' => $person->date_of_birth?->format('Y-m-d'),
        'date_of_death' => $person->date_of_death?->format('Y-m-d'),
        'categories'    => $person->peopleCategories->map(fn ($c) => [
            'id'   => $c->id,
            'name' => $c->name,
        ])->values(),
        'current_role'  => $currentRole ? [
            'title'      => $currentRole->position?->title ?? $currentRole->custom_role ?? 'পদবী অজানা',
            'from_year'  => $currentRole->from_date?->format('Y'),
            'is_current' => true,
        ] : null,
        'histories'     => $person->histories->map(fn ($h) => [
            'title'      => $h->position?->title ?? $h->custom_role ?? 'পদবী অজানা',
            'is_current' => (bool) $h->is_current,
            'from_year'  => $h->from_date?->format('Y'),
            'to_year'    => $h->to_date?->format('Y'),
        ])->values(),
        'views_count'   => views($person)->count(),
        'reactions' => [
            'like_count'        => $person->countReaction('like'),
            'dislike_count'     => $person->countReaction('dislike'),
            'user_has_liked'    => false,
            'user_has_disliked' => false,
        ],
    ];
}

    private function transformCreator(User $user): array
    {
        return [
            'id'             => $user->id,
            'name'           => $user->name,
            'slug'           => $user->slug,
            'avatar_url'     => $user->avatar_url ?? null,
            'profession'     => $user->profession,
            'is_verified'    => (bool) $user->email_verified_at,
            'last_active_at' => $user->last_active_at?->diffForHumans(),
        ];
    }
}