<?php

use App\Models\Comment;
use Livewire\Volt\Component;
use Flux\Flux;

new class extends Component {
    public $model;
    public string $content = '';

    protected function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }

    public function mount($model): void
    {
        $this->model = $model;
    }

    public function ensureAuthenticated(string $message = 'লগইন করতে হবে।'): bool
    {
        if (auth()->check()) {
            return true;
        }

        Flux::modal('auth-modal')->show();
        Flux::toast(text: $message, variant: 'warning');

        return false;
    }

    public function authCheck(): void
    {
        Flux::modal('auth-modal')->show();
    }

    public function submit(): void
    {
        if (!$this->ensureAuthenticated('মন্তব্য করার জন্য লগইন করতে হবে।')) {
            return;
        }

        $this->validate();

        Comment::create([
            'content' => $this->content,
            'user_id' => auth()->id(),
            'commentable_id' => $this->model->id,
            'commentable_type' => get_class($this->model),
            'parent_id' => null,
            'depth' => 0,
        ]);

        $this->reset('content');
        $this->dispatch('commentAdded');

        Flux::toast(text: 'আপনার মন্তব্য সফলভাবে রেকর্ড করা হয়েছে!', variant: 'success');
    }
}; ?>

<div>
    <flux:field>
        <flux:textarea wire:model.live="content" resize="none" placeholder="মন্তব্য লিখুন..." rows="3"
            class="" />

        <div class="flex items-center justify-between mt-3">
            <flux:text class="text-sm text-zinc-500">
                মন্তব্য জমা দেওয়ার জন্য প্রস্তুত?
            </flux:text>

            @auth
                <flux:button variant="primary" color="black" class="!rounded-full" wire:click="submit" size="sm">
                    মন্তব্য করুন
                </flux:button>
            @else
                <flux:button variant="primary" color="black" class="!rounded-full" wire:click="authCheck" size="sm">
                    মন্তব্য করুন
                </flux:button>
            @endauth
        </div>
    </flux:field>
</div>
