<?php
/**
 * Admin Properties List Page
 * 
 * Comprehensive property management interface with:
 * - Advanced filtering and search
 * - Bulk actions
 * - Status management
 * - Quick edit features
 * - Pagination
 * - Statistics
 */

require_once '../../config/database.php';
require_once '../../classes/Property.php';
require_once '../../classes/Category.php';

// Check authentication
// if (!isLoggedIn() || !isAdmin()) {
//     redirect('../../login.php');
// }

// Initialize classes
$property = new Property();
$category = new Category();

// Get all categories for filter
$categories = $category->getAll();

// Handle bulk actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $action = $_POST['bulk_action'];
    $selectedIds = isset($_POST['selected_properties']) ? $_POST['selected_properties'] : [];
    
    if (!empty($selectedIds)) {
        switch ($action) {
            case 'delete':
                $deleted = $property->bulkDelete($selectedIds);
                $message = "{$deleted} properties deleted successfully";
                $messageType = 'success';
                break;
                
            case 'mark_sold':
                $updated = $property->bulkUpdateStatus($selectedIds, 'sold');
                $message = "{$updated} properties marked as sold";
                $messageType = 'success';
                break;
                
            case 'mark_available':
                $updated = $property->bulkUpdateStatus($selectedIds, 'available');
                $message = "{$updated} properties marked as available";
                $messageType = 'success';
                break;
                
            case 'mark_rented':
                $updated = $property->bulkUpdateStatus($selectedIds, 'rented');
                $message = "{$updated} properties marked as rented";
                $messageType = 'success';
                break;
        }
    } else {
        $message = 'Please select at least one property';
        $messageType = 'error';
    }
}

// Handle quick status toggle
if (isset($_GET['toggle_featured'])) {
    $id = intval($_GET['toggle_featured']);
    if ($property->toggleFeatured($id)) {
        $message = 'Featured status updated';
        $messageType = 'success';
    }
}

// Get filter parameters
$filters = [];
$searchTerm = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? intval($_GET['category']) : '';
$typeFilter = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

if ($searchTerm) $filters['search'] = $searchTerm;
if ($categoryFilter) $filters['category'] = $categoryFilter;
if ($typeFilter) $filters['type'] = $typeFilter;
if ($statusFilter) {
    $filters['status'] = $statusFilter;
} else {
    unset($filters['status']); // Show all statuses by default in admin
}

// Pagination setup
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Get properties with filters
$properties = $property->getAll($filters, $perPage, $offset, 'created_at', 'DESC');

// Get total count for pagination
$totalProperties = $property->count($filters);
$totalPages = ceil($totalProperties / $perPage);

// Get statistics
$stats = $property->getStatistics();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Properties - Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f5f5f5;
        }
        
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
            margin-top: 10vh;
        }
        
        /* Sidebar - Reuse from dashboard */
        .admin-sidebar {
            width: 250px;
            background: #1a1a1a;
            color: white;
            position: fixed;
            height: calc(100vh - 10vh);
            overflow-y: auto;
            padding: 2rem 0;
        }
        
        .admin-sidebar h2 {
            padding: 0 1.5rem;
            margin-bottom: 2rem;
            color: #ff0000;
        }
        
        .admin-sidebar ul {
            list-style: none;
            padding: 0;
            display: flex;
            flex-direction: column;
        }
        
        .admin-sidebar ul li a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }
        
        .admin-sidebar ul li a:hover,
        .admin-sidebar ul li a.active {
            background: #ff0000;
            color: white;
        }
        
        /* Main Content */
        .admin-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
        }
        
        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .page-header h1 {
            color: #333;
            margin: 0;
        }
        
        .header-actions {
            display: flex;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #ff0000, #cc0000);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 0, 0, 0.3);
        }
        
        /* Statistics Mini Cards */
        .mini-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .mini-stat {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .mini-stat h3 {
            font-size: 2rem;
            color: #ff0000;
            margin: 0;
        }
        
        .mini-stat p {
            margin: 0.5rem 0 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Filters Section */
        .filters-section {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .filters-section h3 {
            margin: 0 0 1rem 0;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .filters-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-group label {
            font-weight: 600;
            color: #666;
            margin-bottom: 0.3rem;
            font-size: 0.9rem;
        }
        
        .filter-group input,
        .filter-group select {
            padding: 0.6rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #ff0000;
        }
        
        /* Bulk Actions */
        .bulk-actions {
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .bulk-actions select {
            padding: 0.6rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .btn-bulk {
            background: #333;
            color: white;
            padding: 0.6rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .btn-bulk:hover {
            background: #555;
        }
        
        /* Properties Table */
        .properties-table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table thead {
            background: #f8f9fa;
        }
        
        table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
            white-space: nowrap;
        }
        
        table td {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            color: #666;
        }
        
        table tr:hover {
            background: #f9f9f9;
        }
        
        .property-thumbnail {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .property-title {
            font-weight: 600;
            color: #333;
            max-width: 250px;
        }
        
        .property-location {
            font-size: 0.85rem;
            color: #999;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            margin-top: 0.3rem;
        }
        
        .badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .badge-sale {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .badge-rent {
            background: rgba(0, 123, 255, 0.1);
            color: #007bff;
        }
        
        .badge-available {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .badge-sold {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .badge-rented {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .featured-star {
            color: #ffc107;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .featured-star:hover {
            transform: scale(1.2);
        }
        
        .featured-star.inactive {
            color: #ddd;
        }
        
        .price-cell {
            font-weight: 700;
            color: #ff0000;
            white-space: nowrap;
        }
        
        .actions-cell {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-icon {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-view {
            background: rgba(0, 123, 255, 0.1);
            color: #007bff;
        }
        
        .btn-view:hover {
            background: #007bff;
            color: white;
        }
        
        .btn-edit {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .btn-edit:hover {
            background: #ffc107;
            color: #333;
        }
        
        .btn-delete {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .btn-delete:hover {
            background: #dc3545;
            color: white;
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            padding: 2rem 1rem;
        }
        
        .pagination a,
        .pagination span {
            padding: 0.6rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            background: white;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        .pagination a:hover {
            background: #ff0000;
            color: white;
            border-color: #ff0000;
        }
        
        .pagination .current {
            background: #ff0000;
            color: white;
            border-color: #ff0000;
            font-weight: 600;
        }
        
        .pagination .disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Alert Messages */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #999;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
        
        .empty-state h3 {
            color: #666;
            margin-bottom: 1rem;
        }
        
        /* Checkbox styling */
        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        #select-all {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav>
            <div class="logo">LIFETIME ADMIN</div>
            <ul>
                <li><a href="../../index.php" target="_blank">View Site</a></li>
            </ul>
            <div class="buttons">
                <span style="color: white; margin-right: 1rem;">
                    <?php echo $_SESSION['full_name']; ?>
                </span>
                <a href="../../logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <h2><i class="fas fa-cog"></i> Admin Panel</h2>
            <ul>
                <li>
                    <a href="../dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="list.php" class="active">
                        <i class="fas fa-home"></i> Properties
                    </a>
                </li>
                <li>
                    <a href="add.php">
                        <i class="fas fa-plus-circle"></i> Add Property
                    </a>
                </li>
                <li>
                    <a href="../categories/manage.php">
                        <i class="fas fa-tags"></i> Categories
                    </a>
                </li>
                <li>
                    <a href="../users/manage.php">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1><i class="fas fa-home"></i> Manage Properties</h1>
                    <p style="margin: 0.5rem 0 0; color: #666;">
                        Total: <?php echo $totalProperties; ?> properties
                    </p>
                </div>
                <div class="header-actions">
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Property
                    </a>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Mini Statistics -->
            <div class="mini-stats">
                <div class="mini-stat">
                    <h3><?php echo $stats['available']; ?></h3>
                    <p>Available</p>
                </div>
                <div class="mini-stat">
                    <h3><?php echo $stats['sold']; ?></h3>
                    <p>Sold</p>
                </div>
                <div class="mini-stat">
                    <h3><?php echo $stats['rented']; ?></h3>
                    <p>Rented</p>
                </div>
                <div class="mini-stat">
                    <h3><?php echo $stats['featured']; ?></h3>
                    <p>Featured</p>
                </div>  
               
            </div>

            <!-- Filters Section -->
            <div class="filters-section">
                <h3><i class="fas fa-filter"></i> Filters</h3>
                <form method="GET" action="" class="filters-form">
                    <div class="filter-group">
                        <label>Search</label>
                        <input type="text" name="search" placeholder="Title, location..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label>Category</label>
                        <select name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $categoryFilter == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Type</label>
                        <select name="type">
                            <option value="">All Types</option>
                            <option value="sale" <?php echo $typeFilter === 'sale' ? 'selected' : ''; ?>>For Sale</option>
                            <option value="rent" <?php echo $typeFilter === 'rent' ? 'selected' : ''; ?>>For Rent</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="">All Statuses</option>
                            <option value="available" <?php echo $statusFilter === 'available' ? 'selected' : ''; ?>>Available</option>
                            <option value="sold" <?php echo $statusFilter === 'sold' ? 'selected' : ''; ?>>Sold</option>
                            <option value="rented" <?php echo $statusFilter === 'rented' ? 'selected' : ''; ?>>Rented</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                    
                    <?php if ($searchTerm || $categoryFilter || $typeFilter || $statusFilter): ?>
                        <div class="filter-group">
                            <a href="list.php" class="btn" style="background: #6c757d; color: white;">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Bulk Actions -->
            <?php if (!empty($properties)): ?>
            <form method="POST" action="" id="bulkForm">
                <div class="bulk-actions">
                    <label style="font-weight: 600; color: #333;">
                        <input type="checkbox" id="select-all"> Select All
                    </label>
                    <select name="bulk_action" id="bulk_action">
                        <option value="">-- Bulk Actions --</option>
                        <option value="mark_available">Mark as Available</option>
                        <option value="mark_sold">Mark as Sold</option>
                        <option value="mark_rented">Mark as Rented</option>
                        <option value="delete">Delete Selected</option>
                    </select>
                    <button type="submit" class="btn-bulk" onclick="return confirmBulkAction()">
                        <i class="fas fa-check"></i> Apply
                    </button>
                    <span style="color: #666; font-size: 0.9rem;">
                        <span id="selected-count">0</span> selected
                    </span>
                </div>

                <!-- Properties Table -->
                <div class="properties-table-container">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th width="40">
                                        <input type="checkbox" id="select-all-header">
                                    </th>
                                    <th>Image</th>
                                    <th>Property</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Featured</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($properties as $prop): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="selected_properties[]" value="<?php echo $prop['id']; ?>" class="property-checkbox">
                                        </td>
                                        <td>
                                            <img 
                                                src="../../assets/uploads/properties/<?php echo $prop['image'] ?: 'default.jpg'; ?>" 
                                                alt="Property" 
                                                class="property-thumbnail"
                                                onerror="this.src='../../img/default-property.jpg'"
                                            >
                                        </td>
                                        <td>
                                            <div class="property-title">
                                                <?php echo htmlspecialchars($prop['title']); ?>
                                            </div>
                                            <div class="property-location">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?php echo htmlspecialchars($prop['location']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($prop['category_name']); ?></td>
                                        <td class="price-cell">
                                            $<?php echo number_format($prop['price'], 2); ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $prop['property_type']; ?>">
                                                <?php echo ucfirst($prop['property_type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $prop['status']; ?>">
                                                <?php echo ucfirst($prop['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?toggle_featured=<?php echo $prop['id']; ?>&<?php echo http_build_query($_GET); ?>" 
                                               title="Toggle Featured">
                                                <i class="fas fa-star featured-star <?php echo $prop['featured'] ? '' : 'inactive'; ?>"></i>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="actions-cell">
                                                <a href="../../property-detail.php?id=<?php echo $prop['id']; ?>" 
                                                   class="btn-icon btn-view" 
                                                   title="View" 
                                                   target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit.php?id=<?php echo $prop['id']; ?>" 
                                                   class="btn-icon btn-edit" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete.php?id=<?php echo $prop['id']; ?>" 
                                                   class="btn-icon btn-delete" 
                                                   title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this property?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&<?php echo http_build_query(array_diff_key($_GET, ['page' => ''])); ?>">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php else: ?>
                                <span class="disabled">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </span>
                            <?php endif; ?>

                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            
                            for ($i = $startPage; $i <= $endPage; $i++):
                            ?>
                                <?php if ($i == $page): ?>
                                    <span class="current"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_diff_key($_GET, ['page' => ''])); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&<?php echo http_build_query(array_diff_key($_GET, ['page' => ''])); ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="disabled">
                                    Next <i class="fas fa-chevron-right"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
            <?php else: ?>
                <!-- Empty State -->
                <div class="properties-table-container">
                    <div class="empty-state">
                        <i class="fas fa-home"></i>
                        <h3>No Properties Found</h3>
                        <p>
                            <?php if ($searchTerm || $categoryFilter || $typeFilter || $statusFilter): ?>
                                No properties match your filters. Try adjusting your search criteria.
                            <?php else: ?>
                                Get started by adding your first property.
                            <?php endif; ?>
                        </p>
                        <a href="add.php" class="btn btn-primary" style="margin-top: 1rem;">
                            <i class="fas fa-plus"></i> Add Property
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Select all functionality
        const selectAllCheckboxes = document.querySelectorAll('#select-all, #select-all-header');
        const propertyCheckboxes = document.querySelectorAll('.property-checkbox');
        const selectedCount = document.getElementById('selected-count');
        
        // Update select all checkboxes
        selectAllCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                propertyCheckboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
                updateSelectedCount();
                // Sync both select-all checkboxes
                selectAllCheckboxes.forEach(cb => cb.checked = this.checked);
            });
        });
        
        // Update count when individual checkbox changes
        propertyCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedCount);
        });
        
        // Update selected count display
        function updateSelectedCount() {
            const count = document.querySelectorAll('.property-checkbox:checked').length;
            selectedCount.textContent = count;
            
            // Update select-all checkbox state
            const allChecked = count === propertyCheckboxes.length && count > 0;
            selectAllCheckboxes.forEach(cb => cb.checked = allChecked);
        }
        
        // Confirm bulk action
        function confirmBulkAction() {
            const action = document.getElementById('bulk_action').value;
            const count = document.querySelectorAll('.property-checkbox:checked').length;
            
            if (!action) {
                alert('Please select an action');
                return false;
            }
            
            if (count === 0) {
                alert('Please select at least one property');
                return false;
            }
            
            let message = '';
            if (action === 'delete') {
                message = `Are you sure you want to delete ${count} propert${count > 1 ? 'ies' : 'y'}?`;
            } else {
                message = `Apply this action to ${count} propert${count > 1 ? 'ies' : 'y'}?`;
            }
            
            return confirm(message);
        }
        
        // Initialize count
        updateSelectedCount();
    </script>
</body>
</html>