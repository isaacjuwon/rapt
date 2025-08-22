<?php

declare(strict_types=1);

namespace App\Contracts\Payment;

interface PaymentContract
{
    /**
     * Initialize a payment transaction.
     *
     * @param array $data
     * @return array
     */
    public function initializePayment(array $data): array;

    /**
     * Verify a payment transaction.
     *
     * @param string $reference
     * @return array
     */
    public function verifyPayment(string $reference): array;

    /**
     * Get supported payment methods.
     *
     * @return array
     */
    public function getSupportedMethods(): array;

    /**
     * Check if a payment method is supported.
     *
     * @param string $method
     * @return bool
     */
    public function supports(string $method): bool;
}
