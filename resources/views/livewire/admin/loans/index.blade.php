<?php

use Flux\Flux;
use App\Models\Loan;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Masmerise\Toaster\Toaster;
use Livewire\Attributes\Layout;
use App\Events\Loan\LoanApproved;
use Livewire\Attributes\Computed;
use App\Events\Loan\LoanDisapproved;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;


new #[Layout('components.layouts.admin')] #[Title('Manage Loans')] class extends Component {

    use WithPagination;

    #[Url('search', keep: false)]
    public string $search = '';

    #[Url('sort', keep: true)]
    public string $sortBy = 'created_at';

    #[Url('dir', keep: true)]
    public string $sortDirection = 'desc';

    #[Url('status', keep: true)]
    public ?string $statusFilter = null;

    public ?Loan $selected_loan = null;

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
        $this->resetErrorBag();
        $this->selected_loan = null;
    }

    /**
     * Sort the loans by the given column.
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
     * Get the loans with pagination.
     */
    #[Computed]
    public function loans(): LengthAwarePaginator
    {
        return Loan::query()
            ->with('user')
            ->when($this->search, function (Builder $query) {
                $query->where('loan_number', 'like', "%{$this->search}%")
                    ->orWhere('purpose', 'like', "%{$this->search}%")
                    ->orWhereHas('user', function (Builder $query) {
                        $query->where('name', 'like', "%{$this->search}%")
                            ->orWhere('email', 'like', "%{$this->search}%");
                    });
            })
            ->when($this->statusFilter, function (Builder $query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->sortBy, function (Builder $query) {
                $query->orderBy($this->sortBy, $this->sortDirection);
            })
            ->paginate(10)
            ->onEachSide(2);
    }

    /**
     * Approve a loan.
     */
    public function approveLoan(Loan $loan): void
    {
        if ($loan->status !== Loan::STATUS_PENDING) {
            Toaster::error(__('Only pending loans can be approved.'));
            return;
        }

        $loan->update([
            'status' => Loan::STATUS_APPROVED,
        ]);

        // Dispatch loan approved event
        LoanApproved::dispatch($loan);

        $this->resetValues();
        Toaster::success(__('Loan approved successfully.'));
    }

    /**
     * Disapprove a loan.
     */
    public function disapproveLoan(Loan $loan): void
    {
        if ($loan->status !== Loan::STATUS_PENDING) {
            Toaster::error(__('Only pending loans can be disapproved.'));
            return;
        }

        $loan->update([
            'status' => Loan::STATUS_REJECTED,
        ]);

        // Dispatch loan disapproved event
        LoanDisapproved::dispatch($loan);

        $this->resetValues();
        Toaster::success(__('Loan disapproved successfully.'));
    }

    /**
     * Get status options for filter.
     */
    public function getStatusOptions(): array
    {
        return [
            '' => __('All Status'),
            Loan::STATUS_PENDING => __('Pending'),
            Loan::STATUS_APPROVED => __('Approved'),
            Loan::STATUS_DISBURSED => __('Disbursed'),
            Loan::STATUS_ACTIVE => __('Active'),
            Loan::STATUS_COMPLETED => __('Completed'),
            Loan::STATUS_DEFAULTED => __('Defaulted'),
            Loan::STATUS_REJECTED => __('Rejected'),
        ];
    }

    /**
     * Get status badge variant.
     */
    public function getStatusBadgeVariant(string $status): string
    {
        return match ($status) {
            Loan::STATUS_PENDING => 'warning',
            Loan::STATUS_APPROVED => 'success',
            Loan::STATUS_DISBURSED => 'info',
            Loan::STATUS_ACTIVE => 'success',
            Loan::STATUS_COMPLETED => 'success',
            Loan::STATUS_DEFAULTED => 'danger',
            Loan::STATUS_REJECTED => 'danger',
            default => 'secondary',
        };
    }
}; ?>

<div class="space-y-6">
    <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">{{ __('Loan Management') }}</flux:heading>
            <flux:subheading>
                {{ __('Review and manage loan applications. Approve or disapprove pending loans.') }}
            </flux:subheading>
        </div>

        <flux:spacer />
    </div>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <div class="mb-2 sm:mb-0">
                <flux:input
                    wire:model.live.debounce.250ms="search"
                    type="text"
                    icon="magnifying-glass"
                    placeholder="{{ __('Search loans...') }}"
                    autocomplete="off"
                    clearable
                    class="w-full sm:max-w-72" />
            </div>

            <flux:select
                wire:model.live="statusFilter"
                placeholder="{{ __('Filter by status') }}"
                class="w-full sm:max-w-48">
                @foreach ($this->getStatusOptions() as $value => $label)
                <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    <div>
        <flux:table>
            <flux:table.columns>
                <flux:table.column
                    wire:click="sort('loan_number')"
                    sortable
                    :sorted="$sortBy === 'loan_number'"
                    :direction="$sortDirection"
                    align="start">
                    {{ __('Loan Number') }}
                </flux:table.column>

                <flux:table.column
                    wire:click="sort('user_id')"
                    sortable
                    :sorted="$sortBy === 'user_id'"
                    :direction="$sortDirection"
                    align="start">
                    {{ __('Applicant') }}
                </flux:table.column>

                <flux:table.column
                    wire:click="sort('principal_amount')"
                    sortable
                    :sorted="$sortBy === 'principal_amount'"
                    :direction="$sortDirection">
                    {{ __('Amount') }}
                </flux:table.column>

                <flux:table.column
                    wire:click="sort('status')"
                    sortable
                    :sorted="$sortBy === 'status'"
                    :direction="$sortDirection">
                    {{ __('Status') }}
                </flux:table.column>

                <flux:table.column
                    wire:click="sort('created_at')"
                    sortable
                    :sorted="$sortBy === 'created_at'"
                    :direction="$sortDirection">
                    {{ __('Applied') }}
                </flux:table.column>

                <flux:table.column align="end">
                    {{ __('Actions') }}
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->loans as $loan)
                <flux:table.row
                    wire:target="search,statusFilter"
                    wire:loading.delay.long.class="opacity-75"
                    wire:key="{{ $loan->id }}">
                    <flux:table.cell align="start">
                        <div>
                            <flux:heading class="!mb-0 text-sm">
                                {{ $loan->loan_number }}
                            </flux:heading>
                            <flux:text size="xs" class="text-gray-500">
                                {{ $loan->getTypeLabel() }}
                            </flux:text>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell align="start">
                        <div>
                            <flux:heading class="!mb-0 text-sm">
                                {{ $loan->user->name }}
                            </flux:heading>
                            <flux:text size="xs" class="text-gray-500">
                                {{ $loan->user->email }}
                            </flux:text>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="text-right">
                            <flux:heading class="!mb-0 text-sm font-medium">
                                {{ number_format($loan->principal_amount, 2) }}
                            </flux:heading>
                            <flux:text size="xs" class="text-gray-500">
                                {{ $loan->interest_rate }}% interest
                            </flux:text>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge
                            size="sm"
                            :variant="$this->getStatusBadgeVariant($loan->status)">
                            {{ $loan->getStatusLabel() }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        <flux:text size="sm">
                            {{ $loan->created_at->format('M d, Y') }}
                        </flux:text>
                        <flux:text size="xs" class="text-gray-500">
                            {{ $loan->created_at->diffForHumans() }}
                        </flux:text>
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <div class="flex gap-2">
                            @if ($loan->status === Loan::STATUS_PENDING)
                            <flux:button
                                wire:click="approveLoan({{ $loan->id }})"
                                variant="primary"
                                size="sm"
                                icon="check-circle">
                                {{ __('Approve') }}
                            </flux:button>

                            <flux:button
                                wire:click="disapproveLoan({{ $loan->id }})"
                                variant="danger"
                                size="sm"
                                icon="x-circle">
                                {{ __('Reject') }}
                            </flux:button>
                            @else
                            <flux:text size="sm" class="text-gray-500">
                                {{ __('No actions available') }}
                            </flux:text>
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="6">
                        <div class="flex items-center justify-center gap-2.5 py-32">
                            <flux:icon.inbox variant="mini" />
                            <flux:heading>
                                {{ __('No loans found.') }}
                            </flux:heading>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        {{ $this->loans->links() }}
    </div>

    <!-- Loan Details Modal -->
    <flux:modal
        wire:close="resetValues"
        name="loan-details"
        class="w-lg max-w-[calc(100vw-3rem)]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Loan Details') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Detailed information about the selected loan.') }}
                </flux:text>
            </div>

            @if ($selected_loan)
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:text size="sm" class="text-gray-500">{{ __('Loan Number') }}</flux:text>
                        <flux:heading size="sm">{{ $selected_loan->loan_number }}</flux:heading>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-gray-500">{{ __('Status') }}</flux:text>
                        <flux:badge
                            size="sm"
                            :variant="$this->getStatusBadgeVariant($selected_loan->status)">
                            {{ $selected_loan->getStatusLabel() }}
                        </flux:badge>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:text size="sm" class="text-gray-500">{{ __('Principal Amount') }}</flux:text>
                        <flux:heading size="sm">{{ number_format($selected_loan->principal_amount, 2) }}</flux:heading>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-gray-500">{{ __('Interest Rate') }}</flux:text>
                        <flux:heading size="sm">{{ $selected_loan->interest_rate }}%</flux:heading>
                    </div>
                </div>

                <div>
                    <flux:text size="sm" class="text-gray-500">{{ __('Purpose') }}</flux:text>
                    <flux:heading size="sm">{{ $selected_loan->purpose }}</flux:heading>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:text size="sm" class="text-gray-500">{{ __('Applied Date') }}</flux:text>
                        <flux:heading size="sm">{{ $selected_loan->created_at->format('M d, Y') }}</flux:heading>
                    </div>
                    <div>
                        <flux:text size="sm" class="text-gray-500">{{ __('Term') }}</flux:text>
                        <flux:heading size="sm">{{ $selected_loan->term_months }} {{ __('months') }}</flux:heading>
                    </div>
                </div>
            </div>
            @endif

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Close') }}</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>