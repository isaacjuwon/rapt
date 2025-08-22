<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-6">
        <!-- Welcome Section -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Welcome back, {{ auth()->user()->name }}!
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Here's what's happening with your account today.
                </p>
            </div>
            <flux:button variant="primary">
                <flux:icon name="bell" class="!size-4" />
                Notifications
            </flux:button>
        </div>

        <!-- Main Dashboard Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Wallet Card (Takes 2 columns on large screens) -->
            <div class="lg:col-span-2">
                <livewire:components.wallet-card />
            </div>

            <!-- Quick Stats -->
            <div class="space-y-4">
                <flux:card class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Transactions</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">24</p>
                        </div>
                        <flux:icon name="chart-bar" class="!size-8 text-blue-600" />
                    </div>
                </flux:card>

                <flux:card class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Active Services</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">3</p>
                        </div>
                        <flux:icon name="wifi" class="!size-8 text-green-600" />
                    </div>
                </flux:card>

                <flux:card class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Reward Points</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">1,250</p>
                        </div>
                        <flux:icon name="star" class="!size-8 text-yellow-600" />
                    </div>
                </flux:card>
            </div>
        </div>

        <!-- Recent Activity Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <flux:card class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Transactions</h3>
                    <flux:button variant="ghost" size="sm">View All</flux:button>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between py-2">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                <flux:icon name="arrow-down" class="!size-4 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Deposit</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">2 hours ago</p>
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-green-600">+₦5,000.00</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                <flux:icon name="device-phone-mobile" class="!size-4 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Airtime Purchase</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Yesterday</p>
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-red-600">-₦1,000.00</span>
                    </div>
                </div>
            </flux:card>

            <flux:card class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <flux:button variant="outline" class="h-20 flex flex-col items-center justify-center space-y-2">
                        <flux:icon name="device-phone-mobile" class="!size-6" />
                        <span class="text-sm">Buy Airtime</span>
                    </flux:button>
                    <flux:button variant="outline" class="h-20 flex flex-col items-center justify-center space-y-2">
                        <flux:icon name="wifi" class="!size-6" />
                        <span class="text-sm">Buy Data</span>
                    </flux:button>
                    <flux:button variant="outline" class="h-20 flex flex-col items-center justify-center space-y-2">
                        <flux:icon name="tv" class="!size-6" />
                        <span class="text-sm">Pay TV</span>
                    </flux:button>
                    <flux:button variant="outline" class="h-20 flex flex-col items-center justify-center space-y-2">
                        <flux:icon name="bolt" class="!size-6" />
                        <span class="text-sm">Pay Electricity</span>
                    </flux:button>
                </div>
            </flux:card>
        </div>
    </div>
</x-layouts.app>