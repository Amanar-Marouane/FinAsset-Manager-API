<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Bank
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BankAccount> $accounts
 */
class Bank extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Accounts belonging to this bank.
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }
}
