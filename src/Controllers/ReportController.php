<?php

namespace App\Controllers;

use App\Services\ReportService;
use App\Core\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        $filters = ReportRequest::validateFilters($_GET);

        Logger::info("Filtering report", $filters);

        $service = new ReportService();
        $products = $service->getReportProducts($filters);

        return new Response(
            json_encode(['data' => $products]),
            200,
            ['Content-Type' => 'application/json']
        );
    }

    public static function exportCSV(): Response
    {
        $filters = ReportRequest::validateFilters($_GET);

        $service = new ReportService();
        $products = $service->getReportProducts($filters);

        $csv = fopen('php://temp', 'r+');

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

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return new Response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="report.csv"',
        ]);
    }

    public static function exportPDF(): Response
    {
        $filters = ReportRequest::validateFilters($_GET);

        $service = new ReportService();
        $products = $service->getReportProducts($filters);

        $html = View::render('report_pdf', [
            'products' => $products
        ], true);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="report.pdf"'
            ]
        );
    }
}
