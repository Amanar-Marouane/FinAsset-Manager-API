<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Class Credit
 *
 * @property int $id
 * @property string $montant Decimal(15,2) Net with interest
 * @property string|null $montant_net Decimal(15,2) Principal without interest
 * @property string|null $montantNet CamelCase accessor/mutator for montant_net
 * @property string|null $monthly_payment Decimal(15,2)
 * @property string|null $organization
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Credit extends Model
{
    protected $fillable = [
        'montant',
        'montant_net',
        'monthly_payment',
        'organization',
        'montantNet',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'montant_net' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
    ];

    protected function montantNet(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->attributes['montant_net'] ?? null,
            set: fn($value) => ['montant_net' => $value]
        );
    }
}
