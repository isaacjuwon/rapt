<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final readonly class PurchaseData
{
    public function __construct(
        public string $network,
        public string $phone,
        public int $planCode,
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
                'data_plan' => $this->planCode,
                'ref' => $this->reference,
                'ported' => $this->ported,
            ]
        ];
    }

    /**
     * Create a data purchase entity from array data.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            network: $data['network'],
            phone: $data['phone'],
            planCode: $data['plan_code'],
            reference: $data['reference'],
            ported: $data['ported'] ?? false,
        );
    }

    /**
     * Create a simple data purchase.
     *
     * @param string $network
     * @param string $phone
     * @param int $planCode
     * @param string|null $reference
     * @return self
     */
    public static function simple(string $network, string $phone, int $planCode, ?string $reference = null): self
    {
        return new self(
            network: $network,
            phone: $phone,
            planCode: $planCode,
            reference: $reference ?? self::generateReference(),
        );
    }

    /**
     * Create MTN data purchase.
     *
     * @param string $phone
     * @param int $planCode
     * @param string|null $reference
     * @return self
     */
    public static function mtn(string $phone, int $planCode, ?string $reference = null): self
    {
        return self::simple('01', $phone, $planCode, $reference);
    }

    /**
     * Create Glo data purchase.
     *
     * @param string $phone
     * @param int $planCode
     * @param string|null $reference
     * @return self
     */
    public static function glo(string $phone, int $planCode, ?string $reference = null): self
    {
        return self::simple('02', $phone, $planCode, $reference);
    }

    /**
     * Create 9Mobile data purchase.
     *
     * @param string $phone
     * @param int $planCode
     * @param string|null $reference
     * @return self
     */
    public static function nineMobile(string $phone, int $planCode, ?string $reference = null): self
    {
        return self::simple('03', $phone, $planCode, $reference);
    }

    /**
     * Create Airtel data purchase.
     *
     * @param string $phone
     * @param int $planCode
     * @param string|null $reference
     * @return self
     */
    public static function airtel(string $phone, int $planCode, ?string $reference = null): self
    {
        return self::simple('04', $phone, $planCode, $reference);
    }

    /**
     * Create data purchase for ported number.
     *
     * @param string $network
     * @param string $phone
     * @param int $planCode
     * @param string|null $reference
     * @return self
     */
    public static function ported(string $network, string $phone, int $planCode, ?string $reference = null): self
    {
        return new self(
            network: $network,
            phone: $phone,
            planCode: $planCode,
            reference: $reference ?? self::generateReference(),
            ported: true,
        );
    }

    /**
     * Create MTN 1GB SME data purchase.
     *
     * @param string $phone
     * @param string|null $reference
     * @return self
     */
    public static function mtn1GB(string $phone, ?string $reference = null): self
    {
        return self::mtn($phone, 57, $reference); // 1GB SME plan
    }

    /**
     * Create MTN 2GB SME data purchase.
     *
     * @param string $phone
     * @param string|null $reference
     * @return self
     */
    public static function mtn2GB(string $phone, ?string $reference = null): self
    {
        return self::mtn($phone, 59, $reference); // 2GB SME plan
    }

    /**
     * Create MTN 5GB SME data purchase.
     *
     * @param string $phone
     * @param string|null $reference
     * @return self
     */
    public static function mtn5GB(string $phone, ?string $reference = null): self
    {
        return self::mtn($phone, 61, $reference); // 5GB SME plan
    }

    /**
     * Create Glo 1GB data purchase.
     *
     * @param string $phone
     * @param string|null $reference
     * @return self
     */
    public static function glo1GB(string $phone, ?string $reference = null): self
    {
        return self::glo($phone, 1442, $reference); // 1GB plan
    }

    /**
     * Create Airtel 1GB data purchase.
     *
     * @param string $phone
     * @param string|null $reference
     * @return self
     */
    public static function airtel1GB(string $phone, ?string $reference = null): self
    {
        return self::airtel($phone, 420, $reference); // 1GB AWOOF plan
    }

    /**
     * Generate a unique reference.
     *
     * @return string
     */
    public static function generateReference(): string
    {
        return 'DATA_' . time() . '_' . uniqid();
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
            'plan_code' => $this->planCode,
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
     * Get plan details.
     *
     * @return array|null
     */
    public function getPlanDetails(): ?array
    {
        $plans = match ($this->network) {
            '01' => [
                57 => ['name' => '1GB (SME) - 30days', 'amount' => 620],
                58 => ['name' => '500MB (SME) - 30days', 'amount' => 420],
                59 => ['name' => '2GB (SME) - 30days', 'amount' => 1240],
                60 => ['name' => '3GB (SME) - 30days', 'amount' => 1860],
                61 => ['name' => '5GB (SME) - 30days', 'amount' => 3100],
                62 => ['name' => '10GB (SME) - 30days', 'amount' => 6200],
            ],
            '02' => [
                1442 => ['name' => 'Glo Data (SME) N450 - 1GB 30 days', 'amount' => 450],
                1443 => ['name' => 'Glo Data (SME) N1,350 - 3GB 30 days', 'amount' => 1350],
                1444 => ['name' => 'Glo Data (SME) N4500 - 10GB - 30 Days', 'amount' => 4500],
                1445 => ['name' => 'Glo Data (SME) N900 - 2GB 30 days', 'amount' => 900],
                1447 => ['name' => 'Glo Data (SME) N2250 - 5GB 30 days', 'amount' => 2250],
            ],
            '03' => [
                103 => ['name' => '1.6GB (SME) - 30days', 'amount' => 500],
                105 => ['name' => '2.3GB (SME) - 30days', 'amount' => 700],
                107 => ['name' => '3.3GB (SME) - 30days', 'amount' => 1000],
                109 => ['name' => '5GB (SME) - 30days', 'amount' => 1800],
                112 => ['name' => '10GB (SME) - 30days', 'amount' => 3050],
            ],
            '04' => [
                86 => ['name' => '500MB (CG) - 30days', 'amount' => 425],
                420 => ['name' => '1GB (AWOOF) - 1 DAY VALIDITY', 'amount' => 400],
                421 => ['name' => '3GB (AWOOF) - 7 DAYS VALIDITY', 'amount' => 1200],
                423 => ['name' => '10GB (AWOOF) - 30 DAYS VALIDITY', 'amount' => 3500],
            ],
            default => [],
        };

        return $plans[$this->planCode] ?? null;
    }

    /**
     * Get plan amount.
     *
     * @return int|null
     */
    public function getPlanAmount(): ?int
    {
        $plan = $this->getPlanDetails();
        return $plan['amount'] ?? null;
    }

    /**
     * Get plan name.
     *
     * @return string|null
     */
    public function getPlanName(): ?string
    {
        $plan = $this->getPlanDetails();
        return $plan['name'] ?? null;
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
     * Check if the entity is valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->hasValidPhoneNumber() && 
               !empty($this->network) && 
               !empty($this->reference) && 
               $this->planCode > 0;
    }
}
