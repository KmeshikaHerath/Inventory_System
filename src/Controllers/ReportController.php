<?php

namespace App\Controllers;

use App\Services\ReportService;
use App\Core\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use App\Requests\ReportRequest;
use App\Core\Logger;
use App\Models\Product;
use Exception;

/**
 * Class ReportController
 *
 * Handles report-related operations such as:
 * - Displaying report page
 * - Filtering report data (DataTable)
 * - Exporting CSV reports
 * - Exporting PDF reports
 */
class ReportController
{
    /**
     * Show report index page with categories.
     *
     * @return Response
     */
    public static function index(): Response
    {
        try {
            Logger::info("Report index page accessed");

            $productModel = new Product();
            $categories = $productModel->getCategory();

            return new Response(
                View::render('report', [
                    'categories' => $categories
                ])
            );
        } catch (\Throwable $e) {
            Logger::error("Error in ReportController@index", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return new Response("Something went wrong", 500);
        }
    }

    /**
     * Fetch filtered report data for DataTables.
     *
     * @return Response JSON response containing paginated data
     */
    public static function filter(): Response
    {
        try {
            $service = new ReportService();

            $draw = (int)($_GET['draw'] ?? 1);
            $start = (int)($_GET['start'] ?? 0);
            $length = (int)($_GET['length'] ?? 10);

            $search = $_GET['search']['value'] ?? '';

            $filters = ReportRequest::validateFilters($_GET);

            $result = $service->getReportProductsDataTable(
                $filters,
                $start,
                $length,
                $search
            );

            Logger::info("Report filter executed", [
                'filters' => $filters,
                'search' => $search
            ]);

            return new Response(
                json_encode([
                    "draw" => $draw,
                    "recordsTotal" => $result['recordsTotal'],
                    "recordsFiltered" => $result['recordsFiltered'],
                    "data" => $result['data']
                ]),
                200,
                ['Content-Type' => 'application/json']
            );
        } catch (Exception $e) {
            Logger::error("Error in ReportController@filter", [
                'message' => $e->getMessage()
            ]);

            return new Response(json_encode([
                "error" => "Failed to load data"
            ]), 500);
        }
    }

    /**
     * Export report data as CSV file.
     *
     * @return BinaryFileResponse CSV file download response
     */
    public static function exportCSV(): Response
    {
        try {
            $filters = ReportRequest::validateFilters($_GET);
            $search = $_GET['search'] ?? '';

            $service = new ReportService();
            $filePath = $service->generateCsvReport($filters, $search);

            Logger::info("CSV exported successfully", [
                'file' => $filePath
            ]);

            return new Response(
                $filePath,
                200,
                [
                    'Content-Type' => 'text/plain'
                ]
            );
        } catch (Exception $e) {
            Logger::error("CSV export failed", [
                'message' => $e->getMessage()
            ]);

            return new Response(
                json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]),
                500,
                [
                    'Content-Type' => 'application/json'
                ]
            );
        }
    }

    /**
     * Export report data as PDF file.
     *
     * @return BinaryFileResponse PDF file download response
     */
    public static function exportPDF(): Response
    {
        try {
            $filters = ReportRequest::validateFilters($_GET);
            $search = $_GET['search'] ?? '';

            $service = new ReportService();
            $filePath = $service->generatePdfReport($filters, $search);

            Logger::info("PDF exported successfully", [
                'file' => $filePath
            ]);

            return new Response(
                $filePath,
                200,
                [
                    'Content-Type' => 'text/plain'
                ]
            );
        } catch (Exception $e) {
            Logger::error("PDF export failed", [
                'message' => $e->getMessage()
            ]);

            return new Response(
                json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]),
                500,
                [
                    'Content-Type' => 'application/json'
                ]
            );
        }
    }
}
