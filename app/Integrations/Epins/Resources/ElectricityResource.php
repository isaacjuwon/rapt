<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Resources;

use App\Integrations\Epins\Entities\PurchaseElectricity;
use App\Integrations\Epins\EpinsConnector;
use Throwable;

final readonly class ElectricityResource
{
    public function __construct(
        private EpinsConnector $connector,
    ) {}

    /**
     * Validate meter number.
     *
     * @param string $serviceId
     * @param string $meterNumber
     * @param string $meterType
     * @return array
     */
    public function validateMeter(string $serviceId, string $meterNumber, string $meterType): array
    {
        return $this->connector->validateService($serviceId, $meterNumber, $meterType);
    }

    /**
     * Purchase electricity token.
     *
     * @param PurchaseElectricity $electricity
     * @return array
     */
    public function purchase(PurchaseElectricity $electricity): array
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: '/biller/',
                options: $electricity->toRequestBody()
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json();
    }

    /**
     * Purchase electricity with raw data.
     *
     * @param array $data
     * @return array
     */
    public function purchaseRaw(array $data): array
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: '/biller/',
                options: ['json' => $data]
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json();
    }

    /**
     * Check if electricity purchase was successful.
     *
     * @param array $response
     * @return bool
     */
    public function isSuccessful(array $response): bool
    {
        return $this->connector->isSuccessful($response);
    }

    /**
     * Get electricity token from response.
     *
     * @param array $response
     * @return string|null
     */
    public function getToken(array $response): ?string
    {
        return $response['description']['Token'] ?? null;
    }

    /**
     * Get units from response.
     *
     * @param array $response
     * @return string|null
     */
    public function getUnits(array $response): ?string
    {
        return $response['description']['Units'] ?? null;
    }

    /**
     * Get meter number from response.
     *
     * @param array $response
     * @return string|null
     */
    public function getMeterNumber(array $response): ?string
    {
        return $response['description']['meterNumber'] ?? null;
    }

    /**
     * Get product name from response.
     *
     * @param array $response
     * @return string|null
     */
    public function getProductName(array $response): ?string
    {
        return $response['description']['product_name'] ?? null;
    }

    /**
     * Get customer name from validation response.
     *
     * @param array $response
     * @return string|null
     */
    public function getCustomerName(array $response): ?string
    {
        return $response['Customer'] ?? null;
    }

    /**
     * Get customer address from validation response.
     *
     * @param array $response
     * @return string|null
     */
    public function getCustomerAddress(array $response): ?string
    {
        return $response['Address'] ?? null;
    }

    /**
     * Get supported electricity services.
     *
     * @return array
     */
    public static function getSupportedServices(): array
    {
        return [
            'ikeja-electric' => 'Ikeja Electric (IKEDC)',
            'eko-electric' => 'Eko Electric (EKEDC)',
            'portharcourt-electric' => 'Port Harcourt Electric (PHEDC)',
            'jos-electric' => 'Jos Electric (JEDC)',
            'kano-electric' => 'Kano Electric (KEDC)',
            'ibadan-electric' => 'Ibadan Electric (IBEDC)',
            'enugu-electric' => 'Enugu Electric (EEDC)',
            'abuja-electric' => 'Abuja Electric (AEDC)',
            'benin-electric' => 'Benin Electric (BEDC)',
        ];
    }

    /**
     * Get meter types.
     *
     * @return array
     */
    public static function getMeterTypes(): array
    {
        return [
            'prepaid' => 'Prepaid Meter',
            'postpaid' => 'Postpaid Meter',
        ];
    }

    /**
     * Get minimum purchase amounts by service.
     *
     * @return array
     */
    public static function getMinimumAmounts(): array
    {
        return [
            'ikeja-electric' => 500,
            'eko-electric' => 500,
            'portharcourt-electric' => 500,
            'jos-electric' => 500,
            'kano-electric' => 500,
            'ibadan-electric' => 500,
            'enugu-electric' => 500,
            'abuja-electric' => 500,
            'benin-electric' => 500,
        ];
    }

    /**
     * Get maximum purchase amounts by service.
     *
     * @return array
     */
    public static function getMaximumAmounts(): array
    {
        return [
            'ikeja-electric' => 50000,
            'eko-electric' => 50000,
            'portharcourt-electric' => 50000,
            'jos-electric' => 50000,
            'kano-electric' => 50000,
            'ibadan-electric' => 50000,
            'enugu-electric' => 50000,
            'abuja-electric' => 50000,
            'benin-electric' => 50000,
        ];
    }

    /**
     * Validate meter number format.
     *
     * @param string $meterNumber
     * @return bool
     */
    public static function isValidMeterNumber(string $meterNumber): bool
    {
        // Basic validation - meter numbers are typically 10-13 digits
        return preg_match('/^\d{10,13}$/', $meterNumber) === 1;
    }

    /**
     * Validate purchase amount for service.
     *
     * @param string $service
     * @param int $amount
     * @return bool
     */
    public static function isValidAmount(string $service, int $amount): bool
    {
        $minimums = self::getMinimumAmounts();
        $maximums = self::getMaximumAmounts();

        $minAmount = $minimums[$service] ?? 500;
        $maxAmount = $maximums[$service] ?? 50000;

        return $amount >= $minAmount && $amount <= $maxAmount;
    }

    /**
     * Get service display name.
     *
     * @param string $serviceId
     * @return string
     */
    public static function getServiceDisplayName(string $serviceId): string
    {
        $services = self::getSupportedServices();
        return $services[$serviceId] ?? $serviceId;
    }

    /**
     * Calculate estimated units for amount (rough estimate).
     *
     * @param int $amount
     * @param float $ratePerKwh
     * @return float
     */
    public static function estimateUnits(int $amount, float $ratePerKwh = 50.0): float
    {
        return round($amount / $ratePerKwh, 2);
    }

    /**
     * Format token for display.
     *
     * @param string $token
     * @return string
     */
    public static function formatToken(string $token): string
    {
        // Format token with spaces for better readability
        return chunk_split($token, 4, ' ');
    }
}
