<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final readonly class PurchaseAirtime
{
    public function __construct(
        public string $network,
        public string $phone,
        public int $amount,
        public string $reference,
        public bool $ported = false,
    ) {}

    /**
     * Convert the entity to a request body array.
     *
     * @return array
     */
    public function toRequestBody(): array
    {
        return [
            'json' => [
                'network' => $this->network,
                'phone' => $this->phone,
                'amount' => $this->amount,
                'ref' => $this->reference,
                'ported' => $this->ported,
            ]
        ];
    }

    /**
     * Create an airtime purchase entity from array data.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            network: $data['network'],
            phone: $data['phone'],
            amount: $data['amount'],
            reference: $data['reference'],
            ported: $data['ported'] ?? false,
        );
    }

    /**
     * Create a simple airtime purchase.
     *
     * @param string $network
     * @param string $phone
     * @param int $amount
     * @param string|null $reference
     * @return self
     */
    public static function simple(string $network, string $phone, int $amount, ?string $reference = null): self
    {
        return new self(
            network: $network,
            phone: $phone,
            amount: $amount,
            reference: $reference ?? self::generateReference(),
        );
    }

    /**
     * Create MTN airtime purchase.
     *
     * @param string $phone
     * @param int $amount
     * @param string|null $reference
     * @return self
     */
    public static function mtn(string $phone, int $amount, ?string $reference = null): self
    {
        return self::simple('01', $phone, $amount, $reference);
    }

    /**
     * Create Glo airtime purchase.
     *
     * @param string $phone
     * @param int $amount
     * @param string|null $reference
     * @return self
     */
    public static function glo(string $phone, int $amount, ?string $reference = null): self
    {
        return self::simple('02', $phone, $amount, $reference);
    }

    /**
     * Create 9Mobile airtime purchase.
     *
     * @param string $phone
     * @param int $amount
     * @param string|null $reference
     * @return self
     */
    public static function nineMobile(string $phone, int $amount, ?string $reference = null): self
    {
        return self::simple('03', $phone, $amount, $reference);
    }

    /**
     * Create Airtel airtime purchase.
     *
     * @param string $phone
     * @param int $amount
     * @param string|null $reference
     * @return self
     */
    public static function airtel(string $phone, int $amount, ?string $reference = null): self
    {
        return self::simple('04', $phone, $amount, $reference);
    }

    /**
     * Create airtime purchase for ported number.
     *
     * @param string $network
     * @param string $phone
     * @param int $amount
     * @param string|null $reference
     * @return self
     */
    public static function ported(string $network, string $phone, int $amount, ?string $reference = null): self
    {
        return new self(
            network: $network,
            phone: $phone,
            amount: $amount,
            reference: $reference ?? self::generateReference(),
            ported: true,
        );
    }

    /**
     * Generate a unique reference.
     *
     * @return string
     */
    public static function generateReference(): string
    {
        return 'AIR_' . time() . '_' . uniqid();
    }

    /**
     * Convert to array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'network' => $this->network,
            'phone' => $this->phone,
            'amount' => $this->amount,
            'reference' => $this->reference,
            'ported' => $this->ported,
        ];
    }

    /**
     * Get network display name.
     *
     * @return string
     */
    public function getNetworkName(): string
    {
        return match ($this->network) {
            '01' => 'MTN',
            '02' => 'Glo',
            '03' => '9Mobile',
            '04' => 'Airtel',
            default => $this->network,
        };
    }

    /**
     * Format phone number for display.
     *
     * @return string
     */
    public function getFormattedPhone(): string
    {
        $phone = $this->phone;
        if (strlen($phone) === 11 && str_starts_with($phone, '0')) {
            return substr($phone, 0, 4) . ' ' . substr($phone, 4, 3) . ' ' . substr($phone, 7);
        }
        return $phone;
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
     * Check if this is a ported number purchase.
     *
     * @return bool
     */
    public function isPortedNumber(): bool
    {
        return $this->ported;
    }

    /**
     * Validate phone number format.
     *
     * @return bool
     */
    public function hasValidPhoneNumber(): bool
    {
        return preg_match('/^(0|\+234)[7-9][0-1]\d{8}$/', $this->phone) === 1;
    }

    /**
     * Validate amount range.
     *
     * @param int $minAmount
     * @param int $maxAmount
     * @return bool
     */
    public function hasValidAmount(int $minAmount = 50, int $maxAmount = 50000): bool
    {
        return $this->amount >= $minAmount && $this->amount <= $maxAmount;
    }

    /**
     * Get validation errors.
     *
     * @return array
     */
    public function getValidationErrors(): array
    {
        $errors = [];

        if (!$this->hasValidPhoneNumber()) {
            $errors[] = 'Invalid phone number format';
        }

        if (!$this->hasValidAmount()) {
            $errors[] = 'Amount must be between ₦50 and ₦50,000';
        }

        if (empty($this->network)) {
            $errors[] = 'Network is required';
        }

        if (empty($this->reference)) {
            $errors[] = 'Reference is required';
        }

        return $errors;
    }

    /**
     * Check if the entity is valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return empty($this->getValidationErrors());
    }
}
