@can('view-dashboard')
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.dashboard*')" icon="chart-bar"
        heading="{{ __('Admin Dashboard') }}" class="grid">
        <flux:sidebar.item icon="home" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')"
            wire:navigate.hover>{{ __('Dashboard') }}</flux:sidebar.item>
        <flux:sidebar.item icon="home" :href="route('admin.dashboard.session')"
            :current="request()->routeIs('admin.dashboard.session')" wire:navigate.hover>{{ __('Session Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="home" :href="route('admin.dashboard.missing-data')"
            :current="request()->routeIs('admin.dashboard.missing-data')" wire:navigate.hover>{{ __('Missing Data Manage') }}
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
        @endcan

        @can('manage-roles')
            <flux:sidebar.item icon="user-group" :href="route('admin.users.role.manage')"
                :current="request()->routeIs('admin.users.role.manage')" wire:navigate.hover>{{ __('Manage Users Role') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="user-group" :href="route('admin.users.permission.manage')"
                :current="request()->routeIs('admin.users.permission.manage')" wire:navigate.hover>
                {{ __('Manage Users Permission') }}
            </flux:sidebar.item>
        @endcan
    </flux:sidebar.group>
@endif

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
        <flux:sidebar.item icon="user-circle" :href="route('admin.bangladesh.minister')"
            :current="request()->routeIs('admin.bangladesh.minister')" wire:navigate.hover>{{ __('Minister Manage') }}
        </flux:sidebar.item>
    </flux:sidebar.group>
@endcan

@can('manage-islam')
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.islam.*')" icon="moon" heading="{{ __('Islam') }}"
        class="grid">
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
        <flux:sidebar.item icon="home" :href="route('admin.health.food')" :current="request()->routeIs('admin.health.food')"
            wire:navigate.hover>{{ __('Food Manage') }}
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

@can('manage-education')
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.education.*')" icon="academic-cap"
        heading="{{ __('Education') }}" class="grid">
        <flux:sidebar.item icon="building-library" :href="route('admin.education.class')"
            :current="request()->routeIs('admin.education.class')" wire:navigate.hover>{{ __('Class Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="book-open" :href="route('admin.education.subject')"
            :current="request()->routeIs('admin.education.subject')" wire:navigate.hover>{{ __('Subject Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="question-mark-circle" :href="route('admin.education.questions')"
            :current="request()->routeIs('admin.education.questions')" wire:navigate.hover>{{ __('Question Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="clipboard-document" :href="route('admin.education.test')"
            :current="request()->routeIs('admin.education.test')" wire:navigate.hover>{{ __('Test Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="clipboard-document-list" :href="route('admin.education.test-questions')"
            :current="request()->routeIs('admin.education.test-questions')" wire:navigate.hover>
            {{ __('Test Question Manage') }}
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
            :current="request()->routeIs('admin.contact.contact-number')" wire:navigate.hover>{{ __('Number Manage') }}
        </flux:sidebar.item>
    </flux:sidebar.group>
@endcan

@can('manage-signs')
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.sign.*')" icon="exclamation-triangle"
        heading="{{ __('Sign') }}" class="grid">
        <flux:sidebar.item icon="folder" :href="route('admin.sign.category-manage')"
            :current="request()->routeIs('admin.sign.category-manage')" wire:navigate.hover>{{ __('Category Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="home" :href="route('admin.sign.manage')" :current="request()->routeIs('admin.sign.manage')"
            wire:navigate.hover>{{ __('Sign Manage') }}
        </flux:sidebar.item>
    </flux:sidebar.group>
@endcan

@can('manage-buysell')
    <flux:sidebar.group expandable :expanded="request()->routeIs('admin.buysell.*')" icon="shopping-cart"
        heading="{{ __('Buy & Sell') }}" class="grid">
        <flux:sidebar.item icon="folder" :href="route('admin.buysell.category-manage')"
            :current="request()->routeIs('admin.buysell.category-manage')" wire:navigate.hover>
            {{ __('Category Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="cube" :href="route('admin.buysell.item-manage')"
            :current="request()->routeIs('admin.buysell.item-manage')" wire:navigate.hover>{{ __('Item Manage') }}
        </flux:sidebar.item>
        <flux:sidebar.item icon="arrow-path" :href="route('admin.buysell.manage')"
            :current="request()->routeIs('admin.buysell.manage')" wire:navigate.hover>{{ __('Buy & Sell Manage') }}
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