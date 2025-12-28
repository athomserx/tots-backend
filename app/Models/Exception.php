<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $date
 * @property bool $is_closed
 * @property int $space_id
 * @property string $override_open_time
 * @property string $override_close_time
 */
class Exception extends Model
{
    protected $fillable = [
        'date',
        'is_closed',
        'space_id',
        'override_open_time',
        'override_close_time'
    ];

    protected $casts = [
        'date' => 'date',
        'is_closed' => 'boolean'
    ];

    public function space()
    {
        return $this->belongsTo(Space::class);
    }
}
