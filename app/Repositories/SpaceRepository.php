<?php

namespace App\Repositories;

use App\Models\Space;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class SpaceRepository
{
    public function getAll(array $queryParams): LengthAwarePaginator
    {
        $query = Space::query();

        if (isset($queryParams['$filter'])) {
            $this->applyFilters($query, $queryParams['$filter']);
        }
        if (isset($queryParams['$orderby'])) {
            $this->applyOrderBy($query, $queryParams['$orderby']);
        }

        $skip = isset($queryParams['$skip']) ? (int) $queryParams['$skip'] : 0;
        $top = isset($queryParams['$top']) ? (int) $queryParams['$top'] : 15;

        $page = ($skip / $top) + 1;

        return $query->paginate(perPage: $top, page: (int) $page);
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

    /**
     * Apply OData filters to query
     */
    protected function applyFilters(Builder $query, string $filter): void
    {
        if (preg_match("/substringof\('([^']+)',\s*(\w+)\)/i", $filter, $matches)) {
            $value = $matches[1];
            $field = $matches[2];
            $query->where($field, 'like', "%{$value}%");
            return;
        }

        if (preg_match("/contains\((\w+),\s*'([^']+)'\)/i", $filter, $matches)) {
            $field = $matches[1];
            $value = $matches[2];
            $query->where($field, 'like', "%{$value}%");
            return;
        }

        if (preg_match("/(\w+)\s+eq\s+'?([^']+)'?/i", $filter, $matches)) {
            $field = $matches[1];
            $value = trim($matches[2], "'");
            $query->where($field, '=', $value);
            return;
        }

        if (preg_match("/(\w+)\s+ne\s+'?([^']+)'?/i", $filter, $matches)) {
            $field = $matches[1];
            $value = trim($matches[2], "'");
            $query->where($field, '!=', $value);
            return;
        }
        if (preg_match("/(\w+)\s+gt\s+(\d+\.?\d*)/i", $filter, $matches)) {
            $field = $matches[1];
            $value = $matches[2];
            $query->where($field, '>', $value);
            return;
        }

        if (preg_match("/(\w+)\s+lt\s+(\d+\.?\d*)/i", $filter, $matches)) {
            $field = $matches[1];
            $value = $matches[2];
            $query->where($field, '<', $value);
            return;
        }

        if (preg_match("/(\w+)\s+ge\s+(\d+\.?\d*)/i", $filter, $matches)) {
            $field = $matches[1];
            $value = $matches[2];
            $query->where($field, '>=', $value);
            return;
        }

        if (preg_match("/(\w+)\s+le\s+(\d+\.?\d*)/i", $filter, $matches)) {
            $field = $matches[1];
            $value = $matches[2];
            $query->where($field, '<=', $value);
            return;
        }
    }

    protected function applyOrderBy(Builder $query, string $orderBy): void
    {
        $parts = explode(',', $orderBy);

        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match("/(\w+)\s*(asc|desc)?/i", $part, $matches)) {
                $field = $matches[1];
                $direction = isset($matches[2]) ? strtolower($matches[2]) : 'asc';
                $query->orderBy($field, $direction);
            }
        }
    }
}
