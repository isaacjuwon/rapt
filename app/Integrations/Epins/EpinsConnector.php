<?php

declare(strict_types=1);

namespace App\Integrations\Epins;

use App\Integrations\Epins\Resources\AirtimeResource;
use App\Integrations\Epins\Resources\DataResource;
use App\Integrations\Epins\Resources\ElectricityResource;
use App\Integrations\Epins\Resources\TvSubscriptionResource;
use App\Integrations\Epins\Resources\WalletResource;
use App\Integrations\Epins\Resources\RechargeCardResource;
use App\Integrations\Epins\Resources\ExamsResource;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

final readonly class EpinsConnector
{
    public function __construct(
        private PendingRequest $request,
    ) {}

    /**
     * Get airtime resource.
     *
     * @return AirtimeResource
     */
    public function airtime(): AirtimeResource
    {
        return new AirtimeResource(
            connector: $this,
        );
    }

    /**
     * Get data resource.
     *
     * @return DataResource
     */
    public function data(): DataResource
    {
        return new DataResource(
            connector: $this,
        );
    }

    /**
     * Get electricity resource.
     *
     * @return ElectricityResource
     */
    public function electricity(): ElectricityResource
    {
        return new ElectricityResource(
            connector: $this,
        );
    }

    /**
     * Get TV subscription resource.
     *
     * @return TvSubscriptionResource
     */
    public function tvSubscription(): TvSubscriptionResource
    {
        return new TvSubscriptionResource(
            connector: $this,
        );
    }

    /**
     * Get wallet resource.
     *
     * @return WalletResource
     */
    public function wallet(): WalletResource
    {
        return new WalletResource(
            connector: $this,
        );
    }

    /**
     * Get recharge card resource.
     *
     * @return RechargeCardResource
     */
    public function rechargeCard(): RechargeCardResource
    {
        return new RechargeCardResource(
            connector: $this,
        );
    }

    /**
     * Get exams resource.
     *
     * @return ExamsResource
     */
    public function exams(): ExamsResource
    {
        return new ExamsResource(
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
            abstract: EpinsConnector::class,
            concrete: fn () => new EpinsConnector(
                request: Http::baseUrl(
                    url: config('services.epins.url', 'https://api.epins.com.ng/core'),
                )->timeout(
                    seconds: 30,
                )->withHeaders(
                    headers: [
                        'Authorization' => 'Bearer ' . config('services.epins.api_key'),
                        'Content-Type' => 'application/json',
                    ],
                )->acceptJson(),
            ),
        );
    }

    /**
     * Test the connection to E-pins API.
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $this->wallet()->getBalance();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Get wallet balance.
     *
     * @return array
     */
    public function getWalletBalance(): array
    {
        try {
            $response = $this->send('GET', '/account/');
            return $response->json('description', []);
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    /**
     * Validate a service (meter number, smartcard, etc.).
     *
     * @param string $serviceId
     * @param string $billerNumber
     * @param string $vcode
     * @return array
     */
    public function validateService(string $serviceId, string $billerNumber, string $vcode): array
    {
        try {
            $response = $this->send('POST', '/merchant-verify/', [
                'json' => [
                    'serviceId' => $serviceId,
                    'billerNumber' => $billerNumber,
                    'vcode' => $vcode,
                ]
            ]);
        } catch (\Throwable $exception) {
            throw $exception;
        }

        return $response->json('description', []);
    }

    /**
     * Get service variations.
     *
     * @param string $service
     * @return array
     */
    public function getVariations(string $service): array
    {
        try {
            $response = $this->send('GET', "/v2/autho/variations/?service={$service}");
            return $response->json('data', []);
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    /**
     * Check if response is successful.
     *
     * @param array $response
     * @return bool
     */
    public function isSuccessful(array $response): bool
    {
        return ($response['code'] ?? 0) === 101;
    }

    /**
     * Get error message from response.
     *
     * @param array $response
     * @return string|null
     */
    public function getErrorMessage(array $response): ?string
    {
        if ($this->isSuccessful($response)) {
            return null;
        }

        return match ($response['code'] ?? 0) {
            400 => 'Invalid request method',
            103 => 'Invalid account credentials',
            107 => 'Invalid amount',
            102 => 'Low wallet balance',
            1007 => 'Transaction blocked',
            1009 => 'Account blocked',
            104 => 'Transaction ID already exists',
            105 => 'Transaction failed',
            108 => 'Below minimum amount allowed',
            118 => 'Missing service ID',
            207 => 'PIN unavailable',
            303 => 'Invalid service ID',
            304 => 'Unauthorized access',
            default => 'Unknown error occurred',
        };
    }
}
