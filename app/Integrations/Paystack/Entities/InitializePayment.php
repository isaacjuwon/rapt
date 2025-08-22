<?php

declare(strict_types=1);

namespace App\Integrations\Paystack\Entities;

final readonly class InitializePayment
{
    public function __construct(
        public string $email,
        public int $amount, // Amount in kobo
        public ?string $reference = null,
        public string $currency = 'NGN',
        public ?string $callbackUrl = null,
        public ?array $metadata = null,
        public ?array $customFields = null,
        public ?array $channels = null,
        public ?string $subaccount = null,
        public ?int $transactionCharge = null,
        public ?string $bearer = null,
        public ?string $plan = null,
        public ?int $invoiceLimit = null,
        public ?array $split = null,
    ) {}

    /**
     * Convert the entity to a request body array.
     *
     * @return array
     */
    public function toRequestBody(): array
    {
        $body = [
            'json' => array_filter([
                'email' => $this->email,
                'amount' => $this->amount,
                'reference' => $this->reference,
                'currency' => $this->currency,
                'callback_url' => $this->callbackUrl,
                'metadata' => $this->metadata,
                'custom_fields' => $this->customFields,
                'channels' => $this->channels,
                'subaccount' => $this->subaccount,
                'transaction_charge' => $this->transactionCharge,
                'bearer' => $this->bearer,
                'plan' => $this->plan,
                'invoice_limit' => $this->invoiceLimit,
                'split' => $this->split,
            ], fn($value) => $value !== null)
        ];

        return $body;
    }

    /**
     * Create a payment entity from array data.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            amount: $data['amount'],
            reference: $data['reference'] ?? null,
            currency: $data['currency'] ?? 'NGN',
            callbackUrl: $data['callback_url'] ?? null,
            metadata: $data['metadata'] ?? null,
            customFields: $data['custom_fields'] ?? null,
            channels: $data['channels'] ?? null,
            subaccount: $data['subaccount'] ?? null,
            transactionCharge: $data['transaction_charge'] ?? null,
            bearer: $data['bearer'] ?? null,
            plan: $data['plan'] ?? null,
            invoiceLimit: $data['invoice_limit'] ?? null,
            split: $data['split'] ?? null,
        );
    }

    /**
     * Create a simple payment.
     *
     * @param string $email
     * @param int $amount Amount in kobo
     * @param string|null $reference
     * @param string $currency
     * @return self
     */
    public static function simple(string $email, int $amount, ?string $reference = null, string $currency = 'NGN'): self
    {
        return new self(
            email: $email,
            amount: $amount,
            reference: $reference ?? self::generateReference(),
            currency: $currency,
        );
    }

    /**
     * Create a payment with callback URL.
     *
     * @param string $email
     * @param int $amount
     * @param string $callbackUrl
     * @param string|null $reference
     * @param string $currency
     * @return self
     */
    public static function withCallback(string $email, int $amount, string $callbackUrl, ?string $reference = null, string $currency = 'NGN'): self
    {
        return new self(
            email: $email,
            amount: $amount,
            reference: $reference ?? self::generateReference(),
            currency: $currency,
            callbackUrl: $callbackUrl,
        );
    }

    /**
     * Create a payment with metadata.
     *
     * @param string $email
     * @param int $amount
     * @param array $metadata
     * @param string|null $reference
     * @param string $currency
     * @return self
     */
    public static function withMetadata(string $email, int $amount, array $metadata, ?string $reference = null, string $currency = 'NGN'): self
    {
        return new self(
            email: $email,
            amount: $amount,
            reference: $reference ?? self::generateReference(),
            currency: $currency,
            metadata: $metadata,
        );
    }

    /**
     * Create a payment with specific channels.
     *
     * @param string $email
     * @param int $amount
     * @param array $channels
     * @param string|null $reference
     * @param string $currency
     * @return self
     */
    public static function withChannels(string $email, int $amount, array $channels, ?string $reference = null, string $currency = 'NGN'): self
    {
        return new self(
            email: $email,
            amount: $amount,
            reference: $reference ?? self::generateReference(),
            currency: $currency,
            channels: $channels,
        );
    }

    /**
     * Create a payment with subaccount.
     *
     * @param string $email
     * @param int $amount
     * @param string $subaccount
     * @param string|null $reference
     * @param string $currency
     * @return self
     */
    public static function withSubaccount(string $email, int $amount, string $subaccount, ?string $reference = null, string $currency = 'NGN'): self
    {
        return new self(
            email: $email,
            amount: $amount,
            reference: $reference ?? self::generateReference(),
            currency: $currency,
            subaccount: $subaccount,
        );
    }

    /**
     * Create a split payment.
     *
     * @param string $email
     * @param int $amount
     * @param array $split
     * @param string|null $reference
     * @param string $currency
     * @return self
     */
    public static function withSplit(string $email, int $amount, array $split, ?string $reference = null, string $currency = 'NGN'): self
    {
        return new self(
            email: $email,
            amount: $amount,
            reference: $reference ?? self::generateReference(),
            currency: $currency,
            split: $split,
        );
    }

    /**
     * Generate a unique reference.
     *
     * @return string
     */
    public static function generateReference(): string
    {
        return 'PAY_' . time() . '_' . uniqid();
    }

    /**
     * Convert amount from naira to kobo.
     *
     * @param float $naira
     * @return int
     */
    public static function nairaToKobo(float $naira): int
    {
        return (int) ($naira * 100);
    }

    /**
     * Convert amount from kobo to naira.
     *
     * @param int $kobo
     * @return float
     */
    public static function koboToNaira(int $kobo): float
    {
        return $kobo / 100;
    }

    /**
     * Get amount in naira.
     *
     * @return float
     */
    public function getAmountInNaira(): float
    {
        return self::koboToNaira($this->amount);
    }

    /**
     * Convert to array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'amount' => $this->amount,
            'reference' => $this->reference,
            'currency' => $this->currency,
            'callback_url' => $this->callbackUrl,
            'metadata' => $this->metadata,
            'custom_fields' => $this->customFields,
            'channels' => $this->channels,
            'subaccount' => $this->subaccount,
            'transaction_charge' => $this->transactionCharge,
            'bearer' => $this->bearer,
            'plan' => $this->plan,
            'invoice_limit' => $this->invoiceLimit,
            'split' => $this->split,
        ];
    }

    /**
     * Check if this is a recurring payment.
     *
     * @return bool
     */
    public function isRecurring(): bool
    {
        return !empty($this->plan);
    }

    /**
     * Check if this is a split payment.
     *
     * @return bool
     */
    public function isSplitPayment(): bool
    {
        return !empty($this->split) || !empty($this->subaccount);
    }
}
