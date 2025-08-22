<?php

declare(strict_types=1);

namespace App\Integrations\Paystack;

use App\Integrations\Paystack\Resources\PaymentResource;
use App\Integrations\Paystack\Resources\DedicatedVirtualAccountResource;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

final readonly class PaystackConnector
{
    public function __construct(
        private PendingRequest $request,
    ) {}

    /**
     * Get payments resource.
     *
     * @return PaymentResource
     */
    public function payments(): PaymentResource
    {
        return new PaymentResource(
            connector: $this,
        );
    }

    /**
     * Get dedicated virtual accounts resource.
     *
     * @return DedicatedVirtualAccountResource
     */
    public function dedicatedVirtualAccounts(): DedicatedVirtualAccountResource
    {
        return new DedicatedVirtualAccountResource(
            connector: $this,
        );
    }

    /**
     * Send HTTP request through the connector.
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return Response
     */
    public function send(string $method, string $uri, array $options = []): Response
    {
        return $this->request->send(
            method: $method,
            url: $uri,
            options: $options,
        )->throw();
    }

    /**
     * Register the connector with the application container.
     *
     * @param Application $app
     * @return void
     */
    public static function register(Application $app): void
    {
        $app->bind(
            abstract: PaystackConnector::class,
            concrete: fn () => new PaystackConnector(
                request: Http::baseUrl(
                    url: config('services.paystack.url', 'https://api.paystack.co'),
                )->timeout(
                    seconds: 30,
                )->withHeaders(
                    headers: [
                        'Authorization' => 'Bearer ' . config('services.paystack.secret_key'),
                        'Content-Type' => 'application/json',
                    ],
                )->acceptJson(),
            ),
        );
    }

    /**
     * Test the connection to Paystack API.
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $this->send('GET', '/bank');
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Verify a transaction.
     *
     * @param string $reference
     * @return array
     */
    public function verifyTransaction(string $reference): array
    {
        try {
            $response = $this->send('GET', "/transaction/verify/{$reference}");
            return $response->json('data', []);
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    /**
     * List supported banks.
     *
     * @return array
     */
    public function listBanks(): array
    {
        try {
            $response = $this->send('GET', '/bank');
            return $response->json('data', []);
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    /**
     * Resolve account number.
     *
     * @param string $accountNumber
     * @param string $bankCode
     * @return array
     */
    public function resolveAccountNumber(string $accountNumber, string $bankCode): array
    {
        try {
            $response = $this->send('GET', '/bank/resolve', [
                'query' => [
                    'account_number' => $accountNumber,
                    'bank_code' => $bankCode,
                ]
            ]);
            return $response->json('data', []);
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    /**
     * Get transaction fees.
     *
     * @param int $amount Amount in kobo
     * @param string $currency
     * @return array
     */
    public function getTransactionFees(int $amount, string $currency = 'NGN'): array
    {
        try {
            $response = $this->send('GET', '/transaction/check_authorization', [
                'query' => [
                    'amount' => $amount,
                    'currency' => $currency,
                ]
            ]);
            return $response->json('data', []);
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }
}
