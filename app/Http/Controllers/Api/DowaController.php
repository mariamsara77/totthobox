<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dowa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class DowaController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->query('search', ''));

        $allData = Cache::remember('dowas_list', now()->addHour(), function () {
            return Dowa::query()
                ->active()
                ->latest()
                ->select(['id', 'bangla_name', 'slug', 'type', 'bangla_text', 'bangla_fojilot', 'is_featured'])
                ->with('media')
                ->get();
        });

        $filtered = $allData;
        if ($search !== '') {
            $term = mb_strtolower($search, 'UTF-8');
            $filtered = $allData->filter(function ($item) use ($term) {
                return str_contains(mb_strtolower($item->bangla_name ?? '', 'UTF-8'), $term)
                    || str_contains(mb_strtolower(strip_tags($item->bangla_fojilot ?? ''), 'UTF-8'), $term)
                    || str_contains(mb_strtolower(strip_tags($item->bangla_text ?? ''), 'UTF-8'), $term);
            })->values();
        }

        $creators = Cache::remember('dowas_contributors', now()->addHour(), function () {
            $causerIds = Activity::query()
                ->where('subject_type', Dowa::class)
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
                'items' => $filtered->map(fn ($item) => $this->transformListItem($item))->values(),
                'total' => $filtered->count(),
                'creators' => $creators,
                'search' => $search,
            ],
        ]);
    }

    public function show(string $slug)
    {
        $dowa = Cache::remember("dowa_show_{$slug}", 3600, function () use ($slug) {
            return Dowa::query()
                ->active()
                ->where('slug', $slug)
                ->with('media')
                ->firstOrFail();
        });

        views($dowa)->record();

        $audioUrl = $dowa->getFirstMediaUrl('audio')
            ?: ($dowa->audio ? asset($dowa->audio) : null);

        return response()->json([
            'success' => true,
            'data' => [
                'item' => $this->transformShowItem($dowa, $audioUrl),
                'views' => views($dowa)->count(),
                'shareable_text' => $this->buildShareableText($dowa),
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

        $item = Dowa::findOrFail($id);
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

    public function reactionStatus(int $id)
    {
        $item = Dowa::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'has_like' => auth()->check() ? $item->hasReaction('like') : false,
                'has_dislike' => auth()->check() ? $item->hasReaction('dislike') : false,
                'like_count' => $item->countReaction('like'),
                'dislike_count' => $item->countReaction('dislike'),
            ],
        ]);
    }

    private function transformListItem(Dowa $item): array
    {
        $media = $item->getMedia('images') ?? collect();

        return [
            'id' => $item->id,
            'bangla_name' => strip_tags($item->bangla_name ?? ''),
            'slug' => $item->slug,
            'type' => $item->type,
            'type_name' => $item->type ?? null,
            'bangla_text' => strip_tags($item->bangla_text ?? ''),
            'media' => $media->map(fn ($m) => [
                'id' => $m->id,
                'url' => $m->getUrl(),
                'thumb' => $m->getUrl('thumb') ?: $m->getUrl(),
            ])->values(),
            'media_count' => $media->count(),
            'is_featured' => (bool) $item->is_featured,
        ];
    }

    private function transformShowItem(Dowa $item, ?string $audioUrl): array
    {
        $media = $item->getMedia('images') ?? collect();

        return [
            'id' => $item->id,
            'bangla_name' => $item->bangla_name,
            'arabic_name' => $item->arabic_name,
            'arabic_text' => $item->arabic_text,
            'bangla_text' => $item->bangla_text,
            'bangla_meaning' => $item->bangla_meaning,
            'bangla_fojilot' => $item->bangla_fojilot,
            'slug' => $item->slug,
            'type' => $item->type,
            'is_featured' => (bool) $item->is_featured,
            'audio_url' => $audioUrl,
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

    private function buildShareableText(Dowa $dowa): string
    {
        $text = "✨ {$dowa->bangla_name}";
        if ($dowa->arabic_name) {
            $text .= " ({$dowa->arabic_name})";
        }
        $text .= " ✨\n\n";
        if ($dowa->arabic_text) {
            $text .= "🕌 আরবি:\n{$dowa->arabic_text}\n\n";
        }
        if ($dowa->bangla_text) {
            $text .= "🗣️ উচ্চারণ:\n{$dowa->bangla_text}\n\n";
        }
        if ($dowa->bangla_meaning) {
            $text .= "📖 অর্থ:\n" . strip_tags($dowa->bangla_meaning);
        }
        return $text;
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