<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Resources;

use App\Integrations\Epins\Entities\PurchaseData;
use App\Integrations\Epins\EpinsConnector;
use App\Integrations\Epins\Responses\DataPurchaseResponse;
use App\Integrations\Epins\Responses\RechargeCardGenerateResponse;
use App\Integrations\Epins\Responses\VariationsResponse;
use Throwable;

final readonly class DataResource
{
    public function __construct(
        private EpinsConnector $connector,
    ) {}

    /**
     * Purchase data bundle.
     *
     * @param PurchaseData $data
     * @return array
     */
    public function purchase(PurchaseData $data): array
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: '/data/',
                options: $data->toRequestBody()
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json();
    }

    /**
     * Purchase data with raw data.
     *
     * @param array $data
     * @return array
     */
    public function purchaseRaw(array $data): array
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: '/data/',
                options: ['json' => $data]
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json();
    }

    /**
     * Get data plan variations.
     *
     * @return array
     */
    public function getVariations(): array
    {
        return $this->connector->getVariations('data');
    }

    /**
     * Purchase data card PIN.
     *
     * @param string $network
     * @param int $dataPlan
     * @param int $pinQuantity
     * @param string $reference
     * @return array
     */
    public function purchaseDataCard(string $network, int $dataPlan, int $pinQuantity, string $reference): array
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: '/datacard/',
                options: [
                    'json' => [
                        'service' => 'datacard',
                        'network' => $network,
                        'DataPlan' => $dataPlan,
                        'pinQuantity' => $pinQuantity,
                        'ref' => $reference,
                    ]
                ]
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return $response->json();
    }

    /**
     * Check if data purchase was successful.
     *
     * @param array $response
     * @return bool
     */
    public function isSuccessful(array $response): bool
    {
        return $this->connector->isSuccessful($response);
    }

    /**
     * Get transaction reference from response.
     *
     * @param array $response
     * @return string|null
     */
    public function getTransactionReference(array $response): ?string
    {
        return $response['description']['ref'] ?? null;
    }

    /**
     * Get amount from response.
     *
     * @param array $response
     * @return string|null
     */
    public function getAmount(array $response): ?string
    {
        return $response['description']['amount'] ?? null;
    }

    /**
     * Get transaction date from response.
     *
     * @param array $response
     * @return string|null
     */
    public function getTransactionDate(array $response): ?string
    {
        return $response['description']['transaction_date'] ?? 
               $response['description']['TransactionDate'] ?? null;
    }

    /**
     * Get network variations.
     *
     * @return array
     */
    public static function getNetworkVariations(): array
    {
        return [
            '01' => 'MTN',
            '02' => 'Glo',
            '03' => '9Mobile',
            '04' => 'Airtel',
        ];
    }

    /**
     * Get popular MTN data plans.
     *
     * @return array
     */
    public static function getMtnPlans(): array
    {
        return [
            57 => ['name' => '1GB (SME) - 30days', 'amount' => 620],
            58 => ['name' => '500MB (SME) - 30days', 'amount' => 420],
            59 => ['name' => '2GB (SME) - 30days', 'amount' => 1240],
            60 => ['name' => '3GB (SME) - 30days', 'amount' => 1860],
            61 => ['name' => '5GB (SME) - 30days', 'amount' => 3100],
            62 => ['name' => '10GB (SME) - 30days', 'amount' => 6200],
        ];
    }

    /**
     * Get popular Airtel data plans.
     *
     * @return array
     */
    public static function getAirtelPlans(): array
    {
        return [
            86 => ['name' => '500MB (CG) - 30days', 'amount' => 425],
            420 => ['name' => '1GB (AWOOF) - 1 DAY VALIDITY', 'amount' => 400],
            421 => ['name' => '3GB (AWOOF) - 7 DAYS VALIDITY', 'amount' => 1200],
            423 => ['name' => '10GB (AWOOF) - 30 DAYS VALIDITY', 'amount' => 3500],
        ];
    }

    /**
     * Get popular Glo data plans.
     *
     * @return array
     */
    public static function getGloPlans(): array
    {
        return [
            1442 => ['name' => 'Glo Data (SME) N450 - 1GB 30 days', 'amount' => 450],
            1443 => ['name' => 'Glo Data (SME) N1,350 - 3GB 30 days', 'amount' => 1350],
            1444 => ['name' => 'Glo Data (SME) N4500 - 10GB - 30 Days', 'amount' => 4500],
            1445 => ['name' => 'Glo Data (SME) N900 - 2GB 30 days', 'amount' => 900],
            1447 => ['name' => 'Glo Data (SME) N2250 - 5GB 30 days', 'amount' => 2250],
        ];
    }

    /**
     * Get popular 9Mobile data plans.
     *
     * @return array
     */
    public static function get9MobilePlans(): array
    {
        return [
            103 => ['name' => '1.6GB (SME) - 30days', 'amount' => 500],
            105 => ['name' => '2.3GB (SME) - 30days', 'amount' => 700],
            107 => ['name' => '3.3GB (SME) - 30days', 'amount' => 1000],
            109 => ['name' => '5GB (SME) - 30days', 'amount' => 1800],
            112 => ['name' => '10GB (SME) - 30days', 'amount' => 3050],
        ];
    }

    /**
     * Get data plan by network and plan code.
     *
     * @param string $network
     * @param int $planCode
     * @return array|null
     */
    public static function getDataPlan(string $network, int $planCode): ?array
    {
        $plans = match ($network) {
            '01', 'mtn' => self::getMtnPlans(),
            '02', 'glo' => self::getGloPlans(),
            '03', '9mobile' => self::get9MobilePlans(),
            '04', 'airtel' => self::getAirtelPlans(),
            default => [],
        };

        return $plans[$planCode] ?? null;
    }

    /**
     * Search data plans by amount range.
     *
     * @param string $network
     * @param int $minAmount
     * @param int $maxAmount
     * @return array
     */
    public static function searchPlansByAmount(string $network, int $minAmount, int $maxAmount): array
    {
        $plans = match ($network) {
            '01', 'mtn' => self::getMtnPlans(),
            '02', 'glo' => self::getGloPlans(),
            '03', '9mobile' => self::get9MobilePlans(),
            '04', 'airtel' => self::getAirtelPlans(),
            default => [],
        };

        return array_filter($plans, function ($plan) use ($minAmount, $maxAmount) {
            return $plan['amount'] >= $minAmount && $plan['amount'] <= $maxAmount;
        });
    }

    /**
     * Get cheapest plan for a network.
     *
     * @param string $network
     * @return array|null
     */
    public static function getCheapestPlan(string $network): ?array
    {
        $plans = match ($network) {
            '01', 'mtn' => self::getMtnPlans(),
            '02', 'glo' => self::getGloPlans(),
            '03', '9mobile' => self::get9MobilePlans(),
            '04', 'airtel' => self::getAirtelPlans(),
            default => [],
        };

        if (empty($plans)) {
            return null;
        }

        $cheapest = null;
        $lowestAmount = PHP_INT_MAX;

        foreach ($plans as $code => $plan) {
            if ($plan['amount'] < $lowestAmount) {
                $lowestAmount = $plan['amount'];
                $cheapest = array_merge($plan, ['code' => $code]);
            }
        }

        return $cheapest;
    }
}
