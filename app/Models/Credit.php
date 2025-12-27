<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Class Credit
 *
 * @property int $id
 * @property string $to
 * @property string $montant Decimal(15,2) Net with interest
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Credit extends Model
{
    protected $fillable = [
        'to',
        'montant',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
    ];

    /**
     * The entries for this credit.
     */
    public function entries()
    {
        return $this->hasMany(CreditEntry::class);
    }
}
