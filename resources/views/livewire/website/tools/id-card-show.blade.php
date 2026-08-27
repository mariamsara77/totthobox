<?php

use Livewire\Component;
use App\Models\IdCard;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

new class extends Component {
    public IdCard $card;

    public function mount(IdCard $idCard): void
    {
        abort_unless($idCard->user_id === Auth::id(), 403);
        $this->card = $idCard;
    }

    public function delete(): void
    {
        $this->card->clearMediaCollection('photo');
        $this->card->clearMediaCollection('logo');
        $this->card->delete();

        $this->dispatch('notify', type: 'success', message: 'আইডি কার্ড মুছে ফেলা হয়েছে');
        $this->redirect(route('tools.id-card-generator'), navigate: true);
    }

    public function downloadPdf()
    {
        $pdf = Pdf::loadView('id-cards.print', ['card' => $this->card])
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'NotoSansBengali')
            ->setOption('dpi', 150);

        return response()->streamDownload(fn() => print $pdf->output(), $this->card->card_number . '.pdf');
    }
};
?>

<x-seo title="{{ $card->display_name }} – ID Card | {{ $card->card_number }}"
    description="View and download ID card of {{ $card->display_name }}. Card number: {{ $card->card_number }}. Print-ready PDF available."
    keywords="id card, {{ $card->display_name }}, {{ $card->card_number }}, id card download" type="website" />

<div class="max-w-5xl mx-auto space-y-8 py-6">
    <div class="flex justify-between items-start items-center gap-4">
        <div>
            <flux:heading size="xl">{{ $card->display_name }}</flux:heading>
            <flux:text class="text-zinc-500 mt-1 font-mono">{{ $card->card_number }}</flux:text>
        </div>

        <div class="flex flex-wrap gap-4">
            <flux:button variant="ghost" icon="arrow-left" :href="route('tools.id-card-generator')" wire:navigate>ফিরে
                যান</flux:button>
            <flux:button variant="outline" icon="pencil"
                :href="route('tools.id-card-generator', ['edit' => $card->id])" wire:navigate>এডিট</flux:button>
            <flux:button variant="outline" icon="arrow-down-tray" wire:click="downloadPdf" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="downloadPdf">PDF ডাউনলোড</span>
                <span wire:loading wire:target="downloadPdf">তৈরি হচ্ছে...</span>
            </flux:button>
            <flux:button variant="primary" icon="printer" onclick="window.print()">প্রিন্ট করুন</flux:button>
            <flux:button variant="danger" icon="trash" wire:click="delete"
                wire:confirm="আপনি কি নিশ্চিতভাবে এই আইডি কার্ড মুছে ফেলতে চান?">ডিলিট</flux:button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <flux:card class="overflow-hidden">
                <div
                    class="p-4 bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center">
                    <flux:heading size="sm">কার্ড প্রিভিউ</flux:heading>
                    <flux:badge size="sm"
                        :color="match($card->status) {
                                                                                                                                                                                                                        'active' => 'green',
                                                                                                                                                                                                                        'expired' => 'amber',
                                                                                                                                                                                                                        'revoked' => 'red',
                                                                                                                                                                                                                        'suspended' => 'orange',
                                                                                                                                                                                                                        default => 'zinc',
                                                                                                                                                                                                                    }">
                        {{ ucfirst($card->status) }}
                    </flux:badge>
                </div>

                <div class="p-8 flex justify-center bg-zinc-100 dark:bg-zinc-950">
                    <div
                        class="{{ ($card->design_settings['orientation'] ?? 'horizontal') === 'vertical'
                            ? 'max-w-[280px] aspect-[0.63/1]'
                            : 'max-w-md aspect-[1.586/1]' }} shadow-2xl rounded-2xl overflow-hidden print-area mx-auto">
                        @include('id-cards.templates.card', ['card' => $card])
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="p-5">
                    <flux:heading size="sm" class="mb-4">ডিজাইন তথ্য</flux:heading>
                    <div class="grid grid-cols-2  gap-4 text-sm">
                        <div>
                            <flux:text size="sm" class="text-zinc-500">টেমপ্লেট</flux:text>
                            <p class="font-medium capitalize">{{ $card->template_name }}</p>
                        </div>
                        <div>
                            <flux:text size="sm" class="text-zinc-500">ভাষা</flux:text>
                            <p class="font-medium">
                                {{ match ($card->language) {
                                    'bn' => 'শুধু বাংলা',
                                    'en' => 'শুধু ইংরেজি',
                                    default => 'বাংলা + ইংরেজি',
                                } }}
                            </p>
                        </div>
                        <div>
                            <flux:text size="sm" class="text-zinc-500">কার্ড টাইপ</flux:text>
                            <p class="font-medium capitalize">{{ $card->card_type }}</p>
                        </div>
                        <div>
                            <flux:text size="sm" class="text-zinc-500">তৈরির তারিখ</flux:text>
                            <p class="font-medium">{{ $card->created_at->format('d M, Y') }}</p>
                        </div>
                    </div>
                </div>
            </flux:card>
        </div>

        <div class="space-y-6">
            <flux:card>
                <div class="p-5 space-y-5">
                    <flux:heading size="sm">ব্যক্তিগত তথ্য</flux:heading>
                    <div class="space-y-4 text-sm">
                        <div>
                            <flux:text size="sm" class="text-zinc-500">নাম (English)</flux:text>
                            <p class="font-medium">{{ $card->name_en ?: '—' }}</p>
                        </div>
                        <div>
                            <flux:text size="sm" class="text-zinc-500">নাম (বাংলা)</flux:text>
                            <p class="font-medium">{{ $card->name_bn ?: '—' }}</p>
                        </div>
                        <div>
                            <flux:text size="sm" class="text-zinc-500">পদবী</flux:text>
                            <p class="font-medium">{{ $card->designation_bn ?: $card->designation_en ?: '—' }}</p>
                        </div>
                        <div>
                            <flux:text size="sm" class="text-zinc-500">প্রতিষ্ঠান</flux:text>
                            <p class="font-medium">{{ $card->organization_bn ?: $card->organization_en ?: '—' }}</p>
                        </div>
                        <div>
                            <flux:text size="sm" class="text-zinc-500">ডিপার্টমেন্ট</flux:text>
                            <p class="font-medium">{{ $card->department_bn ?: $card->department_en ?: '—' }}</p>
                        </div>

                        <flux:separator />

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <flux:text size="sm" class="text-zinc-500">রক্তের গ্রুপ</flux:text>
                                <p class="font-medium">{{ $card->blood_group ?: '—' }}</p>
                            </div>
                            <div>
                                <flux:text size="sm" class="text-zinc-500">NID / পাসপোর্ট</flux:text>
                                <p class="font-medium">{{ $card->nid_or_passport ?: '—' }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <flux:text size="sm" class="text-zinc-500">জন্ম তারিখ</flux:text>
                                <p class="font-medium">{{ $card->date_of_birth?->format('d M, Y') ?: '—' }}</p>
                            </div>
                            <div>
                                <flux:text size="sm" class="text-zinc-500">স্ট্যাটাস</flux:text>
                                <p class="font-medium capitalize">{{ $card->status }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <flux:text size="sm" class="text-zinc-500">ইস্যু তারিখ</flux:text>
                                <p class="font-medium">{{ $card->issue_date?->format('d M, Y') ?: '—' }}</p>
                            </div>
                            <div>
                                <flux:text size="sm" class="text-zinc-500">মেয়াদ উত্তীর্ণ</flux:text>
                                <p class="font-medium {{ $card->isExpired() ? 'text-red-500 font-semibold' : '' }}">
                                    {{ $card->expiry_date?->format('d M, Y') ?: '—' }}
                                    @if ($card->isExpired())
                                        <span class="text-xs">(মেয়াদোত্তীর্ণ)</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <flux:separator />

                        <div>
                            <flux:text size="sm" class="text-zinc-500">মোবাইল</flux:text>
                            <p class="font-medium">{{ $card->phone ?: '—' }}</p>
                        </div>
                        <div>
                            <flux:text size="sm" class="text-zinc-500">ইমেইল</flux:text>
                            <p class="font-medium">{{ $card->email ?: '—' }}</p>
                        </div>

                        @if ($card->emergency_contact_name || $card->emergency_contact_phone)
                            <div>
                                <flux:text size="sm" class="text-zinc-500">জরুরি যোগাযোগ</flux:text>
                                <p class="font-medium">
                                    {{ $card->emergency_contact_name }}
                                    @if ($card->emergency_contact_phone)
                                        <span class="text-zinc-500">({{ $card->emergency_contact_phone }})</span>
                                    @endif
                                </p>
                            </div>
                        @endif

                        @if ($card->address_en || $card->address_bn)
                            <div>
                                <flux:text size="sm" class="text-zinc-500">ঠিকানা</flux:text>
                                <p class="font-medium text-sm leading-relaxed">
                                    {{ $card->address_bn ?: $card->address_en }}
                                </p>
                            </div>
                        @endif

                        @if (!empty($card->custom_fields))
                            <flux:separator />
                            <flux:heading size="sm">অতিরিক্ত তথ্য</flux:heading>
                            @foreach ($card->custom_fields as $key => $value)
                                <div>
                                    <flux:text size="sm" class="text-zinc-500">{{ $key }}</flux:text>
                                    <p class="font-medium">{{ $value }}</p>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="p-5 space-y-4">
                    <flux:heading size="sm">ভেরিফিকেশন</flux:heading>
                    <div class="flex justify-center">
                        <img src="{{ $card->qrImageUrl(140) }}" alt="Verification QR"
                            class="rounded-lg border border-zinc-400/25">
                    </div>
                    <div class="space-y-3 text-sm">
                        <div>
                            <flux:text size="sm" class="text-zinc-500">UUID</flux:text>
                            <p class="font-mono text-xs break-all select-all">{{ $card->uuid }}</p>
                        </div>
                        <div>
                            <flux:text size="sm" class="text-zinc-500">পাবলিক ভেরিফিকেশন লিংক</flux:text>
                            <a href="{{ $card->verifyUrl() }}" target="_blank"
                                class="text-indigo-600 dark:text-indigo-400 text-sm break-all hover:underline block mt-1">
                                {{ $card->verifyUrl() }}
                            </a>
                        </div>
                        @if ($card->nfc_serial)
                            <div>
                                <flux:text size="sm" class="text-zinc-500">NFC Serial</flux:text>
                                <p class="font-mono text-xs">{{ $card->nfc_serial }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</div>

@assets
    <style>
        @media print {
            body * {
                visibility: hidden !important;
            }

            .print-area,
            .print-area * {
                visibility: visible !important;
            }

            .print-area {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 85.6mm !important;
                height: 53.98mm !important;
            }

            @page {
                size: 85.6mm 53.98mm;
                margin: 0;
            }
        }
    </style>
@endassets
