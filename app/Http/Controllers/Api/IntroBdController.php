<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IntroBd;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;

class IntroBdController extends Controller
{
    // GET /api/intro-bd?search=
   public function index(Request $request)
{
    $search = trim((string) $request->get('search', ''));

    $all = Cache::remember('intro_bd_list_v8', now()->addHours(6), function () {
        return IntroBd::query()
            ->with(['media'])
            ->orderBy('sort_order')
            ->orderBy('category_order')
            ->orderBy('title')
            ->get()
            ->map(fn ($item) => $this->transformListItem($item));
    });

    if ($search !== '') {
        $term = mb_strtolower($search, 'UTF-8');
        $all = $all->filter(function ($item) use ($term) {
            return str_contains(mb_strtolower($item['title'] ?? '', 'UTF-8'), $term)
                || str_contains(mb_strtolower(strip_tags($item['description'] ?? ''), 'UTF-8'), $term)
                || str_contains(mb_strtolower($item['intro_category'] ?? '', 'UTF-8'), $term);
        })->values();
    }

    $grouped = $all->groupBy(fn ($item) => $item['intro_category'] ?: 'সাধারণ তথ্য');

    return response()->json([
        'data'  => $grouped,
        'total' => $all->count(),
    ]);
}

    // GET /api/intro-bd/{slug}
    public function show(string $slug)
{
    $item = IntroBd::with(['media'])
        ->where(function ($q) use ($slug) {
            $q->where('slug', $slug)->orWhere('id', $slug);
        })
        ->first();

    if (!$item) {
        return response()->json(['message' => 'Not found'], 404);
    }

    views($item)->record();

    return response()->json([
        'data' => $this->transformShowItem($item),
    ]);
}

    // GET /api/intro-bd/creators
    public function creators()
    {
        $creators = Cache::remember('intro_bd_contributors', now()->addHour(), function () {
            $causerIds = Activity::query()
                ->where('subject_type', IntroBd::class)
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

    // GET /api/intro-bd/{id}/creators
    public function itemCreators(int $id)
    {
        $creators = Cache::remember("intro_bd_creators_{$id}", now()->addHour(), function () use ($id) {
            $causerIds = Activity::query()
                ->where('subject_type', IntroBd::class)
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

    // POST /api/intro-bd/{id}/react
    public function react(Request $request, int $id)
    {
        $request->validate(['type' => 'required|in:like,dislike']);

        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'লগইন করা প্রয়োজন',
            ], 401);
        }

        $item = IntroBd::findOrFail($id);
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
        $item = IntroBd::findOrFail($id);

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

    // ---------- Transformers ----------

    private function transformListItem(IntroBd $item): array
{
    $imageUrl = $item->getFirstMediaUrl('intro_images', 'thumb')
        ?: $item->getFirstMediaUrl('intro_images')
        ?: $item->getFirstMediaUrl('default', 'thumb')
        ?: $item->getFirstMediaUrl('default');

    return [
        'id'             => $item->id,
        'title'          => $item->title,
        'slug'           => $item->slug,
        'intro_category' => $item->intro_category,
        'description'    => $item->description,
        'image_url'      => $imageUrl,
    ];
}

 private function transformShowItem(IntroBd $item): array
{
    // সরাসরি media relationship থেকে নাও (সবচেয়ে নির্ভরযোগ্য)
    $images = $item->media
        ->where('collection_name', 'intro_images')
        ->map(function ($media) {
            return [
                'url'     => $media->getUrl(),
                'caption' => $media->getCustomProperty('caption') ?? null,
                'thumb'   => $media->hasGeneratedConversion('thumb') 
                                ? $media->getUrl('thumb') 
                                : $media->getUrl(),
            ];
        })
        ->values()
        ->toArray();

    // যদি intro_images না পায়, তাহলে সব মিডিয়া নাও
    if (empty($images)) {
        $images = $item->media->map(function ($media) {
            return [
                'url'     => $media->getUrl(),
                'caption' => $media->getCustomProperty('caption') ?? null,
                'thumb'   => $media->hasGeneratedConversion('thumb') 
                                ? $media->getUrl('thumb') 
                                : $media->getUrl(),
            ];
        })->values()->toArray();
    }

    return [
        'id'             => $item->id,
        'title'          => $item->title,
        'slug'           => $item->slug,
        'intro_category' => $item->intro_category,
        'description'    => $item->description,
        'image_url'      => $images[0]['url'] ?? null,
        'images'         => $images,
        'views_count'    => views($item)->count(),
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
            'is_online'      => method_exists($user, 'isOnline') ? $user->isOnline() : false,
            'last_active_at' => $user->last_active_at?->diffForHumans(),
        ];
    }
}