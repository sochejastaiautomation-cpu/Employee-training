<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Product.php';
require_once __DIR__ . '/../src/ExcelHandler.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'download_sample') {
    ExcelHandler::generateSampleExcel();
}

if ($action === 'upload_products' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = 'Please select a valid file';
        header('Location: dashboard.php');
        exit;
    }
    
    $file = $_FILES['csv_file'];
    $allowed_types = ['text/csv', 'text/plain', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    
    // Get file extension
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Validate file extension
    if (!in_array($file_ext, ['csv', 'xlsx', 'xls', 'txt'])) {
        $_SESSION['error'] = 'Please upload a CSV or Excel file';
        header('Location: dashboard.php');
        exit;
    }
    
    // Additional check: use file extension for validation
    // mime_content_type may not be available on all servers
    
    try {
        $temp_file = $file['tmp_name'];
        $product_obj = new Product($conn);
        $results = ExcelHandler::importProductsFromCSV($temp_file, $product_obj);
        
        if ($results['success']) {
            $message = "✓ Successfully imported {$results['imported']} products";
            if ($results['skipped'] > 0) {
                $message .= " ({$results['skipped']} skipped)";
            }
            $_SESSION['message'] = $message;
            
            if (!empty($results['errors'])) {
                $_SESSION['import_errors'] = implode("\n", array_slice($results['errors'], 0, 10));
                if (count($results['errors']) > 10) {
                    $_SESSION['import_errors'] .= "\n... and " . (count($results['errors']) - 10) . " more errors";
                }
            }
        } else {
            $_SESSION['error'] = 'Import failed: ' . implode(', ', $results['errors']);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Import error: ' . $e->getMessage();
    }
    
    // Clean up temp file
    unlink($temp_file);
    
    header('Location: dashboard.php');
    exit;
}

echo json_encode(['error' => 'Invalid request']);
