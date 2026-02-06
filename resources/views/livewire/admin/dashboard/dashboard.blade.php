<div class="space-y-6">
    <flux:header>
        <flux:heading class="" size="xl" level="1">Admin Dashboard</flux:heading>
        <flux:text class="">Monitor your site analytics in real-time</flux:text>
    </flux:header>
    <livewire:admin.dashboard.real-time-visitors lazy>
        <livewire:admin.dashboard.analytics-chart lazy>

            <livewire:admin.dashboard.visitor-dashboard lazy>
                @push('scripts')
                    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
                @endpush

</div>