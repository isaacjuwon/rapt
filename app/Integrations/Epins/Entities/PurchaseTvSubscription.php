<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final readonly class PurchaseTvSubscription
{
    public function __construct(
        public string $serviceId,
        public string $smartcardNumber,
        public string $vcode,
        public string $reference,
        public ?string $customerName = null,
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
            'billerNumber' => $this->smartcardNumber,
            'vcode' => $this->vcode,
            'ref' => $this->reference,
        ];

        if ($this->customerName) {
            $data['customer_name'] = $this->customerName;
        }

        if ($this->customerPhone) {
            $data['customer_phone'] = $this->customerPhone;
        }

        return ['json' => $data];
    }

    /**
     * Create a TV subscription entity from array data.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            serviceId: $data['service_id'],
            smartcardNumber: $data['smartcard_number'],
            vcode: $data['vcode'],
            reference: $data['reference'],
            customerName: $data['customer_name'] ?? null,
            customerPhone: $data['customer_phone'] ?? null,
        );
    }

    /**
     * Create a simple TV subscription purchase.
     *
     * @param string $serviceId
     * @param string $smartcardNumber
     * @param string $vcode
     * @param string|null $reference
     * @return self
     */
    public static function simple(
        string $serviceId,
        string $smartcardNumber,
        string $vcode,
        ?string $reference = null
    ): self {
        return new self(
            serviceId: $serviceId,
            smartcardNumber: $smartcardNumber,
            vcode: $vcode,
            reference: $reference ?? self::generateReference(),
        );
    }

    /**
     * Create DStv subscription purchase.
     *
     * @param string $smartcardNumber
     * @param string $bouquetCode
     * @param string|null $reference
     * @return self
     */
    public static function dstv(string $smartcardNumber, string $bouquetCode, ?string $reference = null): self
    {
        return self::simple('dstv', $smartcardNumber, $bouquetCode, $reference);
    }

    /**
     * Create GOtv subscription purchase.
     *
     * @param string $smartcardNumber
     * @param string $bouquetCode
     * @param string|null $reference
     * @return self
     */
    public static function gotv(string $smartcardNumber, string $bouquetCode, ?string $reference = null): self
    {
        return self::simple('gotv', $smartcardNumber, $bouquetCode, $reference);
    }

    /**
     * Create StarTimes subscription purchase.
     *
     * @param string $smartcardNumber
     * @param string $bouquetCode
     * @param string|null $reference
     * @return self
     */
    public static function startimes(string $smartcardNumber, string $bouquetCode, ?string $reference = null): self
    {
        return self::simple('startimes', $smartcardNumber, $bouquetCode, $reference);
    }

    /**
     * Create DStv Padi subscription.
     *
     * @param string $smartcardNumber
     * @param string|null $reference
     * @return self
     */
    public static function dstvPadi(string $smartcardNumber, ?string $reference = null): self
    {
        return self::dstv($smartcardNumber, 'PADI', $reference);
    }

    /**
     * Create DStv Compact subscription.
     *
     * @param string $smartcardNumber
     * @param string|null $reference
     * @return self
     */
    public static function dstvCompact(string $smartcardNumber, ?string $reference = null): self
    {
        return self::dstv($smartcardNumber, 'COMPACT', $reference);
    }

    /**
     * Create DStv Premium subscription.
     *
     * @param string $smartcardNumber
     * @param string|null $reference
     * @return self
     */
    public static function dstvPremium(string $smartcardNumber, ?string $reference = null): self
    {
        return self::dstv($smartcardNumber, 'PREMIUM', $reference);
    }

    /**
     * Create GOtv Smallie subscription.
     *
     * @param string $smartcardNumber
     * @param string|null $reference
     * @return self
     */
    public static function gotvSmallie(string $smartcardNumber, ?string $reference = null): self
    {
        return self::gotv($smartcardNumber, 'SMALLIE', $reference);
    }

    /**
     * Create GOtv Jolli subscription.
     *
     * @param string $smartcardNumber
     * @param string|null $reference
     * @return self
     */
    public static function gotvJolli(string $smartcardNumber, ?string $reference = null): self
    {
        return self::gotv($smartcardNumber, 'JOLLI', $reference);
    }

    /**
     * Create TV subscription with customer details.
     *
     * @param string $serviceId
     * @param string $smartcardNumber
     * @param string $vcode
     * @param string $customerName
     * @param string|null $customerPhone
     * @param string|null $reference
     * @return self
     */
    public static function withCustomerDetails(
        string $serviceId,
        string $smartcardNumber,
        string $vcode,
        string $customerName,
        ?string $customerPhone = null,
        ?string $reference = null
    ): self {
        return new self(
            serviceId: $serviceId,
            smartcardNumber: $smartcardNumber,
            vcode: $vcode,
            reference: $reference ?? self::generateReference(),
            customerName: $customerName,
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
        return 'TV_' . time() . '_' . uniqid();
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
            'smartcard_number' => $this->smartcardNumber,
            'vcode' => $this->vcode,
            'reference' => $this->reference,
            'customer_name' => $this->customerName,
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
            'dstv' => 'DStv',
            'gotv' => 'GOtv',
            'startimes' => 'StarTimes',
            default => $this->serviceId,
        };
    }

    /**
     * Get bouquet details.
     *
     * @return array|null
     */
    public function getBouquetDetails(): ?array
    {
        $bouquets = match ($this->serviceId) {
            'dstv' => [
                'PADI' => ['name' => 'DStv Padi', 'amount' => 2150],
                'YANGA' => ['name' => 'DStv Yanga', 'amount' => 2950],
                'CONFAM' => ['name' => 'DStv Confam', 'amount' => 5300],
                'COMPACT' => ['name' => 'DStv Compact', 'amount' => 9000],
                'COMPACTP' => ['name' => 'DStv Compact Plus', 'amount' => 14250],
                'PREMIUM' => ['name' => 'DStv Premium', 'amount' => 21000],
            ],
            'gotv' => [
                'SMALLIE' => ['name' => 'GOtv Smallie', 'amount' => 900],
                'JINJA' => ['name' => 'GOtv Jinja', 'amount' => 1900],
                'JOLLI' => ['name' => 'GOtv Jolli', 'amount' => 2800],
                'MAX' => ['name' => 'GOtv Max', 'amount' => 4150],
            ],
            'startimes' => [
                'NOVA' => ['name' => 'StarTimes Nova', 'amount' => 900],
                'BASIC' => ['name' => 'StarTimes Basic', 'amount' => 1800],
                'SMART' => ['name' => 'StarTimes Smart', 'amount' => 2500],
                'CLASSIC' => ['name' => 'StarTimes Classic', 'amount' => 2700],
                'SUPER' => ['name' => 'StarTimes Super', 'amount' => 4200],
            ],
            default => [],
        };

        return $bouquets[$this->vcode] ?? null;
    }

    /**
     * Get bouquet name.
     *
     * @return string|null
     */
    public function getBouquetName(): ?string
    {
        $bouquet = $this->getBouquetDetails();
        return $bouquet['name'] ?? null;
    }

    /**
     * Get bouquet amount.
     *
     * @return int|null
     */
    public function getBouquetAmount(): ?int
    {
        $bouquet = $this->getBouquetDetails();
        return $bouquet['amount'] ?? null;
    }

    /**
     * Format amount for display.
     *
     * @param string $currency
     * @return string|null
     */
    public function getFormattedAmount(string $currency = '₦'): ?string
    {
        $amount = $this->getBouquetAmount();
        return $amount ? $currency . number_format($amount, 2) : null;
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
     * Validate smartcard number format.
     *
     * @return bool
     */
    public function hasValidSmartcardNumber(): bool
    {
        return match ($this->serviceId) {
            'dstv', 'gotv' => preg_match('/^\d{10,11}$/', $this->smartcardNumber) === 1,
            'startimes' => preg_match('/^\d{10,13}$/', $this->smartcardNumber) === 1,
            default => preg_match('/^\d{10,13}$/', $this->smartcardNumber) === 1,
        };
    }

    /**
     * Check if the entity is valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->hasValidSmartcardNumber() && 
               !empty($this->serviceId) && 
               !empty($this->vcode) && 
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

        if (!$this->hasValidSmartcardNumber()) {
            $errors[] = 'Invalid smartcard number format';
        }

        if (empty($this->serviceId)) {
            $errors[] = 'Service ID is required';
        }

        if (empty($this->vcode)) {
            $errors[] = 'Bouquet code is required';
        }

        if (empty($this->reference)) {
            $errors[] = 'Reference is required';
        }

        return $errors;
    }
}
