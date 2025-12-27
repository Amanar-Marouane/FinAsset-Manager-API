<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PretEntry
 *
 * @property int $id
 * @property int $pret_id
 * @property string $amount Decimal(12,2)
 * @property int $month
 * @property int $year
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class PretEntry extends Model
{
    protected $fillable = [
        'pret_id',
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
     * The pret that this entry belongs to.
     */
    public function pret()
    {
        return $this->belongsTo(Pret::class);
    }
}
