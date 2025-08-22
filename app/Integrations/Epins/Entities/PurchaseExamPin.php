<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Entities;

final readonly class PurchaseExamPin
{
    public function __construct(
        public string $service,
        public string $vcode,
        public int $amount,
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
                'service' => $this->service,
                'vcode' => $this->vcode,
                'amount' => $this->amount,
                'ref' => $this->reference,
            ]
        ];
    }

    /**
     * Create an exam PIN purchase entity from array data.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            service: $data['service'],
            vcode: $data['vcode'],
            amount: $data['amount'],
            reference: $data['reference'],
        );
    }

    /**
     * Create a simple exam PIN purchase.
     *
     * @param string $service
     * @param string $vcode
     * @param int $amount
     * @param string|null $reference
     * @return self
     */
    public static function simple(string $service, string $vcode, int $amount, ?string $reference = null): self
    {
        return new self(
            service: $service,
            vcode: $vcode,
            amount: $amount,
            reference: $reference ?? self::generateReference(),
        );
    }

    /**
     * Create WAEC result checker PIN purchase.
     *
     * @param string|null $reference
     * @return self
     */
    public static function waec(?string $reference = null): self
    {
        return self::simple('waec', 'waecdirect', 1000, $reference);
    }

    /**
     * Create NECO result checker PIN purchase.
     *
     * @param string|null $reference
     * @return self
     */
    public static function neco(?string $reference = null): self
    {
        return self::simple('neco', 'necodirect', 1000, $reference);
    }

    /**
     * Create JAMB result checker PIN purchase.
     *
     * @param string|null $reference
     * @return self
     */
    public static function jamb(?string $reference = null): self
    {
        return self::simple('jamb', 'jambdirect', 1000, $reference);
    }

    /**
     * Create NABTEB result checker PIN purchase.
     *
     * @param string|null $reference
     * @return self
     */
    public static function nabteb(?string $reference = null): self
    {
        return self::simple('nabteb', 'nabtebdirect', 1000, $reference);
    }

    /**
     * Generate a unique reference.
     *
     * @return string
     */
    public static function generateReference(): string
    {
        return 'EXAM_' . time() . '_' . uniqid();
    }

    /**
     * Convert to array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'service' => $this->service,
            'vcode' => $this->vcode,
            'amount' => $this->amount,
            'reference' => $this->reference,
        ];
    }

    /**
     * Get service display name.
     *
     * @return string
     */
    public function getServiceDisplayName(): string
    {
        return match ($this->service) {
            'waec' => 'WAEC Result Checker',
            'neco' => 'NECO Result Checker',
            'jamb' => 'JAMB Result Checker',
            'nabteb' => 'NABTEB Result Checker',
            default => strtoupper($this->service) . ' Result Checker',
        };
    }

    /**
     * Get service description.
     *
     * @return string
     */
    public function getServiceDescription(): string
    {
        return match ($this->service) {
            'waec' => 'West African Examinations Council Result Checker PIN',
            'neco' => 'National Examinations Council Result Checker PIN',
            'jamb' => 'Joint Admissions and Matriculation Board Result Checker PIN',
            'nabteb' => 'National Business and Technical Examinations Board Result Checker PIN',
            default => $this->getServiceDisplayName() . ' PIN',
        };
    }

    /**
     * Get portal URL for the service.
     *
     * @return string|null
     */
    public function getPortalUrl(): ?string
    {
        return match ($this->service) {
            'waec' => 'https://www.waecdirect.org',
            'neco' => 'https://www.mynecoexams.com',
            'jamb' => 'https://www.jamb.gov.ng',
            'nabteb' => 'https://www.nabteb.gov.ng',
            default => null,
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
     * Get usage instructions.
     *
     * @return string
     */
    public function getUsageInstructions(): string
    {
        $serviceName = $this->getServiceDisplayName();
        $portalUrl = $this->getPortalUrl();
        
        $instructions = "Visit {$serviceName} portal";
        if ($portalUrl) {
            $instructions .= " at {$portalUrl}";
        }
        $instructions .= ", select result checker, enter your exam details and use this PIN to check your results.";
        
        return $instructions;
    }

    /**
     * Get exam body contact information.
     *
     * @return array
     */
    public function getContactInfo(): array
    {
        return match ($this->service) {
            'waec' => [
                'name' => 'West African Examinations Council',
                'phone' => '+234-1-7747999',
                'email' => 'info@waecnigeria.org',
                'website' => 'https://www.waecnigeria.org',
            ],
            'neco' => [
                'name' => 'National Examinations Council',
                'phone' => '+234-9-2340721',
                'email' => 'info@neco.gov.ng',
                'website' => 'https://www.neco.gov.ng',
            ],
            'jamb' => [
                'name' => 'Joint Admissions and Matriculation Board',
                'phone' => '+234-9-2900800',
                'email' => 'info@jamb.gov.ng',
                'website' => 'https://www.jamb.gov.ng',
            ],
            'nabteb' => [
                'name' => 'National Business and Technical Examinations Board',
                'phone' => '+234-9-2341134',
                'email' => 'info@nabteb.gov.ng',
                'website' => 'https://www.nabteb.gov.ng',
            ],
            default => [
                'name' => $this->getServiceDisplayName(),
                'phone' => null,
                'email' => null,
                'website' => $this->getPortalUrl(),
            ],
        ];
    }

    /**
     * Check if this is a valid exam service.
     *
     * @return bool
     */
    public function hasValidService(): bool
    {
        return in_array($this->service, ['waec', 'neco', 'jamb', 'nabteb']);
    }

    /**
     * Check if amount is valid.
     *
     * @return bool
     */
    public function hasValidAmount(): bool
    {
        return $this->amount > 0 && $this->amount <= 5000; // Reasonable range for exam PINs
    }

    /**
     * Check if the entity is valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->hasValidService() && 
               $this->hasValidAmount() && 
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

        if (!$this->hasValidService()) {
            $errors[] = 'Invalid exam service. Supported: waec, neco, jamb, nabteb';
        }

        if (!$this->hasValidAmount()) {
            $errors[] = 'Invalid amount. Must be between ₦1 and ₦5,000';
        }

        if (empty($this->vcode)) {
            $errors[] = 'Variation code (vcode) is required';
        }

        if (empty($this->reference)) {
            $errors[] = 'Reference is required';
        }

        return $errors;
    }

    /**
     * Get purchase summary.
     *
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'service' => $this->getServiceDisplayName(),
            'description' => $this->getServiceDescription(),
            'amount' => $this->getFormattedAmount(),
            'portal_url' => $this->getPortalUrl(),
            'reference' => $this->reference,
            'usage_instructions' => $this->getUsageInstructions(),
        ];
    }

    /**
     * Check if this is a WAEC PIN.
     *
     * @return bool
     */
    public function isWaecPin(): bool
    {
        return $this->service === 'waec';
    }

    /**
     * Check if this is a NECO PIN.
     *
     * @return bool
     */
    public function isNecoPin(): bool
    {
        return $this->service === 'neco';
    }

    /**
     * Check if this is a JAMB PIN.
     *
     * @return bool
     */
    public function isJambPin(): bool
    {
        return $this->service === 'jamb';
    }

    /**
     * Check if this is a NABTEB PIN.
     *
     * @return bool
     */
    public function isNabtebPin(): bool
    {
        return $this->service === 'nabteb';
    }
}
