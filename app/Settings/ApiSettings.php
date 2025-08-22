<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ApiSettings extends Settings
{
    public array $configurations = [];

    public static function group(): string
    {
        return 'api';
    }

    public static function encrypted(): array
    {
        return ['configurations'];
    }

    public static function repository(): ?string
    {
        return 'api_settings';
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->configurations[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->configurations[$key] = $value;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->configurations);
    }

    public function all(): array
    {
        return $this->configurations;
    }

    public function remove(string $key): void
    {
        unset($this->configurations[$key]);
    }
}
