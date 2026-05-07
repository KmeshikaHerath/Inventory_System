<?php

namespace App\Requests;

class ReportRequest
{
    public static function validateFilters(array $filters): array
    {
        return [
            'category_id' => isset($filters['category_id']) && is_numeric($filters['category_id'])
                ? (int)$filters['category_id']
                : null,

            'sku' => isset($filters['sku'])
                ? trim($filters['sku'])
                : null,

            'status' => isset($filters['status']) &&
                in_array($filters['status'], ['active', 'inactive'])
                ? $filters['status']
                : null,
        ];
    }
}