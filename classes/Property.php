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

    // Get all properties with advanced filtering
    public function getAll($filters = [], $limit = 10, $offset = 0, $orderBy = 'created_at', $orderDir = 'DESC'){
        try {
            // Base query with join to get related data
            $query = "SELECT p.*, 
            c.name as category_name, 
            c.icon as category_icon,
            u.full_name as agent_name, 
            u.phone as agent_phone,
            u.email as agent_email
            FROM {$this->table} p 
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN users u ON p.created_by = u.id 
            WHERE 1 = 1;
            ";

            $params = [];

            // category filter
            if (!empty($filters['category'])) {
                $query .= " AND p.category_id = :category";
                $params[':category'] = $filters['category'];

            }

            // property type filter (sale/rent)
            if (!empty($filters['type'])) {
                $query .= " AND p.property_type = :type";
                $params[':type'] = $filters['type'];

            }

            // status filter
            if (isset($filters['status'])) {
                $query .= " AND p.status = :status";
                $params[':status'] = $filters['status'];
            }else{
                // By default only show available properties
                $query .= " AND p.status = 'available";
            }

            // Featured filter
            if (isset($filters['featured'])) {
                $query .= " AND p.featured = :featured";
                $params[':featured'] = $filters['featured'];
            }
            
            // Price range filters
            if (!empty($filters['price_min'])) {
                $query .= " AND p.price >= :price_min";
                $params[':price_min'] = $filters['price_min'];
            }
            
            if (!empty($filters['price_max'])) {
                $query .= " AND p.price <= :price_max";
                $params[':price_max'] = $filters['price_max'];
            }
            
            // Bedrooms filter
            if (!empty($filters['bedrooms'])) {
                $query .= " AND p.bedrooms >= :bedrooms";
                $params[':bedrooms'] = $filters['bedrooms'];
            }
            
            // Bathrooms filter
            if (!empty($filters['bathrooms'])) {
                $query .= " AND p.bathrooms >= :bathrooms";
                $params[':bathrooms'] = $filters['bathrooms'];
            }
            
            // Area range filters
            if (!empty($filters['area_min'])) {
                $query .= " AND p.area >= :area_min";
                $params[':area_min'] = $filters['area_min'];
            }
            
            if (!empty($filters['area_max'])) {
                $query .= " AND p.area <= :area_max";
                $params[':area_max'] = $filters['area_max'];
            }

            // search filter - searches in title, description, location, and address
            if (!empty($filters['search'])) {
                $query .= " AND (p.title LIKE :search 
                OR p.description LIKE :search 
                OR p.location LIKE :search 
                OR p.address LIKE :search)";

                $params[':search'] = '%'. $filters['search'] . '%';
            }

             // Validate and sanitize ORDER BY to prevent SQL injection
            $allowedOrderFields = ['created_at', 'price', 'title', 'bedrooms', 'bathrooms', 'area'];
            if (!in_array($orderBy, $allowedOrderFields)) {
                $orderBy = 'created_at';
            }
            
            // Validate order direction
            $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';
            
            // Add ordering
            $query .= " ORDER BY p.{$orderBy} {$orderDir}";
            
            // Add pagination
            $query .= " LIMIT :limit OFFSET :offset";
            
            // Prepare statement
            $stmt = $this->conn->prepare($query);

            // bind filter parameters
            foreach ($params as $key => $value) {
                $stmt-> bindValue($key, $value);
            }

            // Bind pagination parameters (must be integers)
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $stmt->fetchAll();

        } catch (PDOException $th) {
            error_log("Get property error: " . $th->getMessage());
            return [];
        }
    }

       /**
     * Get Property by ID with Full Details
     * 
     * Retrieves a single property with all related information
     * 
     * @param int $id Property ID
     * @return array|false Property data or false if not found
     */
    public function getById($id) {
        try {
            $query = "SELECT p.*, 
                      c.name as category_name, 
                      c.icon as category_icon,
                      c.description as category_description,
                      u.full_name as agent_name, 
                      u.phone as agent_phone,
                      u.email as agent_email,
                      u.id as agent_id
                      FROM {$this->table} p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      LEFT JOIN users u ON p.created_by = u.id 
                      WHERE p.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $property = $stmt->fetch();
            
            // If property found, add additional calculated fields
            if ($property) {
                $property['price_per_sqm'] = $property['area'] > 0 ? 
                    round($property['price'] / $property['area'], 2) : 0;
                $property['is_featured'] = (bool)$property['featured'];
                $property['formatted_price'] = '$' . number_format($property['price'], 2);
            }
            
            return $property;
            
        } catch (PDOException $e) {
            error_log("Get property error: " . $e->getMessage());
            return false;
        }
    }

    // update property
     public function update($id, $data) {
        try {
            // Build dynamic update query
            $query = "UPDATE {$this->table} SET 
                      title = :title,
                      description = :description,
                      price = :price,
                      category_id = :category_id,
                      property_type = :property_type,
                      bedrooms = :bedrooms,
                      bathrooms = :bathrooms,
                      area = :area,
                      location = :location,
                      address = :address,
                      featured = :featured,
                      status = :status";
            
            // Only update image if new one is provided
            if (!empty($data['image'])) {
                $query .= ", image = :image";
            }
            
            $query .= " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind parameters
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':category_id', $data['category_id'], PDO::PARAM_INT);
            $stmt->bindParam(':property_type', $data['property_type']);
            $stmt->bindParam(':bedrooms', $data['bedrooms'], PDO::PARAM_INT);
            $stmt->bindParam(':bathrooms', $data['bathrooms'], PDO::PARAM_INT);
            $stmt->bindParam(':area', $data['area']);
            $stmt->bindParam(':location', $data['location']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':featured', $data['featured'], PDO::PARAM_INT);
            $stmt->bindParam(':status', $data['status']);
            
            if (!empty($data['image'])) {
                $stmt->bindParam(':image', $data['image']);
            }
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Update property error: " . $e->getMessage());
            return false;
        }
    }
}
