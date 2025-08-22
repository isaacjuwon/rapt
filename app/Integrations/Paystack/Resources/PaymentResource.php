<?php

declare(strict_types=1);

namespace App\Integrations\Paystack\Resources;

use App\Integrations\Paystack\Entities\InitializePayment;
use App\Integrations\Paystack\PaystackConnector;
use Illuminate\Support\Collection;
use Throwable;

final readonly class PaymentResource
{
    public function __construct(
        private PaystackConnector $connector,
    ) {}

    /**
     * Initialize a payment transaction.
     *
     * @param InitializePayment $payment
     * @return array
     */
    public function initialize(InitializePayment $payment): array
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: '/transaction/initialize',
                options: $payment->toRequestBody()
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Initialize payment with raw data.
     *
     * @param array $data
     * @return array
     */
    public function initializeRaw(array $data): array
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: '/transaction/initialize',
                options: ['json' => $data]
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Verify a payment transaction.
     *
     * @param string $reference
     * @return array
     */
    public function verify(string $reference): array
    {
        try {
            $response = $this->connector->send(
                method: 'GET',
                uri: "/transaction/verify/{$reference}"
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * List all transactions.
     *
     * @param array $params
     * @return Collection
     */
    public function list(array $params = []): Collection
    {
        try {
            $response = $this->connector->send(
                method: 'GET',
                uri: '/transaction',
                options: ['query' => $params]
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->collect('data');
    }

    /**
     * Get a specific transaction.
     *
     * @param string $id
     * @return array
     */
    public function get(string $id): array
    {
        try {
            $response = $this->connector->send(
                method: 'GET',
                uri: "/transaction/{$id}"
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Charge authorization (for recurring payments).
     *
     * @param array $data
     * @return array
     */
    public function chargeAuthorization(array $data): array
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: '/transaction/charge_authorization',
                options: ['json' => $data]
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Get payment timeline.
     *
     * @param string $id
     * @return array
     */
    public function timeline(string $id): array
    {
        try {
            $response = $this->connector->send(
                method: 'GET',
                uri: "/transaction/timeline/{$id}"
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Get transaction totals.
     *
     * @param array $params
     * @return array
     */
    public function totals(array $params = []): array
    {
        try {
            $response = $this->connector->send(
                method: 'GET',
                uri: '/transaction/totals',
                options: ['query' => $params]
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Export transactions.
     *
     * @param array $params
     * @return array
     */
    public function export(array $params = []): array
    {
        try {
            $response = $this->connector->send(
                method: 'GET',
                uri: '/transaction/export',
                options: ['query' => $params]
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Partial debit (for split payments).
     *
     * @param array $data
     * @return array
     */
    public function partialDebit(array $data): array
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: '/transaction/partial_debit',
                options: ['json' => $data]
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Check if transaction is successful.
     *
     * @param string $reference
     * @return bool
     */
    public function isSuccessful(string $reference): bool
    {
        try {
            $transaction = $this->verify($reference);
            return $transaction['status'] === 'success';
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Get transaction amount in kobo.
     *
     * @param string $reference
     * @return int|null
     */
    public function getAmount(string $reference): ?int
    {
        try {
            $transaction = $this->verify($reference);
            return $transaction['amount'] ?? null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Get transaction status.
     *
     * @param string $reference
     * @return string|null
     */
    public function getStatus(string $reference): ?string
    {
        try {
            $transaction = $this->verify($reference);
            return $transaction['status'] ?? null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Get customer details from transaction.
     *
     * @param string $reference
     * @return array|null
     */
    public function getCustomer(string $reference): ?array
    {
        try {
            $transaction = $this->verify($reference);
            return $transaction['customer'] ?? null;
        } catch (Throwable) {
            return null;
        }
    }
}
