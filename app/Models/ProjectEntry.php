<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProjectEntry
 *
 * @property int $id
 * @property int $project_id
 * @property string $amount Decimal(12,2)
 * @property int $month
 * @property int $year
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ProjectEntry extends Model
{
    protected $fillable = [
        'project_id',
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
     * The project that this entry belongs to.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
