@can('view-dashboard')
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.dashboard*')" icon="chart-bar"
        heading="{{ __('Admin Dashboard') }}" class="grid">
        <flux:sidebar.item icon="home" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')"
            wire:navigate.hover>{{ __('Dashboard') }}</flux:sidebar.item>
        <flux:sidebar.item icon="home" :href="route('admin.dashboard.session')"
            :current="request()->routeIs('admin.dashboard.session')" wire:navigate.hover>{{ __('Session Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="home" :href="url('/pulse')" :current="request()->routeIs('admin.dashboard.pulse')"
            wire:navigate.hover>{{ __('Pule Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="home" :href="route('admin.dashboard.missing-data')"
            :current="request()->routeIs('admin.dashboard.missing-data')" wire:navigate.hover>
            {{ __('Missing Data Manage') }}
        </flux:sidebar.item>

        @can('system-manager')
            <flux:sidebar.item icon="command-line" :href="route('admin.dashboard.system-manager.terminal-command')"
                :current="request()->routeIs('admin.dashboard.system-manager.terminal-command')" wire:navigate.hover>
                {{ __('Terminal Command Manager') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="circle-stack" :href="route('admin.dashboard.system-manager.database-monitor')"
                :current="request()->routeIs('admin.dashboard.system-manager.database-monitor')" wire:navigate.hover>
                {{ __('Database Monitor') }}
            </flux:sidebar.item>
        @endcan
        @can('google indexing request')
            <flux:sidebar.item icon="home" :href="route('admin.dashboard.google-indexing')"
                :current="request()->routeIs('admin.dashboard.google-indexing')" wire:navigate.hover>
                {{ __('Google Indexing Request') }}
            </flux:sidebar.item>
        @endcan
    </flux:sidebar.group>

@endcan

@can('view-analytics')
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.analytics.*')" icon="users"
        heading="{{ __('Analytics') }}" class="grid">
        <flux:sidebar.item icon="user-group" :href="route('admin.analytics.page-performance')"
            :current="request()->routeIs('admin.analytics.page-performance')" wire:navigate.hover>
            {{ __('Page Analytics') }}
        </flux:sidebar.item>
    </flux:sidebar.group>
@endcan



@if (auth()->user()->can('manage-users') || auth()->user()->can('manage-roles'))
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.users.*')" icon="users"
        heading="{{ __('User Manage') }}" class="grid">

        @can('manage-users')
            <flux:sidebar.item icon="user-group" :href="route('admin.users.manage')"
                :current="request()->routeIs('admin.users.manage')" wire:navigate.hover>{{ __('Manage Users') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="user-group" :href="route('admin.users.activity.all')"
                :current="request()->routeIs('admin.users.activity.all')" wire:navigate.hover>
                {{ __('All Users Activity') }}
            </flux:sidebar.item>
        @endcan

        @can('manage-roles')
            <flux:sidebar.item icon="user-group" :href="route('admin.users.role.manage')"
                :current="request()->routeIs('admin.users.role.manage')" wire:navigate.hover>
                {{ __('Manage Users Role') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="user-group" :href="route('admin.users.permission.manage')"
                :current="request()->routeIs('admin.users.permission.manage')" wire:navigate.hover>
                {{ __('Manage Users Permission') }}
            </flux:sidebar.item>
        @endcan
    </flux:sidebar.group>
@endif

@if (auth()->user()->can('manage-apps') || auth()->user()->can('manage-roles'))
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.apps.*')" icon="users"
        heading="{{ __('App Manage') }}" class="grid">
        @can('manage-apps')
            <flux:sidebar.item icon="user-group" :href="route('admin.apps.manage')"
                :current="request()->routeIs('admin.apps.manage')" wire:navigate.hover>
                {{ __('Manage Apps & Resources') }}
            </flux:sidebar.item>
        @endcan
    </flux:sidebar.group>
@endif

@can('manage-ai-generator')
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.ai-generator.*')" icon="flag"
        heading="{{ __('AI Generators') }}" class="grid">
        <flux:sidebar.item icon="document-text" :href="route('admin.ai-generator.all-models')"
            :current="request()->routeIs('admin.ai-generator.all-models')" wire:navigate.hover>
            {{ __('All Models') }}
        </flux:sidebar.item>

    </flux:sidebar.group>
@endcan
@can('manage-bangladesh')
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.bangladesh.*')" icon="flag"
        heading="{{ __('Bangladesh Data') }}" class="grid">
        <flux:sidebar.item icon="document-text" :href="route('admin.bangladesh.introduction')"
            :current="request()->routeIs('admin.bangladesh.introduction')" wire:navigate.hover>
            {{ __('Introduction Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="home" :href="route('admin.bangladesh.tourism')"
            :current="request()->routeIs('admin.bangladesh.tourism')" wire:navigate.hover>{{ __('Tourism Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="clock" :href="route('admin.bangladesh.history')"
            :current="request()->routeIs('admin.bangladesh.history')" wire:navigate.hover>{{ __('History Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="building-office" :href="route('admin.bangladesh.establishment')"
            :current="request()->routeIs('admin.bangladesh.establishment')" wire:navigate.hover>
            {{ __('Establishment Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="calendar" :href="route('admin.bangladesh.holiday')"
            :current="request()->routeIs('admin.bangladesh.holiday')" wire:navigate.hover>{{ __('Holiday Manage') }}
        </flux:sidebar.item>
    </flux:sidebar.group>
@endcan

@can('manage-location')
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.location.*')" icon="map"
        heading="{{ __('Location Management') }}" class="grid">

        <flux:sidebar.item icon="globe-alt" :href="route('admin.location.division')"
            :current="request()->routeIs('admin.location.division')" wire:navigate.hover>
            {{ __('Division Manage') }}
        </flux:sidebar.item>

        <flux:sidebar.item icon="building-library" :href="route('admin.location.district')"
            :current="request()->routeIs('admin.location.district')" wire:navigate.hover>
            {{ __('District Manage') }}
        </flux:sidebar.item>

        <flux:sidebar.item icon="building-office-2" :href="route('admin.location.thana')"
            :current="request()->routeIs('admin.location.thana')" wire:navigate.hover>
            {{ __('Thana Manage') }}
        </flux:sidebar.item>

    </flux:sidebar.group>
@endcan

@can('manage-person')
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.person.*')" icon="users"
        heading="{{ __('People Management') }}">

        {{-- Master Profile --}}
        <flux:sidebar.item icon="user-group" :href="route('admin.person.manage')"
            :current="request()->routeIs('admin.person.manage')" wire:navigate.hover>
            {{ __('All People') }}
        </flux:sidebar.item>

        {{-- Categories --}}
        <flux:sidebar.item icon="tag" :href="route('admin.person.category')"
            :current="request()->routeIs('admin.person.category')" wire:navigate.hover>
            {{ __('Categories') }}
        </flux:sidebar.item>

        {{-- Positions --}}
        <flux:sidebar.item icon="briefcase" :href="route('admin.person.position')"
            :current="request()->routeIs('admin.person.position')" wire:navigate.hover>
            {{ __('Positions') }}
        </flux:sidebar.item>

        {{-- Role History --}}
        <flux:sidebar.item icon="clock" :href="route('admin.person.role-history')"
            :current="request()->routeIs('admin.person.role-history')" wire:navigate.hover>
            {{ __('Role History') }}
        </flux:sidebar.item>

    </flux:sidebar.group>
@endcan

@can('manage-islam')
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.islam.*')" icon="moon"
        heading="{{ __('Islam') }}" class="grid">
        <flux:sidebar.item icon="book-open" :href="route('admin.islam.basicislam')"
            :current="request()->routeIs('admin.islam.basicislam')" wire:navigate.hover>{{ __('Basic Islam Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="hand-raised" :href="route('admin.islam.dowa')"
            :current="request()->routeIs('admin.islam.dowa')" wire:navigate.hover>{{ __('Dowa Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="bookmark" :href="route('admin.islam.para')"
            :current="request()->routeIs('admin.islam.para')" wire:navigate.hover>{{ __('Para Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="document" :href="route('admin.islam.surah')"
            :current="request()->routeIs('admin.islam.surah')" wire:navigate.hover>{{ __('Surah Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="academic-cap" :href="route('admin.islam.quran')"
            :current="request()->routeIs('admin.islam.quran')" wire:navigate.hover>{{ __('Quran Manage') }}
        </flux:sidebar.item>
    </flux:sidebar.group>
@endcan

@can('manage-health')
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.health.*')" icon="heart"
        heading="{{ __('Health') }}" class="grid">
        <flux:sidebar.item icon="tag" :href="route('admin.health.food.category')"
            :current="request()->routeIs('admin.health.food.category')" wire:navigate.hover>
            {{ __('Food Category Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="home" :href="route('admin.health.food')"
            :current="request()->routeIs('admin.health.food')" wire:navigate.hover>{{ __('Food Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="beaker" :href="route('admin.health.vitamins')"
            :current="request()->routeIs('admin.health.vitamins')" wire:navigate.hover>{{ __('Vitamins Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="cube" :href="route('admin.health.nutrient')"
            :current="request()->routeIs('admin.health.nutrient')" wire:navigate.hover>{{ __('Nutrient Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="link" :href="route('admin.health.food.nutrient')"
            :current="request()->routeIs('admin.health.food.nutrient')" wire:navigate.hover>
            {{ __('Food Nutrient Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="shield-check" :href="route('admin.health.basic-health')"
            :current="request()->routeIs('admin.health.basic-health')" wire:navigate.hover>{{ __('Basic Health') }}
        </flux:sidebar.item>
    </flux:sidebar.group>
@endcan

@can('manage-contacts')
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.contact.*')" icon="phone"
        heading="{{ __('Contact') }}" class="grid">
        <flux:sidebar.item icon="folder" :href="route('admin.contact.contact-category')"
            :current="request()->routeIs('admin.contact.contact-category')" wire:navigate.hover>
            {{ __('Category Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="phone" :href="route('admin.contact.contact-number')"
            :current="request()->routeIs('admin.contact.contact-number')" wire:navigate.hover>
            {{ __('Number Manage') }}
        </flux:sidebar.item>
    </flux:sidebar.group>
@endcan

@can('manage-signs')
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.sign.*')" icon="exclamation-triangle"
        heading="{{ __('Sign') }}" class="grid">
        <flux:sidebar.item icon="folder" :href="route('admin.sign.category-manage')"
            :current="request()->routeIs('admin.sign.category-manage')" wire:navigate.hover>
            {{ __('Category Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="home" :href="route('admin.sign.manage')"
            :current="request()->routeIs('admin.sign.manage')" wire:navigate.hover>{{ __('Sign Manage') }}
        </flux:sidebar.item>
    </flux:sidebar.group>
@endcan


@can('manage-excel')
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.excel.*')" icon="table-cells"
        heading="{{ __('Excel Expert') }}" class="grid">

        {{-- এক্সেল টিউটোরিয়াল ও ফর্মুলা ম্যানেজমেন্ট --}}
        <flux:sidebar.item icon="document-text" :href="route('admin.excel.formula-manage')"
            :current="request()->routeIs('admin.excel.formula-manage')" wire:navigate.hover>
            {{ __('Manage Tutorials') }}
        </flux:sidebar.item>

        {{-- ভবিষ্যতে যদি আরও রাউট যোগ করেন, যেমন ক্যাটেগরি বা প্র্যাকটিস ফাইল --}}
        {{-- <flux:sidebar.item icon="academic-cap" :href="route('admin.excel.category')" ...> --}}
    </flux:sidebar.group>
@endcan
@can('manage-news')
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.news.*')" icon="newspaper"
        heading="{{ __('News Headlines') }}" class="grid">

        {{-- এক্সেল টিউটোরিয়াল ও ফর্মুলা ম্যানেজমেন্ট --}}
        <flux:sidebar.item icon="document-text" :href="route('admin.news.headlines-manage')"
            :current="request()->routeIs('admin.news.headlines-manage')" wire:navigate.hover>
            {{ __('Manage Headlines') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="document-text" :href="route('admin.news.headlines-dashboard')"
            :current="request()->routeIs('admin.news.headlines-dashboard')" wire:navigate.hover>
            {{ __('Headlines Dashboard') }}
        </flux:sidebar.item>

        {{-- ভবিষ্যতে যদি আরও রাউট যোগ করেন, যেমন ক্যাটেগরি বা প্র্যাকটিস ফাইল --}}
        {{-- <flux:sidebar.item icon="academic-cap" :href="route('admin.excel.category')" ...> --}}
    </flux:sidebar.group>
@endcan
@can('manage-blogs')
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.blogs.*')" icon="newspaper"
        heading="{{ __('Blogs') }}" class="grid">

        <flux:sidebar.item icon="document-text" :href="route('admin.blogs.live-matches')"
            :current="request()->routeIs('admin.blogs.live-matches')" wire:navigate.hover>
            {{ __('Manage live TV') }}
        </flux:sidebar.item>
    </flux:sidebar.group>
@endcan
@can('manage-messages')
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.messages.*')" icon="newspaper"
        heading="{{ __('Messages') }}" class="grid">

        <flux:sidebar.item icon="document-text" :href="route('admin.messages.messages-manager')"
            :current="request()->routeIs('admin.messages.messages-manager')" wire:navigate.hover>
            {{ __('Messages Manage') }}
        </flux:sidebar.item>
    </flux:sidebar.group>
@endcan
@can('manage-notification')
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.notification.*')" icon="newspaper"
        heading="{{ __('Notification') }}" class="grid">

        <flux:sidebar.item icon="document-text" :href="route('admin.notification.notification-manager')"
            :current="request()->routeIs('admin.notification.notification-manager')" wire:navigate.hover>
            {{ __('Notification Manage') }}
        </flux:sidebar.item>
    </flux:sidebar.group>
@endcan
