<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final readonly class PurchaseElectricity
{
    public function __construct(
        public string $serviceId,
        public string $meterNumber,
        public string $meterType,
        public int $amount,
        public string $reference,
        public ?string $customerName = null,
        public ?string $customerAddress = null,
        public ?string $customerPhone = null,
    ) {}

    /**
     * Convert the entity to a request body array.
     *
     * @return array
     */
    public function toRequestBody(): array
    {
        $data = [
            'serviceId' => $this->serviceId,
            'meterNumber' => $this->meterNumber,
            'vcode' => $this->meterType,
            'amount' => $this->amount,
            'ref' => $this->reference,
        ];

        if ($this->customerName) {
            $data['customer_name'] = $this->customerName;
        }

        if ($this->customerAddress) {
            $data['customer_address'] = $this->customerAddress;
        }

        if ($this->customerPhone) {
            $data['customer_phone'] = $this->customerPhone;
        }

        return ['json' => $data];
    }

    /**
     * Create an electricity purchase entity from array data.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            serviceId: $data['service_id'],
            meterNumber: $data['meter_number'],
            meterType: $data['meter_type'],
            amount: $data['amount'],
            reference: $data['reference'],
            customerName: $data['customer_name'] ?? null,
            customerAddress: $data['customer_address'] ?? null,
            customerPhone: $data['customer_phone'] ?? null,
        );
    }

    /**
     * Create a simple electricity purchase.
     *
     * @param string $serviceId
     * @param string $meterNumber
     * @param string $meterType
     * @param int $amount
     * @param string|null $reference
     * @return self
     */
    public static function simple(
        string $serviceId,
        string $meterNumber,
        string $meterType,
        int $amount,
        ?string $reference = null
    ): self {
        return new self(
            serviceId: $serviceId,
            meterNumber: $meterNumber,
            meterType: $meterType,
            amount: $amount,
            reference: $reference ?? self::generateReference(),
        );
    }

    /**
     * Create IKEDC prepaid electricity purchase.
     *
     * @param string $meterNumber
     * @param int $amount
     * @param string|null $reference
     * @return self
     */
    public static function ikedcPrepaid(string $meterNumber, int $amount, ?string $reference = null): self
    {
        return self::simple('ikeja-electric', $meterNumber, 'prepaid', $amount, $reference);
    }

    /**
     * Create EKEDC prepaid electricity purchase.
     *
     * @param string $meterNumber
     * @param int $amount
     * @param string|null $reference
     * @return self
     */
    public static function ekedcPrepaid(string $meterNumber, int $amount, ?string $reference = null): self
    {
        return self::simple('eko-electric', $meterNumber, 'prepaid', $amount, $reference);
    }

    /**
     * Create AEDC prepaid electricity purchase.
     *
     * @param string $meterNumber
     * @param int $amount
     * @param string|null $reference
     * @return self
     */
    public static function aedcPrepaid(string $meterNumber, int $amount, ?string $reference = null): self
    {
        return self::simple('abuja-electric', $meterNumber, 'prepaid', $amount, $reference);
    }

    /**
     * Create PHEDC prepaid electricity purchase.
     *
     * @param string $meterNumber
     * @param int $amount
     * @param string|null $reference
     * @return self
     */
    public static function phedcPrepaid(string $meterNumber, int $amount, ?string $reference = null): self
    {
        return self::simple('portharcourt-electric', $meterNumber, 'prepaid', $amount, $reference);
    }

    /**
     * Create electricity purchase with customer details.
     *
     * @param string $serviceId
     * @param string $meterNumber
     * @param string $meterType
     * @param int $amount
     * @param string $customerName
     * @param string|null $customerAddress
     * @param string|null $customerPhone
     * @param string|null $reference
     * @return self
     */
    public static function withCustomerDetails(
        string $serviceId,
        string $meterNumber,
        string $meterType,
        int $amount,
        string $customerName,
        ?string $customerAddress = null,
        ?string $customerPhone = null,
        ?string $reference = null
    ): self {
        return new self(
            serviceId: $serviceId,
            meterNumber: $meterNumber,
            meterType: $meterType,
            amount: $amount,
            reference: $reference ?? self::generateReference(),
            customerName: $customerName,
            customerAddress: $customerAddress,
            customerPhone: $customerPhone,
        );
    }

    /**
     * Generate a unique reference.
     *
     * @return string
     */
    public static function generateReference(): string
    {
        return 'ELEC_' . time() . '_' . uniqid();
    }

    /**
     * Convert to array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'service_id' => $this->serviceId,
            'meter_number' => $this->meterNumber,
            'meter_type' => $this->meterType,
            'amount' => $this->amount,
            'reference' => $this->reference,
            'customer_name' => $this->customerName,
            'customer_address' => $this->customerAddress,
            'customer_phone' => $this->customerPhone,
        ];
    }

    /**
     * Get service display name.
     *
     * @return string
     */
    public function getServiceDisplayName(): string
    {
        return match ($this->serviceId) {
            'ikeja-electric' => 'Ikeja Electric (IKEDC)',
            'eko-electric' => 'Eko Electric (EKEDC)',
            'portharcourt-electric' => 'Port Harcourt Electric (PHEDC)',
            'jos-electric' => 'Jos Electric (JEDC)',
            'kano-electric' => 'Kano Electric (KEDC)',
            'ibadan-electric' => 'Ibadan Electric (IBEDC)',
            'enugu-electric' => 'Enugu Electric (EEDC)',
            'abuja-electric' => 'Abuja Electric (AEDC)',
            'benin-electric' => 'Benin Electric (BEDC)',
            default => $this->serviceId,
        };
    }

    /**
     * Get meter type display name.
     *
     * @return string
     */
    public function getMeterTypeDisplayName(): string
    {
        return match ($this->meterType) {
            'prepaid' => 'Prepaid Meter',
            'postpaid' => 'Postpaid Meter',
            default => $this->meterType,
        };
    }

    /**
     * Format amount for display.
     *
     * @param string $currency
     * @return string
     */
    public function getFormattedAmount(string $currency = '₦'): string
    {
        return $currency . number_format($this->amount, 2);
    }

    /**
     * Estimate units (rough calculation).
     *
     * @param float $ratePerKwh
     * @return float
     */
    public function getEstimatedUnits(float $ratePerKwh = 50.0): float
    {
        return round($this->amount / $ratePerKwh, 2);
    }

    /**
     * Check if customer details are provided.
     *
     * @return bool
     */
    public function hasCustomerDetails(): bool
    {
        return !empty($this->customerName);
    }

    /**
     * Validate meter number format.
     *
     * @return bool
     */
    public function hasValidMeterNumber(): bool
    {
        return preg_match('/^\d{10,13}$/', $this->meterNumber) === 1;
    }

    /**
     * Validate amount range.
     *
     * @return bool
     */
    public function hasValidAmount(): bool
    {
        $minimums = [
            'ikeja-electric' => 500,
            'eko-electric' => 500,
            'portharcourt-electric' => 500,
            'jos-electric' => 500,
            'kano-electric' => 500,
            'ibadan-electric' => 500,
            'enugu-electric' => 500,
            'abuja-electric' => 500,
            'benin-electric' => 500,
        ];

        $maximums = [
            'ikeja-electric' => 50000,
            'eko-electric' => 50000,
            'portharcourt-electric' => 50000,
            'jos-electric' => 50000,
            'kano-electric' => 50000,
            'ibadan-electric' => 50000,
            'enugu-electric' => 50000,
            'abuja-electric' => 50000,
            'benin-electric' => 50000,
        ];

        $minAmount = $minimums[$this->serviceId] ?? 500;
        $maxAmount = $maximums[$this->serviceId] ?? 50000;

        return $this->amount >= $minAmount && $this->amount <= $maxAmount;
    }

    /**
     * Check if the entity is valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->hasValidMeterNumber() && 
               $this->hasValidAmount() && 
               !empty($this->serviceId) && 
               !empty($this->meterType) && 
               !empty($this->reference);
    }

    /**
     * Get validation errors.
     *
     * @return array
     */
    public function getValidationErrors(): array
    {
        $errors = [];

        if (!$this->hasValidMeterNumber()) {
            $errors[] = 'Invalid meter number format (should be 10-13 digits)';
        }

        if (!$this->hasValidAmount()) {
            $errors[] = 'Amount must be between ₦500 and ₦50,000';
        }

        if (empty($this->serviceId)) {
            $errors[] = 'Service ID is required';
        }

        if (empty($this->meterType)) {
            $errors[] = 'Meter type is required';
        }

        if (empty($this->reference)) {
            $errors[] = 'Reference is required';
        }

        return $errors;
    }
}
