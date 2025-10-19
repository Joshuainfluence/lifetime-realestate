<?php

require_once __DIR__ . '/../config/database.php';


class Property
{
    // database connection 
    private $conn;

    // table name
    private $table = 'properties';

    // allowed image types
    private $allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

    // maximum file size(5MB)
    private $maxFileSize = 5242880;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    private function validatePropertyData($data)
    {
        // check required fields
        $required = ['title', 'price', 'category_id', 'property_type', 'location', 'status'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                error_log("Validation error: Missing required field {$field}");
                return false;
            }
        }

        // validate price to make sure it is a number and positive
        if (!is_numeric($data['price']) || $data['price'] < 0) {
            error_log("Validation error: Invalid Price");
            return false;
        }

        // validate property_type
        if (!in_array($data['property_type'], ['sale', 'rent'])) {
            error_log("Validation error: Invalid property type");
            return false;
        }

        return true;
    }

    // function to create property
    public function create($data)
    {
        try {
            // validate required fields
            if (!$this->validatePropertyData($data)) {
                return false;
            }

            // SQL query with all fields
            $query = "INSERT INTO {$this->table} 
                      (title, description, price, category_id, property_type, 
                       bedrooms, bathrooms, area, location, address, image, 
                       featured, status, created_by, created_at) 
                      VALUES 
                      (:title, :description, :price, :category_id, :property_type, 
                       :bedrooms, :bathrooms, :area, :location, :address, :image, 
                       :featured, :status, :created_by, NOW())";

            $stmt = $this->conn->prepare($query);

            // Bind all parameters with proper types
            $stmt->bindParam(':title', $data['title'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindParam(':price', $data['price'], PDO::PARAM_STR);
            $stmt->bindParam(':category_id', $data['category_id'], PDO::PARAM_INT);
            $stmt->bindParam(':property_type', $data['property_type'], PDO::PARAM_STR);
            $stmt->bindParam(':bedrooms', $data['bedrooms'], PDO::PARAM_INT);
            $stmt->bindParam(':bathrooms', $data['bathrooms'], PDO::PARAM_INT);
            $stmt->bindParam(':area', $data['area'], PDO::PARAM_STR);
            $stmt->bindParam(':location', $data['location'], PDO::PARAM_STR);
            $stmt->bindParam(':address', $data['address'], PDO::PARAM_STR);
            $stmt->bindParam(':image', $data['image'], PDO::PARAM_STR);
            $stmt->bindParam(':featured', $data['featured'], PDO::PARAM_INT);
            $stmt->bindParam(':status', $data['status'], PDO::PARAM_STR);
            $stmt->bindParam(':created_by', $data['created_by'], PDO::PARAM_INT);

            // Execute and return the new property ID
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $th) {
            error_log("property creation Error  : " . $th->getMessage());
            return false;
        }
    }
}
