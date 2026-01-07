<?php

namespace App\Services;

use App\Models\Reservation;
use App\Repositories\ReservationRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ReservationService
{
    public function __construct(
        protected ReservationRepository $repository
    ) {
    }

    public function getReservations(array $queryParams): LengthAwarePaginator
    {
        return $this->repository->getAll($queryParams);
    }

    public function getReservation(int $id): ?Reservation
    {
        return $this->repository->findById($id);
    }

    public function createReservation(array $data): Reservation
    {
        return $this->repository->create($data);
    }

    public function updateReservation(Reservation $reservation, array $data): Reservation
    {
        return $this->repository->update($reservation, $data);
    }

    public function deleteReservation(Reservation $reservation): bool
    {
        return $this->repository->delete($reservation);
    }
}
