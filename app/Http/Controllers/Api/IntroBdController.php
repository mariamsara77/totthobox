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

        $all = Cache::remember('intro_bd_list_v5', now()->addHours(6), function () {
            return IntroBd::query()
                ->with(['media'])
                ->latest('id')
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

    // GET /api/intro-bd/{slug}  — AppResource show-এর মতো
    public function show(string $slug)
    {
        $item = Cache::remember("intro_bd_show_{$slug}", 3600, function () use ($slug) {
            return IntroBd::with(['media'])
                ->where(function ($q) use ($slug) {
                    $q->where('slug', $slug)->orWhere('id', $slug);
                })
                ->first();
        });

        if (!$item) {
            return response()->json(['message' => 'Not found'], 404);
        }

        // Cache থেকে আসা model — reactions fresh রাখতে reload
        $item = IntroBd::with(['media'])->find($item->id);

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
                ->get();
        });

        return response()->json([
            'data' => $creators->map(fn ($user) => $this->transformCreator($user)),
        ]);
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
                ->get();
        });

        return response()->json([
            'data' => $creators->map(fn ($user) => $this->transformCreator($user)),
        ]);
    }

    // POST /api/intro-bd/{id}/react  — AppResource::react exact copy
    public function react(Request $request, int $id)
    {
        $request->validate(['type' => 'required|in:like,dislike']);

        $item = IntroBd::findOrFail($id);
        $item->react($request->type);
        $item->refresh();

        // Show cache clear (reload-এ পুরনো count যাতে না আসে)
        Cache::forget("intro_bd_show_{$item->slug}");
        Cache::forget("intro_bd_show_{$item->id}");

        return response()->json([
            'like_count'    => $item->countReaction('like'),
            'dislike_count' => $item->countReaction('dislike'),
            'has_like'      => $item->hasReaction('like'),
            'has_dislike'   => $item->hasReaction('dislike'),
        ]);
    }

    // GET /api/intro-bd/{id}/reaction-status  — AppResource exact copy
    public function reactionStatus(int $id)
    {
        $item = IntroBd::findOrFail($id);

        return response()->json([
            'has_like'    => auth()->check() ? $item->hasReaction('like') : false,
            'has_dislike' => auth()->check() ? $item->hasReaction('dislike') : false,
        ]);
    }

    // ---------- Helpers ----------

    private function transformListItem(IntroBd $item): array
    {
        return [
            'id'             => $item->id,
            'title'          => $item->title,
            'slug'           => $item->slug,
            'intro_category' => $item->intro_category,
            'description'    => $item->description,
            'image_url'      => $item->getFirstMediaUrl('intro_images', 'thumb')
                                ?: $item->getFirstMediaUrl('intro_images')
                                ?: $item->getFirstMediaUrl('images'),
        ];
    }

    // AppResource transformApp($app, true) এর মতো
    private function transformShowItem(IntroBd $item): array
    {
        return [
            'id'             => $item->id,
            'title'          => $item->title,
            'slug'           => $item->slug,
            'intro_category' => $item->intro_category,
            'description'    => $item->description,
            'image_url'      => $item->getFirstMediaUrl('intro_images')
                                ?: $item->getFirstMediaUrl('images'),
            'views_count'    => views($item)->count(),
            'views_count_bn' => function_exists('bn_num')
                ? bn_num(views($item)->count())
                : views($item)->count(),

            // AppResource full transform-এর মতো flat fields
            'like_count'    => $item->countReaction('like'),
            'dislike_count' => $item->countReaction('dislike'),
            'has_like'      => auth()->check() ? $item->hasReaction('like') : false,
            'has_dislike'   => auth()->check() ? $item->hasReaction('dislike') : false,

            // Frontend InteractiveActions-এর জন্য nested-ও রাখুন
            'reactions' => [
                'like_count'        => $item->countReaction('like'),
                'dislike_count'     => $item->countReaction('dislike'),
                'user_has_liked'    => auth()->check() ? $item->hasReaction('like') : false,
                'user_has_disliked' => auth()->check() ? $item->hasReaction('dislike') : false,
            ],

            'created_at' => $item->created_at,
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