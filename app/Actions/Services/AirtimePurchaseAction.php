<?php

namespace App\Actions\Services;

use App\Managers\ApiManager;
use App\Integrations\Epins\Entities\PurchaseAirtime;

class AirtimePurchaseAction
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private ApiManager $manager,
    ) {
        //
    }

    public function handle(array $data)
    {
        $payload = new PurchaseAirtime(
            network: $data['network'],
            phone: $data['phone'],
            amount: $data['amount'],
            reference: $data['reference'],
            ported: $data['ported'] ?? false,
        );

        $result = $this->driver()->airtime()->purchase($payload);

        //record event
        return $result;
    }

    public function driver(): ApiManager
    {
        return $this->manager->driver('epins');
    }
}
