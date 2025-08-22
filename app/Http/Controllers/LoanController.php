<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Member;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    /**
     * Display a listing of the loans.
     */
    public function index()
    {
        $loans = Loan::with(['member', 'account'])->latest()->paginate(10);
        return view('loans.index', compact('loans'));
    }

    /**
     * Show the form for creating a new loan.
     */
    public function create()
    {
        $members = Member::where('status', 'active')->get();
        return view('loans.create', compact('members'));
    }

    /**
     * Get accounts for a member (AJAX).
     */
    public function getAccounts(Member $member)
    {
        $accounts = $member->accounts()->where('account_type', 'loan')->where('status', 'active')->get();
        return response()->json($accounts);
    }

    /**
     * Store a newly created loan in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'account_id' => 'required|exists:accounts,id',
            'loan_type' => 'required|in:personal,business,education,agriculture,emergency',
            'principal_amount' => 'required|numeric|min:1',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'term_months' => 'required|integer|min:1|max:60',
            'payment_frequency' => 'required|in:weekly,biweekly,monthly',
            'disbursement_date' => 'required|date',
            'first_payment_date' => 'required|date|after:disbursement_date',
            'purpose' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Calculate total installments based on term and payment frequency
        $totalInstallments = $this->calculateTotalInstallments(
            $request->term_months,
            $request->payment_frequency
        );

        // Calculate total payable amount (principal + interest)
        $interestAmount = ($request->principal_amount * $request->interest_rate * $request->term_months) / 1200; // Monthly interest
        $totalPayable = $request->principal_amount + $interestAmount;

        // Generate unique loan number
        $loanNumber = 'LN' . date('Y') . str_pad(Loan::count() + 1, 5, '0', STR_PAD_LEFT);

        // Calculate expected end date
        $expectedEndDate = $this->calculateExpectedEndDate(
            $request->first_payment_date,
            $totalInstallments,
            $request->payment_frequency
        );

        // Create loan
        $loan = Loan::create([
            'member_id' => $request->member_id,
            'account_id' => $request->account_id,
            'loan_number' => $loanNumber,
            'loan_type' => $request->loan_type,
            'principal_amount' => $request->principal_amount,
            'interest_rate' => $request->interest_rate,
            'total_payable' => $totalPayable,
            'remaining_balance' => $totalPayable,
            'term_months' => $request->term_months,
            'total_installments' => $totalInstallments,
            'disbursement_date' => $request->disbursement_date,
            'first_payment_date' => $request->first_payment_date,
            'expected_end_date' => $expectedEndDate,
            'payment_frequency' => $request->payment_frequency,
            'status' => 'pending',
            'purpose' => $request->purpose,
            'notes' => $request->notes,
        ]);

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Loan application created successfully and is pending approval.');
    }

    /**
     * Display the specified loan.
     */
    public function show(Loan $loan)
    {
        $loan->load(['member', 'account', 'payments']);
        return view('loans.show', compact('loan'));
    }

    /**
     * Show the form for editing the specified loan.
     */
    public function edit(Loan $loan)
    {
        if (!in_array($loan->status, ['pending', 'approved'])) {
            return redirect()->route('loans.show', $loan)
                ->with('error', 'Cannot edit a loan that has been disbursed or is active.');
        }

        return view('loans.edit', compact('loan'));
    }

    /**
     * Update the specified loan in storage.
     */
    public function update(Request $request, Loan $loan)
    {
        if (!in_array($loan->status, ['pending', 'approved'])) {
            return redirect()->route('loans.show', $loan)
                ->with('error', 'Cannot update a loan that has been disbursed or is active.');
        }

        $request->validate([
            'interest_rate' => 'required|numeric|min:0|max:100',
            'disbursement_date' => 'required|date',
            'first_payment_date' => 'required|date|after:disbursement_date',
            'purpose' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Recalculate total payable amount if interest rate changed
        $totalPayable = $loan->total_payable;
        if ($request->interest_rate != $loan->interest_rate) {
            $interestAmount = ($loan->principal_amount * $request->interest_rate * $loan->term_months) / 1200;
            $totalPayable = $loan->principal_amount + $interestAmount;
        }

        // Recalculate expected end date if first payment date changed
        $expectedEndDate = $loan->expected_end_date;
        if ($request->first_payment_date != $loan->first_payment_date) {
            $expectedEndDate = $this->calculateExpectedEndDate(
                $request->first_payment_date,
                $loan->total_installments,
                $loan->payment_frequency
            );
        }

        // Update loan
        $loan->update([
            'interest_rate' => $request->interest_rate,
            'total_payable' => $totalPayable,
            'remaining_balance' => $totalPayable,
            'disbursement_date' => $request->disbursement_date,
            'first_payment_date' => $request->first_payment_date,
            'expected_end_date' => $expectedEndDate,
            'purpose' => $request->purpose,
            'notes' => $request->notes,
        ]);

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Loan updated successfully.');
    }

    /**
     * Approve the specified loan.
     */
    public function approve(Loan $loan)
    {
        if ($loan->status !== 'pending') {
            return redirect()->route('loans.show', $loan)
                ->with('error', 'Only pending loans can be approved.');
        }

        $loan->update(['status' => 'approved']);

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Loan approved successfully.');
    }

    /**
     * Reject the specified loan.
     */
    public function reject(Request $request, Loan $loan)
    {
        if ($loan->status !== 'pending') {
            return redirect()->route('loans.show', $loan)
                ->with('error', 'Only pending loans can be rejected.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);

        $loan->update([
            'status' => 'rejected',
            'notes' => $loan->notes . '\nRejection reason: ' . $request->rejection_reason,
        ]);

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Loan rejected successfully.');
    }

    /**
     * Show the form for disbursing the specified loan.
     */
    public function disbursementForm(Loan $loan)
    {
        if ($loan->status !== 'approved') {
            return redirect()->route('loans.show', $loan)
                ->with('error', 'Only approved loans can be disbursed.');
        }

        return view('loans.disburse', compact('loan'));
    }

    /**
     * Disburse the specified loan.
     */
    public function disburse(Request $request, Loan $loan)
    {
        if ($loan->status !== 'approved') {
            return redirect()->route('loans.show', $loan)
                ->with('error', 'Only approved loans can be disbursed.');
        }

        $request->validate([
            'method' => 'required|in:cash,bank_transfer,mobile_money,check',
            'reference_number' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $transaction = $loan->disburse(
                $request->method,
                $request->description
            );

            if ($request->filled('reference_number')) {
                $transaction->update(['reference_number' => $request->reference_number]);
            }

            return redirect()->route('loans.show', $loan)
                ->with('success', 'Loan disbursed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error disbursing loan: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for making a payment on the specified loan.
     */
    public function paymentForm(Loan $loan)
    {
        if (!in_array($loan->status, ['disbursed', 'active'])) {
            return redirect()->route('loans.show', $loan)
                ->with('error', 'Only disbursed or active loans can receive payments.');
        }

        $nextPayment = $loan->payments()->where('status', 'pending')->orderBy('installment_number')->first();

        return view('loans.payment', compact('loan', 'nextPayment'));
    }

    /**
     * Process a payment for the specified loan.
     */
    public function payment(Request $request, Loan $loan)
    {
        if (!in_array($loan->status, ['disbursed', 'active'])) {
            return redirect()->route('loans.show', $loan)
                ->with('error', 'Only disbursed or active loans can receive payments.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => 'required|in:cash,bank_transfer,mobile_money,check',
            'reference_number' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
            'payment_for_installment' => 'nullable|exists:loan_payments,id',
        ]);

        try {
            // If payment is for a specific installment
            if ($request->filled('payment_for_installment')) {
                $loanPayment = LoanPayment::findOrFail($request->payment_for_installment);
                $transaction = $loanPayment->markAsPaid(
                    $request->amount,
                    $request->method,
                    $request->description
                );
            } else {
                // General payment
                $transaction = $loan->makePayment(
                    $request->amount,
                    $request->method,
                    $request->description
                );
            }

            if ($request->filled('reference_number')) {
                $transaction->update(['reference_number' => $request->reference_number]);
            }

            return redirect()->route('loans.show', $loan)
                ->with('success', 'Payment processed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }

    /**
     * Display the payment schedule for the specified loan.
     */
    public function schedule(Loan $loan)
    {
        $payments = $loan->payments()->orderBy('installment_number')->paginate(15);
        return view('loans.schedule', compact('loan', 'payments'));
    }

    /**
     * Calculate total installments based on term and payment frequency.
     */
    private function calculateTotalInstallments(int $termMonths, string $paymentFrequency): int
    {
        switch ($paymentFrequency) {
            case 'weekly':
                return $termMonths * 4; // Approximately 4 weeks per month
            case 'biweekly':
                return $termMonths * 2; // 2 biweekly payments per month
            case 'monthly':
            default:
                return $termMonths;
        }
    }

    /**
     * Calculate expected end date based on first payment date, total installments, and payment frequency.
     */
    private function calculateExpectedEndDate(string $firstPaymentDate, int $totalInstallments, string $paymentFrequency): string
    {
        $date = new \DateTime($firstPaymentDate);

        for ($i = 1; $i < $totalInstallments; $i++) {
            switch ($paymentFrequency) {
                case 'weekly':
                    $date->modify('+1 week');
                    break;
                case 'biweekly':
                    $date->modify('+2 weeks');
                    break;
                case 'monthly':
                default:
                    $date->modify('+1 month');
                    break;
            }
        }

        return $date->format('Y-m-d');
    }
}
