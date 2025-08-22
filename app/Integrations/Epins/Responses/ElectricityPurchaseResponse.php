<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Responses;

final readonly class ElectricityPurchaseResponse
{
    public function __construct(
        public int $code,
        public ?string $message,
        public ?ElectricityPurchaseData $description,
    ) {}

    /**
     * @param array{
     *     code:int,
     *     message?:string|null,
     *     description?:array|null,
     * } $data
     * @return ElectricityPurchaseResponse
     */
    public static function make(array $data): ElectricityPurchaseResponse
    {
        return new ElectricityPurchaseResponse(
            code: $data['code'],
            message: $data['message'] ?? null,
            description: isset($data['description']) && is_array($data['description']) 
                ? ElectricityPurchaseData::make($data['description']) 
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
     * Get the meter number.
     *
     * @return string|null
     */
    public function getMeterNumber(): ?string
    {
        return $this->description?->meterNumber;
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
     * Get the customer address.
     *
     * @return string|null
     */
    public function getCustomerAddress(): ?string
    {
        return $this->description?->customerAddress;
    }

    /**
     * Get the token.
     *
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->description?->token;
    }

    /**
     * Get the units.
     *
     * @return float|null
     */
    public function getUnits(): ?float
    {
        return $this->description?->units;
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

final readonly class ElectricityPurchaseData
{
    public function __construct(
        public ?string $ref,
        public ?float $amount_paid,
        public ?string $transaction_date,
        public ?string $meterNumber,
        public ?string $customerName,
        public ?string $customerAddress,
        public ?string $token,
        public ?float $units,
        public ?string $serviceId,
    ) {}

    /**
     * @param array $data
     * @return ElectricityPurchaseData
     */
    public static function make(array $data): ElectricityPurchaseData
    {
        return new ElectricityPurchaseData(
            ref: $data['ref'] ?? null,
            amount_paid: isset($data['amount_paid']) ? (float) $data['amount_paid'] : null,
            transaction_date: $data['transaction_date'] ?? null,
            meterNumber: $data['meterNumber'] ?? null,
            customerName: $data['customerName'] ?? null,
            customerAddress: $data['customerAddress'] ?? null,
            token: $data['token'] ?? null,
            units: isset($data['units']) ? (float) $data['units'] : null,
            serviceId: $data['serviceId'] ?? null,
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
            'meterNumber' => $this->meterNumber,
            'customerName' => $this->customerName,
            'customerAddress' => $this->customerAddress,
            'token' => $this->token,
            'units' => $this->units,
            'serviceId' => $this->serviceId,
        ];
    }
}
