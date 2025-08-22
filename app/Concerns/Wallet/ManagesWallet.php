<?php

declare(strict_types=1);

namespace App\Concerns\Wallet;

trait ManagesWallet
{
    use HandlesDeposit;
    use HandlesPayment;
    use HasWallet;
}
