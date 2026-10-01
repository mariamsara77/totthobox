<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;

class AppResourceController extends Controller
{
    /**
     * GET /api/apps
     */
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->get('search', ''));
        $platform = trim((string) $request->get('platform', ''));

        $perPage = min(
            max((int) $request->get('per_page', 12), 1),
            50
        );

        $apps = AppResource::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when(
                $platform !== '',
                fn ($query) => $query->where('platform', $platform)
            )
            ->with('media')
            ->latest('id')
            ->paginate($perPage);

        return response()->json([
            'data' => $apps->getCollection()
                ->map(fn (AppResource $app) => $this->transformApp($app))
                ->values(),

            'meta' => [
                'current_page' => $apps->currentPage(),
                'last_page'    => $apps->lastPage(),
                'per_page'     => $apps->perPage(),
                'total'        => $apps->total(),
                'has_more'     => $apps->hasMorePages(),
            ],
        ]);
    }

    /**
     * GET /api/apps/{slug}
     */
    public function show(string $slug): JsonResponse
    {
        $slug = trim($slug);

        if ($slug === '') {
            return response()->json([
                'message' => 'App not found',
            ], 404);
        }

        $cacheKey = "app_resource_show_{$slug}";

        $app = Cache::remember(
            $cacheKey,
            now()->addHour(),
            function () use ($slug) {
                return AppResource::query()
                    ->with('media')
                    ->where(function ($query) use ($slug) {
                        $query->where('slug', $slug);

                        if (ctype_digit($slug)) {
                            $query->orWhere('id', (int) $slug);
                        }
                    })
                    ->first();
            }
        );

        if (!$app) {
            return response()->json([
                'message' => 'App not found',
            ], 404);
        }

        views($app)->record();

        return response()->json([
            'data' => $this->transformApp($app, true),
        ]);
    }

    /**
     * GET /api/apps/creators
     */
    public function creators(): JsonResponse
    {
        $creators = Cache::remember(
            'app_resource_contributors',
            now()->addHour(),
            function () {
                $causerIds = Activity::query()
                    ->where('subject_type', AppResource::class)
                    ->whereNotNull('causer_id')
                    ->distinct()
                    ->pluck('causer_id');

                if ($causerIds->isEmpty()) {
                    return collect();
                }

                return User::query()
                    ->whereIn('id', $causerIds)
                    ->select([
                        'id',
                        'name',
                        'avatar',
                        'slug',
                        'email_verified_at',
                        'last_active_at',
                        'profession',
                    ])
                    ->with('media')
                    ->get();
            }
        );

        return response()->json([
            'data' => $creators
                ->map(fn (User $user) => $this->transformCreator($user))
                ->values(),
        ]);
    }

    /**
     * GET /api/apps/{id}/creators
     */
    public function appCreators(int $id): JsonResponse
    {
        $creators = Cache::remember(
            "app_resource_creators_{$id}",
            now()->addHour(),
            function () use ($id) {
                $causerIds = Activity::query()
                    ->where('subject_type', AppResource::class)
                    ->where('subject_id', $id)
                    ->whereNotNull('causer_id')
                    ->distinct()
                    ->pluck('causer_id');

                if ($causerIds->isEmpty()) {
                    return collect();
                }

                return User::query()
                    ->whereIn('id', $causerIds)
                    ->select([
                        'id',
                        'name',
                        'avatar',
                        'slug',
                        'email_verified_at',
                        'last_active_at',
                        'profession',
                    ])
                    ->with('media')
                    ->get();
            }
        );

        return response()->json([
            'data' => $creators
                ->map(fn (User $user) => $this->transformCreator($user))
                ->values(),
        ]);
    }

    /**
     * POST /api/apps/{id}/download
     *
     * Compatibility endpoint:
     * This does NOT download or serve a software file.
     * It only returns the official external source URL.
     */
    public function download(int $id): JsonResponse
    {
        $app = AppResource::query()->findOrFail($id);

        $url = trim((string) $app->external_url);

        if (
            $app->download_type !== 'external' ||
            $url === '' ||
            !$this->isValidOfficialUrl($url)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'অফিসিয়াল সোর্স পাওয়া যায়নি।',
            ], 404);
        }

        $app->increment('download_count');
        $app->refresh();

        return response()->json([
            'success'        => true,
            'download_url'   => $url,
            'download_count' => (int) ($app->download_count ?? 0),
        ]);
    }

    /**
     * POST /api/apps/{id}/react
     */
    public function react(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:like,dislike'],
        ]);

        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'লগইন করা প্রয়োজন',
            ], 401);
        }

        $app = AppResource::query()->findOrFail($id);

        $app->react($validated['type']);
        $app->refresh();

        return response()->json([
            'success'           => true,
            'like_count'        => $app->countReaction('like'),
            'dislike_count'     => $app->countReaction('dislike'),
            'user_has_liked'    => $app->hasReaction('like'),
            'user_has_disliked' => $app->hasReaction('dislike'),
        ]);
    }

    /**
     * GET /api/apps/{id}/reaction-status
     */
    public function reactionStatus(int $id): JsonResponse
    {
        $app = AppResource::query()->findOrFail($id);

        $userHasLiked = false;
        $userHasDisliked = false;

        if (auth()->check()) {
            $userHasLiked = $app->hasReaction('like');
            $userHasDisliked = $app->hasReaction('dislike');
        }

        return response()->json([
            'success'           => true,
            'user_has_liked'    => $userHasLiked,
            'user_has_disliked' => $userHasDisliked,
            'like_count'        => $app->countReaction('like'),
            'dislike_count'     => $app->countReaction('dislike'),
        ]);
    }

    /**
     * Transform app data for API response.
     */
    private function transformApp(
        AppResource $app,
        bool $full = false
    ): array {
        $data = [
            'id'             => $app->id,
            'name'           => $app->name,
            'slug'           => $app->slug,
            'version'        => $app->version,
            'platform'       => $app->platform,
            'description'    => $app->description,
            'download_count' => (int) ($app->download_count ?? 0),
            'views_count'    => views($app)->count(),
            'icon_url'       => $app->getFirstMediaUrl('app_icons', 'thumb')
                ?: $app->getFirstMediaUrl('app_icons'),
            'created_at'     => $app->created_at,
        ];

        if ($full) {
            /*
             * Kept for frontend compatibility.
             *
             * This is NOT a file download type anymore.
             * Only external source is allowed.
             */
            $data['download_type'] = 'external';
            $data['external_url'] = $this->isValidOfficialUrl(
                trim((string) $app->external_url)
            )
                ? trim((string) $app->external_url)
                : null;

            $data['reactions'] = [
                'like_count'        => $app->countReaction('like'),
                'dislike_count'     => $app->countReaction('dislike'),
                'user_has_liked'    => auth()->check()
                    ? $app->hasReaction('like')
                    : false,
                'user_has_disliked' => auth()->check()
                    ? $app->hasReaction('dislike')
                    : false,
            ];
        }

        return $data;
    }

    /**
     * Transform creator data.
     */
    private function transformCreator(User $user): array
    {
        return [
            'id'             => $user->id,
            'name'           => $user->name,
            'slug'           => $user->slug,
            'avatar_url'     => $user->avatar_url ?? null,
            'profession'     => $user->profession,
            'is_verified'    => (bool) $user->email_verified_at,
            'is_online'      => method_exists($user, 'isOnline')
                ? $user->isOnline()
                : false,
            'last_active_at' => $user->last_active_at?->diffForHumans(),
        ];
    }

    /**
     * Only allow secure HTTP(S) URLs.
     */
    private function isValidOfficialUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parts = parse_url($url);

        if (!$parts) {
            return false;
        }

        $scheme = strtolower($parts['scheme'] ?? '');

        if (!in_array($scheme, ['https'], true)) {
            return false;
        }

        return !empty($parts['host']);
    }
}