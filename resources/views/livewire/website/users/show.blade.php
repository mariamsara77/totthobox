<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;

new #[Layout('components.layouts.app.header')] class extends Component {
    public $user;
    public int $perPage = 12;
    public int $page = 1;

    public function mount($slug)
    {
        $this->user = User::where('slug', $slug)->firstOrFail();
    }

    public function loadMore()
    {
        $this->page++;
    }

    public function getAllDataProperty()
    {
        // শুধু যে মডেলগুলো এখনো আছে
        $allowedTypes = [\App\Models\BuySellPost::class, \App\Models\TourismBd::class, \App\Models\HistoryBd::class, \App\Models\BasicIslam::class, \App\Models\ExcelTutorial::class, \App\Models\IntroBd::class, \App\Models\AppResource::class, \App\Models\Dowa::class];

        $items = cache()->remember("user_activity_valid_{$this->user->id}", 1800, function () use ($allowedTypes) {
            return $this->user
                ->actions()
                ->where('description', 'created')
                ->whereIn('subject_type', $allowedTypes) // deleted model error আসবে না
                ->with('subject')
                ->latest()
                ->get()
                ->map(function ($activity) {
                    $model = $activity->subject;

                    if (!$model) {
                        return null;
                    }

                    $config = $this->getTypeConfig(get_class($model));
                    $url = $this->getDynamicUrl($model, $config);

                    // শুধু valid URL থাকা আইটেমই রাখব
                    if (!$this->isValidUrl($url)) {
                        return null;
                    }

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

        // Infinite scroll এর জন্য slice
        return $items->take($this->page * $this->perPage);
    }

    private function getTitle($model): string
    {
        return $model->title ?? ($model->name ?? ($model->bangla_name ?? ($model->bn_name ?? ($model->heading ?? ($model->subject ?? 'শিরোনামহীন')))));
    }

    private function getDescription($model): string
    {
        $text = $model->description ?? ($model->body ?? ($model->arabic_text ?? ($bangla_text ?? ($model->content ?? ($model->details ?? ($model->short_description ?? ($model->summary ?? ($model->bn_description ?? ''))))))));

        return Str::limit(strip_tags($text), 80);
    }

    private function getThumbnail($model): ?string
    {
        // Spatie Media Library আছে কিনা চেক
        if (!method_exists($model, 'getFirstMediaUrl') && !method_exists($model, 'getMedia')) {
            return null;
        }

        // প্রতিটা মডেলের জন্য সম্ভাব্য collection নাম
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

        // সম্ভাব্য conversion নাম (খালি = original)
        $conversions = ['thumb', 'preview', 'small', 'medium', 'webp', ''];

        foreach ($collections as $collection) {
            foreach ($conversions as $conversion) {
                try {
                    $url = $conversion === '' ? $model->getFirstMediaUrl($collection) : $model->getFirstMediaUrl($collection, $conversion);

                    if (!empty($url)) {
                        return $url;
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        }

        // লাস্ট ফলব্যাক: যেকোনো মিডিয়া থেকে নাও
        try {
            $media = $model->getMedia()->first();
            if ($media) {
                return $media->getUrl('thumb') ?: $media->getUrl();
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return null;
    }

    public function getHasMoreProperty()
    {
        $allowedTypes = [\App\Models\BuySellPost::class, \App\Models\TourismBd::class, \App\Models\HistoryBd::class, \App\Models\BasicIslam::class, \App\Models\ExcelTutorial::class, \App\Models\IntroBd::class, \App\Models\AppResource::class, \App\Models\Dowa::class];

        $total = cache()->remember("user_activity_valid_count_{$this->user->id}", 1800, function () use ($allowedTypes) {
            return $this->user
                ->actions()
                ->where('description', 'created')
                ->whereIn('subject_type', $allowedTypes)
                ->with('subject')
                ->latest()
                ->get()
                ->filter(function ($activity) {
                    $model = $activity->subject;
                    if (!$model) {
                        return false;
                    }
                    $config = $this->getTypeConfig(get_class($model));
                    $url = $this->getDynamicUrl($model, $config);
                    return $this->isValidUrl($url);
                })
                ->count();
        });

        return $this->page * $this->perPage < $total;
    }

    private function isValidUrl(?string $url): bool
    {
        if (empty($url) || $url === '#' || $url === '/') {
            return false;
        }

        if (str_starts_with($url, '?') || str_starts_with($url, '/?')) {
            return false;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return (bool) filter_var($url, FILTER_VALIDATE_URL);
        }

        return str_starts_with($url, '/');
    }

    private function getTypeConfig(string $class): array
    {
        return match ($class) {
            \App\Models\BuySellPost::class => [
                'label' => 'ক্রয়/বিক্রয়',
                'icon' => 'shopping-bag',
                'route' => 'buysell.buysell-single',
                'param' => 'slug',
                'needs_param' => true,
            ],
            \App\Models\TourismBd::class => [
                'label' => 'পর্যটন কেন্দ্র',
                'icon' => 'camera',
                'route' => 'bangladesh.tourism.show',
                'param' => 'slug',
                'needs_param' => true,
            ],
            \App\Models\HistoryBd::class => [
                'label' => 'ইতিহাস',
                'icon' => 'book-open',
                'route' => 'bangladesh.history.show',
                'param' => 'slug',
                'needs_param' => true,
            ],
            \App\Models\BasicIslam::class => [
                'label' => 'ইসলামিক জ্ঞান',
                'icon' => 'moon',
                'route' => 'islam.basicislam.show',
                'param' => 'slug',
                'needs_param' => true,
            ],
            \App\Models\ExcelTutorial::class => [
                'label' => 'এক্সেল টিউটোরিয়াল',
                'icon' => 'table-cells',
                'route' => 'excel.view',
                'param' => 'slug',
                'needs_param' => true,
            ],
            \App\Models\IntroBd::class => [
                'label' => 'বাংলাদেশ পরিচিতি',
                'icon' => 'globe-alt',
                'route' => 'bangladesh.introduction.show',
                'param' => 'slug',
                'needs_param' => true,
            ],
            \App\Models\AppResource::class => [
                'label' => 'অ্যাপ সম্পর্কিত সম্পদ',
                'icon' => 'globe-alt',
                'route' => 'software.show',
                'param' => 'slug',
                'needs_param' => true,
            ],
            \App\Models\Dowa::class => [
                'label' => 'দোয়া',
                'icon' => 'book-open',
                'route' => 'islam.dowan.show',
                'param' => 'slug',
                'needs_param' => true,
            ],
            default => [
                'label' => 'কন্টেন্ট',
                'icon' => 'document-text',
                'route' => null,
                'param' => null,
                'needs_param' => false,
            ],
        };
    }

    private function getDynamicUrl($model, array $config): ?string
    {
        if (method_exists($model, 'url')) {
            try {
                $url = $model->url();
                return $this->isValidUrl($url) ? $url : null;
            } catch (\Throwable $e) {
            }
        }

        if (empty($config['route'])) {
            return null;
        }

        try {
            if (!$config['needs_param']) {
                $url = route($config['route']);
                return $this->isValidUrl($url) ? $url : null;
            }

            $paramValue = $model->slug ?? ($model->id ?? null);
            if (empty($paramValue)) {
                return null;
            }

            $url = route($config['route'], [$config['param'] => $paramValue]);
            return $this->isValidUrl($url) ? $url : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
};
?>

<main class="max-w-2xl mx-auto p-4 space-y-6">

    <x-seo title="{{ $user->name }} - প্রোফাইল"
        description="{{ $user->bio ? Str::limit(strip_tags($user->bio), 150) : $user->name . '-এর প্রোফাইল তথ্য এবং প্রকাশিত কন্টেন্ট।' }}"
        canonical="{{ route('users.show', $user->slug) }}"
        image="{{ $user->getFirstMediaUrl('avatars') ?: asset('images/default-avatar.png') }}"
        keywords="{{ $user->name }}, প্রোফাইল, তথ্যবক্স, {{ $user->profession ?? '' }}">
        <meta property="og:type" content="profile" />
        <meta property="og:title" content="{{ $user->name }} - প্রোফাইল" />
        <meta property="og:description"
            content="{{ $user->bio ? Str::limit(strip_tags($user->bio), 150) : 'Totthobox-এ ' . $user->name . '-এর প্রোফাইল।' }}" />
        <meta property="og:url" content="{{ route('users.show', $user->slug) }}" />
        <meta property="og:image"
            content="{{ $user->getFirstMediaUrl('avatars') ?: asset('images/default-avatar.png') }}" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="{{ $user->name }} - প্রোফাইল" />
        <meta name="twitter:description"
            content="{{ $user->bio ? Str::limit(strip_tags($user->bio), 150) : 'Totthobox-এ ' . $user->name . '-এর প্রোফাইল।' }}" />
        <meta name="twitter:image"
            content="{{ $user->getFirstMediaUrl('avatars') ?: asset('images/default-avatar.png') }}" />
    </x-seo>

    {{-- Profile Header --}}
    <header class="overflow-hidden">
        <div class="flex flex-col md:flex-row gap-8 items-start">
            <div class="relative">
                <flux:avatar name="{{ $user->name }}" badge badge:color="{{ $user->isOnline() ? 'green' : 'zinc' }}"
                    src="{{ $user->getFirstMediaUrl('avatars') }}" class="size-32 md:size-40 text-4xl"
                    alt="{{ $user->name }}-এর প্রোফাইল ছবি" />
            </div>

            <div class="flex-1 space-y-4">
                <div>
                    <div class="flex items-center gap-4 flex-wrap">
                        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                            {{ $user->name }}
                        </h1>
                        @if ($user->hasRole(['admin', 'super admin']))
                            <flux:badge color="teal" size="sm" inset="top bottom">ভেরিফাইড এডমিন</flux:badge>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap gap-4 text-sm text-zinc-500">
                    @if ($user->location)
                        <div class="flex items-center gap-4">
                            <flux:icon.map-pin variant="mini" aria-hidden="true" />
                            <span>{{ $user->location }}</span>
                        </div>
                    @endif
                    <div class="flex items-center gap-4">
                        <flux:icon.briefcase variant="mini" aria-hidden="true" />
                        <span>
                            {{ $user->getRoleNames()->first() === 'Student' ? 'শিক্ষার্থী' : ($user->getRoleNames()->first() === 'Admin' ? 'এডমিন' : $user->profession ?? 'ব্যবহারকারী') }}
                        </span>
                    </div>
                </div>

                <div class="flex gap-4">
                    <flux:button size="sm" icon="chat-bubble-left-right" variant="filled"
                        href="{{ route('messages', $user->slug) }}">
                        মেসেজ পাঠান
                    </flux:button>
                    <flux:button size="sm" icon="share" variant="ghost" data-share-button
                        data-url="{{ route('users.show', $user->slug) }}">
                        প্রোফাইল শেয়ার
                    </flux:button>
                </div>
            </div>
        </div>
    </header>

    {{-- About & Contact --}}
    <section class="mt-8 space-y-8">
        @if ($user->bio)
            <div>
                <h2 class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
                    {{ $user->name }} সম্পর্কে
                </h2>
                <div class="mt-2 leading-relaxed text-sm text-zinc-600 dark:text-zinc-300 ql-text-format">
                    {!! $user->bio !!}
                </div>
            </div>
        @endif

        <flux:card variant="subtle" class="space-y-4">
            <h2 class="text-base font-semibold text-zinc-800 dark:text-zinc-200">যোগাযোগের তথ্য</h2>
            <div class="space-y-4">
                @if ($user->thana || $user->district || $user->address)
                    <div>
                        <span class="block font-medium text-zinc-800 dark:text-zinc-200 text-sm">ঠিকানা</span>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            {{ collect([$user->address, $user->thana?->name, $user->district?->name])->filter()->join(', ') }}
                        </p>
                    </div>
                @endif

                @if ($user->education)
                    <div>
                        <span class="block font-medium text-zinc-800 dark:text-zinc-200 text-sm">শিক্ষা</span>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $user->education }}</p>
                        @if ($user->is_student && $user->classLevel)
                            <p class="text-xs text-zinc-500 mt-1">শ্রেণী: {{ $user->classLevel->name }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </flux:card>
    </section>

    <flux:separator class="my-8" />

    {{-- Published Content + Infinite Scroll --}}
    <section class="space-y-8" id="published-content">
        <div class="flex items-center gap-4">
            <div class="p-2 bg-teal-50 dark:bg-teal-500/10 rounded-lg">
                <flux:icon.square-3-stack-3d class="size-6 text-teal-600 dark:text-teal-400" />
            </div>
            <h2 class="text-xl font-bold text-zinc-800 dark:text-zinc-200">
                প্রকাশিত কন্টেন্ট সমূহ
            </h2>
        </div>

        <div class="space-y-6">
            @forelse ($this->allData as $data)
                <article class="p-5 rounded-2xl border border-zinc-400/10 bg-zinc-400/10  transition-all space-y-3.5">
                    <div class="flex gap-4 items-start">
                        {{-- Image --}}
                        @if (!empty($data['thumbnail']))
                            <div class="">
                                <flux:avatar src="{{ $data['thumbnail'] }}" alt="{{ $data['title'] }}" />
                            </div>
                        @endif
                        <div>
                            <div class="flex justify-between items-start gap-4">
                                <flux:badge color="teal" size="sm" variant="subtle">{{ $data['type_label'] }}
                                </flux:badge>
                                <time class="text-xs text-zinc-500 font-medium shrink-0">
                                    {{ $data['created_at']->diffForHumans() }}
                                </time>
                            </div>

                            <div class="space-y-1">
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 truncate">
                                    {{ $data['title'] }}
                                </h3>
                                <p class="line-clamp-2 text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $data['description'] ?: 'কোনো সংক্ষিপ্ত বিবরণ দেওয়া নেই।' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-between border-t border-zinc-400/25">
                        <flux:link href="{{ $data['url'] }}" icon-trailing="arrow-right" class="text-sm"
                            variant="subtle">
                            বিস্তারিত দেখুন
                        </flux:link>
                    </div>

                </article>
            @empty
                <div
                    class="col-span-full py-12 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-2xl">
                    <flux:icon.document-magnifying-glass
                        class="mx-auto size-12 text-zinc-300 dark:text-zinc-700 mb-4" />
                    <h3 class="text-base font-medium text-zinc-500">কোনো তথ্য পাওয়া যায়নি</h3>
                    <p class="text-sm text-zinc-400 mt-1">এখনো কোনো কন্টেন্ট প্রকাশ করা হয়নি।</p>
                </div>
            @endforelse
        </div>

        {{-- Infinite Scroll Trigger --}}
        @if ($this->hasMore)
            <div x-data x-intersect.margin.200px="$wire.loadMore()" class="flex justify-center py-8">
                <div class="flex items-center gap-4">
                    <flux:icon.loading />
                </div>
            </div>
        @endif
    </section>

    <section class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 p-5 space-y-3">
        <h2 class="text-lg font-bold text-zinc-800 dark:text-zinc-200">
            {{ $user->name }}-এর প্রোফাইল সম্পর্কে
        </h2>
        <div class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-300 space-y-3">
            <p>
                এটি <strong>{{ $user->name }}</strong>-এর Totthobox প্রোফাইল।
                এখানে ব্যবহারকারীর পরিচিতি এবং প্রকাশিত কন্টেন্ট দেখা যায়।
            </p>
            <p>
                প্রকাশিত পোস্ট, টিউটোরিয়াল বা অন্যান্য তথ্য “বিস্তারিত দেখুন” থেকে খোলা যায়।
            </p>
        </div>
    </section>

</main>
