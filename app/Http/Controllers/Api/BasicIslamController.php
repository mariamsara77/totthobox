<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BasicIslam;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;

class BasicIslamController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->query('search', ''));

        $allData = Cache::remember('basic_islams_list', now()->addHour(), function () {
            return BasicIslam::query()
                ->with(['media'])
                ->latest('id')
                ->get();
        });

        $filtered = $allData;
        if ($search !== '') {
            $term = mb_strtolower($search, 'UTF-8');
            $filtered = $allData->filter(function ($item) use ($term) {
                return str_contains(mb_strtolower($item->title ?? '', 'UTF-8'), $term)
                    || str_contains(mb_strtolower(strip_tags($item->description ?? ''), 'UTF-8'), $term);
            })->values();
        }

        $creators = Cache::remember('basic_islams_contributors', now()->addHour(), function () {
            $causerIds = Activity::query()
                ->where('subject_type', BasicIslam::class)
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

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $filtered->map(fn ($item) => $this->transformItem($item))->values(),
                'total' => $filtered->count(),
                'creators' => $creators,
                'search' => $search,
            ],
        ]);
    }

    public function show(string $slug)
    {
        $item = BasicIslam::where('slug', $slug)
            ->with(['media'])
            ->firstOrFail();

        views($item)->record();

        $creators = Cache::remember("basic_islam_creators_{$item->id}", now()->addHour(), function () use ($item) {
            $causerIds = Activity::query()
                ->where('subject_type', BasicIslam::class)
                ->where('subject_id', $item->id)
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

        return response()->json([
            'success' => true,
            'data' => [
                'item' => $this->transformItem($item, true),
                'creators' => $creators,
                'views' => views($item)->count(),
            ],
        ]);
    }

    /**
     * Livewire-এর মতো react
     * POST /api/islam/basic/{id}/react
     */
    public function react(Request $request, int $id)
    {
        // Livewire: if (!auth()->check()) { toast + modal; return; }
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'রিয়্যাকশন করার জন্য লগইন করতে হবে।',
            ], 401);
        }

        $request->validate([
            'type' => 'required|in:like,dislike',
        ]);

        $item = BasicIslam::findOrFail($id);
        $item->react($request->type);   // HasReactions trait
        $item->refresh();

        return response()->json([
            'success' => true,
            'message' => 'আপনার রিয়্যাকশন সফলভাবে রেকর্ড করা হয়েছে!',
            'data' => [
                'like_count'    => $item->countReaction('like'),
                'dislike_count' => $item->countReaction('dislike'),
                'has_like'      => $item->hasReaction('like'),
                'has_dislike'   => $item->hasReaction('dislike'),
            ],
        ]);
    }

    /**
     * Current user-এর reaction status
     * GET /api/islam/basic/{id}/reaction-status
     */
    public function reactionStatus(int $id)
    {
        $item = BasicIslam::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'has_like'    => auth()->check() ? $item->hasReaction('like') : false,
                'has_dislike' => auth()->check() ? $item->hasReaction('dislike') : false,
                'like_count'  => $item->countReaction('like'),
                'dislike_count' => $item->countReaction('dislike'),
            ],
        ]);
    }

    private function transformItem(BasicIslam $item, bool $full = false): array
    {
        $media = $item->getMedia('images');

        $data = [
            'id' => $item->id,
            'title' => strip_tags($item->title ?? ''),
            'slug' => $item->slug,
            'type' => $item->type,
            'type_name' => $item->typeName,
            'description' => $full ? ($item->description ?? '') : strip_tags($item->description ?? ''),
            'description_plain' => strip_tags($item->description ?? ''),
            'media' => $media->map(fn ($m) => [
                'id' => $m->id,
                'url' => $m->getUrl(),
                'thumb' => $m->getUrl('thumb'),
            ])->values(),
            'media_count' => $media->count(),
            'first_media_url' => $item->getFirstMediaUrl('images') ?: null,
            'is_featured' => (bool) $item->is_featured,
            'created_at' => $item->created_at?->toISOString(),
        ];

        if ($full) {
            $data['like_count'] = $item->countReaction('like');
            $data['dislike_count'] = $item->countReaction('dislike');
            $data['has_like'] = auth()->check() ? $item->hasReaction('like') : false;
            $data['has_dislike'] = auth()->check() ? $item->hasReaction('dislike') : false;
        }

        return $data;
    }

    private function transformCreator(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'slug' => $user->slug,
            'avatar_url' => $user->avatar_url ?? $user->getFirstMediaUrl('avatar') ?? null,
            'profession' => $user->profession ?? 'কন্টেন্ট কন্ট্রিবিউটর',
            'email_verified' => !is_null($user->email_verified_at),
            'is_online' => method_exists($user, 'isOnline') ? $user->isOnline() : false,
            'last_active_at' => $user->last_active_at?->diffForHumans(),
            'last_active_bn' => $user->last_active_at
                ? (function_exists('bn_num') ? bn_num($user->last_active_at->diffForHumans()) : $user->last_active_at->diffForHumans())
                : 'অজানা',
        ];
    }
}