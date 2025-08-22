<?php

declare(strict_types=1);

namespace App\Integrations\Paystack\Entities;

final readonly class CreateDedicatedVirtualAccount
{
    public function __construct(
        public string $customer,
        public ?string $preferredBank = null,
        public ?string $subaccount = null,
        public ?array $split = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $phone = null,
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
                'customer' => $this->customer,
                'preferred_bank' => $this->preferredBank,
                'subaccount' => $this->subaccount,
                'split' => $this->split,
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'phone' => $this->phone,
            ], fn($value) => $value !== null)
        ];

        return $body;
    }

    /**
     * Create a dedicated virtual account entity from array data.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            customer: $data['customer'],
            preferredBank: $data['preferred_bank'] ?? null,
            subaccount: $data['subaccount'] ?? null,
            split: $data['split'] ?? null,
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            phone: $data['phone'] ?? null,
        );
    }

    /**
     * Create a simple dedicated virtual account.
     *
     * @param string $customer Customer code or email
     * @param string|null $preferredBank
     * @return self
     */
    public static function simple(string $customer, ?string $preferredBank = null): self
    {
        return new self(
            customer: $customer,
            preferredBank: $preferredBank,
        );
    }

    /**
     * Create a dedicated virtual account with customer details.
     *
     * @param string $customer
     * @param string $firstName
     * @param string $lastName
     * @param string|null $phone
     * @param string|null $preferredBank
     * @return self
     */
    public static function withCustomerDetails(
        string $customer,
        string $firstName,
        string $lastName,
        ?string $phone = null,
        ?string $preferredBank = null
    ): self {
        return new self(
            customer: $customer,
            preferredBank: $preferredBank,
            firstName: $firstName,
            lastName: $lastName,
            phone: $phone,
        );
    }

    /**
     * Create a dedicated virtual account with subaccount.
     *
     * @param string $customer
     * @param string $subaccount
     * @param string|null $preferredBank
     * @return self
     */
    public static function withSubaccount(string $customer, string $subaccount, ?string $preferredBank = null): self
    {
        return new self(
            customer: $customer,
            preferredBank: $preferredBank,
            subaccount: $subaccount,
        );
    }

    /**
     * Create a dedicated virtual account with split configuration.
     *
     * @param string $customer
     * @param array $split
     * @param string|null $preferredBank
     * @return self
     */
    public static function withSplit(string $customer, array $split, ?string $preferredBank = null): self
    {
        return new self(
            customer: $customer,
            preferredBank: $preferredBank,
            split: $split,
        );
    }

    /**
     * Create a dedicated virtual account for a specific bank.
     *
     * @param string $customer
     * @param string $bankSlug
     * @return self
     */
    public static function forBank(string $customer, string $bankSlug): self
    {
        return new self(
            customer: $customer,
            preferredBank: $bankSlug,
        );
    }

    /**
     * Create a dedicated virtual account for Wema Bank.
     *
     * @param string $customer
     * @return self
     */
    public static function forWemaBank(string $customer): self
    {
        return self::forBank($customer, 'wema-bank');
    }

    /**
     * Create a dedicated virtual account for Titan Paystack.
     *
     * @param string $customer
     * @return self
     */
    public static function forTitanPaystack(string $customer): self
    {
        return self::forBank($customer, 'titan-paystack');
    }

    /**
     * Create a dedicated virtual account for VFD Microfinance Bank.
     *
     * @param string $customer
     * @return self
     */
    public static function forVfdBank(string $customer): self
    {
        return self::forBank($customer, 'vfd');
    }

    /**
     * Convert to array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'customer' => $this->customer,
            'preferred_bank' => $this->preferredBank,
            'subaccount' => $this->subaccount,
            'split' => $this->split,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'phone' => $this->phone,
        ];
    }

    /**
     * Check if this account has split configuration.
     *
     * @return bool
     */
    public function hasSplit(): bool
    {
        return !empty($this->split) || !empty($this->subaccount);
    }

    /**
     * Check if customer details are provided.
     *
     * @return bool
     */
    public function hasCustomerDetails(): bool
    {
        return !empty($this->firstName) && !empty($this->lastName);
    }

    /**
     * Get full customer name.
     *
     * @return string|null
     */
    public function getFullName(): ?string
    {
        if ($this->firstName && $this->lastName) {
            return trim($this->firstName . ' ' . $this->lastName);
        }

        return null;
    }

    /**
     * Check if a preferred bank is specified.
     *
     * @return bool
     */
    public function hasPreferredBank(): bool
    {
        return !empty($this->preferredBank);
    }
}
