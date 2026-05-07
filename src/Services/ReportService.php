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

    public function getReportProducts(array $filter = [])
    {
        return $this->reportModel->getReportProducts($filter);
    }

    public function getCategories()
    {
        return $this->reportModel->getCategory();
    }
}
