<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Str;

new #[Layout('components.layouts.app.header')] class extends Component {
    use WithPagination;

    public $user;

    public function mount($slug)
    {
        // ডাইনামিক্যালি রিলেশন চেক করার চেয়ে ইম্পর্ট্যান্ট রিলেশনগুলো লোড করা ভালো
        $this->user = User::where('slug', $slug)->firstOrFail();
    }

    public function getAllDataProperty()
    {
        return cache()->remember("user_content_{$this->user->id}", 36, function () {
            $combined = collect();

            $contentTypes = [
                'buySellPosts' => [
                    'label' => 'ক্রয়/বিক্রয়',
                    'icon' => 'shopping-bag',
                    'url_type' => 'method',
                    'key' => 'url',
                ],
                'introbds' => [
                    'label' => 'বাংলাদেশের পরিচিতি ও তথ্য',
                    'icon' => 'globe-alt',
                    'url_type' => 'route',
                    'key' => 'bangladesh.introduction',
                ],
                'tourismBd' => [
                    'label' => 'বাংলাদেশের সকল পর্যটন কেন্দ্র',
                    'icon' => 'camera',
                    'url_type' => 'route',
                    'key' => 'bangladesh.tourism.show',
                    'param' => 'slug',
                ],
                'historyBd' => [
                    'label' => 'বাংলাদেশের সমৃদ্ধ ইতিহাসের',
                    'icon' => 'book-open',
                    'url_type' => 'route',
                    'key' => 'bangladesh.history.show',
                    'param' => 'slug',
                ],
                'basicIslam' => [
                    'label' => 'ইসলামের মৌলিক জ্ঞান',
                    'icon' => 'book-open',
                    'url_type' => 'route',
                    'key' => 'islam.basicislam',
                ],
            ];

            foreach ($contentTypes as $relation => $config) {
                if (method_exists($this->user, $relation)) {
                    $items = $this->user->$relation()->latest()->take(5)->get();

                    foreach ($items as $item) {
                        $url = '#';

                        if ($config['url_type'] === 'route') {
                            // যদি প্যারামিটার থাকে (যেমন স্লাগ), তবে তা পাস করুন
                            $params = isset($config['param']) ? [$item->{$config['param']}] : [];
                            $url = route($config['key'], $params);
                        } elseif (method_exists($item, $config['key'])) {
                            $url = $item->{$config['key']}();
                        }

                        $combined->push([
                            'type_label' => $config['label'],
                            'icon' => $config['icon'],
                            'title' => $item->title ?? ($item->name ?? 'শিরোনামহীন'),
                            'description' => Str::limit(strip_tags($item->description ?? ($item->body ?? '')), 80),
                            'created_at' => $item->created_at ?? now(),
                            'url' => $url,
                        ]);
                    }
                }
            }
            return $combined->sortByDesc('created_at')->take(12);
        });
    }

    // টাইপ অনুযায়ী আইকন সেট করার জন্য
    private function getIconForType($type)
    {
        return match ($type) {
            'buySellPosts' => 'shopping-bag',
            'testQuestions' => 'academic-cap',
            'excelTutorials' => 'document-text',
            default => 'view-columns',
        };
    }
};
?>



<section class="max-w-2xl mx-auto p-4">
    <div class="overflow-hidden">
        {{-- প্রোফাইল হেডার সেকশন --}}
        <div class="flex flex-col md:flex-row gap-8 items-start">
            <div class="relative">
                <flux:avatar name="{{ $user->name }}" badge badge:color="{{ $user->isOnline() ? 'green' : 'zinc' }}"
                    src="{{ $user->getFirstMediaUrl('avatars', 'thumb') }}" class="size-32 md:size-40 text-4xl" />
            </div>

            <div class="flex-1 space-y-4">
                <div>
                    <div class="flex items-center gap-3">
                        <flux:heading size="xl" level="1">{{ $user->name }}</flux:heading>
                        @if ($user->hasRole(['admin', 'super admin']))
                            <flux:badge color="teal" size="sm" inset="top bottom">ভেরিফাইড এডমিন</flux:badge>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap gap-4 text-sm text-zinc-500">
                    @if ($user->location)
                        <div class="flex items-center gap-2">
                            <flux:icon.map-pin variant="mini" />
                            {{ $user->location }}
                        </div>
                    @endif

                    <div class="flex items-center gap-2">
                        <flux:icon.briefcase variant="mini" />
                        <span>{{ $user->getRoleNames()->first() === 'Student' ? 'শিক্ষার্থী' : ($user->getRoleNames()->first() === 'Admin' ? 'এডমিন' : $user->profession ?? 'ব্যবহারকারী') }}</span>
                    </div>
                </div>

                <div class="flex gap-3">
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

        {{-- বিস্তারিত তথ্য --}}
        <div class="mt-8 space-y-8">
            @if ($user->bio)
                <div>
                    <flux:heading level="2" size="lg">{{ $user->name }} সম্পর্কে</flux:heading>
                    <flux:text class="mt-2 leading-relaxed ql-text-format">
                        {!! $user->bio !!}
                    </flux:text>
                </div>
            @endif

            @if ($user->description)
                <div>
                    <flux:heading level="2" size="lg">বিস্তারিত বিবরণ</flux:heading>
                    <flux:text class="mt-2 prose dark:prose-invert">
                        {!! nl2br(e($user->description)) !!}
                    </flux:text>
                </div>
            @endif

            <flux:card variant="subtle" class="space-y-4">
                <flux:heading size="md">যোগাযোগের তথ্য</flux:heading>

                <div class="space-y-4">
                    <flux:description>
                        <span class="block font-medium text-zinc-800 dark:text-zinc-200">ইমেইল</span>
                        {{ $user->email }}
                    </flux:description>

                    @if ($user->thana || $user->district || $user->address)
                        <flux:description>
                            <span class="block font-medium text-zinc-800 dark:text-zinc-200">ঠিকানা</span>
                            {{ collect([$user->address, $user->thana?->name, $user->district?->name])->filter()->join(', ') }}
                        </flux:description>
                    @endif

                    @if ($user->education)
                        <flux:description>
                            <span class="block font-medium text-zinc-800 dark:text-zinc-200">শিক্ষা</span>
                            {{ $user->education }}
                            @if ($user->is_student && $user->classLevel)
                                <div class="text-xs text-zinc-500 mt-1">শ্রেণী: {{ $user->classLevel->name }}</div>
                            @endif
                        </flux:description>
                    @endif
                </div>
            </flux:card>
        </div>
    </div>

    <flux:separator class="my-8" />

    <div class="space-y-8">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-teal-50 dark:bg-teal-500/10 rounded-lg">
                <flux:icon.square-3-stack-3d class="size-6 text-teal-600 dark:text-teal-400" />
            </div>
            <flux:heading size="xl">প্রকাশিত কন্টেন্ট সমূহ</flux:heading>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @forelse($this->allData as $data)
                <flux:card class="flex flex-col gap-4">
                    <div class="flex justify-between items-start gap-4">
                        <flux:badge color="teal" size="sm" variant="subtle">{{ $data['type_label'] }}
                        </flux:badge>
                        <time
                            class="text-xs text-zinc-500 font-medium shrink-0">{{ $data['created_at']->diffForHumans() }}</time>
                    </div>

                    <div class="space-y-1">
                        <flux:heading size="lg" class="truncate">{{ $data['title'] }}</flux:heading>
                        <flux:text class="line-clamp-2 text-zinc-600 dark:text-zinc-400">
                            {{ $data['description'] ?: 'কোনো সংক্ষিপ্ত বিবরণ দেওয়া নেই।' }}
                        </flux:text>
                    </div>

                    <div class="mt-auto pt-2">
                        <flux:link href="{{ $data['url'] }}" icon-trailing="arrow-right" variant="primary">
                            বিস্তারিত দেখুন
                        </flux:link>
                    </div>
                </flux:card>
            @empty
                <div
                    class="col-span-full py-16 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-2xl">
                    <flux:icon.document-magnifying-glass
                        class="mx-auto size-12 text-zinc-300 dark:text-zinc-700 mb-4" />
                    <flux:heading size="md" class="text-zinc-500">কোনো তথ্য পাওয়া যায়নি</flux:heading>
                    <flux:text>এখনো কোনো কন্টেন্ট প্রকাশ করা হয়নি।</flux:text>
                </div>
            @endforelse
        </div>
    </div>
</section>
