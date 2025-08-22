<div class="flex gap-8 max-md:flex-col">
    <!-- Settings Navigation -->
    <div class="w-48 flex-shrink-0">
        <flux:navlist variant="outline">
            @if(request()->is('admin/*'))
            <!-- Admin Settings Navigation -->
            <flux:navlist.item :href="route('admin.settings.general')" :current="request()->routeIs('admin.settings.general')" wire:navigate>General</flux:navlist.item>
            <flux:navlist.item :href="route('admin.settings.api')" :current="request()->routeIs('admin.settings.api')" wire:navigate>API</flux:navlist.item>
            <flux:navlist.item :href="route('admin.settings.security')" :current="request()->routeIs('admin.settings.security')" wire:navigate>Security</flux:navlist.item>
            <flux:navlist.item :href="route('admin.settings.mail')" :current="request()->routeIs('admin.settings.mail')" wire:navigate>Mail</flux:navlist.item>
            <flux:navlist.item :href="route('admin.settings.notifications')" :current="request()->routeIs('admin.settings.notifications')" wire:navigate>Notifications</flux:navlist.item>
            <flux:navlist.item :href="route('admin.settings.shares')" :current="request()->routeIs('admin.settings.shares')" wire:navigate>Shares</flux:navlist.item>
            <flux:navlist.item :href="route('admin.settings.loans')" :current="request()->routeIs('admin.settings.loans')" wire:navigate>Loans</flux:navlist.item>
            @else
            <!-- User Settings Navigation -->
            <flux:navlist.item :href="route('settings.profile')" wire:navigate>Profile</flux:navlist.item>
            <flux:navlist.item :href="route('settings.password')" wire:navigate>Password</flux:navlist.item>
            <flux:navlist.item :href="route('settings.appearance')" wire:navigate>Appearance</flux:navlist.item>
            @endif
        </flux:navlist>
    </div>

    <!-- Settings Content -->
    <div class="flex-1 min-w-0">
        {{ $slot }}
    </div>
</div>