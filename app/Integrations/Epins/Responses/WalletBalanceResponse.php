<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Responses;

final readonly class WalletBalanceResponse
{
    public function __construct(
        public int $code,
        public ?string $message,
        public ?WalletBalanceData $description,
    ) {}

    /**
     * @param array{
     *     code:int,
     *     message?:string|null,
     *     description?:array|null,
     * } $data
     * @return WalletBalanceResponse
     */
    public static function make(array $data): WalletBalanceResponse
    {
        return new WalletBalanceResponse(
            code: $data['code'],
            message: $data['message'] ?? null,
            description: isset($data['description']) && is_array($data['description']) 
                ? WalletBalanceData::make($data['description']) 
                : null,
        );
    }

    /**
     * Check if the balance check was successful.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->code === 101;
    }

    /**
     * Get the account balance.
     *
     * @return float|null
     */
    public function getBalance(): ?float
    {
        return $this->description?->balance;
    }

    /**
     * Get the account number.
     *
     * @return string|null
     */
    public function getAccountNumber(): ?string
    {
        return $this->description?->accountNumber;
    }

    /**
     * Get the account name.
     *
     * @return string|null
     */
    public function getAccountName(): ?string
    {
        return $this->description?->accountName;
    }

    /**
     * Get the account status.
     *
     * @return string|null
     */
    public function getAccountStatus(): ?string
    {
        return $this->description?->accountStatus;
    }

    /**
     * Get the currency.
     *
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return $this->description?->currency;
    }

    /**
     * Get the last transaction date.
     *
     * @return string|null
     */
    public function getLastTransactionDate(): ?string
    {
        return $this->description?->lastTransactionDate;
    }

    /**
     * Get the error message if the balance check failed.
     *
     * @return string|null
     */
    public function getErrorMessage(): ?string
    {
        if ($this->isSuccessful()) {
            return null;
        }

        return match ($this->code) {
            400 => 'Invalid request method',
            103 => 'Invalid account credentials',
            102 => 'Low wallet balance',
            1007 => 'Transaction blocked',
            1009 => 'Account blocked',
            304 => 'Unauthorized access',
            default => $this->message ?? 'Unknown error occurred',
        };
    }

    /**
     * Check if the account is active.
     *
     * @return bool
     */
    public function isAccountActive(): bool
    {
        return $this->description?->accountStatus === 'active';
    }

    /**
     * Get formatted balance with currency symbol.
     *
     * @param string $symbol
     * @return string
     */
    public function getFormattedBalance(string $symbol = '₦'): string
    {
        $balance = $this->getBalance() ?? 0;
        return $symbol . number_format($balance, 2);
    }

    /**
     * Convert to array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'description' => $this->description?->toArray(),
        ];
    }
}

final readonly class WalletBalanceData
{
    public function __construct(
        public ?float $balance,
        public ?string $accountNumber,
        public ?string $accountName,
        public ?string $accountStatus,
        public ?string $currency,
        public ?string $lastTransactionDate,
    ) {}

    /**
     * @param array $data
     * @return WalletBalanceData
     */
    public static function make(array $data): WalletBalanceData
    {
        return new WalletBalanceData(
            balance: isset($data['balance']) ? (float) $data['balance'] : null,
            accountNumber: $data['accountNumber'] ?? null,
            accountName: $data['accountName'] ?? null,
            accountStatus: $data['accountStatus'] ?? null,
            currency: $data['currency'] ?? null,
            lastTransactionDate: $data['lastTransactionDate'] ?? null,
        );
    }

    /**
     * Convert to array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'balance' => $this->balance,
            'accountNumber' => $this->accountNumber,
            'accountName' => $this->accountName,
            'accountStatus' => $this->accountStatus,
            'currency' => $this->currency,
            'lastTransactionDate' => $this->lastTransactionDate,
        ];
    }
}
