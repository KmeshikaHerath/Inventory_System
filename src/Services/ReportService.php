<?php

namespace App\Services;

use App\Models\Product;
use App\Core\View;
use Dompdf\Dompdf;
use App\Core\Database;
use PDO;

/**
 * Class ReportService
 *
 * Handles report generation for products including
 * data retrieval, PDF export, and CSV export.
 */
class ReportService
{
    private $reportModel;
    private $conn;

    /**
     * ReportService constructor.
     * Initializes product model and database connection.
     */
    public function __construct()
    {
        $this->reportModel = new Product();

        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Get all product categories.
     *
     * @return mixed
     */
    public function getCategories()
    {
        return $this->reportModel->getCategory();
    }

    /**
     * Get paginated product data for DataTable.
     *
     * @param array $filters Filter conditions (category_id, sku, status)
     * @param int $start Pagination start index
     * @param int $length Number of records to fetch
     * @param string $search Global search keyword
     * @return array
     */
    public function getReportProductsDataTable($filters, $start, $length, $search): array
    {
        $queryData = $this->getReportDataQuery($filters, $search);
        $baseQuery = $queryData['baseQuery'];
        $params = $queryData['params'];

        $data = $this->reportModel->getProducts($params, $baseQuery, $length, $start);
        $recordsFiltered = $this->reportModel->getFilteredCount($params, $baseQuery);
        $recordsTotal = $this->reportModel->getTotalCount();

        return [
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ];
    }

    /**
     * Build dynamic SQL query conditions based on filters and search.
     *
     * @param array $filters
     * @param string $search
     * @return array
     */
    public function getReportDataQuery($filters, $search)
    {
        $baseQuery = "FROM products p
                      LEFT JOIN categories c ON p.category_id = c.id
                      WHERE p.deleted_at IS NULL";

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

        return ['baseQuery' => $baseQuery, 'params' => $params];
    }

    /**
     * Generate PDF report and save it to server.
     *
     * @param array $filters
     * @param string $search
     * @return string File path of generated PDF
     */
    public function generatePdfReport($filters, $search): string
    {
        $queryData = $this->getReportDataQuery($filters, $search);
        $baseQuery = $queryData['baseQuery'];
        $params = $queryData['params'];

        $database = Database::getInstance();
        $conn = $database->getConnection();

        $sql = "SELECT p.*, c.name AS category_name {$baseQuery}";
        $stmt = $conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $html = View::render('report_pdf', [
            'products' => $products
        ], false);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $projectDir = dirname(__DIR__, 2);
        $directory = $projectDir . '/public/Download_files/pdf';

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $filename = 'report_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.pdf';
        $filePath = $directory . '/' . $filename;

        file_put_contents($filePath, $dompdf->output());

        return $filePath;
    }

    /**
     * Generate CSV report using SQL INTO OUTFILE.
     *
     * @param array $filters
     * @param string $search
     * @return string File path of generated CSV
     */

    public function generateCsvReport($filters, $search): string
    {
        $queryData = $this->getReportDataQuery($filters, $search);
        $baseQuery = $queryData['baseQuery'];
        $params = $queryData['params'];

        $database = Database::getInstance();
        $conn = $database->getConnection();

        //Prepare Directory and Absolute Path
        $projectDir = dirname(__DIR__, 2);
        $directory = $projectDir . '/public/Download_files/csv';
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $filename = 'report_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.csv';
        $filePath = $directory . '/' . $filename;

        //Build SQL with INTO OUTFILE
        $sql = "
        SELECT 'ID', 'Name', 'Price', 'Quantity', 'SKU', 'Description', 'Status', 'Category'
        UNION ALL
        SELECT p.id, p.name, p.price, p.quantity, p.sku, p.description, p.status, c.name
        {$baseQuery}
        INTO OUTFILE {$conn->quote($filePath)}
        FIELDS TERMINATED BY ',' 
        OPTIONALLY ENCLOSED BY '\"' 
        LINES TERMINATED BY '\\n'
    ";

        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        // Execute
        $stmt->execute();

        return $filePath;
    }
}
