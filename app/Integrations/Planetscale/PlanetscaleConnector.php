<?php

declare(strict_types=1);

namespace App\Integrations\Planetscale;

use App\Integrations\Planetscale\Resources\BackupResource;
use App\Integrations\Planetscale\Resources\DatabaseResource;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

final readonly class PlanetscaleConnector
{
    public function __construct(
        private PendingRequest $request,
    ) {}

    /**
     * Get databases resource.
     *
     * @return DatabaseResource
     */
    public function databases(): DatabaseResource
    {
        return new DatabaseResource(
            connector: $this,
        );
    }

    /**
     * Get backups resource.
     *
     * @return BackupResource
     */
    public function backups(): BackupResource
    {
        return new BackupResource(
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
            abstract: PlanetscaleConnector::class,
            concrete: fn () => new PlanetscaleConnector(
                request: Http::baseUrl(
                    url: config('services.planetscale.url'),
                )->timeout(
                    seconds: 15,
                )->withHeaders(
                    headers: [
                        'Authorization' => 'Basic ' . base64_encode(
                            config('services.planetscale.id') . ':' . config('services.planetscale.token')
                        ),
                    ],
                )->asJson()->acceptJson(),
            ),
        );
    }

    /**
     * Test the connection to PlanetScale API.
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $this->send('GET', '/organizations');
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Get the current user information.
     *
     * @return array
     */
    public function me(): array
    {
        try {
            $response = $this->send('GET', '/me');
            return $response->json('data', []);
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    /**
     * List all organizations.
     *
     * @return array
     */
    public function organizations(): array
    {
        try {
            $response = $this->send('GET', '/organizations');
            return $response->json('data', []);
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }
}
