<?php

namespace App\Http\Controllers;

use App\Actions\Payment\PaymentAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    protected $paymentAction;

    public function __construct(PaymentAction $paymentAction)
    {
        $this->paymentAction = $paymentAction;
    }

    /**
     * Handle Paystack payment callback
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
    {
        try {
            // Get the transaction reference from the request
            $reference = $request->query('reference') ?: $request->input('trxref');

            if (!$reference) {
                return redirect()->route('wallet.fund')
                    ->with('error', 'No payment reference provided.');
            }

            // Verify payment and fund wallet
            $result = $this->paymentAction->verifyPaymentAndFundWallet($reference);

            if ($result['success']) {
                                return redirect()->route('wallet.index')
                    ->with('success', "Wallet funded successfully! " . \Illuminate\Support\Number::currency($result['amount']) . " has been added to your {$result['wallet_type']} wallet.");
            } else {
                return redirect()->route('wallet.fund')
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return redirect()->route('wallet.fund')
                ->with('error', 'Payment verification failed: ' . $e->getMessage());
        }
    }
}
