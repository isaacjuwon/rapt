<?php

declare(strict_types=1);

namespace App\Jobs\Payment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class CreditWalletJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $userId,
        private float $amount,
        private string $walletType,
        private string $description,
        private string $reference
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user = User::find($this->userId);

            if (!$user) {
                $this->fail(new \Exception("User not found: {$this->userId}"));
                return;
            }

            // Use the trait's creditWallet method if available
            if (method_exists($user, 'creditWallet')) {
                $user->creditWallet($this->amount, $this->description);
            } else {
                // Fallback to manual wallet crediting
                $wallet = $user->wallets()->where('type', $this->walletType)->first();

                if (!$wallet) {
                    $this->fail(new \Exception("Wallet not found for user {$this->userId} with type {$this->walletType}"));
                    return;
                }

                $wallet->balance += $this->amount;
                $wallet->save();

                $wallet->logTransaction(
                    'increment',
                    $this->amount,
                    $this->description,
                    $this->reference
                );
            }
        } catch (\Exception $e) {
            $this->fail($e);
        }
    }
}
