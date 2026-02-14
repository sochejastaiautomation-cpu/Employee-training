<?php
require_once __DIR__ . '/../config/db.php';

class Product {
    private $conn;
    private $table = 'products';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all products
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get single product
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE product_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Create product
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (product_name, product_type, brand, material, price, delivery, variants, features, faqs) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            return ["success" => false, "message" => "Prepare failed: " . $this->conn->error];
        }
        
        $stmt->bind_param(
            "ssssdssss",
            $data['product_name'],
            $data['product_type'],
            $data['brand'],
            $data['material'],
            $data['price'],
            $data['delivery'],
            $data['variants'],
            $data['features'],
            $data['faqs']
        );
        
        if ($stmt->execute()) {
            return ["success" => true, "id" => $this->conn->insert_id, "message" => "Product created successfully"];
        } else {
            return ["success" => false, "message" => "Error: " . $stmt->error];
        }
    }

    // Update product
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET product_name = ?, product_type = ?, brand = ?, material = ?, 
                      price = ?, delivery = ?, variants = ?, features = ?, faqs = ? 
                  WHERE product_id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        if (!$stmt) {
            return ["success" => false, "message" => "Prepare failed: " . $this->conn->error];
        }
        
        $stmt->bind_param(
            "ssssdssssi",
            $data['product_name'],
            $data['product_type'],
            $data['brand'],
            $data['material'],
            $data['price'],
            $data['delivery'],
            $data['variants'],
            $data['features'],
            $data['faqs'],
            $id
        );
        
        if ($stmt->execute()) {
            return ["success" => true, "message" => "Product updated successfully"];
        } else {
            return ["success" => false, "message" => "Error: " . $stmt->error];
        }
    }

    // Delete product
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE product_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return ["success" => true, "message" => "Product deleted successfully"];
        } else {
            return ["success" => false, "message" => "Error: " . $stmt->error];
        }
    }
}
?>
