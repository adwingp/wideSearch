<?php

namespace App\Services;

class PaginationHelper
{
    /**
     * Paginate an array or Laravel Collection and return paginated data with meta info.
     *
     * @param array|\Illuminate\Support\Collection $items
     * @param int $perPage
     * @param int $page
     * @param string|null $query
     * @param string $dataKey
     * @return array
     */
    public static function paginate($items, int $perPage, int $page, ?string $query = null, string $dataKey = 'data')
    {
        if ($items instanceof \Illuminate\Support\Collection) {
            $items = $items->values();
            $total = $items->count();
            $paginated = $items->forPage($page, $perPage)->values();
        } else {
            $items = array_values($items);
            $total = count($items);
            $offset = ($page - 1) * $perPage;
            $paginated = array_slice($items, $offset, $perPage);
        }
        $meta = [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
        ];
        if (!is_null($query)) {
            $meta['query'] = $query;
        }
        return [
            $dataKey => $paginated,
            'meta' => $meta,
        ];
    }
}
