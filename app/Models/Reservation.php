<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $space_id
 * @property string $start
 * @property string $end
 * @property string $type
 */
class Reservation extends Model
{
    protected $fillable = [
        'user_id',
        'space_id',
        'start',
        'end',
        'type',
        'event_name'
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    // Filter by blocks reservations
    public function scopeBlocks($query)
    {
        return $query->where('type', 'block');
    }
}
