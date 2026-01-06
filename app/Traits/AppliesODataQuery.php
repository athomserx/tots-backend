<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait AppliesODataQuery
{
    /**
     * Apply OData query parameters to a query builder
     *
     * @param Builder $query
     * @param array $queryParams
     * @return void
     */
    protected function applyODataQuery(Builder $query, array $queryParams): void
    {
        // Apply filters
        if (isset($queryParams['$filter'])) {
            $this->applyODataFilters($query, $queryParams['$filter']);
        }

        // Apply ordering
        if (isset($queryParams['$orderby'])) {
            $this->applyODataOrderBy($query, $queryParams['$orderby']);
        }
    }

    /**
     * Get pagination parameters from OData query
     *
     * @param array $queryParams
     * @return array{skip: int, top: int, page: int}
     */
    protected function getODataPagination(array $queryParams): array
    {
        $skip = isset($queryParams['$skip']) ? (int) $queryParams['$skip'] : 0;
        $top = isset($queryParams['$top']) ? (int) $queryParams['$top'] : 15;
        $page = ($skip / $top) + 1;

        return [
            'skip' => $skip,
            'top' => $top,
            'page' => (int) $page,
        ];
    }

    /**
     * Apply OData filters to query
     *
     * Supports the following OData operators:
     * - substringof('value', field) - Contains search
     * - contains(field, 'value') - Contains search (alternative syntax)
     * - field eq 'value' - Equality
     * - field ne 'value' - Not equal
     * - field gt value - Greater than
     * - field lt value - Less than
     * - field ge value - Greater or equal
     * - field le value - Less or equal
     *
     * @param Builder $query
     * @param string $filter
     * @return void
     */
    protected function applyODataFilters(Builder $query, string $filter): void
    {
        // Parse substringof - format: substringof('value', field)
        if (preg_match("/substringof\('([^']+)',\s*(\w+)\)/i", $filter, $matches)) {
            $value = $matches[1];
            $field = $matches[2];
            $query->where($field, 'like', "%{$value}%");
            return;
        }

        // Parse contains - format: contains(field, 'value')
        if (preg_match("/contains\((\w+),\s*'([^']+)'\)/i", $filter, $matches)) {
            $field = $matches[1];
            $value = $matches[2];
            $query->where($field, 'like', "%{$value}%");
            return;
        }

        // Parse equality - format: field eq 'value' or field eq value
        if (preg_match("/(\w+)\s+eq\s+'?([^']+)'?/i", $filter, $matches)) {
            $field = $matches[1];
            $value = trim($matches[2], "'");
            $query->where($field, '=', $value);
            return;
        }

        // Parse not equal - format: field ne 'value'
        if (preg_match("/(\w+)\s+ne\s+'?([^']+)'?/i", $filter, $matches)) {
            $field = $matches[1];
            $value = trim($matches[2], "'");
            $query->where($field, '!=', $value);
            return;
        }

        // Parse greater than - format: field gt value
        if (preg_match("/(\w+)\s+gt\s+(\d+\.?\d*)/i", $filter, $matches)) {
            $field = $matches[1];
            $value = $matches[2];
            $query->where($field, '>', $value);
            return;
        }

        // Parse less than - format: field lt value
        if (preg_match("/(\w+)\s+lt\s+(\d+\.?\d*)/i", $filter, $matches)) {
            $field = $matches[1];
            $value = $matches[2];
            $query->where($field, '<', $value);
            return;
        }

        // Parse greater than or equal - format: field ge value
        if (preg_match("/(\w+)\s+ge\s+(\d+\.?\d*)/i", $filter, $matches)) {
            $field = $matches[1];
            $value = $matches[2];
            $query->where($field, '>=', $value);
            return;
        }

        // Parse less than or equal - format: field le value
        if (preg_match("/(\w+)\s+le\s+(\d+\.?\d*)/i", $filter, $matches)) {
            $field = $matches[1];
            $value = $matches[2];
            $query->where($field, '<=', $value);
            return;
        }
    }

    /**
     * Apply OData orderby to query
     *
     * Supports format: field asc|desc
     * Multiple fields can be comma-separated: field1 asc, field2 desc
     *
     * @param Builder $query
     * @param string $orderBy
     * @return void
     */
    protected function applyODataOrderBy(Builder $query, string $orderBy): void
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
