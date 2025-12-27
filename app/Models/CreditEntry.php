<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CreditEntry
 *
 * @property int $id
 * @property int $credit_id
 * @property string $amount Decimal(12,2)
 * @property int $month
 * @property int $year
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CreditEntry extends Model
{
    protected $fillable = [
        'credit_id',
        'amount',
        'month',
        'year',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'month' => 'integer',
        'year' => 'integer',
    ];

    /**
     * The credit that this entry belongs to.
     */
    public function credit()
    {
        return $this->belongsTo(Credit::class);
    }
}
