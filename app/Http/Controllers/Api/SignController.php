<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sign;
use App\Models\SignCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;

class SignController extends Controller
{
    /**
     * List: all signs OR by category slug
     * GET /api/signs/{slug}?search=
     * slug = "all" → সব সাইন
     */
    public function index(Request $request, string $slug = 'all')
    {
        $search = trim($request->query('search', ''));
        $isAll = $slug === 'all';

        $category = null;
        if (!$isAll) {
            $category = Cache::remember("sign_category_{$slug}", now()->addHour(), function () use ($slug) {
                return SignCategory::query()->where('slug', $slug)->first();
            });

            if (!$category) {
                return response()->json(['success' => false, 'message' => 'Category not found'], 404);
            }
        }

        $cacheKey = $isAll ? 'sign_all_signs' : "sign_category_signs_{$slug}";

        $allData = Cache::remember($cacheKey, now()->addHour(), function () use ($isAll, $category) {
            if ($isAll) {
                return Sign::query()
                    ->with(['media', 'category'])
                    ->latest()
                    ->get();
            }
            return $category->signs()->with('media')->latest()->get();
        });

        $filtered = $allData;
        if ($search !== '') {
            $term = mb_strtolower($search, 'UTF-8');
            $filtered = $allData->filter(function ($item) use ($term) {
                return str_contains(mb_strtolower($item->name ?? '', 'UTF-8'), $term)
                    || str_contains(mb_strtolower(strip_tags($item->description ?? ''), 'UTF-8'), $term);
            })->values();
        }

        $creators = Cache::remember('signs_contributors', now()->addHour(), function () {
            $causerIds = Activity::query()
                ->where('subject_type', Sign::class)
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
                'is_all' => $isAll,
                'category' => $category ? $this->transformCategory($category) : null,
                'items' => $filtered->map(fn ($item) => $this->transformListItem($item, $isAll))->values(),
                'total' => $filtered->count(),
                'creators' => $creators,
                'search' => $search,
            ],
        ]);
    }

    /**
     * Show single sign
     * GET /api/signs/{category}/{sign}
     */
    public function show(string $category, string $sign)
    {
        $data = Cache::remember("sign_show_{$category}_{$sign}", 3600, function () use ($category, $sign) {
            $cat = SignCategory::where('slug', $category)->first();
            if (!$cat) {
                return null;
            }

            $signModel = $cat->signs()
                ->with('media')
                ->where(function ($q) use ($sign) {
                    $q->where('slug', $sign)->orWhere('id', $sign);
                })
                ->first();

            if (!$signModel) {
                return null;
            }

            return ['category' => $cat, 'sign' => $signModel];
        });

        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $signModel = $data['sign'];
        views($signModel)->record();

        $creators = Cache::remember("sign_creators_{$signModel->id}", now()->addHour(), function () use ($signModel) {
            $causerIds = Activity::query()
                ->where('subject_type', Sign::class)
                ->where('subject_id', $signModel->id)
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
                'category' => $this->transformCategory($data['category']),
                'item' => $this->transformShowItem($signModel),
                'creators' => $creators,
                'views' => views($signModel)->count(),
            ],
        ]);
    }

    public function react(Request $request, int $id)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'রিয়্যাকশন করার জন্য লগইন করতে হবে।',
            ], 401);
        }

        $request->validate(['type' => 'required|in:like,dislike']);

        $item = Sign::findOrFail($id);
        $item->react($request->type);
        $item->refresh();

        return response()->json([
            'success' => true,
            'message' => 'আপনার রিয়্যাকশন সফলভাবে রেকর্ড করা হয়েছে!',
            'data' => [
                'like_count' => $item->countReaction('like'),
                'dislike_count' => $item->countReaction('dislike'),
                'has_like' => $item->hasReaction('like'),
                'has_dislike' => $item->hasReaction('dislike'),
            ],
        ]);
    }

    private function transformCategory(SignCategory $cat): array
    {
        return [
            'id' => $cat->id,
            'name' => $cat->name,
            'slug' => $cat->slug,
            'icon' => $cat->icon,
            'short_description' => $cat->short_description,
        ];
    }

    private function transformListItem(Sign $item, bool $isAll = false): array
    {
        $thumb = $item->getFirstMediaUrl('images', 'thumb') ?: $item->getFirstMediaUrl('images');

        return [
            'id' => $item->id,
            'name' => $item->name ?? 'অজানা সংকেত',
            'slug' => $item->slug ?: (string) $item->id,
            'description_plain' => strip_tags($item->description ?? ''),
            'thumb' => $thumb ?: null,
            'category' => $isAll && $item->relationLoaded('category') && $item->category
                ? [
                    'id' => $item->category->id,
                    'name' => $item->category->name,
                    'slug' => $item->category->slug,
                ]
                : null,
        ];
    }

    private function transformShowItem(Sign $item): array
    {
        $media = $item->getMedia('images');

        return [
            'id' => $item->id,
            'name' => $item->name ?? 'অজানা সংকেত',
            'slug' => $item->slug ?: (string) $item->id,
            'description' => $item->description,
            'description_plain' => strip_tags($item->description ?? ''),
            'media' => $media->map(fn ($m) => [
                'id' => $m->id,
                'url' => $m->getUrl(),
                'thumb' => $m->getUrl('thumb') ?: $m->getUrl(),
            ])->values(),
            'first_media_url' => $item->getFirstMediaUrl('images') ?: null,
            'like_count' => $item->countReaction('like'),
            'dislike_count' => $item->countReaction('dislike'),
            'has_like' => auth()->check() ? $item->hasReaction('like') : false,
            'has_dislike' => auth()->check() ? $item->hasReaction('dislike') : false,
        ];
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
            'last_active_bn' => $user->last_active_at
                ? (function_exists('bn_num') ? bn_num($user->last_active_at->diffForHumans()) : $user->last_active_at->diffForHumans())
                : 'অজানা',
        ];
    }
}