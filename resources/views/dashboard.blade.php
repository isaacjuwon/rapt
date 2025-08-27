<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <!-- Welcome Section -->
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Welcome back, {{ auth()->user()->name }}!
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                Here's a quick overview of your account.
            </p>
        </div>

        <!-- Main Dashboard Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="lg:col-span-2 flex flex-col gap-6">
                <livewire:components.wallet-card />

                <livewire:components.recent-transactions-card />
            </div>

            <!-- Right Column (Quick Actions) -->
            <div class="flex flex-col gap-6">
                <flux:card class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Links</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <x-dashboard.quick-link :href="route('services.airtime')" icon="device-phone-mobile" label="Buy Airtime" />
                        <x-dashboard.quick-link :href="route('services.data')" icon="wifi" label="Buy Data" />
                        <x-dashboard.quick-link :href="route('services.cable')" icon="tv" label="Pay TV" />
                        <x-dashboard.quick-link :href="route('services.electricity')" icon="bolt" label="Pay Electricity" />
                        <x-dashboard.quick-link :href="route('services.education')" icon="academic-cap" label="Buy Education Pin" />
                    </div>
                </flux:card>
            </div>
        </div>
    </div>
</x-layouts.app>
