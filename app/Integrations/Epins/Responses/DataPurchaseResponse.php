<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Responses;

final readonly class DataPurchaseResponse
{
    public function __construct(
        public int $code,
        public ?string $message,
        public ?DataPurchaseData $description,
    ) {}

    /**
     * @param array{
     *     code:int,
     *     message?:string|null,
     *     description?:array|null,
     * } $data
     * @return DataPurchaseResponse
     */
    public static function make(array $data): DataPurchaseResponse
    {
        return new DataPurchaseResponse(
            code: $data['code'],
            message: $data['message'] ?? null,
            description: isset($data['description']) && is_array($data['description']) 
                ? DataPurchaseData::make($data['description']) 
                : null,
        );
    }

    /**
     * Check if the purchase was successful.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->code === 101;
    }

    /**
     * Get the transaction reference.
     *
     * @return string|null
     */
    public function getTransactionReference(): ?string
    {
        return $this->description?->ref;
    }

    /**
     * Get the amount paid.
     *
     * @return float|null
     */
    public function getAmountPaid(): ?float
    {
        return $this->description?->amount_paid;
    }

    /**
     * Get the transaction date.
     *
     * @return string|null
     */
    public function getTransactionDate(): ?string
    {
        return $this->description?->transaction_date ?? $this->description?->TransactionDate;
    }

    /**
     * Get the phone number.
     *
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->description?->phone;
    }

    /**
     * Get the network.
     *
     * @return string|null
     */
    public function getNetwork(): ?string
    {
        return $this->description?->network;
    }

    /**
     * Get the data plan.
     *
     * @return string|null
     */
    public function getDataPlan(): ?string
    {
        return $this->description?->DataPlan;
    }

    /**
     * Get the error message if the purchase failed.
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
            107 => 'Invalid amount',
            102 => 'Low wallet balance',
            1007 => 'Transaction blocked',
            1009 => 'Account blocked',
            104 => 'Transaction ID already exists',
            105 => 'Transaction failed',
            108 => 'Below minimum amount allowed',
            118 => 'Missing service ID',
            207 => 'PIN unavailable',
            303 => 'Invalid service ID',
            304 => 'Unauthorized access',
            default => $this->message ?? 'Unknown error occurred',
        };
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

final readonly class DataPurchaseData
{
    public function __construct(
        public ?string $ref,
        public ?float $amount_paid,
        public ?string $transaction_date,
        public ?string $TransactionDate,
        public ?string $phone,
        public ?string $network,
        public ?string $DataPlan,
    ) {}

    /**
     * @param array $data
     * @return DataPurchaseData
     */
    public static function make(array $data): DataPurchaseData
    {
        return new DataPurchaseData(
            ref: $data['ref'] ?? null,
            amount_paid: isset($data['amount_paid']) ? (float) $data['amount_paid'] : null,
            transaction_date: $data['transaction_date'] ?? null,
            TransactionDate: $data['TransactionDate'] ?? null,
            phone: $data['phone'] ?? null,
            network: $data['network'] ?? null,
            DataPlan: $data['DataPlan'] ?? null,
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
            'ref' => $this->ref,
            'amount_paid' => $this->amount_paid,
            'transaction_date' => $this->transaction_date,
            'TransactionDate' => $this->TransactionDate,
            'phone' => $this->phone,
            'network' => $this->network,
            'DataPlan' => $this->DataPlan,
        ];
    }
}
