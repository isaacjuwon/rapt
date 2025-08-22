<?php

declare(strict_types=1);

namespace App\Integrations\Epins\Resources;

use App\Integrations\Epins\Entities\PurchaseTvSubscription;
use App\Integrations\Epins\EpinsConnector;
use App\Integrations\Epins\Responses\TvSubscriptionResponse;
use Throwable;

final readonly class TvSubscriptionResource
{
    public function __construct(
        private EpinsConnector $connector,
    ) {}

    /**
     * Purchase TV subscription.
     *
     * @param PurchaseTvSubscription $subscription
     * @return TvSubscriptionResponse
     */
    public function purchase(PurchaseTvSubscription $subscription): TvSubscriptionResponse
    {
        try {
            $response = $this->connector->send(
                method: 'POST',
                uri: '/biller/',
                options: $subscription->toRequestBody()
            );
        } catch (Throwable $exception) {
            throw $exception;
        }

        return TvSubscriptionResponse::make($response->json());
    }
}
