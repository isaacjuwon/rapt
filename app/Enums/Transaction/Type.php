<?php

namespace App\Enums\Transaction;

enum Type: string
{
    case Payment = 'payment';
    case Deposit = 'deposit';
    case Refund = 'refund';
    case Withdrawal = 'withdrawal';


    public static function match(string|null $value): Type
    {
        return match ($value) {
            Type::Deposit->value => Type::Deposit,
            Type::Refund->value => Type::Refund,
            Type::Withdrawal->value => Type::Withdrawal,
            Type:: Payment->value => Type::Payment,
            default => Type::Payment,
        };
    }

}
