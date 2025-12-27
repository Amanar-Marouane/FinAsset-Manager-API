<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Pret
 *
 * Represents an outgoing loan (opposite of Credit). No organization field.
 *
 * @property int $id
 * @property string $organization
 * @property string $montant Decimal(15,2)
 * @property string $montant_net Decimal(15,2)
 * @property string|null $monthly_payment Decimal(15,2)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Pret extends Model
{
    protected $fillable = [
        'organization',
        'montant',
        'montant_net',
        'monthly_payment',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'montant_net' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
    ];

    /**
     * The entries for this pret.
     */
    public function entries()
    {
        return $this->hasMany(PretEntry::class);
    }
}
