<?php

namespace App\Services;

use App\Models\Product;
use App\Requests\ReportRequest;

class ReportService
{
    private $reportModel;

    public function __construct()
    {
        $this->reportModel = new Product();
    }

    public function getCategories()
    {
        return $this->reportModel->getCategory();
    }

    public function getReportProductsDataTable($filters, $start, $length, $search): array
    {
        $baseQuery = "FROM products p
                      LEFT JOIN categories c ON p.category_id = c.id
                      WHERE 1=1";

        $params = [];

        // FILTERS
        if (!empty($filters['category_id'])) {
            $baseQuery .= " AND p.category_id = :category_id";
            $params[':category_id'] = $filters['category_id'];
        }

        if (!empty($filters['sku'])) {
            $baseQuery .= " AND p.sku LIKE :sku";
            $params[':sku'] = '%' . $filters['sku'] . '%';
        }

        if (!empty($filters['status'])) {
            $baseQuery .= " AND p.status = :status";
            $params[':status'] = $filters['status'];
        }

        // SEARCH
        if (!empty($search)) {
            $baseQuery .= " AND (p.name LIKE :search OR p.sku LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $data = $this->reportModel->getProducts($params, $baseQuery, $length, $start);
        $recordsFiltered = $this->reportModel->getFilteredCount($params, $baseQuery);
        $recordsTotal = $this->reportModel->getTotalCount();

        return [
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ];
    }
}
