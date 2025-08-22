<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Concerns\HasLoans;
use App\Concerns\HasShares;
use App\Concerns\Wallet\HasWallet;
use App\Concerns\Wallet\HandlesDeposit;
use App\Enums\WalletType;
use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasLoans;
    use HasShares;
    use HasWallet;
    use HandlesDeposit;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }



    public function oAuthConnections(): HasMany
    {
        return $this->hasMany(
            related: OauthConnection::class,
            foreignKey: 'user_id'
        );
    }

    /**
     * Debit user's wallet (for share purchases)
     */
    public function debitWallet(float $amount, ?string $notes = null): void
    {
        $this->payFromWallet(WalletType::MAIN, $amount, $notes);
    }

    /**
     * Credit user's wallet (for share sales)
     */
    public function creditWallet(float $amount, ?string $notes = null): void
    {
        $this->deposit(WalletType::MAIN, $amount, $notes);
    }
}
