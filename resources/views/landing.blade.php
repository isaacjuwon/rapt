<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-900">
    <!-- Navigation -->
    <nav class="fixed top-0 w-full bg-white/90 dark:bg-zinc-900/90 backdrop-blur-md border-b border-gray-200 dark:border-zinc-800 z-50">
        <flux:container class="flex h-16 items-center justify-between">
            <a href="{{ route('home') }}" wire:navigate class="flex items-center space-x-2">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="w-8 h-8">
                <span class="font-semibold text-gray-900 dark:text-white">Rapt</span>
            </a>

            <div class="flex items-center space-x-4">
                @auth
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center space-x-2 px-4 py-2 rounded-lg bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 dark:hover:bg-zinc-700 transition-colors">
                    <div class="w-6 h-6 rounded-full bg-gradient-to-r from-blue-500 to-purple-600"></div>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Dashboard</span>
                </a>
                @else
                <flux:button variant="ghost" size="sm" href="{{ route('login') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    Log In
                </flux:button>
                <flux:button variant="primary" size="sm" href="{{ route('register') }}" class="animate-pulse-slow">
                    Get Started
                </flux:button>
                @endauth
            </div>
        </flux:container>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-zinc-900 dark:via-zinc-800 dark:to-zinc-900">
        <!-- Animated background elements -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-10 w-96 h-96 bg-blue-400 rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-float"></div>
            <div class="absolute top-40 right-10 w-96 h-96 bg-purple-400 rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-float animate-delay-2000"></div>
            <div class="absolute bottom-20 left-1/2 w-96 h-96 bg-indigo-400 rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-float animate-delay-4000"></div>
        </div>

        <flux:container class="relative text-center">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-6xl md:text-7xl font-bold tracking-tight mb-6 animate-fade-in-up">
                    <span class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 bg-clip-text text-transparent">
                        Recharge Made Simple
                    </span>
                </h1>

                <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-400 mb-12 max-w-2xl mx-auto animate-fade-in-up animate-delay-200">
                    All your recharge needs in one place. Fast, secure, and reliable.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center animate-fade-in-up animate-delay-400">
                    <flux:button variant="primary" size="lg" href="{{ route('register') }}" class="px-8 py-4 text-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
                        Get Started Now
                    </flux:button>
                    <flux:button variant="ghost" size="lg" href="#services" class="px-8 py-4 text-lg font-semibold border-2 border-gray-300 dark:border-zinc-600 hover:border-gray-400 dark:hover:border-zinc-500 transition-all duration-300">
                        Explore Services
                    </flux:button>
                </div>

                <!-- Trust indicators -->
                <div class="mt-16 flex justify-center items-center space-x-8 animate-fade-in-up animate-delay-600">
                    <div class="flex items-center space-x-2 text-gray-600 dark:text-gray-400">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm">Secure Payments</span>
                    </div>
                    <div class="flex items-center space-x-2 text-gray-600 dark:text-gray-400">
                        <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm">Instant Delivery</span>
                    </div>
                    <div class="flex items-center space-x-2 text-gray-600 dark:text-gray-400">
                        <svg class="w-5 h-5 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm">24/7 Support</span>
                    </div>
                </div>
            </div>
        </flux:container>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-24 bg-white dark:bg-zinc-900">
        <flux:container>
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4 text-gray-900 dark:text-white">
                    Our Services
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    Everything you need for seamless digital transactions
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach([
                ['icon' => '📱', 'title' => 'Airtime', 'desc' => 'Instant topup for all networks', 'gradient' => 'from-blue-500 to-cyan-500'],
                ['icon' => '🌐', 'title' => 'Data', 'desc' => 'Affordable data plans', 'gradient' => 'from-green-500 to-emerald-500'],
                ['icon' => '📺', 'title' => 'Cable TV', 'desc' => 'Renew subscriptions instantly', 'gradient' => 'from-purple-500 to-pink-500'],
                ['icon' => '⚡', 'title' => 'Electricity', 'desc' => 'Pay bills with ease', 'gradient' => 'from-yellow-500 to-orange-500'],
                ['icon' => '🎓', 'title' => 'Education', 'desc' => 'Exam & result pins', 'gradient' => 'from-indigo-500 to-blue-500'],
                ['icon' => '💰', 'title' => 'Wallet', 'desc' => 'Secure funding', 'gradient' => 'from-pink-500 to-rose-500']
                ] as $index => $service)
                <div class="group relative p-6 bg-white dark:bg-zinc-800 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-gray-100 dark:border-zinc-700 animate-fade-in-up" style="animation-delay: {{ $index * 100 }}ms">
                    <div class="w-16 h-16 bg-gradient-to-r {{ $service['gradient'] }} rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition-transform duration-300">
                        {{ $service['icon'] }}
                    </div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-900 dark:text-white">{{ $service['title'] }}</h3>
                    <p class="text-gray-600 dark:text-gray-400">{{ $service['desc'] }}</p>
                    <div class="absolute inset-0 bg-gradient-to-r {{ $service['gradient'] }} rounded-2xl opacity-0 group-hover:opacity-5 transition-opacity duration-300"></div>
                </div>
                @endforeach
            </div>
        </flux:container>
    </section>

    <!-- Features Section -->
    <section class="py-24 bg-gray-50 dark:bg-zinc-800">
        <flux:container>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div class="space-y-8">
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white">
                        Why Choose Rapt?
                    </h2>

                    <div class="space-y-6">
                        @foreach([
                        ['title' => 'Lightning Fast', 'desc' => 'Instant delivery guaranteed', 'icon' => '⚡'],
                        ['title' => 'Bank-Level Security', 'desc' => 'Your transactions are protected', 'icon' => '🔒'],
                        ['title' => 'Best Prices', 'desc' => 'Competitive rates always', 'icon' => '💎'],
                        ['title' => '24/7 Support', 'desc' => 'We\'re always here to help', 'icon' => '🎯']
                        ] as $feature)
                        <div class="flex items-start space-x-4 group">
                            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center text-xl group-hover:scale-110 transition-transform duration-300">
                                {{ $feature['icon'] }}
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-1 text-gray-900 dark:text-white">{{ $feature['title'] }}</h3>
                                <p class="text-gray-600 dark:text-gray-400">{{ $feature['desc'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="relative">
                    <div class="w-full h-96 bg-gradient-to-br from-blue-500 to-purple-600 rounded-3xl shadow-2xl flex items-center justify-center">
                        <div class="text-center text-white p-8">
                            <div class="text-6xl mb-4">🚀</div>
                            <h3 class="text-2xl font-bold mb-2">Ready to get started?</h3>
                            <p class="text-lg opacity-90 mb-6">Join thousands of satisfied users today</p>
                            <flux:button variant="secondary" size="lg" href="{{ route('register') }}" class="bg-white text-blue-600 hover:bg-gray-100 border-0">
                                Sign Up Now
                            </flux:button>
                        </div>
                    </div>
                </div>
            </div>
        </flux:container>
    </section>

    <!-- CTA Section -->
    <section class="py-24 bg-gradient-to-r from-blue-600 to-purple-600">
        <flux:container>
            <div class="text-center">
                <h2 class="text-4xl md:text-5xl font-bold mb-6 text-white">
                    Start Your Journey Today
                </h2>
                <p class="text-xl text-white/90 mb-8 max-w-2xl mx-auto">
                    Experience the future of recharge services. Fast, secure, and reliable.
                </p>
                <flux:button variant="secondary" size="lg" href="{{ route('register') }}" class="bg-white text-blue-600 hover:bg-gray-100 border-0 px-12 py-4 text-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
                    Get Started Free
                </flux:button>
            </div>
        </flux:container>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <flux:container>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="w-8 h-8">
                        <span class="font-semibold text-lg">Rapt</span>
                    </div>
                    <p class="text-gray-400">
                        Your trusted partner for all recharge services.
                    </p>
                </div>

                <div>
                    <h3 class="font-semibold mb-4">Services</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">Airtime</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Data</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Cable TV</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Electricity</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-semibold mb-4">Company</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">About Us</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Contact</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Terms of Service</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-semibold mb-4">Support</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">Help Center</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">FAQ</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Live Chat</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Email Support</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 Rapt. All rights reserved.</p>
            </div>
        </flux:container>
    </footer>
</body>

</html>