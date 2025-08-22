<?php

declare(strict_types=1);

namespace App\Integrations\Planetscale\Entities;

final readonly class CreateBackup
{
    public function __construct(
        public string $organization,
        public string $database,
        public string $branch,
        public ?string $name = null,
        public ?string $description = null,
        public bool $includeSchema = true,
        public bool $includeData = true,
        public ?array $tables = null,
        public ?array $excludeTables = null,
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
                'name' => $this->name,
                'description' => $this->description,
                'include_schema' => $this->includeSchema,
                'include_data' => $this->includeData,
                'tables' => $this->tables,
                'exclude_tables' => $this->excludeTables,
            ], fn($value) => $value !== null)
        ];

        return $body;
    }

    /**
     * Create a backup entity from array data.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            organization: $data['organization'],
            database: $data['database'],
            branch: $data['branch'],
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            includeSchema: $data['include_schema'] ?? true,
            includeData: $data['include_data'] ?? true,
            tables: $data['tables'] ?? null,
            excludeTables: $data['exclude_tables'] ?? null,
        );
    }

    /**
     * Create a full backup (schema + data).
     *
     * @param string $organization
     * @param string $database
     * @param string $branch
     * @param string|null $name
     * @param string|null $description
     * @return self
     */
    public static function full(string $organization, string $database, string $branch, ?string $name = null, ?string $description = null): self
    {
        return new self(
            organization: $organization,
            database: $database,
            branch: $branch,
            name: $name ?? 'Full backup - ' . now()->format('Y-m-d H:i:s'),
            description: $description ?? 'Automated full backup including schema and data',
            includeSchema: true,
            includeData: true,
        );
    }

    /**
     * Create a schema-only backup.
     *
     * @param string $organization
     * @param string $database
     * @param string $branch
     * @param string|null $name
     * @param string|null $description
     * @return self
     */
    public static function schemaOnly(string $organization, string $database, string $branch, ?string $name = null, ?string $description = null): self
    {
        return new self(
            organization: $organization,
            database: $database,
            branch: $branch,
            name: $name ?? 'Schema backup - ' . now()->format('Y-m-d H:i:s'),
            description: $description ?? 'Schema-only backup without data',
            includeSchema: true,
            includeData: false,
        );
    }

    /**
     * Create a data-only backup.
     *
     * @param string $organization
     * @param string $database
     * @param string $branch
     * @param string|null $name
     * @param string|null $description
     * @return self
     */
    public static function dataOnly(string $organization, string $database, string $branch, ?string $name = null, ?string $description = null): self
    {
        return new self(
            organization: $organization,
            database: $database,
            branch: $branch,
            name: $name ?? 'Data backup - ' . now()->format('Y-m-d H:i:s'),
            description: $description ?? 'Data-only backup without schema',
            includeSchema: false,
            includeData: true,
        );
    }

    /**
     * Create a backup for specific tables.
     *
     * @param string $organization
     * @param string $database
     * @param string $branch
     * @param array $tables
     * @param string|null $name
     * @param string|null $description
     * @return self
     */
    public static function forTables(string $organization, string $database, string $branch, array $tables, ?string $name = null, ?string $description = null): self
    {
        return new self(
            organization: $organization,
            database: $database,
            branch: $branch,
            name: $name ?? 'Selective backup - ' . now()->format('Y-m-d H:i:s'),
            description: $description ?? 'Backup for specific tables: ' . implode(', ', $tables),
            includeSchema: true,
            includeData: true,
            tables: $tables,
        );
    }

    /**
     * Create a backup excluding specific tables.
     *
     * @param string $organization
     * @param string $database
     * @param string $branch
     * @param array $excludeTables
     * @param string|null $name
     * @param string|null $description
     * @return self
     */
    public static function excluding(string $organization, string $database, string $branch, array $excludeTables, ?string $name = null, ?string $description = null): self
    {
        return new self(
            organization: $organization,
            database: $database,
            branch: $branch,
            name: $name ?? 'Filtered backup - ' . now()->format('Y-m-d H:i:s'),
            description: $description ?? 'Backup excluding tables: ' . implode(', ', $excludeTables),
            includeSchema: true,
            includeData: true,
            excludeTables: $excludeTables,
        );
    }

    /**
     * Convert to array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'organization' => $this->organization,
            'database' => $this->database,
            'branch' => $this->branch,
            'name' => $this->name,
            'description' => $this->description,
            'include_schema' => $this->includeSchema,
            'include_data' => $this->includeData,
            'tables' => $this->tables,
            'exclude_tables' => $this->excludeTables,
        ];
    }

    /**
     * Get a human-readable description of the backup type.
     *
     * @return string
     */
    public function getBackupType(): string
    {
        if ($this->includeSchema && $this->includeData) {
            return 'Full Backup';
        }
        
        if ($this->includeSchema && !$this->includeData) {
            return 'Schema Only';
        }
        
        if (!$this->includeSchema && $this->includeData) {
            return 'Data Only';
        }
        
        return 'Empty Backup';
    }

    /**
     * Check if this is a selective backup.
     *
     * @return bool
     */
    public function isSelective(): bool
    {
        return !empty($this->tables) || !empty($this->excludeTables);
    }
}
