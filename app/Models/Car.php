<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Car
 *
 * @property int $id
 * @property string $name
 * @property string|null $model
 * @property \Illuminate\Support\Carbon|null $bought_at
 * @property string|null $price Decimal(15,2)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Car extends Model
{
    protected $fillable = [
        'name',
        'model',
        'bought_at',
        'price',
    ];

    protected $casts = [
        'year' => 'integer',
        'bought_at' => 'date',
        'price' => 'decimal:2',
        'bought_at' => 'date',
    ];
}
