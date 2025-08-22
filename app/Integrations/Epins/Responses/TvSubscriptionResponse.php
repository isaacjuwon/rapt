<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Responses;

final readonly class TvSubscriptionResponse
{
    public function __construct(
        public int $code,
        public ?string $message,
        public ?TvSubscriptionData $description,
    ) {}

    /**
     * @param array{
     *     code:int,
     *     message?:string|null,
     *     description?:array|null,
     * } $data
     * @return TvSubscriptionResponse
     */
    public static function make(array $data): TvSubscriptionResponse
    {
        return new TvSubscriptionResponse(
            code: $data['code'],
            message: $data['message'] ?? null,
            description: isset($data['description']) && is_array($data['description']) 
                ? TvSubscriptionData::make($data['description']) 
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
     * Get the smartcard number.
     *
     * @return string|null
     */
    public function getSmartcardNumber(): ?string
    {
        return $this->description?->smartcardNumber;
    }

    /**
     * Get the customer name.
     *
     * @return string|null
     */
    public function getCustomerName(): ?string
    {
        return $this->description?->customerName;
    }

    /**
     * Get the bouquet/package name.
     *
     * @return string|null
     */
    public function getBouquet(): ?string
    {
        return $this->description?->bouquet;
    }

    /**
     * Get the service ID.
     *
     * @return string|null
     */
    public function getServiceId(): ?string
    {
        return $this->description?->serviceId;
    }

    /**
     * Get the renewal period (in months).
     *
     * @return int|null
     */
    public function getPeriod(): ?int
    {
        return $this->description?->period;
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

final readonly class TvSubscriptionData
{
    public function __construct(
        public ?string $ref,
        public ?float $amount_paid,
        public ?string $transaction_date,
        public ?string $smartcardNumber,
        public ?string $customerName,
        public ?string $bouquet,
        public ?string $serviceId,
        public ?int $period,
    ) {}

    /**
     * @param array $data
     * @return TvSubscriptionData
     */
    public static function make(array $data): TvSubscriptionData
    {
        return new TvSubscriptionData(
            ref: $data['ref'] ?? null,
            amount_paid: isset($data['amount_paid']) ? (float) $data['amount_paid'] : null,
            transaction_date: $data['transaction_date'] ?? null,
            smartcardNumber: $data['smartcardNumber'] ?? null,
            customerName: $data['customerName'] ?? null,
            bouquet: $data['bouquet'] ?? null,
            serviceId: $data['serviceId'] ?? null,
            period: isset($data['period']) ? (int) $data['period'] : null,
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
            'smartcardNumber' => $this->smartcardNumber,
            'customerName' => $this->customerName,
            'bouquet' => $this->bouquet,
            'serviceId' => $this->serviceId,
            'period' => $this->period,
        ];
    }
}
