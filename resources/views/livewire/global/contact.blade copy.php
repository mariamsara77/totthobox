<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;
use App\Models\Contact;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithFileUploads;

    // Form fields
    public $name;
    public $email;
    public $phone;
    public $subject;
    public $message;
    public $priority = 'normal';
    public $category = 'general';
    public $attachments = [];

    // UI state
    public $activeTab = 'contact';
    public $lang = 'bn';
    public $copySuccess = false;
    public $showFaq = false;
    public $searchQuery = '';

    // Messages
    public $successMessage = null;
    public $errorMessage = null;

    // Contact info
    public $supportEmail = 'support@totthobox.com';
    public $supportPhone = '+880 1845-394603';
    public $supportPhone2 = '+880 1712-345678';
    public $address = 'মিরপুর-১৪, ঢাকা-১২১৬, বাংলাদেশ';
    public $officeHours = 'শনি-বৃহস্পতি: সকাল ৯টা - সন্ধ্যা ৬টা';
    public $emergencyHours = 'জরুরি সাপোর্ট: ২৪/৭';

    // Social links
    public $socialLinks = [
        'facebook' => 'https://facebook.com/totthobox',
        'twitter' => 'https://twitter.com/totthobox',
        'linkedin' => 'https://linkedin.com/company/totthobox',
        'youtube' => 'https://youtube.com/@totthobox',
        'whatsapp' => 'https://wa.me/+8801845394603',
        'messenger' => 'https://m.me/totthobox',
        'telegram' => 'https://t.me/totthobox'
    ];

    // FAQ data
    public $faqs = [
        'bn' => [
            ['q' => 'কিভাবে অ্যাকাউন্ট তৈরি করব?', 'a' => 'হোম পেজে গিয়ে "রেজিস্ট্রেশন" বাটনে ক্লিক করুন এবং প্রয়োজনীয় তথ্য দিয়ে ফর্ম পূরণ করুন।'],
            ['q' => 'পাসওয়ার্ড ভুলে গেলে করণীয়?', 'a' => 'লগইন পেজে "পাসওয়ার্ড ভুলে গেছেন?" লিঙ্কে ক্লিক করে ইমেইল দিয়ে নতুন পাসওয়ার্ড সেট করুন।'],
            ['q' => 'সাপোর্ট পেতে কত সময় লাগে?', 'a' => 'সাধারণত ২৪ ঘণ্টার মধ্যে উত্তর দেওয়া হয়। জরুরি প্রয়োজনে ফোনে যোগাযোগ করুন।'],
            ['q' => 'বাংলা কীবোর্ড কিভাবে ব্যবহার করব?', 'a' => 'আমাদের প্ল্যাটফর্মে বাংলা টাইপ করতে "বাংলা" বাটনে ক্লিক করে Avro বা Bornona ব্যবহার করুন।']
        ],
        'en' => [
            ['q' => 'How to create an account?', 'a' => 'Click on "Registration" button on homepage and fill the form with required information.'],
            ['q' => 'Forgot password?', 'a' => 'Click "Forgot Password?" link on login page and reset with your email.'],
            ['q' => 'Support response time?', 'a' => 'Usually within 24 hours. For emergencies, please call.'],
            ['q' => 'How to type in Bengali?', 'a' => 'Click "Bengali" button and use Avro or Bornona keyboard.']
        ]
    ];

    protected $rules = [
        'name' => 'required|string|min:2|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:20',
        'subject' => 'required|string|max:255',
        'message' => 'required|string|min:10',
        'priority' => 'required|in:low,normal,high,urgent',
        'category' => 'required|in:general,technical,billing,support,feedback',
        'attachments.*' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120'
    ];

    protected $messages = [
        'name.required' => 'নাম দেওয়া আবশ্যক',
        'email.required' => 'ইমেইল দেওয়া আবশ্যক',
        'email.email' => 'সঠিক ইমেইল ঠিকানা দিন',
        'subject.required' => 'বিষয় লেখা আবশ্যক',
        'message.required' => 'বার্তা লেখা আবশ্যক',
        'message.min' => 'বার্তা অন্তত ১০ অক্ষরের হতে হবে',
        'attachments.*.max' => 'ফাইলের সাইজ ৫এমবি এর কম হতে হবে',
        'attachments.*.mimes' => 'শুধু ছবি, পিডিএফ বা ডকুমেন্ট আপলোড করুন'
    ];

    public function mount()
    {
        // Pre-fill for logged in users
        if (Auth::check()) {
            $user = Auth::user();
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
        }

        // Detect language
        $this->lang = str_starts_with(request()->getPreferredLanguage(), 'bn') ? 'bn' : 'en';
    }

    public function submit()
    {
        $this->validate();

        try {
            // Handle file uploads
            $uploadedFiles = [];
            if ($this->attachments) {
                foreach ($this->attachments as $file) {
                    $uploadedFiles[] = $file->store('contacts/' . date('Y/m'), 'public');
                }
            }

            // Save to database
            $contact = Contact::create([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'subject' => $this->subject,
                'message' => $this->message,
                'priority' => $this->priority,
                'category' => $this->category,
                'user_id' => Auth::id(),
                'visitor_id' => request()->ip(),
                'attachments' => $uploadedFiles,
                'metadata' => [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'referer' => request()->header('referer')
                ]
            ]);

            // Send email
            Mail::to($this->supportEmail)->send(new ContactMail(
                $this->name,
                $this->email,
                $this->message,
                $this->subject,
                $this->phone,
                $this->priority,
                $this->category
            ));

            // Send auto-reply to user
            Mail::to($this->email)->send(new \App\Mail\ContactAutoReply(
                $this->name,
                $contact->id
            ));

            // Success message
            $this->successMessage = $this->lang === 'bn'
                ? '✅ আপনার বার্তা পাঠানো হয়েছে। টিকিট আইডি: #' . $contact->id
                : '✅ Message sent successfully. Ticket ID: #' . $contact->id;

            $this->errorMessage = null;

            // Reset form
            $this->reset(['name', 'email', 'phone', 'subject', 'message', 'attachments']);

            // Log activity
            Log::info('New contact submission', ['ticket_id' => $contact->id, 'email' => $this->email]);

        } catch (\Exception $e) {
            $this->errorMessage = $this->lang === 'bn'
                ? 'বার্তা পাঠাতে সমস্যা হয়েছে। আবার চেষ্টা করুন।'
                : 'Error sending message. Please try again.';

            $this->successMessage = null;

            Log::error('Contact form error: ' . $e->getMessage());
        }
    }

    public function copyToClipboard($text)
    {
        $this->dispatch('copy-to-clipboard', text: $text);
        $this->copySuccess = true;

        // Reset after 2 seconds
        $this->dispatch('reset-copy-success', delay: 2000);
    }

    public function getFilteredFaqsProperty()
    {
        if (empty($this->searchQuery)) {
            return $this->faqs[$this->lang];
        }

        return array_filter($this->faqs[$this->lang], function ($faq) {
            return str_contains(strtolower($faq['q']), strtolower($this->searchQuery)) ||
                str_contains(strtolower($faq['a']), strtolower($this->searchQuery));
        });
    }

};?>

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4 sm:px-6 lg:px-8" x-data="{
        lang: @entangle('lang'),
        activeTab: @entangle('activeTab'),
        showFaq: @entangle('showFaq'),
        copySuccess: false,
        copyToClipboard(text) {
            navigator.clipboard.writeText(text);
            this.copySuccess = true;
            setTimeout(() => this.copySuccess = false, 2000);
        }
     }" x-init="
        $wire.on('copy-to-clipboard', (data) => copyToClipboard(data.text));
        $wire.on('reset-copy-success', (data) => setTimeout(() => copySuccess = false, data.delay));
     " itemscope itemtype="https://schema.org/ContactPage">

    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent mb-4"
                x-text="lang === 'en' ? 'How can we help you?' : 'আমরা কিভাবে সাহায্য করতে পারি?'">
            </h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto"
                x-text="lang === 'en' ? 'We are here to help you 24/7. Choose your preferred way to connect with us.' : 'আমরা ২৪/৭ সাহায্যের জন্য এখানে আছি। আপনার পছন্দের মাধ্যমে যোগাযোগ করুন।'">
            </p>
        </div>

        <!-- Language Toggle & Search -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
            <!-- Breadcrumb -->
            <nav class="text-sm breadcrumbs" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex items-center space-x-2">
                    <li class="flex items-center">
                        <a href="{{ url('/') }}" class="text-gray-500 hover:text-purple-600 transition">
                            <i class="bi bi-house-door-fill"></i>
                            <span class="ml-1" x-text="lang === 'en' ? 'Home' : 'হোম'"></span>
                        </a>
                    </li>
                    <li class="flex items-center">
                        <i class="bi bi-chevron-right text-gray-400 text-sm"></i>
                        <span class="ml-2 text-purple-600 font-medium"
                            x-text="lang === 'en' ? 'Support' : 'সাপোর্ট'"></span>
                    </li>
                </ol>
            </nav>

            <div class="flex items-center gap-3">
                <!-- Search Toggle -->
                <button @click="showFaq = !showFaq"
                    class="px-4 py-2 bg-white rounded-lg shadow-sm hover:shadow-md transition border border-gray-200">
                    <i class="bi bi-search text-gray-600"></i>
                    <span class="ml-2" x-text="lang === 'en' ? 'Search FAQs' : 'প্রায়শই জিজ্ঞাসিত প্রশ্ন'"></span>
                </button>

                <!-- Language Switcher -->
                <div class="flex bg-white rounded-lg shadow-sm border border-gray-200 p-1">
                    <button @click="lang = 'en'"
                        :class="lang === 'en' ? 'bg-purple-600 text-white' : 'text-gray-600 hover:bg-gray-100'"
                        class="px-3 py-1 rounded-md text-sm font-medium transition">
                        EN
                    </button>
                    <button @click="lang = 'bn'"
                        :class="lang === 'bn' ? 'bg-purple-600 text-white' : 'text-gray-600 hover:bg-gray-100'"
                        class="px-3 py-1 rounded-md text-sm font-medium transition">
                        বাংলা
                    </button>
                </div>
            </div>
        </div>

        <!-- FAQ Search Section -->
        <div x-show="showFaq" x-transition class="mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="relative">
                    <i class="bi bi-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" wire:model.live.debounce.300ms="searchQuery"
                        class="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        :placeholder="lang === 'en' ? 'Search FAQs...' : 'প্রশ্ন খুঁজুন...'">
                </div>

                @if($searchQuery)
                    <div class="mt-4 space-y-3 max-h-96 overflow-y-auto">
                        @forelse($this->filteredFaqs as $faq)
                            <div class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                <h4 class="font-medium text-gray-800 mb-2">{{ $faq['q'] }}</h4>
                                <p class="text-gray-600 text-sm">{{ $faq['a'] }}</p>
                            </div>
                        @empty
                            <p class="text-center text-gray-500 py-4"
                                x-text="lang === 'en' ? 'No results found' : 'কিছু পাওয়া যায়নি'">
                            </p>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Contact Info Column -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-xl p-6 space-y-6 sticky top-24">
                    <h2 class="text-2xl font-bold text-gray-800 border-b pb-4"
                        x-text="lang === 'en' ? 'Get in Touch' : 'যোগাযোগ করুন'">
                    </h2>

                    <!-- Quick Contact Cards -->
                    <div class="space-y-4">
                        <!-- Live Chat -->
                        <div @click="activeTab = 'livechat'" @keydown.enter="activeTab = 'livechat'" role="button"
                            tabindex="0"
                            class="flex items-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl hover:shadow-md transition cursor-pointer"
                            :class="activeTab === 'livechat' ? 'ring-2 ring-blue-500' : ''">
                            <div
                                class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center text-white text-xl">
                                <i class="bi bi-chat-dots-fill"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="font-semibold text-gray-800"
                                    x-text="lang === 'en' ? 'Live Chat' : 'লাইভ চ্যাট'"></h3>
                                <p class="text-sm text-gray-600"
                                    x-text="lang === 'en' ? 'Instant response' : 'তাৎক্ষণিক উত্তর'"></p>
                            </div>
                            <i class="bi bi-arrow-right ml-auto text-gray-400"></i>
                        </div>

                        <!-- Email -->
                        <div class="flex items-center p-4 bg-red-50 rounded-xl">
                            <div
                                class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center text-white text-xl">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="font-semibold text-gray-800" x-text="lang === 'en' ? 'Email' : 'ইমেইল'"></h3>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-600">{{ $supportEmail }}</span>
                                    <button @click="copyToClipboard('{{ $supportEmail }}')"
                                        class="text-gray-400 hover:text-gray-600 transition">
                                        <i class="bi"
                                            :class="copySuccess ? 'bi-check-lg text-green-500' : 'bi-files'"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="flex items-center p-4 bg-green-50 rounded-xl">
                            <div
                                class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center text-white text-xl">
                                <i class="bi bi-telephone-fill"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="font-semibold text-gray-800" x-text="lang === 'en' ? 'Phone' : 'ফোন'"></h3>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-600">{{ $supportPhone }}</span>
                                    <a :href="'tel:' + '{{ $supportPhone }}'.replace(/\s+/g, '')"
                                        class="text-green-500 hover:text-green-600">
                                        <i class="bi bi-telephone-outbound-fill"></i>
                                    </a>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ $emergencyHours }}</p>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="flex items-center p-4 bg-yellow-50 rounded-xl">
                            <div
                                class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center text-white text-xl">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="font-semibold text-gray-800" x-text="lang === 'en' ? 'Office' : 'ঠিকানা'">
                                </h3>
                                <p class="text-sm text-gray-600">{{ $address }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $officeHours }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="pt-4 border-t">
                        <h3 class="font-semibold text-gray-800 mb-3"
                            x-text="lang === 'en' ? 'Connect With Us' : 'সোশ্যাল মিডিয়া'">
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ $socialLinks['facebook'] }}" target="_blank"
                                class="w-10 h-10 bg-blue-600 text-white rounded-lg flex items-center justify-center hover:bg-blue-700 transition transform hover:scale-110">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="{{ $socialLinks['twitter'] }}" target="_blank"
                                class="w-10 h-10 bg-sky-500 text-white rounded-lg flex items-center justify-center hover:bg-sky-600 transition transform hover:scale-110">
                                <i class="bi bi-twitter-x"></i>
                            </a>
                            <a href="{{ $socialLinks['linkedin'] }}" target="_blank"
                                class="w-10 h-10 bg-blue-700 text-white rounded-lg flex items-center justify-center hover:bg-blue-800 transition transform hover:scale-110">
                                <i class="bi bi-linkedin"></i>
                            </a>
                            <a href="{{ $socialLinks['youtube'] }}" target="_blank"
                                class="w-10 h-10 bg-red-600 text-white rounded-lg flex items-center justify-center hover:bg-red-700 transition transform hover:scale-110">
                                <i class="bi bi-youtube"></i>
                            </a>
                            <a href="{{ $socialLinks['whatsapp'] }}" target="_blank"
                                class="w-10 h-10 bg-green-600 text-white rounded-lg flex items-center justify-center hover:bg-green-700 transition transform hover:scale-110">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                            <a href="{{ $socialLinks['telegram'] }}" target="_blank"
                                class="w-10 h-10 bg-blue-500 text-white rounded-lg flex items-center justify-center hover:bg-blue-600 transition transform hover:scale-110">
                                <i class="bi bi-telegram"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Status Card -->
                    <div class="bg-gray-50 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="font-semibold text-gray-700"
                                x-text="lang === 'en' ? 'Support Status' : 'সাপোর্ট স্ট্যাটাস'">
                            </span>
                            <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full flex items-center">
                                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse mr-1"></span>
                                <span x-text="lang === 'en' ? 'Online' : 'অনলাইন'"></span>
                            </span>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('Average response time') }}</span>
                                <span class="font-medium text-gray-800">
                                    < 15 min</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">{{ __('Tickets resolved today') }}</span>
                                <span class="font-medium text-gray-800">47</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Column -->
            <div class="lg:col-span-2">
                <!-- Tab Navigation -->
                <div class="bg-white rounded-t-2xl shadow-lg border-b border-gray-200">
                    <div class="flex">
                        <button @click="activeTab = 'contact'"
                            :class="activeTab === 'contact' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="flex-1 py-4 px-6 text-center border-b-2 font-medium transition">
                            <i class="bi bi-envelope-paper-fill mr-2"></i>
                            <span x-text="lang === 'en' ? 'Contact Form' : 'যোগাযোগ ফর্ম'"></span>
                        </button>
                        <button @click="activeTab = 'livechat'"
                            :class="activeTab === 'livechat' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="flex-1 py-4 px-6 text-center border-b-2 font-medium transition">
                            <i class="bi bi-chat-dots-fill mr-2"></i>
                            <span x-text="lang === 'en' ? 'Live Chat' : 'লাইভ চ্যাট'"></span>
                        </button>
                        <button @click="activeTab = 'tickets'"
                            :class="activeTab === 'tickets' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="flex-1 py-4 px-6 text-center border-b-2 font-medium transition">
                            <i class="bi bi-ticket-fill mr-2"></i>
                            <span x-text="lang === 'en' ? 'My Tickets' : 'আমার টিকিট'"></span>
                        </button>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="bg-white rounded-b-2xl shadow-xl p-6">
                    <!-- Contact Form Tab -->
                    <div x-show="activeTab === 'contact'" x-transition>
                        @if($successMessage)
                            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                                <div class="flex">
                                    <i class="bi bi-check-circle-fill text-green-500 text-xl mr-3"></i>
                                    <div>
                                        <p class="text-green-800">{{ $successMessage }}</p>
                                        <p class="text-sm text-green-600 mt-1"
                                            x-text="lang === 'en' ? 'We will get back to you soon.' : 'আমরা শীঘ্রই যোগাযোগ করব।'">
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($errorMessage)
                            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                                <div class="flex">
                                    <i class="bi bi-exclamation-circle-fill text-red-500 text-xl mr-3"></i>
                                    <p class="text-red-800">{{ $errorMessage }}</p>
                                </div>
                            </div>
                        @endif

                        <form wire:submit.prevent="submit" class="space-y-6">
                            <!-- Name & Email Row -->
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('Full Name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model="name"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                        :placeholder="lang === 'en' ? 'John Doe' : 'আপনার নাম'">
                                    @error('name') <span class="text-sm text-red-500 mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('Email Address') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" wire:model="email"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                        :placeholder="lang === 'en' ? 'john@example.com' : 'ইমেইল ঠিকানা'">
                                    @error('email') <span class="text-sm text-red-500 mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Phone & Priority Row -->
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('Phone Number') }}
                                    </label>
                                    <input type="tel" wire:model="phone"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                        :placeholder="lang === 'en' ? '+880 1XXX-XXXXXX' : 'ফোন নম্বর'">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('Priority') }}
                                    </label>
                                    <select wire:model="priority"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                        <option value="low">{{ __('Low') }}</option>
                                        <option value="normal">{{ __('Normal') }}</option>
                                        <option value="high">{{ __('High') }}</option>
                                        <option value="urgent">{{ __('Urgent') }}</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Category & Subject Row -->
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('Category') }} <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="category"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                        <option value="general">{{ __('General Inquiry') }}</option>
                                        <option value="technical">{{ __('Technical Support') }}</option>
                                        <option value="billing">{{ __('Billing & Payment') }}</option>
                                        <option value="support">{{ __('Customer Support') }}</option>
                                        <option value="feedback">{{ __('Feedback & Suggestion') }}</option>
                                    </select>
                                    @error('category') <span class="text-sm text-red-500 mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('Subject') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model="subject"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                        :placeholder="lang === 'en' ? 'Brief subject' : 'বিষয় সংক্ষেপে'">
                                    @error('subject') <span class="text-sm text-red-500 mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Message -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Message') }} <span class="text-red-500">*</span>
                                </label>
                                <textarea wire:model="message" rows="5"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                    :placeholder="lang === 'en' ? 'Describe your issue in detail...' : 'বিস্তারিত লিখুন...'"></textarea>
                                @error('message') <span class="text-sm text-red-500 mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Attachments -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Attachments (Optional)') }}
                                </label>
                                <div
                                    class="border-2 border-dashed border-gray-200 rounded-lg p-6 text-center hover:border-purple-500 transition">
                                    <input type="file" wire:model="attachments" multiple class="hidden"
                                        id="file-upload">
                                    <label for="file-upload" class="cursor-pointer">
                                        <i class="bi bi-cloud-upload text-4xl text-gray-400"></i>
                                        <p class="mt-2 text-sm text-gray-600">
                                            <span class="text-purple-600 font-medium">{{ __('Click to upload') }}</span>
                                            {{ __('or drag and drop') }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ __('PNG, JPG, PDF, DOC up to 5MB') }}
                                        </p>
                                    </label>
                                </div>

                                @if($attachments)
                                    <div class="mt-3 space-y-2">
                                        @foreach($attachments as $index => $file)
                                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                                <div class="flex items-center">
                                                    <i class="bi bi-file-earmark-text text-gray-500 mr-2"></i>
                                                    <span
                                                        class="text-sm text-gray-700">{{ $file->getClientOriginalName() }}</span>
                                                    <span class="text-xs text-gray-500 ml-2">
                                                        ({{ round($file->getSize() / 1024) }} KB)
                                                    </span>
                                                </div>
                                                <button type="button" wire:click="removeAttachment({{ $index }})"
                                                    class="text-red-500 hover:text-red-700">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @error('attachments.*') <span class="text-sm text-red-500 mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-500">
                                    <i class="bi bi-shield-check text-green-500"></i>
                                    <span
                                        x-text="lang === 'en' ? 'Your information is secure' : 'আপনার তথ্য নিরাপদ'"></span>
                                </div>
                                <button type="submit"
                                    class="px-8 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-lg font-medium hover:from-purple-700 hover:to-blue-700 transition transform hover:scale-105 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2"
                                    wire:loading.attr="disabled">
                                    <span wire:loading.remove>
                                        <i class="bi bi-send-fill mr-2"></i>
                                        <span x-text="lang === 'en' ? 'Send Message' : 'বার্তা পাঠান'"></span>
                                    </span>
                                    <span wire:loading>
                                        <i class="bi bi-arrow-repeat animate-spin mr-2"></i>
                                        <span x-text="lang === 'en' ? 'Sending...' : 'পাঠানো হচ্ছে...'"></span>
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Live Chat Tab -->
                    <div x-show="activeTab === 'livechat'" x-transition>
                        {{-- <livewire:chat-support /> --}}
                    </div>

                    <!-- My Tickets Tab -->
                    <div x-show="activeTab === 'tickets'" x-transition>
                        {{-- <livewire:user-tickets /> --}}
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="mt-12 text-center text-sm text-gray-500">
            <p
                x-text="lang === 'en' ? 'We typically respond within 24 hours. For urgent matters, please call our support line.' : 'আমরা সাধারণত ২৪ ঘণ্টার মধ্যে উত্তর দেই। জরুরি প্রয়োজনে ফোন করুন।'">
            </p>
            <p class="mt-2">
                <i class="bi bi-clock-history"></i>
                <span x-text="lang === 'en' ? 'Current response time: ' : 'বর্তমান প্রতিক্রিয়া সময়: '"></span>
                <span class="font-medium text-green-600">&lt; 15 minutes</span>
            </p>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .breadcrumbs a:hover {
            color: #7C3AED;
        }
    </style>
</div>