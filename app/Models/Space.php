<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property float $price_per_hour
 * @property int $capacity
 * @property array $images
 */
class Space extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price_per_hour',
        'capacity',
        'images'
    ];

    protected $casts = [
        'images' => 'array',
        'price_per_hour' => 'decimal:2'
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function availabilityRules()
    {
        return $this->hasMany(AvailabilityRule::class);
    }
}
