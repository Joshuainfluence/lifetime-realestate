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
      /**
     * Get All Properties with Advanced Filtering
     * 
     * Supports multiple filters including search, category, price range, etc.
     * Includes pagination support
     * 
     * @param array $filters Associative array of filter options
     * @param int $limit Number of results to return
     * @param int $offset Starting position for pagination
     * @param string $orderBy Field to order by (default: created_at)
     * @param string $orderDir Order direction (ASC or DESC)
     * @return array Array of property objects
     */
    public function getAll($filters = [], $limit = 10, $offset = 0, $orderBy = 'created_at', $orderDir = 'DESC')
    {
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
            } else {
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

                $params[':search'] = '%' . $filters['search'] . '%';
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
                $stmt->bindValue($key, $value);
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
    public function getById($id)
    {
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
    public function update($id, $data)
    {
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


    // delete property
    public function delete($id)
    {
        try {
            // First get the property to delete its image
            $property = $this->getById($id);

            // Delete from database
            $query = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $stmt->execute();

            // Delete image file if exists
            if ($result && $property && !empty($property['image'])) {
                $this->deletePropertyImage($property['image']);
            }

            return $result;
        } catch (PDOException $e) {
            error_log("Delete property error: " . $e->getMessage());
            return false;
        }
    }

    // get featured properties
    public function getFeatured($limit = 6)
    {
        $filters = ['featured' => 1, 'status' => 'available'];
        return $this->getAll($filters, $limit, 0, 'created_at', 'DESC');
    }

    // get latest properties
    public function getLatest($limit = 10)
    {
        return $this->getAll([], $limit, 0, 'created_at', 'DESC');
    }


    /**
     * Get Similar Properties
     * 
     * Finds properties similar to the given property
     * Based on category and price range
     * 
     * @param int $propertyId Current property ID
     * @param int $limit Number of similar properties to return
     * @return array Array of similar properties
     */
    public function getSimilar($propertyId, $limit = 4)
    {
        try {
            $property = $this->getById($propertyId);
            if (!$property) return [];

            // Find properties in same category with similar price (+/- 30%)
            $priceMin = $property['price'] * 0.7;
            $priceMax = $property['price'] * 1.3;

            $query = "SELECT p.*, c.name as category_name 
                      FROM {$this->table} p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      WHERE p.id != :id 
                      AND p.category_id = :category_id 
                      AND p.price BETWEEN :price_min AND :price_max
                      AND p.status = 'available'
                      ORDER BY ABS(p.price - :target_price)
                      LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $propertyId, PDO::PARAM_INT);
            $stmt->bindParam(':category_id', $property['category_id'], PDO::PARAM_INT);
            $stmt->bindParam(':price_min', $priceMin);
            $stmt->bindParam(':price_max', $priceMax);
            $stmt->bindParam(':target_price', $property['price']);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get similar properties error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Count Total Properties
     * 
     * Returns total number of properties with optional filters
     * Used for pagination calculations
     * 
     * @param array $filters Optional filters
     * @return int Total count
     */
    public function count($filters = []) {
        try {
            $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
            $params = [];
            
            // Apply same filters as getAll()
            if (!empty($filters['category'])) {
                $query .= " AND category_id = :category";
                $params[':category'] = $filters['category'];
            }
            
            if (!empty($filters['type'])) {
                $query .= " AND property_type = :type";
                $params[':type'] = $filters['type'];
            }
            
            if (isset($filters['status'])) {
                $query .= " AND status = :status";
                $params[':status'] = $filters['status'];
            } else {
                $query .= " AND status = 'available'";
            }
            
            if (!empty($filters['search'])) {
                $query .= " AND (title LIKE :search OR description LIKE :search OR location LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $result = $stmt->fetch();
            
            return (int)$result['total'];
            
        } catch (PDOException $e) {
            error_log("Count properties error: " . $e->getMessage());
            return 0;
        }
    }



     /**
     * Get Property Statistics
     * 
     * Returns various statistics about properties
     * 
     * @return array Statistics data
     */
    public function getStatistics() {
        try {
            $stats = [];
            
            // Total properties
            $stats['total'] = $this->count(['status' => null]);
            
            // Available properties
            $stats['available'] = $this->count(['status' => 'available']);
            
            // Sold properties
            $stats['sold'] = $this->count(['status' => 'sold']);
            
            // Rented properties
            $stats['rented'] = $this->count(['status' => 'rented']);
            
            // Featured properties
            $stats['featured'] = $this->count(['featured' => 1]);
            
            // Average price
            $query = "SELECT AVG(price) as avg_price FROM {$this->table}";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch();
            $stats['average_price'] = round($result['avg_price'], 2);
            
            // Price range
            $query = "SELECT MIN(price) as min_price, MAX(price) as max_price FROM {$this->table}";
            $stmt = $this->conn->query($query);
            $result = $stmt->fetch();
            $stats['min_price'] = $result['min_price'];
            $stats['max_price'] = $result['max_price'];
            
            // Properties by type
            $query = "SELECT property_type, COUNT(*) as count FROM {$this->table} GROUP BY property_type";
            $stmt = $this->conn->query($query);
            $stats['by_type'] = $stmt->fetchAll();
            
            // Properties by category
            $query = "SELECT c.name, COUNT(p.id) as count 
                      FROM {$this->table} p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      GROUP BY p.category_id, c.name";
            $stmt = $this->conn->query($query);
            $stats['by_category'] = $stmt->fetchAll();
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("Get statistics error: " . $e->getMessage());
            return [];
        }
    }


     /**
     * Toggle Featured Status
     * 
     * Marks/unmarks a property as featured
     * 
     * @param int $id Property ID
     * @return bool True on success, false on failure
     */
    public function toggleFeatured($id) {
        try {
            $query = "UPDATE {$this->table} SET featured = NOT featured WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Toggle featured error: " . $e->getMessage());
            return false;
        }
    }


     /**
     * Update Property Status
     * 
     * Changes property status (available, sold, rented)
     * 
     * @param int $id Property ID
     * @param string $status New status
     * @return bool True on success, false on failure
     */
    public function updateStatus($id, $status) {
        try {
            $allowedStatuses = ['available', 'sold', 'rented'];
            if (!in_array($status, $allowedStatuses)) {
                return false;
            }
            
            $query = "UPDATE {$this->table} SET status = :status WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':status', $status);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update status error: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Handle Image Upload
     * 
     * Uploads and validates property image
     * 
     * @param array $file $_FILES array element
     * @return string|false Image filename on success, false on failure
     */
    public function uploadImage($file) {
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return false;
        }
        
        // Validate file size
        if ($file['size'] > $this->maxFileSize) {
            error_log("File too large: " . $file['size']);
            return false;
        }
        
        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedImageTypes)) {
            error_log("Invalid file type: " . $mimeType);
            return false;
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('property_', true) . '.' . $extension;
        
        // Upload directory
        $uploadDir = __DIR__ . '/../assets/uploads/properties/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $destination = $uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Optionally resize/optimize image here
            return $filename;
        }
        
        return false;
    }


    /**
     * Delete Property Image
     * 
     * Removes image file from server
     * 
     * @param string $filename Image filename
     * @return bool True on success, false on failure
     */
    public function deletePropertyImage($filename) {
        if (empty($filename)) return false;
        
        $filepath = __DIR__ . '/../assets/uploads/properties/' . $filename;
        
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }


    /**
     * Bulk Delete Properties
     * 
     * Deletes multiple properties at once
     * 
     * @param array $ids Array of property IDs
     * @return int Number of properties deleted
     */
    public function bulkDelete($ids) {
        if (empty($ids) || !is_array($ids)) {
            return 0;
        }
        
        $deleted = 0;
        foreach ($ids as $id) {
            if ($this->delete($id)) {
                $deleted++;
            }
        }
        
        return $deleted;
    }

}
