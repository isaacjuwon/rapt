<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            <x-app-logo />
        </a>

        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Dashboard')" class="grid">
                <flux:navlist.item icon="home" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>{{ __('Admin Dashboard') }}</flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group :heading="__('Resources')" class="grid">
                <flux:navlist.item icon="phone" :href="route('admin.airtime.index')" :current="request()->routeIs('admin.airtime.index')" wire:navigate>{{ __('Airtime') }}</flux:navlist.item>
                <flux:navlist.item icon="wifi" :href="route('admin.data.index')" :current="request()->routeIs('admin.data.index')" wire:navigate>{{ __('Data Plans') }}</flux:navlist.item>
                <flux:navlist.item icon="tv" :href="route('admin.cable.index')" :current="request()->routeIs('admin.cable.index')" wire:navigate>{{ __('Cable Plans') }}</flux:navlist.item>
                <flux:navlist.item icon="academic-cap" :href="route('admin.education.index')" :current="request()->routeIs('admin.education.index')" wire:navigate>{{ __('Education Plans') }}</flux:navlist.item>
                <flux:navlist.item icon="bolt" :href="route('admin.electricity.index')" :current="request()->routeIs('admin.electricity.index')" wire:navigate>{{ __('Electricity Plans') }}</flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group :heading="__('Management')" class="grid">
                <flux:navlist.item icon="users" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.index')" wire:navigate>{{ __('Users') }}</flux:navlist.item>
                <flux:navlist.item icon="key" :href="route('admin.permissions.index')" :current="request()->routeIs('admin.permissions.index')" wire:navigate>{{ __('Permissions') }}</flux:navlist.item>
                <flux:navlist.item icon="user-group" :href="route('admin.roles.index')" :current="request()->routeIs('admin.roles.index')" wire:navigate>{{ __('Roles') }}</flux:navlist.item>
                <flux:navlist.item icon="tag" :href="route('admin.brands.index')" :current="request()->routeIs('admin.brands.index')" wire:navigate>{{ __('Brands') }}</flux:navlist.item>
                <flux:navlist.item icon="currency-dollar" :href="route('admin.shares.index')" :current="request()->routeIs('admin.shares.index')" wire:navigate>{{ __('Shares') }}</flux:navlist.item>
                <flux:navlist.item icon="document-text" :href="route('admin.transactions.index')" :current="request()->routeIs('admin.transactions.index')" wire:navigate>{{ __('Transactions') }}</flux:navlist.item>
                <flux:navlist.item icon="credit-card" :href="route('admin.payments.index')" :current="request()->routeIs('admin.payments.index')" wire:navigate>{{ __('Payments') }}</flux:navlist.item>
                <flux:navlist.item icon="document-text" :href="route('admin.loans.index')" :current="request()->routeIs('admin.loans.index')" wire:navigate>{{ __('Loans') }}</flux:navlist.item>
                <flux:navlist.item icon="clipboard-document-list" :href="route('admin.log.logviewer')" :current="request()->routeIs('admin.log.logviewer')" wire:navigate>{{ __('Log Viewer') }}</flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group :heading="__('System')" class="grid">
                <flux:navlist.item icon="cog" :href="route('admin.settings.general')" :current="request()->routeIs('admin.settings.general')" wire:navigate>{{ __('General Settings') }}</flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />

        <flux:navlist variant="outline">
            <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                {{ __('Repository') }}
            </flux:navlist.item>

            <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                {{ __('Documentation') }}
            </flux:navlist.item>
        </flux:navlist>

        <!-- Desktop User Menu -->
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
            <flux:profile
                :name="auth()->user()->name"
                :initials="auth()->user()->initials()"
                icon:trailing="chevrons-up-down" />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile
                :initials="auth()->user()->initials()"
                icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>