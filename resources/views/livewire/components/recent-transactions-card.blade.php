<?php

use Livewire\Volt\Component;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public Collection $transactions;

    public function mount(): void
    {
        $this->transactions = Transaction::where('user_id', Auth::id())
            ->with('transactable')
            ->latest()
            ->take(5)
            ->get();
    }

    public function placeholder()
    {
        return <<<'HTML'
            <flux:card class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Transactions</h3>
                    <div class="h-8 w-20 bg-gray-200 dark:bg-gray-700 rounded-md"></div>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between py-2 animate-pulse">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                            <div class="space-y-2">
                                <div class="h-4 w-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                <div class="h-3 w-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
                            </div>
                        </div>
                        <div class="h-5 w-12 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    </div>
                    <div class="flex items-center justify-between py-2 animate-pulse">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                            <div class="space-y-2">
                                <div class="h-4 w-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                <div class="h-3 w-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
                            </div>
                        </div>
                        <div class="h-5 w-12 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    </div>
                </div>
            </flux:card>
        HTML;
    }
};

?>

<flux:card class="p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Transactions</h3>
        <flux:button variant="ghost" size="sm" :href="route('wallet.index')">View All</flux:button>
    </div>
    <div class="space-y-3">
        @forelse ($transactions as $transaction)
        <div class="flex items-center justify-between py-2">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center bg-gray-100 dark:bg-gray-800">
                    @if(isset($transaction->transactable->image_url))
                    <img src="{{ $transaction->transactable->image_url }}" alt="{{ $transaction->description }}" class="w-full h-full object-cover rounded-full">
                    @else
                    <flux:icon name="arrow-down" class="!size-4 text-gray-400" />
                    @endif
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $transaction->description }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $transaction->created_at->diffForHumans() }}</p>
                </div>
            </div>
            <span @class([ 'text-sm font-semibold' , 'text-green-600'=> $transaction->amount > 0,
                'text-red-600' => $transaction->amount < 0,
                    ])>
                    {{ $transaction->amount > 0 ? '+' : '' }}₦{{ number_format(abs($transaction->amount), 2) }}
            </span>
        </div>
        @empty
        <div class="text-center py-4">
            <p class="text-gray-500 dark:text-gray-400">No recent transactions.</p>
        </div>
        @endforelse
    </div>
</flux:card>
@