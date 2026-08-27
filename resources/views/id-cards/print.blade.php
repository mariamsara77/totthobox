<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $card->card_number }}</title>
    <style>
        @font-face {
            font-family: 'NotoSansBengali';
            src: url('{{ asset('fonts/NotoSansBengali-Regular.ttf') }}');
            font-weight: normal;
        }

        @font-face {
            font-family: 'NotoSansBengali';
            src: url('{{ asset('fonts/NotoSansBengali-Bold.ttf') }}');
            font-weight: bold;
        }

        @page {
            margin: 0;
            size: {{ $pageWidthMm }}mm {{ $pageHeightMm }}mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            width: {{ $pageWidthMm }}mm;
            height: {{ $pageHeightMm }}mm;
            font-family: 'NotoSansBengali', DejaVu Sans, sans-serif;
        }

        .card {
            position: relative;
            width: {{ $pageWidthMm }}mm;
            height: {{ $pageHeightMm }}mm;
            overflow: hidden;
            {{ $theme['bg'] }}
        }

        .abs {
            position: absolute;
        }

        .label {
            font-size: 6.5pt;
            letter-spacing: 0.5pt;
            text-transform: uppercase;
            opacity: 0.75;
        }

        .name {
            font-size: 13pt;
            font-weight: bold;
            line-height: 1.15;
        }

        .sub {
            font-size: 8pt;
            opacity: 0.85;
        }

        .meta-label {
            font-size: 6pt;
            opacity: 0.65;
        }

        .meta-value {
            font-family: monospace;
            font-size: 8.5pt;
        }

        .blood-badge {
            font-size: 8pt;
            font-weight: bold;
            padding: 2pt 6pt;
            border-radius: 4pt;
        }

        .logo-img {
            max-height: 100%;
            max-width: 100%;
        }
    </style>
</head>

<body>
    @php
        $ds = $card->design_settings ?? [];
        $orientation = $ds['orientation'] ?? 'horizontal';
        $primary = $ds['primary_color'] ?? '#4F46E5';
        $secondary = $ds['secondary_color'] ?? '#7C3AED';
        $tpl = $card->template_name ?? 'aurora';

        $photoSrc = $card->photo_url;
        $logoSrc = $card->logo_url;

        $lang = $card->language ?? 'both';
        $pick = fn($en, $bn) => match ($lang) {
            'en' => $en ?: $bn,
            'bn' => $bn ?: $en,
            default => $bn ?: $en,
        };
        $name = $pick($card->name_en, $card->name_bn);
        $subName = $lang === 'both' && $card->name_en && $card->name_bn ? $card->name_en : null;
        $designation = $pick($card->designation_en, $card->designation_bn);
        $organization = $card->organization_bn ?: $card->organization_en;

        $themes = [
            'aurora' => [
                'bg' => "background: linear-gradient(135deg, {$primary}, {$secondary});",
                'text' => '#ffffff',
                'accent' => 'rgba(255,255,255,0.25)',
            ],
            'corporate' => [
                'bg' => 'background: #ffffff; border: 0.5pt solid #e2e8f0;',
                'text' => '#1e293b',
                'accent' => '#f1f5f9',
            ],
            'neon' => ['bg' => 'background: #09090b;', 'text' => '#67e8f9', 'accent' => 'rgba(34,211,238,0.2)'],
            'royal' => [
                'bg' =>
                    'background: linear-gradient(145deg, #1a120b, #3d2b1f 60%, #1a120b); border: 0.5pt solid #d4a017;',
                'text' => '#fef3c7',
                'accent' => 'rgba(217,119,6,0.4)',
            ],
            'glass' => [
                'bg' => 'background: linear-gradient(135deg, #6366f1, #a855f7);',
                'text' => '#ffffff',
                'accent' => 'rgba(255,255,255,0.2)',
            ],
            'geometric' => ['bg' => 'background: #18181b;', 'text' => '#ffffff', 'accent' => 'rgba(99,102,241,0.35)'],
            'minimal' => [
                'bg' => 'background: #ffffff; border: 0.5pt solid #e4e4e7;',
                'text' => '#18181b',
                'accent' => '#f4f4f5',
            ],
            'split' => ['bg' => 'background: #ffffff;', 'text' => '#18181b', 'accent' => '#f4f4f5'],
            'vertical' => [
                'bg' => "background: linear-gradient(to bottom, {$primary}, {$secondary});",
                'text' => '#ffffff',
                'accent' => 'rgba(255,255,255,0.2)',
            ],
        ];
        $theme = $themes[$tpl] ?? $themes['aurora'];

        // CR80 স্ট্যান্ডার্ড সাইজ (mm)
        $pageWidthMm = $orientation === 'vertical' ? 53.98 : 85.6;
        $pageHeightMm = $orientation === 'vertical' ? 85.6 : 53.98;
    @endphp

    <div class="card" style="color: {{ $theme['text'] }};">

        @if ($orientation === 'horizontal')
            {{-- ===== HORIZONTAL LAYOUT (85.6mm x 53.98mm) — সব উপাদান absolute pin করা ===== --}}

            @if (($ds['show_logo'] ?? true) && $logoSrc)
                <div class="abs" style="top:3mm; left:4mm; height:7mm; width:26mm;">
                    <img src="{{ $logoSrc }}" class="logo-img" alt="Logo">
                </div>
            @endif

            <div class="abs label" style="top:12mm; left:4mm;">Identity Card</div>

            <div class="abs name" style="top:15mm; left:4mm; width:52mm;">{{ $name ?: '—' }}</div>

            @if ($subName)
                <div class="abs sub" style="top:21.5mm; left:4mm; width:52mm;">{{ $subName }}</div>
            @endif

            <div class="abs sub" style="top:34mm; left:4mm; width:52mm; font-weight:bold;">{{ $designation }}</div>
            <div class="abs sub" style="top:38mm; left:4mm; width:52mm;">{{ $organization }}</div>

            <div class="abs meta-label" style="top:45.5mm; left:4mm;">Card No</div>
            <div class="abs meta-value" style="top:47.5mm; left:4mm;">{{ $card->card_number }}</div>

            @if (($ds['show_photo'] ?? true) && $photoSrc)
                <div class="abs" style="top:3mm; right:4mm; width:16mm; height:20mm;">
                    <img src="{{ $photoSrc }}"
                        style="width:100%; height:100%; object-fit:cover; border-radius:2mm;" alt="Photo">
                </div>
            @endif

            @if (($ds['show_blood'] ?? true) && $card->blood_group)
                <div class="abs blood-badge" style="bottom:12mm; right:4mm; background: {{ $theme['accent'] }};">
                    {{ $card->blood_group }}</div>
            @endif

            @if ($ds['show_qr'] ?? true)
                <div class="abs" style="bottom:3mm; right:4mm; width:9mm; height:9mm;">
                    <img src="{{ $card->qrImageUrl(90) }}" style="width:100%; height:100%;" alt="QR">
                </div>
            @endif
        @else
            {{-- ===== VERTICAL LAYOUT (53.98mm x 85.6mm) ===== --}}

            @if (($ds['show_logo'] ?? true) && $logoSrc)
                <div class="abs" style="top:4mm; left:0; width:100%; height:8mm; text-align:center;">
                    <img src="{{ $logoSrc }}" style="max-height:100%; max-width:70%;" alt="Logo">
                </div>
            @endif

            @if (($ds['show_photo'] ?? true) && $photoSrc)
                <div class="abs" style="top:15mm; left:50%; margin-left:-11mm; width:22mm; height:26mm;">
                    <img src="{{ $photoSrc }}"
                        style="width:100%; height:100%; object-fit:cover; border-radius:2mm; border: 1pt solid rgba(255,255,255,0.5);"
                        alt="Photo">
                </div>
            @endif

            <div class="abs name" style="top:44mm; left:2mm; width:49.98mm; text-align:center;">{{ $name ?: '—' }}
            </div>
            <div class="abs sub" style="top:50mm; left:2mm; width:49.98mm; text-align:center;">{{ $designation }}
            </div>
            <div class="abs sub" style="top:54mm; left:2mm; width:49.98mm; text-align:center;">{{ $organization }}
            </div>

            <div class="abs" style="bottom:0; left:0; width:100%; height:12mm; background: rgba(0,0,0,0.2);"></div>

            <div class="abs meta-value" style="bottom:4mm; left:3mm;">{{ $card->card_number }}</div>

            @if (($ds['show_blood'] ?? true) && $card->blood_group)
                <div class="abs blood-badge"
                    style="bottom:3mm; right:{{ $ds['show_qr'] ?? true ? '15mm' : '3mm' }}; background: rgba(255,255,255,0.2);">
                    {{ $card->blood_group }}</div>
            @endif

            @if ($ds['show_qr'] ?? true)
                <div class="abs" style="bottom:2mm; right:2mm; width:10mm; height:10mm;">
                    <img src="{{ $card->qrImageUrl(90) }}" style="width:100%; height:100%;" alt="QR">
                </div>
            @endif
        @endif
    </div>
</body>

</html>
