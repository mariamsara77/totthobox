<?php

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app.header')] class extends Component
{
    use WithPagination;

    public User $user;

    public function mount($slug): void
    {
        $this->user = User::where('slug', $slug)->firstOrFail();
    }

    /**
     * Get combined latest content from all user relations.
     * Results are cached for 5 minutes to reduce database load.
     */
    public function getAllDataProperty(): Collection
    {
        $cacheKey = 'user_content_'.$this->user->id;

        return Cache::remember($cacheKey, now()->addMinutes(0), function () {
            $combined = collect();

            // Define all relations that should be considered as "content"
            $relations = [
                'buySellPosts',
                'contactNumbers',  'quran', 'sura',
                'dowa', 'para', 'basicIslam', 'basicHealth',
                'foodDescribes',
                'excelTutorials', 'historyB',
                'tourismBd', 'establishmentBd', 'hospitals', 'ministers', 'signs',
                'introBd', 'holidays',
            ];

            foreach ($relations as $relation) {
                if (! method_exists($this->user, $relation)) {
                    continue;
                }

                try {
                    // Get latest 2 items from this relation
                    $items = $this->user->$relation()->latest()->take(2)->get();

                    foreach ($items as $item) {
                        $combined->push([
                            'model_type' => Str::headline($relation),
                            'title' => $this->extractTitle($item, $relation),
                            'created_at' => $item->created_at ?? now(),
                            'url' => $this->generateUrl($item, $relation),
                        ]);
                    }
                } catch (\Exception $e) {
                    // Log or skip silently – we don't want to break the page
                    continue;
                }
            }

            return $combined
                ->sortByDesc('created_at')
                ->take(20);
        });
    }

    /**
     * Intelligently extract a title from the model based on common field names.
     */
    private function extractTitle($model, string $relation): string
    {
        $possibleFields = [
            'title',
            'name',
            'subject_name',
            'question',
            'content',
            'description',
            'body',
            'caption',
            'headline',
            'label',
        ];

        foreach ($possibleFields as $field) {
            if (isset($model->$field) && ! empty(trim($model->$field))) {
                return Str::limit(strip_tags($model->$field), 60);
            }
        }

        // Fallback: model class name with ID
        return class_basename($model).' #'.$model->getKey();
    }

    /**
     * Generate a URL for the content item.
     * Customize this method according to your application's routes.
     * For now, it returns '#' but can be extended later.
     */
    private function generateUrl($model, string $relation): string
    {
        // Example: you could map relation names to route names
        // switch ($relation) {
        //     case 'buySellItems':
        //         return route('buy-sell.show', $model);
        //     case 'tests':
        //         return route('tests.show', $model);
        //     default:
        //         return '#';
        // }

        // Placeholder – replace with actual logic when routes are defined
        return '#';
    }
};

?>

<section class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Profile Header Card --}}
    <div
        class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-800 overflow-hidden mb-8">
        <div class="p-6 md:p-8">
            <div class="flex flex-col md:flex-row gap-8 items-start">
                {{-- Avatar with online status --}}
                <div class="relative shrink-0">
                    <flux:avatar name="{{ $user->name }}" badge badge:color="{{ $user->isOnline() ? 'green' : 'zinc' }}"
                        src="{{ $user->getFirstMediaUrl('avatars', 'thumb') }}"
                        class="size-32 md:size-40 text-4xl ring-4 ring-white dark:ring-zinc-800" />
                </div>

                {{-- User Info --}}
                <div class="flex-1 space-y-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <flux:heading size="xl" level="1" class="font-bold">{{ $user->name }}</flux:heading>
                        @if($user->hasRole(['admin', 'super admin']))
                        <flux:badge color="teal" size="sm" inset="top bottom" class="uppercase tracking-wide">Verified
                            Admin</flux:badge>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-4 text-sm text-zinc-600 dark:text-zinc-400">
                        @if($user->location)
                        <div class="flex items-center gap-1.5">
                            <flux:icon.map-pin variant="mini" class="size-4" />
                            <span>{{ $user->location }}</span>
                        </div>
                        @endif

                        <div class="flex items-center gap-1.5">
                            <flux:icon.briefcase variant="mini" class="size-4" />
                            <span>
                                @php
                                $role = $user->getRoleNames()->first();
                                @endphp
                                @switch($role)
                                @case('Student') শিক্ষার্থী @break
                                @case('Admin') এডমিন @break
                                @default {{ $user->profession ?? 'ব্যবহারকারী' }}
                                @endswitch
                            </span>
                        </div>

                        @if($user->created_at)
                        <div class="flex items-center gap-1.5">
                            <flux:icon.calendar variant="mini" class="size-4" />
                            <span>যোগদান: {{ $user->created_at->format('M Y') }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="flex gap-3 pt-2">
                        <flux:button size="sm" icon="chat-bubble-left-right" variant="filled"
                            href="{{ route('messages', $user->slug) }}">
                            মেসেজ পাঠান
                        </flux:button>
                        <flux:button size="sm" icon="share" variant="ghost" data-share-button
                            data-url="{{ route('users.show', $user->slug) }}">
                            প্রোফাইল শেয়ার
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Bio and Details --}}
        <div class="lg:col-span-2 space-y-8">
            {{-- About Section --}}
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6">
                <flux:heading size="lg" class="mb-4 font-semibold">{{ $user->name }} সম্পর্কে</flux:heading>
                <div class="prose prose-zinc dark:prose-invert max-w-none ql-text-format">
                    {!! $user->bio ?? '<p class="text-zinc-500 dark:text-zinc-400">এখনও কোনো তথ্য যোগ করা হয়নি।</p>' !!}
                </div>
            </div>

            {{-- Detailed Description --}}
            @if($user->description)
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6">
                <flux:heading size="lg" class="mb-4 font-semibold">বিস্তারিত বিবরণ</flux:heading>
                <div class="prose prose-zinc dark:prose-invert max-w-none">
                    {!! nl2br(e($user->description)) !!}
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column: Contact Info --}}
        <div class="space-y-6">
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6">
                <flux:heading size="md" class="mb-4 font-semibold flex items-center gap-2">
                    <flux:icon.identification class="size-5" />
                    যোগাযোগের তথ্য
                </flux:heading>

                <div class="space-y-4">
                    <div>
                        <span
                            class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">ইমেইল</span>
                        <span class="text-sm text-zinc-800 dark:text-zinc-200 break-all">{{ $user->email }}</span>
                    </div>

                    @if($user->thana || $user->district)
                    <div>
                        <span
                            class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">ঠিকানা</span>
                        <span class="text-sm text-zinc-800 dark:text-zinc-200">
                            {{ $user->address ? $user->address . ', ' : '' }}
                            {{ $user->thana?->name }}{{ $user->thana ? ', ' : '' }}
                            {{ $user->district?->name }}
                        </span>
                    </div>
                    @endif

                    @if($user->education)
                    <div>
                        <span
                            class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">শিক্ষা</span>
                        <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ $user->education }}</span>
                        @if($user->is_student && $user->classLevel)
                        <span class="block text-xs text-zinc-500 mt-1">শ্রেণী: {{ $user->classLevel->name }}</span>
                        @endif
                    </div>
                    @endif

                    @if($user->phone)
                    <div>
                        <span
                            class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wide">ফোন</span>
                        <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ $user->phone }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Optional: Stats Card --}}
            @if(method_exists($user, 'getStatsAttribute'))
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6">
                <flux:heading size="md" class="mb-4 font-semibold flex items-center gap-2">
                    <flux:icon.chart-bar class="size-5" />
                    পরিসংখ্যান
                </flux:heading>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-teal-600">{{ $user->posts_count ?? 0 }}</div>
                        <div class="text-xs text-zinc-500">পোস্ট</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-teal-600">{{ $user->comments_count ?? 0 }}</div>
                        <div class="text-xs text-zinc-500">মন্তব্য</div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- User Content Section --}}
    <div class="mt-12">
        <div class="flex items-center justify-between mb-6">
            <flux:heading size="xl" class="font-bold">সকল প্রকাশিত কন্টেন্ট</flux:heading>
            <flux:text size="sm" class="text-zinc-500">সর্বশেষ ২০টি আইটেম</flux:text>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($this->allData as $data)
            <a href="{{ $data['url'] }}" class="group block">
                <div
                    class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-5 transition-all duration-200 hover:shadow-md hover:border-teal-500 dark:hover:border-teal-600">
                    <div class="flex items-start justify-between mb-3">
                        <flux:badge size="sm" color="teal" class="uppercase tracking-wider text-xs">
                            {{ $data['model_type'] }}
                        </flux:badge>
                        <flux:text size="xs" class="text-zinc-400">
                            {{ $data['created_at']->diffForHumans() }}
                        </flux:text>
                    </div>
                    <flux:heading size="base"
                        class="font-medium line-clamp-2 group-hover:text-teal-600 transition-colors">
                        {{ $data['title'] }}
                    </flux:heading>
                    <div class="mt-4 flex items-center text-sm text-teal-600 group-hover:gap-1 transition-all">
                        <span>দেখুন</span>
                        <flux:icon.arrow-long-right variant="mini" class="size-4" />
                    </div>
                </div>
            </a>
            @empty
            <div class="col-span-full text-center py-12">
                <flux:icon.document-text class="size-12 mx-auto text-zinc-300 dark:text-zinc-700 mb-4" />
                <flux:heading size="lg" class="text-zinc-400 dark:text-zinc-500">কোনো কন্টেন্ট খুঁজে পাওয়া যায়নি
                </flux:heading>
                <flux:text class="text-zinc-400 dark:text-zinc-500 mt-2">এই ব্যবহারকারী এখনো কোনো কন্টেন্ট প্রকাশ
                    করেননি।</flux:text>
            </div>
            @endforelse
        </div>
    </div>
</section>