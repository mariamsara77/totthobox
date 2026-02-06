<div class="">
    @include('partials.toast')
    @auth
        <div>
            {{-- <form wire:submit.prevent="submit" class="space-y-3"> --}}
            <flux:field>
                <flux:textarea wire:model.live="content" resize="none" placeholder="মন্তব্য লিখুন..." rows="auto">
                </flux:textarea>


                <div class="flex items-center justify-between">
                    <flux:text>
                        মন্তব্য জমা দেওয়ার জন্য প্রস্তুত?
                    </flux:text>

                    <flux:button variant="primary" color="black" class="!rounded-full" wire:click="submit"
                        size="sm">
                        মন্তব্য করুন
                    </flux:button>
                </div>
            </flux:field>
            {{-- </form> --}}
        </div>
    @else
        <div>
            <flux:text>
                দয়া করে মন্তব্য করতে সাইন ইন করুন।
            </flux:text>
            <flux:button href="{{ route('login') }}">
                সাইন ইন করুন
            </flux:button>
        </div>
    @endauth
</div>
