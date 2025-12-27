<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Project
 *
 * @property int $id
 * @property string $name
 * @property string $capital Decimal(15,2)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Project extends Model
{
    protected $fillable = [
        'name',
        'capital',
    ];

    protected $casts = [
        'capital' => 'decimal:2',
    ];

    /**
     * The entries for this project.
     */
    public function entries()
    {
        return $this->hasMany(ProjectEntry::class);
    }
}
