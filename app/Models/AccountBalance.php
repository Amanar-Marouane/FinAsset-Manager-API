<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AccountBalance
 *
 * @property int $id
 * @property int $bank_account_id
 * @property int $year
 * @property int $month
 * @property date $date
 * @property decimal $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\BankAccount $account
 */
class AccountBalance extends Model
{
    protected $fillable = [
        'bank_account_id',
        'year',
        'month',
        'date',
        'amount',
        'other_person_money',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'other_person_money' => 'decimal:2',
        'year' => 'integer',
        'month' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            // Auto-extract year and month from date
            if ($model->date) {
                $model->year = $model->date->year;
                $model->month = $model->date->month;
            }
        });

        static::updating(function ($model) {
            // Auto-extract year and month from date if date is being updated
            if ($model->isDirty('date') && $model->date) {
                $model->year = $model->date->year;
                $model->month = $model->date->month;
            }
        });
    }

    /**
     * Account this balance belongs to.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }
}
