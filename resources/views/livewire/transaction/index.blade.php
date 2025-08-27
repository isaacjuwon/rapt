<?php

use Flux\Flux;
use Illuminate\View\View;
use App\Models\Transaction;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;


new #[Title('My Transactions')] class extends Component {

    use WithPagination;

    #[Url('search', keep: false)]
    public string $search = '';

    #[Url('sort', keep: true)]
    public string $sortBy = 'created_at';

    #[Url('dir', keep: true)]
    public string $sortDirection = 'desc';

    public ?string $type = null;

    public ?string $status = null;

    public $transaction;

    /**
     * Reset the pagination when search is updated.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reset the form values.
     */
    public function resetValues(): void
    {
        $this->reset(['type', 'status']);
        $this->resetErrorBag();
    }

    /**
     * Show transaction details in modal.
     */
    #[On('open-transaction-details')]
    public function showTransactionDetails(int $transactionId): void
    {
        $this->transaction = Transaction::find($transactionId);
    }



    /**
     * Sort the transactions by the given column.
     */
    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    /**
     * Get the transactions with pagination for the current user.
     */
    #[Computed]
    public function transactions(): LengthAwarePaginator
    {
        return Transaction::query()
            ->where('user_id', Auth::id())
            ->when($this->search, function (Builder $query) {
                $query->where('reference', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%")
                    ->orWhere('recipient', 'like', "%{$this->search}%");
            })
            ->when($this->type, function (Builder $query) {
                $query->where('type', $this->type);
            })
            ->when($this->status, function (Builder $query) {
                $query->where('status', $this->status);
            })
            ->when($this->sortBy, function (Builder $query) {
                $query->orderBy($this->sortBy, $this->sortDirection);
            })
            ->paginate(10)
            ->onEachSide(2);
    }

    /**
     * Get available transaction types.
     */
    #[Computed]
    public function transactionTypes(): array
    {
        return \App\Enums\Transaction\Type::cases();
    }

    /**
     * Get available transaction statuses.
     */
    #[Computed]
    public function transactionStatuses(): array
    {
        return \App\Enums\Transaction\Status::cases();
    }
}; ?>

<div class="space-y-6">
    <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">{{ __('My Transactions') }}</flux:heading>
            <flux:subheading>
                {{ __('View and manage your transaction history.') }}
            </flux:subheading>
        </div>

        <flux:spacer />
    </div>

    <!-- Filters -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <flux:input
                wire:model.live.debounce.250ms="search"
                type="text"
                icon="magnifying-glass"
                placeholder="{{ __('Search transactions...') }}"
                autocomplete="off"
                clearable
                class="w-full" />
        </div>

        <div class="flex gap-2">
            <flux:select
                wire:model="type"
                placeholder="{{ __('All Types') }}">
                <flux:select.option value="">{{ __('All Types') }}</flux:select.option>
                @foreach ($this->transactionTypes as $type)
                <flux:select.option value="{{ $type->value }}">
                    {{ ucfirst($type->value) }}
                </flux:select.option>
                @endforeach
            </flux:select>

            <flux:select
                wire:model="status"
                placeholder="{{ __('All Statuses') }}">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                @foreach ($this->transactionStatuses as $status)
                <flux:select.option value="{{ $status->value }}">
                    {{ ucfirst($status->value) }}
                </flux:select.option>
                @endforeach
            </flux:select>

            <flux:button
                wire:click="resetValues"
                variant="ghost"
                icon="arrow-path">
                {{ __('Reset') }}
            </flux:button>
        </div>
    </div>

    <div>
        <flux:table>
            <flux:table.columns>
                <flux:table.column
                    wire:click="sort('reference')"
                    sortable
                    :sorted="$sortBy === 'reference'"
                    :direction="$sortDirection"
                    align="start">
                    {{ __('Reference') }}
                </flux:table.column>

                <flux:table.column
                    wire:click="sort('amount')"
                    sortable
                    :sorted="$sortBy === 'amount'"
                    :direction="$sortDirection"
                    align="start">
                    {{ __('Amount') }}
                </flux:table.column>

                <flux:table.column
                    wire:click="sort('type')"
                    sortable
                    :sorted="$sortBy === 'type'"
                    :direction="$sortDirection"
                    align="start">
                    {{ __('Type') }}
                </flux:table.column>

                <flux:table.column
                    wire:click="sort('status')"
                    sortable
                    :sorted="$sortBy === 'status'"
                    :direction="$sortDirection"
                    align="start">
                    {{ __('Status') }}
                </flux:table.column>

                <flux:table.column
                    wire:click="sort('created_at')"
                    sortable
                    :sorted="$sortBy === 'created_at'"
                    :direction="$sortDirection">
                    {{ __('Date') }}
                </flux:table.column>

                <flux:table.column>
                    {{ __('Actions') }}
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->transactions as $transaction)
                <flux:table.row
                    wire:target="search"
                    wire:loading.delay.long.class="opacity-75"
                    wire:key="{{ $transaction->id }}">

                    <flux:table.cell
                        align="start"
                        class="whitespace-nowrap">
                        <div class="flex flex-col">
                            <span class="font-medium">{{ $transaction->reference ?? 'N/A' }}</span>
                            @if($transaction->description)
                            <span class="text-sm text-zinc-500">{{ $transaction->description }}</span>
                            @endif
                        </div>
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        <span class="font-medium">{{ \Illuminate\Support\Number::currency($transaction->amount) }}</span>
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        <flux:badge variant="outline">
                            {{ ucfirst($transaction->type->value) }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        <flux:badge :variant="$transaction->status->getColor()">
                            {{ ucfirst($transaction->status->value) }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        <div class="flex flex-col">
                            <span>{{ $transaction->created_at->format('M d, Y') }}</span>
                            <span class="text-sm text-zinc-500">{{ $transaction->created_at->format('h:i A') }}</span>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <flux:dropdown
                            position="bottom"
                            align="end">
                            <flux:button
                                variant="ghost"
                                size="sm"
                                icon="ellipsis-horizontal"
                                inset="top bottom"
                                aria-label="{{ __('Open action menu') }}"></flux:button>

                            <flux:menu>
                                <flux:menu.item
                                    icon="eye"
                                    wire:click="$dispatch('open-transaction-details', {{ $transaction->id }})">
                                    {{ __('View Details') }}
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="6">
                        <div class="flex items-center justify-center gap-2.5 py-32">
                            <flux:icon.inbox variant="mini" />
                            <flux:heading>
                                {{ __('No transactions found.') }}
                            </flux:heading>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        {{ $this->transactions->links() }}
    </div>

    <!-- Transaction Details Modal -->
    <flux:modal name="transaction-details" class="max-w-2xl">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Transaction Details') }}</flux:heading>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Reference') }}</flux:label>
                    <flux:text>{{ $this->transaction?->reference ?? 'N/A' }}</flux:text>
                </div>

                <div>
                    <flux:label>{{ __('Amount') }}</flux:label>
                    <flux:text>{{ $this->transaction?->amount ? \Illuminate\Support\Number::currency($this->transaction?->amount) : 'N/A' }}</flux:text>
                </div>

                <div>
                    <flux:label>{{ __('Type') }}</flux:label>
                    <flux:badge variant="outline">{{ ucfirst($this->transaction?->type->value) }}</flux:badge>
                </div>

                <div>
                    <flux:label>{{ __('Status') }}</flux:label>
                    <flux:badge :variant="$this->transaction?->status->getColor()">{{ ucfirst($this->transaction?->status->value) }}</flux:badge>
                </div>

                <div>
                    <flux:label>{{ __('Payment Method') }}</flux:label>
                    <flux:text>{{ $this->transaction?->payment_method ?? 'N/A' }}</flux:text>
                </div>

                <div>
                    <flux:label>{{ __('Recipient') }}</flux:label>
                    <flux:text>{{ $this->transaction?->recipient ?? 'N/A' }}</flux:text>
                </div>

                <div class="sm:col-span-2">
                    <flux:label>{{ __('Description') }}</flux:label>
                    <flux:text>{{ $this->transaction?->description ?? 'No description available' }}</flux:text>
                </div>

                <div>
                    <flux:label>{{ __('Created At') }}</flux:label>
                    <flux:text>{{ $this->transaction?->created_at->format('M d, Y h:i A') }}</flux:text>
                </div>

                <div>
                    <flux:label>{{ __('Updated At') }}</flux:label>
                    <flux:text>{{ $this->transaction?->updated_at->format('M d, Y h:i A') }}</flux:text>
                </div>
            </div>

            @if($this->transaction?->response)
            <div>
                <flux:label>{{ __('Response') }}</flux:label>
                <flux:text class="text-sm">{{ $this->transaction?->response }}</flux:text>
            </div>
            @endif
        </div>
    </flux:modal>
</div>

@script
<script>
    $wire.on('open-transaction-details', (transactionId) => {
        $wire.call('showTransactionDetails', transactionId); // Call the Livewire method
        $wire.dispatch('open-modal', 'transaction-details');
    });
</script>
@endscript