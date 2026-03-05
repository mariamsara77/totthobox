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

    public function updatedCurrent($value)
    {
        if (array_key_exists($value, config('translator.supported', []))) {
            Session::put('app_locale', $value);
            Session::save();

            // window.location.reload() এর বদলে আপনি যদি SPA ব্যবহার করেন
            // Volt কম্পোনেন্টে
            $this->js("document.body.style.opacity = '0.5'; window.location.reload();");
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