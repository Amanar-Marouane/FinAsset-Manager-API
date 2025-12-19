<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class BankAccount
 *
 * @property int $id
 * @property int $bank_id
 * @property string $account_number
 * @property string $currency
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Bank $bank
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AccountBalance> $balances
 */
class BankAccount extends Model
{
    protected $fillable = [
        'bank_id',
        'account_name',
        'account_number',
        'currency',
    ];

    /**
     * Bank this account belongs to.
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    /**
     * Balance records for this account.
     */
    public function balances(): HasMany
    {
        return $this->hasMany(AccountBalance::class);
    }

    public function getLastInsertedBalanceAttribute()
    {
        $lastBalance = $this->balances()->orderBy('date', 'desc')->first();
        return $lastBalance;
    }

    protected static function booted(): void
    {
        static::deleting(function (BankAccount $account) {
            if ($account->balances()->exists()) {
                throw new \RuntimeException('Cannot delete bank account with existing balances.');
            }
        });
    }
}
