<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Terrain
 *
 * @property int $id
 * @property string $name
 * @property string|null $address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Terrain extends Model
{
    protected $fillable = [
        'name',
        'address',
    ];
}
