<?php

namespace App\Policies;

use App\Enums\RoleType;
use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    /**
     * Determine if the user can view any reservations.
     * Admins can view all reservations, clients can only view their own (handled in controller).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Reservation $reservation): bool
    {
        if ($user->role_id === RoleType::ADMIN->value) {
            return true;
        }

        return $user->id === $reservation->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Reservation $reservation): bool
    {
        if ($user->role_id === RoleType::ADMIN->value) {
            return true;
        }

        return $user->id === $reservation->user_id;
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        if ($user->role_id === RoleType::ADMIN->value) {
            return true;
        }

        return $user->id === $reservation->user_id;
    }
}
