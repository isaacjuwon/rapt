<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Responses;

final readonly class VariationsResponse
{
    public function __construct(
        public int $code,
        public ?string $message,
        public ?VariationsData $description,
    ) {}

    /**
     * @param array{
     *     code:int,
     *     message?:string|null,
     *     description?:array|null,
     * } $data
     * @return VariationsResponse
     */
    public static function make(array $data): VariationsResponse
    {
        return new VariationsResponse(
            code: $data['code'],
            message: $data['message'] ?? null,
            description: isset($data['description']) && is_array($data['description']) 
                ? VariationsData::make($data['description']) 
                : null,
        );
    }

    /**
     * Check if the variations request was successful.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->code === 101;
    }

    /**
     * Get the variations data.
     *
     * @return array|null
     */
    public function getVariations(): ?array
    {
        return $this->description?->variations;
    }

    /**
     * Get the service type.
     *
     * @return string|null
     */
    public function getServiceType(): ?string
    {
        return $this->description?->serviceType;
    }

    /**
     * Get the service name.
     *
     * @return string|null
     */
    public function getServiceName(): ?string
    {
        return $this->description?->serviceName;
    }

    /**
     * Get the error message if the request failed.
     *
     * @return string|null
     */
    public function getErrorMessage(): ?string
    {
        if ($this->isSuccessful()) {
            return null;
        }

        return match ($this->code) {
            400 => 'Invalid request method',
            103 => 'Invalid account credentials',
            102 => 'Low wallet balance',
            1007 => 'Transaction blocked',
            1009 => 'Account blocked',
            304 => 'Unauthorized access',
            303 => 'Invalid service ID',
            default => $this->message ?? 'Unknown error occurred',
        };
    }

    /**
     * Get variations by network.
     *
     * @param string $network
     * @return array
     */
    public function getVariationsByNetwork(string $network): array
    {
        $variations = $this->getVariations() ?? [];
        return array_filter($variations, function ($variation) use ($network) {
            return str_contains(strtolower($variation['name'] ?? ''), strtolower($network));
        });
    }

    /**
     * Get variations by amount range.
     *
     * @param float $minAmount
     * @param float $maxAmount
     * @return array
     */
    public function getVariationsByAmountRange(float $minAmount, float $maxAmount): array
    {
        $variations = $this->getVariations() ?? [];
        return array_filter($variations, function ($variation) use ($minAmount, $maxAmount) {
            $amount = (float) ($variation['amount'] ?? 0);
            return $amount >= $minAmount && $amount <= $maxAmount;
        });
    }

    /**
     * Get variation by code.
     *
     * @param string $code
     * @return array|null
     */
    public function getVariationByCode(string $code): ?array
    {
        $variations = $this->getVariations() ?? [];
        foreach ($variations as $variation) {
            if (($variation['code'] ?? null) === $code) {
                return $variation;
            }
        }
        return null;
    }

    /**
     * Get the cheapest variation.
     *
     * @return array|null
     */
    public function getCheapestVariation(): ?array
    {
        $variations = $this->getVariations() ?? [];
        if (empty($variations)) {
            return null;
        }

        $cheapest = null;
        $lowestAmount = PHP_FLOAT_MAX;

        foreach ($variations as $variation) {
            $amount = (float) ($variation['amount'] ?? 0);
            if ($amount < $lowestAmount) {
                $lowestAmount = $amount;
                $cheapest = $variation;
            }
        }

        return $cheapest;
    }

    /**
     * Get the most expensive variation.
     *
     * @return array|null
     */
    public function getMostExpensiveVariation(): ?array
    {
        $variations = $this->getVariations() ?? [];
        if (empty($variations)) {
            return null;
        }

        $mostExpensive = null;
        $highestAmount = 0;

        foreach ($variations as $variation) {
            $amount = (float) ($variation['amount'] ?? 0);
            if ($amount > $highestAmount) {
                $highestAmount = $amount;
                $mostExpensive = $variation;
            }
        }

        return $mostExpensive;
    }

    /**
     * Convert to array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'description' => $this->description?->toArray(),
        ];
    }
}

final readonly class VariationsData
{
    public function __construct(
        public ?array $variations,
        public ?string $serviceType,
        public ?string $serviceName,
    ) {}

    /**
     * @param array $data
     * @return VariationsData
     */
    public static function make(array $data): VariationsData
    {
        return new VariationsData(
            variations: $data['variations'] ?? null,
            serviceType: $data['serviceType'] ?? null,
            serviceName: $data['serviceName'] ?? null,
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
            'variations' => $this->variations,
            'serviceType' => $this->serviceType,
            'serviceName' => $this->serviceName,
        ];
    }
}
