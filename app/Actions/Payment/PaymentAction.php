<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Managers\ApiManager;
use App\Contracts\Payment\PaymentContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PaymentAction
{
    public function __construct(
        protected ApiManager $apiManager
    ) {}

    /**
     * Get payment driver for the specified payment method
     *
     * @param string $paymentMethod
     * @return PaymentContract
     */
    public function driver(string $paymentMethod): PaymentContract
    {
        return $this->apiManager->driver($paymentMethod);
    }

    /**
     * Initialize wallet funding payment
     *
     * @param float $amount
     * @param string $walletType
     * @param string $paymentMethod
     * @param string|null $reference
     * @return array
     */
    public function initializeWalletFunding(float $amount, string $walletType, string $paymentMethod, ?string $reference = null): array
    {
        $user = Auth::user();

        // Generate reference if not provided
        $reference = $reference ?? 'WALLET_' . uniqid() . '_' . time();

        // Create metadata for wallet funding
        $metadata = [
            'wallet_type' => $walletType,
            'user_id' => $user->id,
            'payment_type' => 'wallet_funding',
            'payment_method' => $paymentMethod,
            'reference' => $reference,
        ];

        // Create payment initialization data
        $paymentData = [
            'email' => $user->email,
            'amount' => $amount,
            'reference' => $reference,
            'currency' => 'NGN',
            'callback_url' => route('wallet.callback'),
            'metadata' => $metadata,
        ];

        // Initialize payment via driver
        $paymentResponse = $this->driver($paymentMethod)->initializePayment($paymentData);

        return [
            'success' => true,
            'reference' => $reference,
            'authorization_url' => $paymentResponse['authorization_url'] ?? null,
            'access_code' => $paymentResponse['access_code'] ?? null,
            'message' => 'Payment initialized successfully',
        ];
    }

    /**
     * Verify payment and dispatch wallet funding job
     *
     * @param string $reference
     * @return array
     */
    public function verifyPaymentAndFundWallet(string $reference): array
    {
        try {
            // Verify payment
            $paymentData = $this->driver('paystack')->verifyPayment($reference);

            if (!$paymentData || $paymentData['status'] !== 'success') {
                return [
                    'success' => false,
                    'message' => 'Payment verification failed',
                ];
            }

            // Get metadata
            $metadata = $paymentData['metadata'] ?? [];
            $userId = $metadata['user_id'];
            $walletType = $metadata['wallet_type'];
            $amount = $paymentData['amount'];
            $paymentMethod = $metadata['payment_method'] ?? 'paystack';

            // Dispatch CreditWalletJob
            \App\Jobs\Payment\CreditWalletJob::dispatch(
                $userId,
                $amount,
                $walletType,
                'Wallet funding via ' . ucfirst($paymentMethod),
                $reference
            );

            return [
                'success' => true,
                'message' => 'Payment verified successfully. Wallet funding queued.',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Redirect to payment gateway
     *
     * @param string $authorizationUrl
     * @return RedirectResponse
     */
    public function redirectToPayment(string $authorizationUrl): RedirectResponse
    {
        return redirect()->away($authorizationUrl);
    }
}
