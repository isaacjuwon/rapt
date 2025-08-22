<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final readonly class GenerateRechargeCard
{
    public function __construct(
        public string $network,
        public int $pinDenomination,
        public int $pinQuantity,
        public string $reference,
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
                'pinDenomination' => $this->pinDenomination,
                'pinQuantity' => $this->pinQuantity,
                'ref' => $this->reference,
            ]
        ];
    }

    /**
     * Create a recharge card generation entity from array data.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            network: $data['network'],
            pinDenomination: $data['pin_denomination'],
            pinQuantity: $data['pin_quantity'],
            reference: $data['reference'],
        );
    }

    /**
     * Create a simple recharge card generation.
     *
     * @param string $network
     * @param int $pinDenomination
     * @param int $pinQuantity
     * @param string|null $reference
     * @return self
     */
    public static function simple(string $network, int $pinDenomination, int $pinQuantity, ?string $reference = null): self
    {
        return new self(
            network: $network,
            pinDenomination: $pinDenomination,
            pinQuantity: $pinQuantity,
            reference: $reference ?? self::generateReference(),
        );
    }

    /**
     * Create MTN recharge card generation.
     *
     * @param int $pinDenomination
     * @param int $pinQuantity
     * @param string|null $reference
     * @return self
     */
    public static function mtn(int $pinDenomination, int $pinQuantity, ?string $reference = null): self
    {
        return self::simple('mtn', $pinDenomination, $pinQuantity, $reference);
    }

    /**
     * Create Glo recharge card generation.
     *
     * @param int $pinDenomination
     * @param int $pinQuantity
     * @param string|null $reference
     * @return self
     */
    public static function glo(int $pinDenomination, int $pinQuantity, ?string $reference = null): self
    {
        return self::simple('glo', $pinDenomination, $pinQuantity, $reference);
    }

    /**
     * Create 9Mobile recharge card generation.
     *
     * @param int $pinDenomination
     * @param int $pinQuantity
     * @param string|null $reference
     * @return self
     */
    public static function nineMobile(int $pinDenomination, int $pinQuantity, ?string $reference = null): self
    {
        return self::simple('etisalat', $pinDenomination, $pinQuantity, $reference);
    }

    /**
     * Create Airtel recharge card generation.
     *
     * @param int $pinDenomination
     * @param int $pinQuantity
     * @param string|null $reference
     * @return self
     */
    public static function airtel(int $pinDenomination, int $pinQuantity, ?string $reference = null): self
    {
        return self::simple('airtel', $pinDenomination, $pinQuantity, $reference);
    }

    /**
     * Create ₦100 denomination cards.
     *
     * @param string $network
     * @param int $pinQuantity
     * @param string|null $reference
     * @return self
     */
    public static function naira100(string $network, int $pinQuantity, ?string $reference = null): self
    {
        return self::simple($network, 1, $pinQuantity, $reference);
    }

    /**
     * Create ₦200 denomination cards.
     *
     * @param string $network
     * @param int $pinQuantity
     * @param string|null $reference
     * @return self
     */
    public static function naira200(string $network, int $pinQuantity, ?string $reference = null): self
    {
        return self::simple($network, 2, $pinQuantity, $reference);
    }

    /**
     * Create ₦500 denomination cards.
     *
     * @param string $network
     * @param int $pinQuantity
     * @param string|null $reference
     * @return self
     */
    public static function naira500(string $network, int $pinQuantity, ?string $reference = null): self
    {
        return self::simple($network, 5, $pinQuantity, $reference);
    }

    /**
     * Create ₦1000 denomination cards.
     *
     * @param string $network
     * @param int $pinQuantity
     * @param string|null $reference
     * @return self
     */
    public static function naira1000(string $network, int $pinQuantity, ?string $reference = null): self
    {
        return self::simple($network, 10, $pinQuantity, $reference);
    }

    /**
     * Create bulk generation for multiple denominations.
     *
     * @param string $network
     * @param array $denominationQuantities ['denomination' => quantity]
     * @param string|null $baseReference
     * @return array
     */
    public static function bulk(string $network, array $denominationQuantities, ?string $baseReference = null): array
    {
        $cards = [];
        $baseRef = $baseReference ?? 'BULK_' . time();

        foreach ($denominationQuantities as $denomination => $quantity) {
            $reference = $baseRef . '_' . $denomination . '_' . uniqid();
            $cards[] = self::simple($network, $denomination, $quantity, $reference);
        }

        return $cards;
    }

    /**
     * Generate a unique reference.
     *
     * @return string
     */
    public static function generateReference(): string
    {
        return 'PIN_' . time() . '_' . uniqid();
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
            'pin_denomination' => $this->pinDenomination,
            'pin_quantity' => $this->pinQuantity,
            'reference' => $this->reference,
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
            'mtn' => 'MTN',
            'glo' => 'Glo',
            'etisalat' => '9Mobile',
            'airtel' => 'Airtel',
            default => $this->network,
        };
    }

    /**
     * Get denomination amount.
     *
     * @return int
     */
    public function getDenominationAmount(): int
    {
        return match ($this->pinDenomination) {
            1 => 100,
            2 => 200,
            4 => 400,
            5 => 500,
            10 => 1000,
            default => 0,
        };
    }

    /**
     * Get denomination display name.
     *
     * @return string
     */
    public function getDenominationName(): string
    {
        $amount = $this->getDenominationAmount();
        return '₦' . number_format($amount);
    }

    /**
     * Calculate total cost.
     *
     * @return int
     */
    public function getTotalCost(): int
    {
        return $this->getDenominationAmount() * $this->pinQuantity;
    }

    /**
     * Format total cost for display.
     *
     * @param string $currency
     * @return string
     */
    public function getFormattedTotalCost(string $currency = '₦'): string
    {
        return $currency . number_format($this->getTotalCost());
    }

    /**
     * Get generation summary.
     *
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'network' => $this->getNetworkName(),
            'denomination' => $this->getDenominationName(),
            'quantity' => $this->pinQuantity,
            'total_cost' => $this->getFormattedTotalCost(),
            'reference' => $this->reference,
        ];
    }

    /**
     * Validate denomination.
     *
     * @return bool
     */
    public function hasValidDenomination(): bool
    {
        return in_array($this->pinDenomination, [1, 2, 4, 5, 10]);
    }

    /**
     * Validate network.
     *
     * @return bool
     */
    public function hasValidNetwork(): bool
    {
        return in_array(strtolower($this->network), ['mtn', 'glo', 'etisalat', 'airtel']);
    }

    /**
     * Validate quantity.
     *
     * @param int $maxQuantity
     * @return bool
     */
    public function hasValidQuantity(int $maxQuantity = 100): bool
    {
        return $this->pinQuantity > 0 && $this->pinQuantity <= $maxQuantity;
    }

    /**
     * Check if the entity is valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->hasValidDenomination() && 
               $this->hasValidNetwork() && 
               $this->hasValidQuantity() && 
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

        if (!$this->hasValidDenomination()) {
            $errors[] = 'Invalid denomination. Supported: 1 (₦100), 2 (₦200), 4 (₦400), 5 (₦500), 10 (₦1000)';
        }

        if (!$this->hasValidNetwork()) {
            $errors[] = 'Invalid network. Supported: mtn, glo, etisalat, airtel';
        }

        if (!$this->hasValidQuantity()) {
            $errors[] = 'Invalid quantity. Must be between 1 and 100';
        }

        if (empty($this->reference)) {
            $errors[] = 'Reference is required';
        }

        return $errors;
    }

    /**
     * Check if this is a bulk generation.
     *
     * @return bool
     */
    public function isBulkGeneration(): bool
    {
        return $this->pinQuantity > 10;
    }

    /**
     * Get estimated processing time in minutes.
     *
     * @return int
     */
    public function getEstimatedProcessingTime(): int
    {
        // Estimate based on quantity
        if ($this->pinQuantity <= 10) {
            return 1;
        } elseif ($this->pinQuantity <= 50) {
            return 3;
        } else {
            return 5;
        }
    }
}
