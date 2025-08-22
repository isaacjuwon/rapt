<?php

declare(strict_types=1);

namespace App\Integrations\Paystack\Resources;

use App\Integrations\Paystack\Entities\CreateDedicatedVirtualAccount;
use App\Integrations\Paystack\PaystackConnector;
use Illuminate\Support\Collection;
use Throwable;

final readonly class DedicatedVirtualAccountResource
{
    public function __construct(
        private PaystackConnector $connector,
    ) {}

    /**
     * Create a dedicated virtual account.
     *
     * @param CreateDedicatedVirtualAccount $account
     * @return array
     */
    public function create(CreateDedicatedVirtualAccount $account): array
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: '/dedicated_account',
                options: $account->toRequestBody()
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Create dedicated virtual account with raw data.
     *
     * @param array $data
     * @return array
     */
    public function createRaw(array $data): array
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: '/dedicated_account',
                options: ['json' => $data]
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * List dedicated virtual accounts.
     *
     * @param array $params
     * @return Collection
     */
    public function list(array $params = []): Collection
    {
        try {
            $response = $this->connector->send(
                method: 'GET',
                uri: '/dedicated_account',
                options: ['query' => $params]
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->collect('data');
    }

    /**
     * Get a specific dedicated virtual account.
     *
     * @param string $id
     * @return array
     */
    public function get(string $id): array
    {
        try {
            $response = $this->connector->send(
                method: 'GET',
                uri: "/dedicated_account/{$id}"
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Requery dedicated virtual account.
     *
     * @param string $accountNumber
     * @param string $providerSlug
     * @param string|null $date
     * @return array
     */
    public function requery(string $accountNumber, string $providerSlug, ?string $date = null): array
    {
        try {
            $params = [
                'account_number' => $accountNumber,
                'provider_slug' => $providerSlug,
            ];

            if ($date) {
                $params['date'] = $date;
            }

            $response = $this->connector->send(
                method: 'GET',
                uri: '/dedicated_account/requery',
                options: ['query' => $params]
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Deactivate a dedicated virtual account.
     *
     * @param string $id
     * @return array
     */
    public function deactivate(string $id): array
    {
        try {
            $response = $this->connector->send(
                method: 'DELETE',
                uri: "/dedicated_account/{$id}"
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Split dedicated virtual account transaction.
     *
     * @param array $data
     * @return array
     */
    public function splitTransaction(array $data): array
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: '/dedicated_account/split',
                options: ['json' => $data]
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Remove split from dedicated virtual account.
     *
     * @param string $accountNumber
     * @return array
     */
    public function removeSplit(string $accountNumber): array
    {
        try {
            $response = $this->connector->send(
                method: 'DELETE',
                uri: '/dedicated_account/split',
                options: ['json' => ['account_number' => $accountNumber]]
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Get available providers for dedicated virtual accounts.
     *
     * @return Collection
     */
    public function getProviders(): Collection
    {
        try {
            $response = $this->connector->send(
                method: 'GET',
                uri: '/dedicated_account/available_providers'
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->collect('data');
    }

    /**
     * Check if account is active.
     *
     * @param string $id
     * @return bool
     */
    public function isActive(string $id): bool
    {
        try {
            $account = $this->get($id);
            return $account['active'] ?? false;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Get account number.
     *
     * @param string $id
     * @return string|null
     */
    public function getAccountNumber(string $id): ?string
    {
        try {
            $account = $this->get($id);
            return $account['account_number'] ?? null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Get bank details.
     *
     * @param string $id
     * @return array|null
     */
    public function getBankDetails(string $id): ?array
    {
        try {
            $account = $this->get($id);
            return $account['bank'] ?? null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Get customer details.
     *
     * @param string $id
     * @return array|null
     */
    public function getCustomer(string $id): ?array
    {
        try {
            $account = $this->get($id);
            return $account['customer'] ?? null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Get account balance (if available).
     *
     * @param string $id
     * @return int|null
     */
    public function getBalance(string $id): ?int
    {
        try {
            $account = $this->get($id);
            return $account['balance'] ?? null;
        } catch (Throwable) {
            return null;
        }
    }
}
