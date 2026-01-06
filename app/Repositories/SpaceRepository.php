<?php

namespace App\Repositories;

use App\Models\Space;
use App\Traits\AppliesODataQuery;
use Illuminate\Pagination\LengthAwarePaginator;

class SpaceRepository
{
    use AppliesODataQuery;

    public function getAll(array $queryParams): LengthAwarePaginator
    {
        $query = Space::query();

        $this->applyODataQuery($query, $queryParams);

        $pagination = $this->getODataPagination($queryParams);

        return $query->paginate(
            perPage: $pagination['top'],
            page: $pagination['page']
        );
    }

    public function findById(int $id): ?Space
    {
        return Space::find($id);
    }

    public function create(array $data): Space
    {
        return Space::create($data);
    }

    public function update(Space $space, array $data): Space
    {
        $space->update($data);
        return $space->fresh();
    }

    public function delete(Space $space): bool
    {
        return $space->delete();
    }
}
