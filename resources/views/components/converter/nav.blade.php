<flux:tabs variant="segmented" class="mb-8">
    @foreach ([
        'file.converter.image' => ['Image', 'photo'],
        'file.converter.document' => ['Document', 'document-text'],
        'file.converter.media' => ['Video / Audio', 'film'],
        'file.converter.file-data' => ['Data (JSON/XML)', 'code-bracket'],
    ] as $route => [$label, $icon])
        <flux:tab href="{{ route($route) }}" :icon="$icon" :current="request()->routeIs($route)">
            {{ $label }}
        </flux:tab>
    @endforeach
</flux:tabs>
