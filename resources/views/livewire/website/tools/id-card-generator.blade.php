<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\IdCard;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;
use Flux\Flux;

new class extends Component {
    use WithFileUploads, WithPagination;

    public bool $showForm = false;
    public ?int $editingId = null;

    // URL থেকে edit=<id> ধরার জন্য (Show পেজ থেকে এডিট বাটনে ক্লিক করলে এখানে আসে)
    #[Url(as: 'edit', history: false)]
    public ?int $editFromUrl = null;

    // Core
    public string $card_type = 'employee';
    public string $template_name = 'aurora';
    public string $language = 'both';
    public string $status = 'active';

    // English
    public string $name_en = '';
    public string $designation_en = '';
    public string $department_en = '';
    public string $organization_en = '';
    public string $address_en = '';

    // Bangla
    public string $name_bn = '';
    public string $designation_bn = '';
    public string $department_bn = '';
    public string $organization_bn = '';
    public string $address_bn = '';

    // Personal & Contact
    public string $blood_group = '';
    public ?string $date_of_birth = null;
    public string $nid_or_passport = '';
    public string $phone = '';
    public string $email = '';
    public string $emergency_contact_name = '';
    public string $emergency_contact_phone = '';
    public ?string $issue_date = null;
    public ?string $expiry_date = null;

    public array $design_settings = [];

    // Custom Fields
    public array $custom_fields = [];
    public string $new_custom_key = '';
    public string $new_custom_value = '';

    // Images
    public $photo = null;
    public $logo = null;
    public ?string $photoPreviewUrl = null;
    public ?string $logoPreviewUrl = null;
    public ?string $existingPhotoUrl = null;
    public ?string $existingLogoUrl = null;
    public bool $removeExistingPhoto = false;
    public bool $removeExistingLogo = false;

    // Filters
    public string $search = '';
    public string $filterType = '';
    public string $filterTemplate = '';

    public function mount(): void
    {
        $this->design_settings = $this->defaultDesignSettings();

        if ($this->editFromUrl) {
            $this->edit($this->editFromUrl);
        }
    }

    protected function defaultDesignSettings(): array
    {
        return [
            'primary_color' => '#4F46E5',
            'secondary_color' => '#7C3AED',
            'accent_color' => '#F59E0B',
            'orientation' => 'horizontal', // horizontal | vertical
            'show_photo' => true,
            'show_logo' => true,
            'show_blood' => true,
            'show_qr' => true,
            'photo_shape' => 'rounded', // rounded | circle | square
            'photo_size' => 'md', // sm | md | lg | xl
            'photo_border' => true,
            'logo_max_height' => 40,
        ];
    }

    protected function rules(): array
    {
        $templates = implode(',', IdCard::TEMPLATES);
        $types = implode(',', IdCard::CARD_TYPES);
        $statuses = implode(',', IdCard::STATUSES);

        return [
            'name_en' => 'nullable|string|max:150',
            'name_bn' => 'nullable|string|max:150',
            'designation_en' => 'nullable|string|max:150',
            'designation_bn' => 'nullable|string|max:150',
            'department_en' => 'nullable|string|max:150',
            'department_bn' => 'nullable|string|max:150',
            'organization_en' => 'nullable|string|max:150',
            'organization_bn' => 'nullable|string|max:150',
            'address_en' => 'nullable|string|max:500',
            'address_bn' => 'nullable|string|max:500',
            'card_type' => "required|in:{$types}",
            'template_name' => "required|in:{$templates}",
            'status' => "required|in:{$statuses}",
            'language' => 'required|in:bn,en,both',
            'photo' => 'nullable|image|max:5120',
            'logo' => 'nullable|image|max:2048',
            'blood_group' => 'nullable|string|max:5',
            'nid_or_passport' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'emergency_contact_name' => 'nullable|string|max:150',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:issue_date',
            'design_settings' => 'array',
            'custom_fields' => 'array',
        ];
    }

    public function updatedPhoto(): void
    {
        $this->validateOnly('photo');
        $this->photoPreviewUrl = $this->photo->temporaryUrl();
        $this->removeExistingPhoto = false;
    }

    public function updatedLogo(): void
    {
        $this->validateOnly('logo');
        $this->logoPreviewUrl = $this->logo->temporaryUrl();
        $this->removeExistingLogo = false;
    }

    public function clearPhoto(): void
    {
        $this->photo = null;
        $this->photoPreviewUrl = null;
        $this->removeExistingPhoto = true;
    }

    public function clearLogo(): void
    {
        $this->logo = null;
        $this->logoPreviewUrl = null;
        $this->removeExistingLogo = true;
    }

    public function create(): void
    {
        if (!auth()->check()) {
            Flux::toast(
                text: 'লগইন করতে হবে।',
                variant: 'danger', // বা 'warning'
            );

            Flux::modal('auth-modal')->show();
            // অথবা $this->modal('auth-modal')->show();

            return; // ← এটা খুব জরুরি
        }

        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $card = IdCard::where('user_id', auth()->id())->findOrFail($id);

        $this->editingId = $card->id;
        $this->fill($card->only(['card_type', 'template_name', 'language', 'status', 'name_en', 'designation_en', 'department_en', 'organization_en', 'address_en', 'name_bn', 'designation_bn', 'department_bn', 'organization_bn', 'address_bn', 'blood_group', 'nid_or_passport', 'phone', 'email', 'emergency_contact_name', 'emergency_contact_phone']));

        $this->date_of_birth = $card->date_of_birth?->format('Y-m-d');
        $this->issue_date = $card->issue_date?->format('Y-m-d');
        $this->expiry_date = $card->expiry_date?->format('Y-m-d');
        $this->design_settings = array_merge($this->defaultDesignSettings(), $card->design_settings ?? []);
        $this->custom_fields = $card->custom_fields ?? [];
        $this->existingPhotoUrl = $card->photo_url;
        $this->existingLogoUrl = $card->logo_url;
        $this->removeExistingPhoto = false;
        $this->removeExistingLogo = false;
        $this->photo = null;
        $this->logo = null;
        $this->photoPreviewUrl = null;
        $this->logoPreviewUrl = null;
        $this->showForm = true;
    }

    public function addCustomField(): void
    {
        $key = trim($this->new_custom_key);

        if (blank($key)) {
            return;
        }

        if (count($this->custom_fields) >= 20) {
            $this->addError('new_custom_key', 'সর্বোচ্চ ২০টি কাস্টম ফিল্ড যোগ করা যাবে');
            return;
        }

        $this->custom_fields[$key] = trim($this->new_custom_value);
        $this->new_custom_key = '';
        $this->new_custom_value = '';
    }

    public function removeCustomField(string $key): void
    {
        unset($this->custom_fields[$key]);
    }

    public function save(): void
    {
        $this->validate();

        if (blank($this->name_en) && blank($this->name_bn)) {
            $this->addError('name_en', 'অন্তত একটি নাম (বাংলা বা ইংরেজি) দিতে হবে');
            return;
        }

        $data = [
            'user_id' => auth()->id(),
            'card_type' => $this->card_type,
            'template_name' => $this->template_name,
            'language' => $this->language,
            'status' => $this->status,
            'name_en' => $this->name_en,
            'name_bn' => $this->name_bn,
            'designation_en' => $this->designation_en,
            'designation_bn' => $this->designation_bn,
            'department_en' => $this->department_en,
            'department_bn' => $this->department_bn,
            'organization_en' => $this->organization_en,
            'organization_bn' => $this->organization_bn,
            'address_en' => $this->address_en,
            'address_bn' => $this->address_bn,
            'blood_group' => $this->blood_group,
            'date_of_birth' => $this->date_of_birth,
            'nid_or_passport' => $this->nid_or_passport,
            'phone' => $this->phone,
            'email' => $this->email,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'issue_date' => $this->issue_date,
            'expiry_date' => $this->expiry_date,
            'design_settings' => $this->design_settings,
            'custom_fields' => $this->custom_fields,
        ];

        if ($this->editingId) {
            $card = IdCard::where('user_id', auth()->id())->findOrFail($this->editingId);
            $card->update($data);
        } else {
            $card = IdCard::create($data);
        }

        try {
            if ($this->photo) {
                $this->processAndAttachImage($card, 'photo', $this->photo);
            } elseif ($this->removeExistingPhoto) {
                $card->clearMediaCollection('photo');
            }

            if ($this->logo) {
                $this->processAndAttachImage($card, 'logo', $this->logo);
            } elseif ($this->removeExistingLogo) {
                $card->clearMediaCollection('logo');
            }
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: 'ছবি প্রসেস করতে সমস্যা হয়েছে, বাকি তথ্য সেভ হয়েছে।');
        }

        $this->resetForm();
        $this->showForm = false;

        Flux::toast(text: 'সফলভাবে রেকর্ড করা হয়েছে!', variant: 'success');
    }

    protected function processAndAttachImage(IdCard $card, string $collection, $upload): void
    {
        $image = Image::read($upload->getRealPath());

        if ($collection === 'logo') {
            // লোগো — aspect ratio অক্ষুণ্ণ রেখে শুধু max size limit
            $image->scaleDown(width: 400, height: 400);
        } else {
            // ফটো — কার্ডের সাইজে কভার করে ক্রপ
            $image->cover(800, 1000);
            $image->sharpen(8);
        }

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = $tempDir . '/' . Str::uuid() . '.jpg';
        $image->toJpeg(92)->save($tempPath);

        try {
            $card->clearMediaCollection($collection);
            $card
                ->addMedia($tempPath)
                ->usingFileName("{$collection}-{$card->uuid}.jpg")
                ->toMediaCollection($collection);
        } finally {
            @unlink($tempPath);
        }
    }

    public function delete(int $id): void
    {
        $card = IdCard::where('user_id', auth()->id())->findOrFail($id);
        $card->clearMediaCollection('photo');
        $card->clearMediaCollection('logo');
        $card->delete();
        $this->dispatch('notify', type: 'success', message: 'আইডি কার্ড মুছে ফেলা হয়েছে');
    }

    public function resetForm(): void
    {
        $this->resetExcept(['search', 'filterType', 'filterTemplate']);
        $this->design_settings = $this->defaultDesignSettings();
        $this->custom_fields = [];
        $this->editingId = null;
        $this->editFromUrl = null;
        $this->photo = null;
        $this->logo = null;
        $this->photoPreviewUrl = null;
        $this->logoPreviewUrl = null;
        $this->existingPhotoUrl = null;
        $this->existingLogoUrl = null;
        $this->removeExistingPhoto = false;
        $this->removeExistingLogo = false;
    }

    public function getPreviewCardProperty(): object
    {
        $preview = new \stdClass();

        $preview->template_name = $this->template_name;
        $preview->language = $this->language;
        $preview->name_en = $this->name_en ?: 'Your Name';
        $preview->name_bn = $this->name_bn ?: 'আপনার নাম';
        $preview->designation_en = $this->designation_en ?: 'Designation';
        $preview->designation_bn = $this->designation_bn ?: 'পদবী';
        $preview->organization_en = $this->organization_en ?: 'Organization Name';
        $preview->organization_bn = $this->organization_bn ?: 'প্রতিষ্ঠানের নাম';
        $preview->blood_group = $this->blood_group ?: 'B+';
        $preview->card_number = 'IDC-PREVIEW';
        $preview->uuid = 'preview';
        $preview->design_settings = $this->design_settings;
        $preview->custom_fields = $this->custom_fields;
        $preview->photo_url = $this->photoPreviewUrl ?? (!$this->removeExistingPhoto ? $this->existingPhotoUrl : null);
        $preview->logo_url = $this->logoPreviewUrl ?? (!$this->removeExistingLogo ? $this->existingLogoUrl : null);

        return $preview;
    }

    public function render()
    {
        $cards = IdCard::where('user_id', auth()->id())
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name_en', 'like', "%{$this->search}%")
                        ->orWhere('name_bn', 'like', "%{$this->search}%")
                        ->orWhere('card_number', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterType, fn($q) => $q->where('card_type', $this->filterType))
            ->when($this->filterTemplate, fn($q) => $q->where('template_name', $this->filterTemplate))
            ->latest()
            ->paginate(12);

        return $this->view([
            'cards' => $cards,
            'previewCard' => $this->previewCard,
        ]);
    }
};
?>

<x-seo title="{{ $editingId ? 'Edit ID Card' : 'Professional ID Card Generator' }} – Free Online Custom ID Card Maker"
    description="Create fully customizable employee, student, membership, press & visitor ID cards online. Live preview, multiple templates, vertical & horizontal, logo & photo support, PDF/JPG download."
    keywords="id card generator, online id card maker, employee id card, student id card, custom id card, বাংলা আইডি কার্ড, আইডি কার্ড তৈরি"
    image="{{ asset('images/og-id-card.jpg') }}" type="website" />

<div class="space-y-8">
    <div class="flex justify-between items-start items-center gap-4">
        <div>
            <flux:heading size="xl">আইডি কার্ড জেনারেটর</flux:heading>
            <flux:text class="text-zinc-500 mt-1">যেকোনো ধরনের আইডি কার্ড – সম্পূর্ণ কাস্টমাইজেবল + লাইভ প্রিভিউ
            </flux:text>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="create">নতুন আইডি কার্ড</flux:button>
    </div>

    <div class="flex gap-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="নাম বা কার্ড নম্বর..." icon="magnifying-glass"
            class="flex-1" />
        <flux:select wire:model.live="filterType">
            <option value="">সব ধরন</option>
            @foreach (\App\Models\IdCard::CARD_TYPES as $t)
                <option value="{{ $t }}">{{ ucfirst($t) }}</option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="filterTemplate">
            <option value="">সব টেমপ্লেট</option>
            @foreach (\App\Models\IdCard::TEMPLATES as $t)
                <option value="{{ $t }}">{{ ucfirst($t) }}</option>
            @endforeach
        </flux:select>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @forelse($cards as $card)
            <flux:card class="overflow-hidden group hover:shadow-xl transition">
                <div
                    class="relative overflow-hidden bg-zinc-100 dark:bg-zinc-900
                    {{ ($card->design_settings['orientation'] ?? 'horizontal') === 'vertical'
                        ? 'aspect-[0.63/1] max-w-[180px] mx-auto'
                        : 'aspect-[1.586/1]' }}">
                    @include('id-cards.templates.card', ['card' => $card])
                </div>
                <div class="p-4 space-y-3">
                    <div class="flex justify-between items-start gap-4">
                        <div class="min-w-0">
                            <flux:heading size="sm" class="truncate">{{ $card->display_name }}</flux:heading>
                            <flux:text size="sm" class="text-zinc-500 font-mono">{{ $card->card_number }}
                            </flux:text>
                        </div>
                        <flux:badge size="sm"
                            :color="match($card->status) {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            'active' => 'green',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            'expired' => 'amber',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            'suspended' => 'orange',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            default => 'red',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        }">
                            {{ ucfirst($card->status) }}
                        </flux:badge>
                    </div>
                    <div class="flex flex-wrap gap-4">
                        <flux:button size="sm" variant="ghost" icon="eye"
                            :href="route('tools.id-card.show', $card)" wire:navigate>দেখুন</flux:button>
                        <flux:button size="sm" variant="ghost" icon="pencil"
                            wire:click="edit({{ $card->id }})">এডিট</flux:button>
                        <flux:button size="sm" variant="ghost" icon="trash"
                            wire:click="delete({{ $card->id }})" wire:confirm="মুছে ফেলতে চান?">ডিলিট</flux:button>
                    </div>
                </div>
            </flux:card>
        @empty
            <div class="col-span-full py-20 text-center">
                <flux:text class="text-zinc-500">কোনো আইডি কার্ড নেই। নতুন তৈরি করুন।</flux:text>
            </div>
        @endforelse
    </div>

    {{ $cards->links() }}

    <section class="mt-20 prose dark:prose-invert max-w-none border-t border-zinc-200 dark:border-zinc-800 pt-12">
        <h2>Professional Online ID Card Generator</h2>
        <p>Create high-quality, print-ready ID cards for employees, students, members, press and visitors in standard
            CR80 size (85.6mm × 53.98mm), with live preview, multiple templates, and PDF/JPG download.</p>
        <p>আমাদের ফ্রি অনলাইন আইডি কার্ড জেনারেটর দিয়ে আপনি সহজেই প্রফেশনাল আইডি কার্ড তৈরি করতে পারবেন। লাইভ প্রিভিউ,
            একাধিক টেমপ্লেট, বাংলা ও ইংরেজি সাপোর্ট এবং প্রিন্ট-রেডি PDF ডাউনলোড সুবিধা রয়েছে।</p>
    </section>

    <flux:modal wire:model="showForm" class="max-w-6xl">
        <form wire:submit="save" class="space-y-6">
            <div class="flex justify-between items-center">
                <flux:heading size="lg">{{ $editingId ? 'আইডি কার্ড আপডেট' : 'নতুন আইডি কার্ড তৈরি' }}
                </flux:heading>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                <div class="lg:col-span-3 space-y-6 max-h-[75vh] overflow-y-auto pr-2">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:select wire:model.live="card_type" label="কার্ডের ধরন">
                            @foreach (\App\Models\IdCard::CARD_TYPES as $t)
                                <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model.live="template_name" label="টেমপ্লেট">
                            @foreach (\App\Models\IdCard::TEMPLATES as $t)
                                <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model.live="design_settings.orientation" label="ওরিয়েন্টেশন">
                            <option value="horizontal">Horizontal (আড়াআড়ি)</option>
                            <option value="vertical">Vertical (খাড়া)</option>
                        </flux:select>

                        <flux:select wire:model.live="language" label="ভাষা">
                            <option value="both">বাংলা + ইংরেজি</option>
                            <option value="bn">শুধু বাংলা</option>
                            <option value="en">শুধু ইংরেজি</option>
                        </flux:select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input wire:model.live.debounce.400ms="name_en" label="নাম (English)" />
                        <flux:input wire:model.live.debounce.400ms="name_bn" label="নাম (বাংলা)" />
                    </div>
                    @error('name_en')
                        <p class="text-red-500 text-sm -mt-3">{{ $message }}</p>
                    @enderror

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input wire:model.live.debounce.400ms="designation_en" label="পদবী (English)" />
                        <flux:input wire:model.live.debounce.400ms="designation_bn" label="পদবী (বাংলা)" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input wire:model.live.debounce.400ms="organization_en" label="প্রতিষ্ঠান (English)" />
                        <flux:input wire:model.live.debounce.400ms="organization_bn" label="প্রতিষ্ঠান (বাংলা)" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <flux:input wire:model.live="blood_group" label="রক্তের গ্রুপ" />
                        <flux:input type="date" wire:model.live="date_of_birth" label="জন্ম তারিখ" />
                        <flux:input wire:model.live="nid_or_passport" label="NID / পাসপোর্ট" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input type="date" wire:model.live="issue_date" label="ইস্যু তারিখ" />
                        <flux:input type="date" wire:model.live="expiry_date" label="মেয়াদ উত্তীর্ণ" />
                    </div>
                    @error('expiry_date')
                        <p class="text-red-500 text-sm -mt-3">{{ $message }}</p>
                    @enderror

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input wire:model.live="phone" label="মোবাইল" />
                        <flux:input wire:model.live="email" label="ইমেইল" />
                    </div>

                    <flux:separator />
                    <flux:heading size="sm">ডিজাইন</flux:heading>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <flux:label>প্রাইমারি</flux:label>
                            <input type="color" wire:model.live="design_settings.primary_color"
                                class="w-full h-10 rounded-lg cursor-pointer">
                        </div>
                        <div>
                            <flux:label>সেকেন্ডারি</flux:label>
                            <input type="color" wire:model.live="design_settings.secondary_color"
                                class="w-full h-10 rounded-lg cursor-pointer">
                        </div>
                        <flux:select wire:model.live="design_settings.photo_shape" label="ছবির শেপ">
                            <option value="rounded">Rounded</option>
                            <option value="circle">Circle</option>
                            <option value="square">Square</option>
                        </flux:select>
                        <flux:select wire:model.live="design_settings.photo_size" label="ছবির সাইজ">
                            <option value="sm">Small</option>
                            <option value="md">Medium</option>
                            <option value="lg">Large</option>
                            <option value="xl">X-Large</option>
                        </flux:select>
                    </div>

                    <div class="flex flex-wrap gap-4">
                        <flux:checkbox wire:model.live="design_settings.show_photo" label="ছবি দেখাবে" />
                        <flux:checkbox wire:model.live="design_settings.show_logo" label="লোগো দেখাবে" />
                        <flux:checkbox wire:model.live="design_settings.show_blood" label="রক্তের গ্রুপ" />
                        <flux:checkbox wire:model.live="design_settings.show_qr" label="QR কোড (ভেরিফিকেশন)" />
                    </div>

                    <flux:separator />
                    <flux:heading size="sm">ছবি ও লোগো</flux:heading>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <flux:label>কার্ড হোল্ডারের ছবি</flux:label>
                            <input type="file" wire:model="photo" accept="image/*" class="block w-full text-sm">
                            <div wire:loading wire:target="photo" class="text-xs text-zinc-500">আপলোড হচ্ছে...</div>
                            @error('photo')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                            @if ($photoPreviewUrl || ($existingPhotoUrl && !$removeExistingPhoto))
                                <div class="flex items-center gap-4">
                                    <img src="{{ $photoPreviewUrl ?? $existingPhotoUrl }}"
                                        class="h-24 w-20 object-cover rounded-xl shadow">
                                    <flux:button size="sm" variant="ghost" icon="x-mark" type="button"
                                        wire:click="clearPhoto">সরান</flux:button>
                                </div>
                            @endif
                        </div>
                        <div class="space-y-2">
                            <flux:label>প্রতিষ্ঠানের লোগো</flux:label>
                            <input type="file" wire:model="logo" accept="image/*" class="block w-full text-sm">
                            <div wire:loading wire:target="logo" class="text-xs text-zinc-500">আপলোড হচ্ছে...</div>
                            @error('logo')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                            @if ($logoPreviewUrl || ($existingLogoUrl && !$removeExistingLogo))
                                <div class="flex items-center gap-4">
                                    <img src="{{ $logoPreviewUrl ?? $existingLogoUrl }}" class="h-12 object-contain">
                                    <flux:button size="sm" variant="ghost" icon="x-mark" type="button"
                                        wire:click="clearLogo">সরান</flux:button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <flux:separator />
                    <flux:heading size="sm">কাস্টম ফিল্ড</flux:heading>
                    <div class="space-y-2">
                        @foreach ($custom_fields as $key => $value)
                            <div class="flex items-center gap-4 p-2 rounded-lg bg-zinc-50 dark:bg-zinc-800">
                                <span class="font-medium text-sm w-28 truncate">{{ $key }}</span>
                                <span class="flex-1 text-sm truncate">{{ $value }}</span>
                                <flux:button size="sm" variant="ghost" icon="x-mark" type="button"
                                    wire:click="removeCustomField(@js($key))" />
                            </div>
                        @endforeach
                    </div>
                    <div class="flex gap-4">
                        <flux:input wire:model="new_custom_key" placeholder="ফিল্ড নাম" class="flex-1" />
                        <flux:input wire:model="new_custom_value" placeholder="মান" class="flex-1" />
                        <flux:button type="button" variant="outline" wire:click="addCustomField">যোগ</flux:button>
                    </div>
                    @error('new_custom_key')
                        <p class="text-red-500 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div class="lg:col-span-2">
                    <div class="sticky top-0">
                        <flux:heading size="sm" class="mb-3">লাইভ প্রিভিউ</flux:heading>
                        <div
                            class="rounded-2xl overflow-hidden shadow-2xl border border-zinc-400/25 bg-zinc-100 dark:bg-zinc-900">
                            <div
                                class="{{ ($design_settings['orientation'] ?? 'horizontal') === 'vertical'
                                    ? 'aspect-[0.63/1] max-w-[260px] mx-auto'
                                    : 'aspect-[1.586/1]' }} relative">
                                @include('id-cards.templates.card', ['card' => $previewCard])
                            </div>
                        </div>
                        <flux:text size="sm" class="mt-3 text-zinc-500 text-center">উপরের ফর্ম পরিবর্তন করলে
                            প্রিভিউ অটো আপডেট হবে</flux:text>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-4 border-t border-zinc-400/25">
                <flux:button type="button" variant="ghost" wire:click="$set('showForm', false)">বাতিল</flux:button>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ $editingId ? 'আপডেট করুন' : 'সেভ করুন' }}</span>
                    <span wire:loading>প্রসেসিং...</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
