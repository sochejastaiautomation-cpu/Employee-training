<?php

class ExcelHandler {
    
    /**
     * Generate a sample Excel file with column headers
     */
    public static function generateSampleExcel() {
        $filename = "product_sample_" . date('Y-m-d') . ".csv";
        
        // Set headers for file download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Create file pointer connected to output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel to recognize UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Write header row
        $headers = [
            'Product Name',
            'Category',
            'Brand',
            'Material',
            'Price',
            'Available Colors',
            'Features',
            'Photo Links',
            'Video Links',
            'Delivery & Payment Info'
        ];
        fputcsv($output, $headers);
        
        // Write sample data row
        $sample = [
            'Sample Product',
            'Electronics',
            'Brand Name',
            'Plastic',
            '2999.99',
            'Red, Blue, Black',
            'Feature 1
Feature 2
Feature 3',
            'https://example.com/photo1.jpg
https://example.com/photo2.jpg',
            'https://youtube.com/watch?v=xxx
https://vimeo.com/123456',
            'Free delivery in Kathmandu Valley
Cash on Delivery Available'
        ];
        fputcsv($output, $sample);
        
        fclose($output);
        exit;
    }
    
    /**
     * Parse uploaded CSV file and insert products
     */
    public static function importProductsFromCSV($file_path, $product_obj) {
        $results = [
            'success' => true,
            'imported' => 0,
            'skipped' => 0,
            'errors' => []
        ];
        
        if (!file_exists($file_path)) {
            $results['success'] = false;
            $results['errors'][] = "File not found";
            return $results;
        }
        
        $row_num = 0;
        $handle = fopen($file_path, 'r');
        
        // Skip BOM if present
        $first_char = fgetc($handle);
        if ($first_char !== false) {
            if (ord($first_char) !== 0xEF) {
                rewind($handle);
            }
        }
        
        while (($row = fgetcsv($handle, 0, ",")) !== false) {
            $row_num++;
            
            // Skip header row
            if ($row_num === 1) {
                continue;
            }
            
            // Skip empty rows
            if (empty($row[0])) {
                $results['skipped']++;
                continue;
            }
            
            try {
                // Map CSV columns to data array
                $data = [
                    'product_name' => trim($row[0]) ?? '',
                    'product_type' => trim($row[1]) ?? '',
                    'brand' => trim($row[2]) ?? '',
                    'material' => trim($row[3]) ?? '',
                    'price' => floatval($row[4]) ?? 0,
                    'general_info' => trim($row[9]) ?? '',
                    'variants' => json_encode(['colors' => self::parseArray($row[5] ?? '')]),
                    'features' => json_encode(self::parseArray($row[6] ?? '')),
                    'faqs' => json_encode([]),
                    'photo_link' => json_encode(self::parseArray($row[7] ?? '')),
                    'video_link' => json_encode(self::parseArray($row[8] ?? ''))
                ];
                
                // Validate required fields
                if (empty($data['product_name'])) {
                    $results['errors'][] = "Row $row_num: Product name is required";
                    $results['skipped']++;
                    continue;
                }
                
                if ($data['price'] <= 0) {
                    $results['errors'][] = "Row $row_num: Price must be greater than 0";
                    $results['skipped']++;
                    continue;
                }
                
                // Insert product
                $result = $product_obj->create($data);
                if ($result['success']) {
                    $results['imported']++;
                } else {
                    $results['errors'][] = "Row $row_num: " . $result['message'];
                    $results['skipped']++;
                }
            } catch (Exception $e) {
                $results['errors'][] = "Row $row_num: " . $e->getMessage();
                $results['skipped']++;
            }
        }
        
        fclose($handle);
        return $results;
    }
    
    /**
     * Parse comma or newline separated values into array
     */
    private static function parseArray($value) {
        if (empty($value)) {
            return [];
        }
        
        // Try splitting by newline first
        $items = preg_split('/[\r\n]+/', $value);
        
        // If only one item, try splitting by comma
        if (count($items) === 1) {
            $items = array_map('trim', explode(',', $value));
        } else {
            $items = array_map('trim', $items);
        }
        
        // Remove empty items
        $items = array_filter($items, function($item) {
            return !empty($item);
        });
        
        return array_values($items);
    }
}
?>
