<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Building
 *
 * @property int $id
 * @property string $name
 * @property string|null $address
 * @property int $building_type_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\BuildingType $type
 */
class Building extends Model
{
    protected $fillable = [
        'name',
        'address',
        'building_type_id',
    ];

    /**
     * Type of the building (house, garage, apartment, villa, etc.).
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(BuildingType::class, 'building_type_id');
    }
}
