<?php

namespace App\Controllers;

use App\Services\ReportService;
use App\Core\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use App\Requests\ReportRequest;
use App\Core\Logger;
use App\Models\Product;
use Dompdf\Dompdf;

class ReportController
{
    public static function index(): Response
    {
        Logger::info("Report index page accessed");

        $productModel = new Product();
        $categories = $productModel->getCategory();

        return new Response(
            View::render('report', [
                'categories' => $categories
            ])
        );
    }

    public static function filter(): Response
    {
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

        return new Response(
            json_encode([
                "draw" => $draw,
                "recordsTotal" => $result['recordsTotal'],
                "recordsFiltered" => $result['recordsFiltered'],
                "data" => $result['data']
            ]),
            200,
        );
    }
    /*
        * New method to export filtered report data as CSV
*/
    public static function exportCSV(): BinaryFileResponse
    {
        $filters = ReportRequest::validateFilters($_GET);
        $service = new ReportService();
        $result = $service->getReportProductsDataTable($filters, 0, PHP_INT_MAX, '');
        $products = $result['data'];

        $projectDir = dirname(__DIR__, 2);
        $directory = $projectDir . '/public/Download_files/csv';

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $filename = 'report_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.csv';
        $filePath = $directory . '/' . $filename;

        $csv = fopen($filePath, 'w');

        fputcsv($csv, ['ID', 'Name', 'Category', 'SKU', 'Price', 'Quantity', 'Status']);

        foreach ($products as $product) {
            fputcsv($csv, [
                $product['id'],
                $product['name'],
                $product['category_name'],
                $product['sku'],
                $product['price'],
                $product['quantity'],
                $product['status']
            ]);
        }

        fclose($csv);

        return new BinaryFileResponse($filePath, 200, [
            'Content-Type' => 'text/csv',
        ], true, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

    public static function exportPDF(): Response
    {
        $filters = ReportRequest::validateFilters($_GET);

        $service = new ReportService();

        $result = $service->getReportProductsDataTable(
            $filters,
            0,
            PHP_INT_MAX,
            ''
        );

        $products = $result['data'];

        $html = View::render('report_pdf', [
            'products' => $products
        ], true);

        $dompdf = new Dompdf();

        // $dompdf->loadHtml($html);
        $dompdf->loadHtml('hello world');

        $dompdf->setPaper('A4', 'landscape');

        $dompdf->render();
       

        // Create folder path
        $projectDir = dirname(__DIR__, 2);
        $directory = $projectDir . '/public/Download_files/pdf';

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        // File name
        $filename = 'report_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.pdf';

        $filePath = $directory . '/' . $filename;
 
        // Save PDF file to server
        file_put_contents($filePath, $dompdf->output());
var_dump($filePath, $dompdf->output());die;
        // Return download response (optional)
        return new BinaryFileResponse($filePath, 200, [
            'Content-Type' => 'application/pdf',
        ], true, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }
}
