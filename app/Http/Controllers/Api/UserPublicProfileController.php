<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserPublicProfileController extends Controller
{
    public function show($slug, Request $request)
    {
        $user = User::with(['district', 'thana', 'classLevel'])->where('slug', $slug)->firstOrFail();

        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 12);

        $activities = $this->getPublishedContent($user);

        $total = $activities->count();
        $offset = ($page - 1) * $perPage;
        $paginatedItems = $activities->slice($offset, $perPage)->values();

        return response()->json([
            'success' => true,
            'data' => [
                'profile' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'slug' => $user->slug,
                    'avatar' => $user->getFirstMediaUrl('avatars') ?: asset('images/default-avatar.png'),
                    'is_online' => method_exists($user, 'isOnline') ? $user->isOnline() : false,
                    'is_verified_admin' => $user->hasRole(['admin', 'super admin']),
                    'role_label' => $this->getRoleLabel($user),
                    'location' => $this->getLocation($user),
                    'bio' => $user->bio,
                    'education' => $user->education,
                    'class_level' => $user->is_student && $user->classLevel ? $user->classLevel->name : null,
                ],
                'contents' => $paginatedItems,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'has_more' => $total > ($page * $perPage),
                ]
            ]
        ]);
    }

    private function getPublishedContent(User $user)
    {
        $allowedTypes = [
            \App\Models\BuySellPost::class, \App\Models\TourismBd::class, 
            \App\Models\HistoryBd::class, \App\Models\BasicIslam::class, 
            \App\Models\ExcelTutorial::class, \App\Models\IntroBd::class, 
            \App\Models\AppResource::class, \App\Models\Dowa::class
        ];

        return cache()->remember("api_user_activity_valid_{$user->id}", 1800, function () use ($user, $allowedTypes) {
            return $user->actions()
                ->where('description', 'created')
                ->whereIn('subject_type', $allowedTypes)
                ->with('subject')
                ->latest()
                ->get()
                ->map(function ($activity) {
                    $model = $activity->subject;
                    if (!$model) return null;

                    $config = $this->getTypeConfig(get_class($model));
                    $url = $this->getDynamicUrl($model, $config);

                    if (!$this->isValidUrl($url)) return null;

                    return [
                        'type_label' => $config['label'],
                        'icon' => $config['icon'],
                        'title' => $this->getTitle($model),
                        'description' => $this->getDescription($model),
                        'created_at' => $activity->created_at,
                        'url' => $url,
                        'thumbnail' => $this->getThumbnail($model),
                    ];
                })
                ->filter()
                ->values();
        });
    }

    private function getRoleLabel(User $user): string
    {
        $role = $user->getRoleNames()->first();
        if ($role === 'Student') return 'শিক্ষার্থী';
        if ($role === 'Admin') return 'এডমিন';
        return $user->profession ?? 'ব্যবহারকারী';
    }

    private function getLocation(User $user): ?string
    {
        $addressArr = collect([$user->address, $user->thana?->name, $user->district?->name])->filter()->join(', ');
        return $addressArr ?: $user->location;
    }

    private function getTitle($model): string
    {
        return $model->title ?? ($model->name ?? ($model->bangla_name ?? ($model->bn_name ?? ($model->heading ?? ($model->subject ?? 'শিরোনামহীন')))));
    }

    private function getDescription($model): string
    {
        $text = $model->description ?? ($model->body ?? ($model->arabic_text ?? ($model->bangla_text ?? ($model->content ?? ($model->details ?? ($model->short_description ?? ($model->summary ?? ($model->bn_description ?? ''))))))));
        return Str::limit(strip_tags($text), 80);
    }

    private function getThumbnail($model): ?string
    {
        if (!method_exists($model, 'getFirstMediaUrl') && !method_exists($model, 'getMedia')) return null;

        $collectionMap = [
            \App\Models\TourismBd::class => ['tourism_images', 'images', 'image', 'default'],
            \App\Models\BuySellPost::class => ['images', 'image', 'photos', 'default'],
            \App\Models\AppResource::class => ['images', 'app_logos', 'thumb', 'app_icons', 'logo', 'default'],
            \App\Models\HistoryBd::class => ['images', 'image', 'cover', 'default'],
            \App\Models\IntroBd::class => ['images', 'image', 'cover', 'default'],
            \App\Models\BasicIslam::class => ['images', 'image', 'default'],
            \App\Models\ExcelTutorial::class => ['images', 'image', 'thumbnail', 'default'],
            \App\Models\Dowa::class => ['images', 'image', 'default'],
        ];

        $class = get_class($model);
        $collections = $collectionMap[$class] ?? ['images', 'image', 'thumbnail', 'thumb', 'cover', 'photo', 'default', 'avatars', 'tourism_images'];
        $conversions = ['thumb', 'preview', 'small', 'medium', 'webp', ''];

        foreach ($collections as $collection) {
            foreach ($conversions as $conversion) {
                try {
                    $url = $conversion === '' ? $model->getFirstMediaUrl($collection) : $model->getFirstMediaUrl($collection, $conversion);
                    if (!empty($url)) return $url;
                } catch (\Throwable $e) {}
            }
        }

        try {
            $media = $model->getMedia()->first();
            if ($media) return $media->getUrl('thumb') ?: $media->getUrl();
        } catch (\Throwable $e) {}

        return null;
    }

    private function isValidUrl(?string $url): bool
    {
        if (empty($url) || $url === '#' || $url === '/' || str_starts_with($url, '?') || str_starts_with($url, '/?')) {
            return false;
        }
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return (bool) filter_var($url, FILTER_VALIDATE_URL);
        }
        return str_starts_with($url, '/');
    }

    private function getTypeConfig(string $class): array
    {
        // (Volt component-এর সেম getTypeConfig রিটার্ন অ্যারে বসবে)
        return match ($class) {
            \App\Models\BuySellPost::class => ['label' => 'ক্রয়/বিক্রয়', 'icon' => 'shopping-bag', 'route' => 'buysell.buysell-single', 'param' => 'slug', 'needs_param' => true],
            \App\Models\TourismBd::class => ['label' => 'পর্যটন কেন্দ্র', 'icon' => 'camera', 'route' => 'bangladesh.tourism.show', 'param' => 'slug', 'needs_param' => true],
            \App\Models\HistoryBd::class => ['label' => 'ইতিহাস', 'icon' => 'book-open', 'route' => 'bangladesh.history.show', 'param' => 'slug', 'needs_param' => true],
            \App\Models\BasicIslam::class => ['label' => 'ইসলামিক জ্ঞান', 'icon' => 'moon', 'route' => 'islam.basicislam.show', 'param' => 'slug', 'needs_param' => true],
            \App\Models\ExcelTutorial::class => ['label' => 'এক্সেল টিউটোরিয়াল', 'icon' => 'table-cells', 'route' => 'excel.view', 'param' => 'slug', 'needs_param' => true],
            \App\Models\IntroBd::class => ['label' => 'বাংলাদেশ পরিচিতি', 'icon' => 'globe-alt', 'route' => 'bangladesh.introduction.show', 'param' => 'slug', 'needs_param' => true],
            \App\Models\AppResource::class => ['label' => 'অ্যাপ সম্পর্কিত সম্পদ', 'icon' => 'globe-alt', 'route' => 'software.show', 'param' => 'slug', 'needs_param' => true],
            \App\Models\Dowa::class => ['label' => 'দোয়া', 'icon' => 'book-open', 'route' => 'islam.dowan.show', 'param' => 'slug', 'needs_param' => true],
            default => ['label' => 'কন্টেন্ট', 'icon' => 'document-text', 'route' => null, 'param' => null, 'needs_param' => false],
        };
    }

    private function getDynamicUrl($model, array $config): ?string
{
    $url = null;

    if (method_exists($model, 'url')) {
        try {
            $url = $model->url();
            if (!$this->isValidUrl($url)) {
                $url = null;
            }
        } catch (\Throwable $e) {}
    }

    if (!$url && !empty($config['route'])) {
        try {
            if (!$config['needs_param']) {
                $url = route($config['route']);
            } else {
                $paramValue = $model->slug ?? ($model->id ?? null);
                if (!empty($paramValue)) {
                    $url = route($config['route'], [$config['param'] => $paramValue]);
                }
            }
            if (!$this->isValidUrl($url)) {
                $url = null;
            }
        } catch (\Throwable $e) {
            $url = null;
        }
    }

    return $url ? $this->toFrontendUrl($url) : null;
}

/**
 * admin.totthobox.com → totthobox.com
 */
private function toFrontendUrl(string $url): string
{
    $frontend = rtrim(env('FRONTEND_URL', 'https://totthobox.com'), '/');
    $backend  = rtrim(config('app.url'), '/'); // সাধারণত https://admin.totthobox.com

    // Absolute URL হলে domain replace
    if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
        $url = str_replace($backend, $frontend, $url);
        // backup: যদি APP_URL অন্য কিছু হয়
        $url = str_replace('https://admin.totthobox.com', $frontend, $url);
        $url = str_replace('http://admin.totthobox.com', $frontend, $url);
        return $url;
    }

    // Relative path হলে frontend base যোগ
    return $frontend . '/' . ltrim($url, '/');
}
}