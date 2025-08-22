<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Responses;

final readonly class ServiceValidationResponse
{
    public function __construct(
        public int $code,
        public ?string $message,
        public ?ServiceValidationData $description,
    ) {}

    /**
     * @param array{
     *     code:int,
     *     message?:string|null,
     *     description?:array|null,
     * } $data
     * @return ServiceValidationResponse
     */
    public static function make(array $data): ServiceValidationResponse
    {
        return new ServiceValidationResponse(
            code: $data['code'],
            message: $data['message'] ?? null,
            description: isset($data['description']) && is_array($data['description']) 
                ? ServiceValidationData::make($data['description']) 
                : null,
        );
    }

    /**
     * Check if the validation was successful.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->code === 101;
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
     * Get the biller number (meter number, smartcard, etc.).
     *
     * @return string|null
     */
    public function getBillerNumber(): ?string
    {
        return $this->description?->billerNumber;
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
     * Get the service type.
     *
     * @return string|null
     */
    public function getServiceType(): ?string
    {
        return $this->description?->serviceType;
    }

    /**
     * Get the service status.
     *
     * @return string|null
     */
    public function getServiceStatus(): ?string
    {
        return $this->description?->serviceStatus;
    }

    /**
     * Get the minimum amount.
     *
     * @return float|null
     */
    public function getMinimumAmount(): ?float
    {
        return $this->description?->minimumAmount;
    }

    /**
     * Get the maximum amount.
     *
     * @return float|null
     */
    public function getMaximumAmount(): ?float
    {
        return $this->description?->maximumAmount;
    }

    /**
     * Get the validation code.
     *
     * @return string|null
     */
    public function getValidationCode(): ?string
    {
        return $this->description?->validationCode;
    }

    /**
     * Get the error message if the validation failed.
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
     * Check if the service is active.
     *
     * @return bool
     */
    public function isServiceActive(): bool
    {
        return $this->description?->serviceStatus === 'active';
    }

    /**
     * Check if the biller number is valid.
     *
     * @return bool
     */
    public function isBillerNumberValid(): bool
    {
        return $this->isSuccessful() && $this->description?->billerNumber !== null;
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

final readonly class ServiceValidationData
{
    public function __construct(
        public ?string $serviceId,
        public ?string $billerNumber,
        public ?string $customerName,
        public ?string $customerAddress,
        public ?string $serviceType,
        public ?string $serviceStatus,
        public ?float $minimumAmount,
        public ?float $maximumAmount,
        public ?string $validationCode,
    ) {}

    /**
     * @param array $data
     * @return ServiceValidationData
     */
    public static function make(array $data): ServiceValidationData
    {
        return new ServiceValidationData(
            serviceId: $data['serviceId'] ?? null,
            billerNumber: $data['billerNumber'] ?? null,
            customerName: $data['customerName'] ?? null,
            customerAddress: $data['customerAddress'] ?? null,
            serviceType: $data['serviceType'] ?? null,
            serviceStatus: $data['serviceStatus'] ?? null,
            minimumAmount: isset($data['minimumAmount']) ? (float) $data['minimumAmount'] : null,
            maximumAmount: isset($data['maximumAmount']) ? (float) $data['maximumAmount'] : null,
            validationCode: $data['validationCode'] ?? null,
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
            'serviceId' => $this->serviceId,
            'billerNumber' => $this->billerNumber,
            'customerName' => $this->customerName,
            'customerAddress' => $this->customerAddress,
            'serviceType' => $this->serviceType,
            'serviceStatus' => $this->serviceStatus,
            'minimumAmount' => $this->minimumAmount,
            'maximumAmount' => $this->maximumAmount,
            'validationCode' => $this->validationCode,
        ];
    }
}
