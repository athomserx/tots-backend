<?php

namespace App\Repositories;

use App\Models\Reservation;
use App\Traits\AppliesODataQuery;
use Illuminate\Pagination\LengthAwarePaginator;

class ReservationRepository
{
    use AppliesODataQuery;

    public function getAll(array $queryParams): LengthAwarePaginator
    {
        $query = Reservation::query();

        if (isset($queryParams['user_id'])) {
            $query->where('user_id', $queryParams['user_id']);
        }

        $this->applyODataQuery($query, $queryParams);

        $pagination = $this->getODataPagination($queryParams);

        return $query->paginate(
            perPage: $pagination['top'],
            page: $pagination['page']
        );
    }

    public function findById(int $id): ?Reservation
    {
        return Reservation::find($id);
    }

    public function create(array $data): Reservation
    {
        return Reservation::create($data);
    }

    public function update(Reservation $reservation, array $data): Reservation
    {
        $reservation->update($data);
        return $reservation->fresh();
    }

    public function delete(Reservation $reservation): bool
    {
        return $reservation->delete();
    }
}
