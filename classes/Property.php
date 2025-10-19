<?php

require_once __DIR__ . '/../config/database.php';


class Property {
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

    private function validatePropertyData($data){
        // check required fields
        $required = ['title', 'price', 'category_id', 'property_type', 'location', 'status'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                error_log("Validation error: Missing required field {$field}" );
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

    
}