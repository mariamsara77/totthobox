<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;

class AppResourceController extends Controller
{
    // GET /api/apps
    public function index(Request $request)
    {
        $search   = $request->get('search', '');
        $platform = $request->get('platform', '');
        $perPage  = min((int) $request->get('per_page', 10), 50);

        $apps = AppResource::query()
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($platform, fn ($q) => $q->where('platform', $platform))
            ->with(['media'])
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'data' => $apps->map(fn ($app) => $this->transformApp($app)),
            'meta' => [
                'current_page' => $apps->currentPage(),
                'last_page'    => $apps->lastPage(),
                'per_page'     => $apps->perPage(),
                'total'        => $apps->total(),
                'has_more'     => $apps->hasMorePages(),
            ],
        ]);
    }

    // GET /api/apps/{slug}
    public function show(string $slug)
    {
        $app = Cache::remember("app_resource_show_{$slug}", 3600, function () use ($slug) {
            return AppResource::with(['media'])
                ->where('slug', $slug)
                ->orWhere('id', $slug)
                ->first();
        });

        if (!$app) {
            return response()->json(['message' => 'App not found'], 404);
        }

        views($app)->record();

        return response()->json([
            'data' => $this->transformApp($app, true),
        ]);
    }

    // GET /api/apps/creators  (all contributors)
    public function creators()
    {
        $creators = Cache::remember('app_resource_contributors', now()->addHour(), function () {
            $causerIds = Activity::query()
                ->where('subject_type', AppResource::class)
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

    // GET /api/apps/{id}/creators  (creators of a single app)
    public function appCreators(int $id)
    {
        $creators = Cache::remember("app_resource_creators_{$id}", now()->addHour(), function () use ($id) {
            $causerIds = Activity::query()
                ->where('subject_type', AppResource::class)
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

    // POST /api/apps/{id}/download
    public function download(int $id)
    {
        $app = AppResource::findOrFail($id);
        $app->increment('download_count');

        $url = $app->download_type === 'external'
            ? $app->external_url
            : $app->getFirstMediaUrl('app_files');

        return response()->json([
            'download_url' => $url,
            'download_count' => $app->download_count,
        ]);
    }

    // POST /api/apps/{id}/react  (requires auth)
    public function react(Request $request, int $id)
    {
        $request->validate(['type' => 'required|in:like,dislike']);

        $app = AppResource::findOrFail($id);
        $app->react($request->type);
        $app->refresh();

        return response()->json([
            'like_count'    => $app->countReaction('like'),
            'dislike_count' => $app->countReaction('dislike'),
            'has_like'      => $app->hasReaction('like'),
            'has_dislike'   => $app->hasReaction('dislike'),
        ]);
    }

    // ---------- Helpers ----------

    private function transformApp(AppResource $app, bool $full = false): array
    {
        $data = [
            'id'              => $app->id,
            'name'            => $app->name,
            'slug'            => $app->slug,
            'version'         => $app->version,
            'platform'        => $app->platform,
            'description'     => $app->description,
            'download_count'  => $app->download_count ?? 0,
            'views_count'     => views($app)->count(),
            'icon_url'        => $app->getFirstMediaUrl('app_icons', 'thumb') ?: $app->getFirstMediaUrl('app_icons'),
            'download_password' => $app->download_password,
            'created_at'      => $app->created_at,
        ];

        if ($full) {
            $data['download_type'] = $app->download_type;
            $data['external_url']  = $app->external_url;
            $data['like_count']    = $app->countReaction('like');
            $data['dislike_count'] = $app->countReaction('dislike');
            $data['has_like']      = auth()->check() ? $app->hasReaction('like') : false;
            $data['has_dislike']   = auth()->check() ? $app->hasReaction('dislike') : false;
        }

        return $data;
    }

    private function transformCreator(User $user): array
    {
        return [
            'id'               => $user->id,
            'name'             => $user->name,
            'slug'             => $user->slug,
            'avatar_url'       => $user->avatar_url ?? null,
            'profession'       => $user->profession,
            'is_verified'      => (bool) $user->email_verified_at,
            'is_online'        => method_exists($user, 'isOnline') ? $user->isOnline() : false,
            'last_active_at'   => $user->last_active_at?->diffForHumans(),
        ];
    }

    public function reactionStatus(int $id)
{
    $app = AppResource::findOrFail($id);

    return response()->json([
        'has_like'    => auth()->check() ? $app->hasReaction('like') : false,
        'has_dislike' => auth()->check() ? $app->hasReaction('dislike') : false,
    ]);
}
}