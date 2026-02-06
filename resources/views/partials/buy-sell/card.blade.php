@props(['post'])

@php
    $conditionColors = [
        'new' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'like_new' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'used_good' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'used_fair' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        'refurbished' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    ];

    $conditionLabels = [
        'new' => 'ব্র্যান্ড নিউ',
        'like_new' => 'নতুনের মত',
        'used_good' => 'ব্যবহৃত - ভাল',
        'used_fair' => 'ব্যবহৃত - মোটামুটি',
        'refurbished' => 'রিফার্বিশড',
    ];

     $hasImages = $post->images_count > 0 || $post->getMedia('posts')->count() > 0;
@endphp

<div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 overflow-hidden transition-shadow duration-300">
    <!-- Image Section -->
    <div class="relative" data-viewer-gallery="post">
          @if ($hasImages)
          @php
              $imageUrl = $post->getPrimaryImageUrl('thumb');
          @endphp
          <img src="{{ $imageUrl }}" 
               alt="{{ $post->title }}" 
               class="w-full viewer-image h-48 object-cover" 
               loading="lazy"
               onerror="this.src='{{ asset('images/placeholder.png') }}'; this.onerror=null;">
      @else
            <div class="w-full h-48 bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center">
                <div class="text-center text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-sm font-medium">ছবি যোগ করা হয়নি</span>
                </div>
            </div>
        @endif

        <!-- Condition Badge -->
        <div class="absolute top-3 left-3">
            <span
                class="px-2 py-1 text-xs font-semibold rounded-full {{ $conditionColors[$post->condition] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                {{ $conditionLabels[$post->condition] ?? 'অজানা অবস্থা' }}
            </span>
        </div>

        <!-- Stock Badge -->
        @if ($post->stock > 0)
            <div class="absolute top-3 right-3">
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-black/70 text-white">
                    {{ bn_num($post->stock) }} টি
                </span>
            </div>
        @endif
    </div>

    <!-- Content Section -->
    <div class="p-4">
        <!-- Title -->
        <h3 class="font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2 leading-tight">
            {{ $post->title }}
        </h3>

        <!-- Price -->
        <div class="flex items-center gap-2 mb-3">
            @if ($post->discount_price)
                <span class="text-xl font-bold text-red-600">
                    ৳{{ number_format($post->discount_price) }}
                </span>
                <span class="text-sm text-gray-500 line-through">
                    ৳{{ number_format($post->price) }}
                </span>
            @else
                <span class="text-xl font-bold text-gray-900 dark:text-white">
                    ৳{{ number_format($post->price) }}
                </span>
            @endif

            @if ($post->is_negotiable)
                <span
                    class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full">
                    আলোচনা সাপেক্ষ
                </span>
            @endif
        </div>

        <!-- Location & Date -->
        <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400 mb-4">
            <div class="flex items-center gap-1">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span
                    class="truncate">{{ $post->thana?->name ?? ($post->district?->name ?? $post->division?->name) }}</span>
            </div>
            <span>{{ bn_diff_for_humans($post->created_at) }}</span>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-center gap-2 relative">

            <flux:button href="{{ route('buysell.buysell-single', $post->slug) }}" class="" size="sm"
                wire:navigate.hover>
                বিস্তারিত
            </flux:button>

            {{-- <flux:tooltip content="হোয়াটস্যাপ">
                @php
                    $phone = preg_replace('/[^0-9]/', '', $post->whatsapp); // clean the number
                    $message = urlencode(
                        'হ্যালো, আমি আপনার পণ্যটি সম্পর্কে জানতে চাই: ' . route('buysell.buysell-single', $post->slug),
                    );
                    $whatsappLink = "https://wa.me/{$phone}?text={$message}";
                @endphp
                <flux:button href="{{ $whatsappLink }}" target="_blank" icon="whatsapp" size="sm"></flux:button>
            </flux:tooltip> --}}

            <flux:tooltip content="হোয়াটস্যাপে মেসেজ করুন">
                @php
                    // Simple implementation without use statement
                    $phone = preg_replace('/[^0-9]/', '', $post->whatsapp);
                    $phone = ltrim($phone, '0');

                    // Add country code if missing (Bangladesh as default)
                    $countryCodes = ['880', '1', '91', '44', '971', '966'];
                    $hasCountryCode = false;

                    foreach ($countryCodes as $code) {
                        if (str_starts_with($phone, $code)) {
                            $hasCountryCode = true;
                            break;
                        }
                    }

                    if (!$hasCountryCode && !empty($phone)) {
                        $phone = '880' . $phone;
                    }

                    // Validate number
                    $isValid = strlen($phone) >= 10 && strlen($phone) <= 15;

                    // Create message
                    $message = "হ্যালো, \n\nআমি আপনার পণ্যটি সম্পর্কে জানতে চাই:\n";
                    $message .= "পণ্য: {$post->title}\n";

                    if ($post->price) {
                        $message .= "মূল্য: {$post->price}\n";
                    }

                    $message .= "\nলিংক: " . route('buysell.buysell-single', $post->slug);

                    $encodedMessage = urlencode($message);
                    $whatsappLink = $isValid ? "https://wa.me/{$phone}?text={$encodedMessage}" : '#';
                @endphp

                @if ($isValid && !empty($phone))
                    <flux:button href="{{ $whatsappLink }}" target="_blank" icon="whatsapp" size="sm"
                        class="bg-green-600 hover:bg-green-700 text-white">
                        WhatsApp
                    </flux:button>
                @else
                    <flux:button href="#" icon="whatsapp" size="sm" disabled="true"
                        class="bg-gray-400 cursor-not-allowed" title="Invalid WhatsApp number">
                        WhatsApp
                    </flux:button>
                @endif
            </flux:tooltip>

            <script>
                function trackWhatsAppClick(postId) {
                    // Analytics tracking
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'whatsapp_click', {
                            'event_category': 'engagement',
                            'event_label': postId
                        });
                    }
                }
            </script>

            <flux:tooltip content="ম্যাসেজ করুন">
                <flux:button wire:click="showContact({{ $post->id }})" icon="massage" size="sm">

                </flux:button>
            </flux:tooltip>
            @include('partials.buy-sell.message-dropdown')
            <flux:tooltip content="কল করুন">
                <flux:button onclick="window.open('tel:{{ $post->phone }}')" icon="phone" size="sm">

                </flux:button>
            </flux:tooltip>
        </div>
    </div>
</div>
