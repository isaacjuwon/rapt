<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Responses;

use DateTimeInterface;

final readonly class AirtimePurchaseResponse
{
    public function __construct(
        public int $code,
        public ?string $message,
        public ?AirtimePurchaseData $description,
    ) {}

    /**
     * @param array{
     *     code:int,
     *     message?:string|null,
     *     description?:array|null,
     * } $data
     * @return AirtimePurchaseResponse
     */
    public static function make(array $data): AirtimePurchaseResponse
    {
        return new AirtimePurchaseResponse(
            code: $data['code'],
            message: $data['message'] ?? null,
            description: isset($data['description']) && is_array($data['description']) 
                ? AirtimePurchaseData::make($data['description']) 
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
        return $this->description?->transaction_date;
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

final readonly class AirtimePurchaseData
{
    public function __construct(
        public ?string $ref,
        public ?float $amount_paid,
        public ?string $transaction_date,
        public ?string $phone,
        public ?string $network,
    ) {}

    /**
     * @param array $data
     * @return AirtimePurchaseData
     */
    public static function make(array $data): AirtimePurchaseData
    {
        return new AirtimePurchaseData(
            ref: $data['ref'] ?? null,
            amount_paid: isset($data['amount_paid']) ? (float) $data['amount_paid'] : null,
            transaction_date: $data['transaction_date'] ?? null,
            phone: $data['phone'] ?? null,
            network: $data['network'] ?? null,
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
            'phone' => $this->phone,
            'network' => $this->network,
        ];
    }
}
