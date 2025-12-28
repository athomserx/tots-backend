<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $space_id
 * @property int $day_of_week
 * @property string $open_time
 * @property string $close_time
 * @property bool $is_active
 */
class AvailabilityRule extends Model
{
    protected $fillable = [
        'space_id',
        'day_of_week',
        'open_time',
        'close_time',
        'is_active'
    ];

    public function space()
    {
        return $this->belongsTo(Space::class);
    }
}
