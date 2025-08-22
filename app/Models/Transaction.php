<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Enums\Transaction\Type;
use App\Enums\Transaction\Status;
use App\Concerns\Filterable;
use Illuminate\Database\Eloquent\Model;
use App\Models\Builders\TransactionBuilder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;

class Transaction extends Model
{
    
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => Status::class,
            'type' => Type::class,
            'meta' => 'array'
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            related: User::class,
            foreignKey: 'user_id',
        );
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => Status::Failed]);
    }

    public function markAsSuccess(): void
    {
        $this->update(['status' => Status::Success]);
    }

    public function markAsPending(): void
    {
        $this->update(['status' => Status::Pending]);
    }


    public function newEloquentBuilder($query): BuilderContract
    {
        return new TransactionBuilder(
            query: $query
        );
    }

    public function generateCheckInCode(): string
    {
        return Str::random(
            length: 6,
        );
    }



    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }
}
