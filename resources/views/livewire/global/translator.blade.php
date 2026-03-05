<?php
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

new class extends Component {
    public $current;

    public function mount()
    {
        // ব্রাউজার ডিটেকশন লজিক পুরোপুরি রিমুভ করা হয়েছে।
        // সরাসরি সেশন চেক করবে, না থাকলে config এর ডিফল্ট (bn) নিবে।
        $this->current = Session::get('app_locale', config('app.locale', 'bn'));

        if (!Session::has('app_locale')) {
            Session::put('app_locale', $this->current);
            Session::save();
        }
    }

    // Volt Component এর updatedCurrent ফাংশনটি এভাবে রিপ্লেস করুন
    public function updatedCurrent($value)
    {
        if (array_key_exists($value, config('translator.supported', []))) {
            // ১. সেশন আপডেট
            Session::put('app_locale', $value);
            Session::save(); // ফোর্স সেভ

            // ২. কুকি সেট (ব্যাকআপ হিসেবে, যদি সেশন ফেইল করে)
            cookie()->queue(cookie()->forever('app_locale', $value));

            // ৩. জাভাস্ক্রিপ্ট দিয়ে হার্ড রিলোড এবং কুকি নিশ্চিত করা
            // window.location.href ব্যবহার করলে ক্যাশ বাইপাস করার সম্ভাবনা বাড়ে
            $this->js("
            document.cookie = 'app_locale=$value; path=/; max-age=31536000';
            document.body.style.opacity = '0.5';
            setTimeout(() => {
                window.location.href = window.location.href; 
            }, 100);
        ");
        }
    }

    public function with()
    {
        return [
            'locales' => config('translator.supported', [])
        ];
    }
}; ?>

<div class="notranslate" wire:ignore>
    <div class="relative flex items-center gap-2">
        <flux:select wire:model.live="current">

            @forelse($locales as $code => $lang)
                <flux:select.option value="{{ $code }}">
                    {{ $lang['flag'] }} {{ $lang['name'] }}
                </flux:select.option>
            @empty
                <flux:select.option value="bn">🇧🇩 বাংলা</flux:select.option>
            @endforelse
        </flux:select>
    </div>
</div>