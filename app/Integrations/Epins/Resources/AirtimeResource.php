<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Resources;

use App\Integrations\Epins\Entities\PurchaseAirtime;
use App\Integrations\Epins\EpinsConnector;
use App\Integrations\Epins\Responses\AirtimePurchaseResponse;
use Throwable;

final readonly class AirtimeResource
{
    public function __construct(
        private EpinsConnector $connector,
    ) {}

    /**
     * Purchase VTU airtime.
     *
     * @param PurchaseAirtime $airtime
     * @return AirtimePurchaseResponse
     */
    public function purchase(PurchaseAirtime $airtime): AirtimePurchaseResponse
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: '/airtime/',
                options: $airtime->toRequestBody()
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return AirtimePurchaseResponse::make($response->json());
    }

    /**
     * Purchase bulk airtime.
     *
     * @param string $variation Format: "08034893982=100,08023909941=200"
     * @param string $reference
     * @return AirtimePurchaseResponse
     */
    public function purchaseBulk(string $variation, string $reference): AirtimePurchaseResponse
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: '/bulkairtime/',
                options: [
                    'json' => [
                        'variation' => $variation,
                        'ref' => $reference,
                    ]
                ]
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return AirtimePurchaseResponse::make($response->json());
    }
}
