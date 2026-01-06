<?php

namespace App\Services;

use App\Models\Space;
use App\Repositories\SpaceRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class SpaceService
{
    public function __construct(
        protected SpaceRepository $repository
    ) {
    }

    public function getSpaces(array $queryParams): LengthAwarePaginator
    {
        return $this->repository->getAll($queryParams);
    }

    public function getSpace(int $id): ?Space
    {
        return $this->repository->findById($id);
    }

    public function createSpace(array $data): Space
    {
        if (isset($data['images']) && is_string($data['images'])) {
            $data['images'] = json_decode($data['images'], true) ?? [];
        }

        return $this->repository->create($data);
    }

    public function updateSpace(Space $space, array $data): Space
    {
        if (isset($data['images']) && is_string($data['images'])) {
            $data['images'] = json_decode($data['images'], true) ?? [];
        }

        return $this->repository->update($space, $data);
    }

    public function deleteSpace(Space $space): bool
    {
        return $this->repository->delete($space);
    }
}
