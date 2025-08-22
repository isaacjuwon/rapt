<?php

declare(strict_types=1);

namespace App\Integrations\Planetscale\Resources;

use App\Integrations\Planetscale\PlanetscaleConnector;
use Illuminate\Support\Collection;
use Throwable;

final readonly class DatabaseResource
{
    public function __construct(
        private PlanetscaleConnector $connector,
    ) {}

    /**
     * List all databases for an organization.
     *
     * @param string $organization
     * @return Collection
     */
    public function list(string $organization): Collection
    {
        try {
            $response = $this->connector->send(
                method: 'GET',
                uri: "/organizations/{$organization}/databases"
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->collect('data');
    }

    /**
     * Get a specific database.
     *
     * @param string $organization
     * @param string $database
     * @return array
     */
    public function get(string $organization, string $database): array
    {
        try {
            $response = $this->connector->send(
                method: 'GET',
                uri: "/organizations/{$organization}/databases/{$database}"
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Create a new database.
     *
     * @param string $organization
     * @param array $data
     * @return array
     */
    public function create(string $organization, array $data): array
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: "/organizations/{$organization}/databases",
                options: ['json' => $data]
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Delete a database.
     *
     * @param string $organization
     * @param string $database
     * @return bool
     */
    public function delete(string $organization, string $database): bool
    {
        try {
            $this->connector->send(
                method: 'DELETE',
                uri: "/organizations/{$organization}/databases/{$database}"
            );
            return true;
        } catch (Throwable $exception) {
            throw $exception;
        }
    }

    /**
     * Get available regions for databases.
     *
     * @return Collection
     */
    public function regions(): Collection
    {
        try {
            $response = $this->connector->send(
                method: 'GET',
                uri: '/regions'
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->collect('data');
    }

    /**
     * List branches for a database.
     *
     * @param string $organization
     * @param string $database
     * @return Collection
     */
    public function branches(string $organization, string $database): Collection
    {
        try {
            $response = $this->connector->send(
                method: 'GET',
                uri: "/organizations/{$organization}/databases/{$database}/branches"
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->collect('data');
    }

    /**
     * Get a specific branch.
     *
     * @param string $organization
     * @param string $database
     * @param string $branch
     * @return array
     */
    public function getBranch(string $organization, string $database, string $branch): array
    {
        try {
            $response = $this->connector->send(
                method: 'GET',
                uri: "/organizations/{$organization}/databases/{$database}/branches/{$branch}"
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Create a new branch.
     *
     * @param string $organization
     * @param string $database
     * @param array $data
     * @return array
     */
    public function createBranch(string $organization, string $database, array $data): array
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: "/organizations/{$organization}/databases/{$database}/branches",
                options: ['json' => $data]
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Delete a branch.
     *
     * @param string $organization
     * @param string $database
     * @param string $branch
     * @return bool
     */
    public function deleteBranch(string $organization, string $database, string $branch): bool
    {
        try {
            $this->connector->send(
                method: 'DELETE',
                uri: "/organizations/{$organization}/databases/{$database}/branches/{$branch}"
            );
            return true;
        } catch (Throwable $exception) {
            throw $exception;
        }
    }

    /**
     * Get connection strings for a branch.
     *
     * @param string $organization
     * @param string $database
     * @param string $branch
     * @return array
     */
    public function connectionStrings(string $organization, string $database, string $branch): array
    {
        try {
            $response = $this->connector->send(
                method: 'GET',
                uri: "/organizations/{$organization}/databases/{$database}/branches/{$branch}/connection-strings"
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }
}
