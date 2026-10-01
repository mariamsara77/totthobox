<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HistoryBd;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;

class HistoryBdController extends Controller
{
    // GET /api/history-bd?search=&era=&division_id=&district_id=&thana_id=&page=&per_page=
    public function index(Request $request)
    {
        $search     = trim((string) $request->get('search', ''));
        $era        = $request->get('era', '');
        $divisionId = $request->filled('division_id') ? (int) $request->division_id : null;
        $districtId = $request->filled('district_id') ? (int) $request->district_id : null;
        $thanaId    = $request->filled('thana_id') ? (int) $request->thana_id : null;
        $perPage    = min((int) $request->get('per_page', 10), 50);

        $items = HistoryBd::query()
            ->with(['division:id,name', 'district:id,name', 'thana:id,name', 'media'])
            ->active()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('title', 'like', "%{$search}%")
                       ->orWhere('description', 'like', "%{$search}%")
                       ->orWhere('era', 'like', "%{$search}%");
                });
            })
            ->when($era, fn ($q) => $q->where('era', $era))
            ->when($divisionId, fn ($q) => $q->where('division_id', $divisionId))
            ->when($districtId, fn ($q) => $q->where('district_id', $districtId))
            ->when($thanaId, fn ($q) => $q->where('thana_id', $thanaId))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'data' => $items->getCollection()->map(fn ($item) => $this->transformListItem($item)),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'per_page'     => $items->perPage(),
                'total'        => $items->total(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }

    // GET /api/history-bd/filters
    public function filters()
    {
        $divisions = Cache::remember('divisions_list_opt', 86400, function () {
            return Division::orderBy('name')->get(['id', 'name']);
        });

        $eras = Cache::remember('history_bd_eras', 3600, function () {
            return HistoryBd::query()
                ->active()
                ->whereNotNull('era')
                ->where('era', '!=', '')
                ->distinct()
                ->orderBy('era')
                ->pluck('era');
        });

        return response()->json([
            'divisions' => $divisions,
            'eras'      => $eras,
        ]);
    }

    // GET /api/history-bd/districts?division_id=
    public function districts(Request $request)
    {
        $divisionId = (int) $request->get('division_id');
        if (!$divisionId) {
            return response()->json(['data' => []]);
        }

        $data = Cache::remember("districts_div_{$divisionId}", 86400, function () use ($divisionId) {
            return District::where('division_id', $divisionId)
                ->orderBy('name')
                ->get(['id', 'name', 'division_id']);
        });

        return response()->json(['data' => $data]);
    }

    // GET /api/history-bd/thanas?district_id=
    public function thanas(Request $request)
    {
        $districtId = (int) $request->get('district_id');
        if (!$districtId) {
            return response()->json(['data' => []]);
        }

        $data = Cache::remember("thanas_dist_{$districtId}", 86400, function () use ($districtId) {
            return Thana::where('district_id', $districtId)
                ->orderBy('name')
                ->get(['id', 'name', 'district_id']);
        });

        return response()->json(['data' => $data]);
    }

    // GET /api/history-bd/{slug}
   public function show(string $slug)
{
    $item = HistoryBd::with(['division', 'district', 'thana', 'media'])
        ->where(function ($q) use ($slug) {
            $q->where('slug', $slug)->orWhere('id', $slug);
        })
        ->active()
        ->first();

    if (!$item) {
        return response()->json(['message' => 'Not found'], 404);
    }

    views($item)->record();

    return response()->json([
        'data' => $this->transformShowItem($item),
    ]);
}
    public function creators()
    {
        $creators = Cache::remember('history_bd_contributors', now()->addHour(), function () {
            $causerIds = Activity::query()
                ->where('subject_type', HistoryBd::class)
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
        $creators = Cache::remember("history_bd_creators_{$id}", now()->addHour(), function () use ($id) {
            $causerIds = Activity::query()
                ->where('subject_type', HistoryBd::class)
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

    $item = HistoryBd::findOrFail($id);
    $item->react($request->type);
    $item->refresh();

    return response()->json([
        'success'           => true,
        'like_count'        => $item->countReaction('like'),
        'dislike_count'     => $item->countReaction('dislike'),
        'user_has_liked'    => $item->hasReaction('like'),
        'user_has_disliked' => $item->hasReaction('dislike'),
    ]);
}

public function reactionStatus(int $id)
{
    $item = HistoryBd::findOrFail($id);

    $userHasLiked    = false;
    $userHasDisliked = false;

    if (auth()->check()) {
        $userHasLiked    = $item->hasReaction('like');
        $userHasDisliked = $item->hasReaction('dislike');
    }

    return response()->json([
        'success'           => true,
        'user_has_liked'    => $userHasLiked,
        'user_has_disliked' => $userHasDisliked,
        'like_count'        => $item->countReaction('like'),
        'dislike_count'     => $item->countReaction('dislike'),
    ]);
}

    private function transformListItem(HistoryBd $item): array
    {
        return [
            'id'          => $item->id,
            'title'       => $item->title,
            'slug'        => $item->slug,
            'era'         => $item->era,
            'start_year'  => $item->start_year,
            'end_year'    => $item->end_year,
            'is_featured' => (bool) $item->is_featured,
            'description' => $item->description,
            'image_url'   => $item->getFirstMediaUrl('images', 'thumb')
                                ?: $item->getFirstMediaUrl('images')
                                ?: $item->getFirstMediaUrl('default', 'preview')
                                ?: $item->getFirstMediaUrl('default'),
            'thana'       => $item->thana?->name,
            'district'    => $item->district?->name,
            'division'    => $item->division?->name,
        ];
    }

    private function transformShowItem(HistoryBd $item): array
{
    return [
        'id'           => $item->id,
        'title'        => $item->title,
        'slug'         => $item->slug,
        'era'          => $item->era,
        'start_year'   => $item->start_year,
        'end_year'     => $item->end_year,
        'is_featured'  => (bool) $item->is_featured,
        'description'  => $item->description,
        'image_url'    => $item->getFirstMediaUrl('images')
                            ?: $item->getFirstMediaUrl('default'),
        'thana'        => $item->thana?->name,
        'district'     => $item->district?->name,
        'division'     => $item->division?->name,
        'views_count'  => views($item)->count(),

        // Reactions — always false for user status (frontend fetches separately)
        'reactions' => [
            'like_count'        => $item->countReaction('like'),
            'dislike_count'     => $item->countReaction('dislike'),
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