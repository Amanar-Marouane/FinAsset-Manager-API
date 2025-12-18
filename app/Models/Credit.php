<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Credit
 *
 * @property int $id
 * @property string $montant Decimal(15,2) Net with interest
 * @property string|null $monthly_payment Decimal(15,2)
 * @property string|null $organization
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Credit extends Model
{
    protected $fillable = [
        'montant',
        'monthly_payment',
        'organization',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
    ];
}
