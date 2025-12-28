<?php

namespace Database\Seeders;

use App\Models\AvailabilityRule;
use App\Models\Exception;
use App\Models\Reservation;
use App\Models\Role;
use App\Models\Space;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Role::create(['name' => 'client']);
        $adminRole = Role::create(['name' => 'admin']);

        // Admin User
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'role_id' => $adminRole->id,
        ]);

        $spaceA = Space::create([
            'name' => 'Salón Principal',
            'description' => 'Salón principal para eventos',
            'price_per_hour' => 50,
            'capacity' => 20,
            'images' => [],
        ]);

        foreach (range(0, 6) as $day) {
            AvailabilityRule::create([
                'space_id' => null,
                'day_of_week' => $day,
                'open_time' => '14:00:00',
                'close_time' => '03:00:00',
            ]);
        }
    }
}
