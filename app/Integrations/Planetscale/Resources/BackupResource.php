<?php

declare(strict_types=1);

namespace App\Integrations\Planetscale\Resources;

use App\Integrations\Planetscale\Entities\CreateBackup;
use App\Integrations\Planetscale\PlanetscaleConnector;
use Illuminate\Support\Collection;
use Throwable;

final readonly class BackupResource
{
    public function __construct(
        private PlanetscaleConnector $connector,
    ) {}

    /**
     * List all backups for a database branch.
     *
     * @param string $organization
     * @param string $database
     * @param string $branch
     * @return Collection
     */
    public function list(string $organization, string $database, string $branch): Collection
    {
        try {
            $response = $this->connector->send(
                method: 'GET',
                uri: "/organizations/{$organization}/databases/{$database}/branches/{$branch}/backups"
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->collect('data');
    }

    /**
     * Get a specific backup.
     *
     * @param string $organization
     * @param string $database
     * @param string $branch
     * @param string $backup
     * @return array
     */
    public function get(string $organization, string $database, string $branch, string $backup): array
    {
        try {
            $response = $this->connector->send(
                method: 'GET',
                uri: "/organizations/{$organization}/databases/{$database}/branches/{$branch}/backups/{$backup}"
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Create a new backup using entity.
     *
     * @param CreateBackup $entity
     * @return array
     */
    public function create(CreateBackup $entity): array
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: "/organizations/{$entity->organization}/databases/{$entity->database}/branches/{$entity->branch}/backups",
                options: $entity->toRequestBody(),
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Create a new backup with raw data.
     *
     * @param string $organization
     * @param string $database
     * @param string $branch
     * @param array $data
     * @return array
     */
    public function createRaw(string $organization, string $database, string $branch, array $data): array
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: "/organizations/{$organization}/databases/{$database}/branches/{$branch}/backups",
                options: ['json' => $data]
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Delete a backup.
     *
     * @param string $organization
     * @param string $database
     * @param string $branch
     * @param string $backup
     * @return bool
     */
    public function delete(string $organization, string $database, string $branch, string $backup): bool
    {
        try {
            $this->connector->send(
                method: 'DELETE',
                uri: "/organizations/{$organization}/databases/{$database}/branches/{$branch}/backups/{$backup}"
            );
            return true;
        } catch (Throwable $exception) {
            throw $exception;
        }
    }

    /**
     * Restore a backup to a new branch.
     *
     * @param string $organization
     * @param string $database
     * @param string $branch
     * @param string $backup
     * @param array $restoreData
     * @return array
     */
    public function restore(string $organization, string $database, string $branch, string $backup, array $restoreData): array
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: "/organizations/{$organization}/databases/{$database}/branches/{$branch}/backups/{$backup}/restore",
                options: ['json' => $restoreData]
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Get backup download URL.
     *
     * @param string $organization
     * @param string $database
     * @param string $branch
     * @param string $backup
     * @return array
     */
    public function downloadUrl(string $organization, string $database, string $branch, string $backup): array
    {
        try {
            $response = $this->connector->send(
                method: 'GET',
                uri: "/organizations/{$organization}/databases/{$database}/branches/{$branch}/backups/{$backup}/download-url"
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json('data', []);
    }

    /**
     * Get backup status.
     *
     * @param string $organization
     * @param string $database
     * @param string $branch
     * @param string $backup
     * @return string
     */
    public function status(string $organization, string $database, string $branch, string $backup): string
    {
        try {
            $response = $this->connector->send(
                method: 'GET',
                uri: "/organizations/{$organization}/databases/{$database}/branches/{$branch}/backups/{$backup}"
            );
            
            $data = $response->json('data', []);
            return $data['state'] ?? 'unknown';
        } catch (Throwable $exception) {
            throw $exception;
        }
    }

    /**
     * Check if backup is ready.
     *
     * @param string $organization
     * @param string $database
     * @param string $branch
     * @param string $backup
     * @return bool
     */
    public function isReady(string $organization, string $database, string $branch, string $backup): bool
    {
        return $this->status($organization, $database, $branch, $backup) === 'ready';
    }

    /**
     * Wait for backup to be ready (polling).
     *
     * @param string $organization
     * @param string $database
     * @param string $branch
     * @param string $backup
     * @param int $maxWaitSeconds
     * @param int $pollInterval
     * @return bool
     */
    public function waitUntilReady(string $organization, string $database, string $branch, string $backup, int $maxWaitSeconds = 300, int $pollInterval = 10): bool
    {
        $startTime = time();
        
        while ((time() - $startTime) < $maxWaitSeconds) {
            if ($this->isReady($organization, $database, $branch, $backup)) {
                return true;
            }
            
            sleep($pollInterval);
        }
        
        return false;
    }
}
