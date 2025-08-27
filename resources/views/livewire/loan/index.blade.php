<?php

use App\Models\Loan;
use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title, Computed};

new #[Title('My Loans')] class extends Component {
    public $search = '';
    public $filter = 'all';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    #[Computed]
    public function loans()
    {
        return auth()->user()->loans()
            ->when($this->search, fn($q) => $q->where(function ($query) {
                $query->where('loan_number', 'like', "%{$this->search}%")
                    ->orWhere('purpose', 'like', "%{$this->search}%");
            }))
            ->when($this->filter !== 'all', fn($q) => match ($this->filter) {
                'active' => $q->active(),
                'pending' => $q->pending(),
                'completed' => $q->completed(),
                'defaulted' => $q->where('status', Loan::STATUS_DEFAULTED),
                default => $q,
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->get()
            ->map(fn($loan) => [
                'id' => $loan->id,
                'loan_number' => $loan->loan_number,
                'principal_amount' => $loan->principal_amount,
                'interest_rate' => $loan->interest_rate,
                'status' => $loan->status,
                'status_label' => $loan->getStatusLabel(),
                'purpose' => $loan->purpose,
                'progress_percentage' => $loan->getProgressPercentage(),
                'created_at' => $loan->created_at->format('M d, Y'),
            ]);
    }

    #[Computed]
    public function stats()
    {
        $loans = auth()->user()->loans();

        return [
            'total_loans' => $loans->count(),
            'active_loans' => (clone $loans)->active()->count(),
            'outstanding_balance' => $loans->sum('remaining_balance'),
        ];
    }

    #[Computed]
    public function eligibility()
    {
        return auth()->user()->getLoanEligibilityDetails();
    }

    public function sortBy($field): void
    {
        $this->sortBy = $this->sortBy === $field ? $field : $field;
        $this->sortDirection = $this->sortBy === $field && $this->sortDirection === 'asc' ? 'desc' : 'asc';
    }
}; ?>

<div>
    <flux:heading size="xl">My Loans</flux:heading>
    <flux:subheading>Track your loan applications and repayments</flux:subheading>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <flux:card>
            <div class="flex items-center justify-between">
                <div>
                    <flux:text size="sm" color="secondary">Total Loans</flux:text>
                    <flux:text size="lg" weight="semibold">{{ $this->stats['total_loans'] }}</flux:text>
                </div>
                <flux:icon icon="document-text" variant="solid" class="text-blue-500" />
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center justify-between">
                <div>
                    <flux:text size="sm" color="secondary">Active Loans</flux:text>
                    <flux:text size="lg" weight="semibold">{{ $this->stats['active_loans'] }}</flux:text>
                </div>
                <flux:icon icon="clock" variant="solid" class="text-green-500" />
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center justify-between">
                <div>
                    <flux:text size="sm" color="secondary">Outstanding</flux:text>
                    <flux:text size="lg" weight="semibold">{{ \Illuminate\Support\Number::currency($this->stats['outstanding_balance']) }}</flux:text>
                </div>
                <flux:icon icon="currency-dollar" variant="solid" class="text-purple-500" />
            </div>
        </flux:card>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search loans..."
                icon="magnifying-glass" />

            <flux:select wire:model="filter">
                <option value="all">All</option>
                <option value="active">Active</option>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
                <option value="defaulted">Defaulted</option>
            </flux:select>
        </div>

        @if($this->eligibility['can_apply'])
        <flux:button href="{{ route('loan.application') }}" variant="primary">
            <flux:icon icon="plus-circle" slot="prefix" />
            Apply for Loan
        </flux:button>
        @endif
    </div>

    <flux:card>
        <flux:table>
            <flux:table.head>
                <flux:table.heading wire:click="sortBy('loan_number')" class="cursor-pointer">
                    Loan Number {{ $this->sortBy === 'loan_number' ? ($this->sortDirection === 'asc' ? '↑' : '↓') : '' }}
                </flux:table.heading>
                <flux:table.heading wire:click="sortBy('principal_amount')" class="cursor-pointer">
                    Amount {{ $this->sortBy === 'principal_amount' ? ($this->sortDirection === 'asc' ? '↑' : '↓') : '' }}
                </flux:table.heading>
                <flux:table.heading wire:click="sortBy('status')" class="cursor-pointer">
                    Status {{ $this->sortBy === 'status' ? ($this->sortDirection === 'asc' ? '↑' : '↓') : '' }}
                </flux:table.heading>
                <flux:table.heading>Progress</flux:table.heading>
                <flux:table.heading>Actions</flux:table.heading>
            </flux:table.head>

            <flux:table.body>
                @forelse ($this->loans as $loan)
                <flux:table.row>
                    <flux:table.cell>
                        <div>
                            <flux:text weight="medium">{{ $loan['loan_number'] }}</flux:text>
                            <flux:text size="sm" color="secondary">{{ $loan['purpose'] }}</flux:text>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div>
                            <flux:text weight="medium">{{ \Illuminate\Support\Number::currency($loan['principal_amount']) }}</flux:text>
                            <flux:text size="sm" color="secondary">{{ $loan['interest_rate'] }}%</flux:text>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge variant="{{ match($loan['status']) {
                            'active' => 'success',
                            'pending' => 'warning',
                            'completed' => 'muted',
                            'defaulted' => 'danger',
                            default => 'muted'
                        } }}">
                            {{ $loan['status_label'] }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="w-full">
                            <flux:progress :value="$loan['progress_percentage']" class="mb-1" />
                            <flux:text size="xs" color="secondary">{{ number_format($loan['progress_percentage'], 1) }}%</flux:text>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            <flux:button href="{{ route('loan.show', $loan['id']) }}" variant="ghost" size="sm">
                                View
                            </flux:button>
                            @if($loan['status'] === 'active')
                            <flux:button href="{{ route('loan.pay', $loan['id']) }}" variant="primary" size="sm">
                                Pay
                            </flux:button>
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center">
                        <flux:text color="secondary">No loans found</flux:text>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.body>
        </flux:table>
    </flux:card>
</div>