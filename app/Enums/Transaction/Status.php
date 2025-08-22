<?php

namespace App\Enums\Transaction;

enum Status: string
{
    case Pending = 'pending';
    case Success = 'success';
    case Failed = 'failed';


    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'zinc',
            self::Success => 'green',
            self::Failed => 'red',
        };
    }

    public function getLabel(): null|string
    {
        return $this->value;
    }

    public static function match(string|null $value): Status
    {
        return match ($value) {
            Status::Pending->value => Status::Pending,
            Status::Success->value => Status::Success,
            Status::Failed->value => Status::Failed,
            default => Status::Pending,
        };
    }


    public static function toSelect($placeholder = false): array
    {
        $array = [];
        $index = 0;
        if ($placeholder) {
            $array[$index]['id'] = '';
            $array[$index]['name'] = '-- Select --';
            $index++;
        }

        foreach (self::cases() as $case) {
            $array[$index]['id'] = $case->value;
            $array[$index]['name'] = $case->value;
            $index++;
        }

        return $array;
    }



}
