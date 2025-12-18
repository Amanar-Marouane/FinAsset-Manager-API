<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class BuildingType
 *
 * Represents the dynamic type of a building (e.g., house, garage, apartment, villa).
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Building> $buildings
 */
class BuildingType extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Buildings of this type.
     */
    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }
}
