<?php
/**
 * Properties Listing Page (User Frontend)
 * 
 * Features:
 * - Browse all available properties
 * - Advanced filtering and search
 * - Category filtering
 * - Price range filtering
 * - Sorting options
 * - Pagination
 * - Grid/List view toggle
 * - Responsive design
 */

require_once 'config/database.php';
require_once 'classes/Property.php';
require_once 'classes/Category.php';

// Initialize classes
$property = new Property();
$category = new Category();

// Get all categories for filter
$categories = $category->getAll();

// Get filter parameters from URL
$filters = [];
$searchTerm = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? intval($_GET['category']) : '';
$typeFilter = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$priceMin = isset($_GET['price_min']) ? floatval($_GET['price_min']) : '';
$priceMax = isset($_GET['price_max']) ? floatval($_GET['price_max']) : '';
$bedrooms = isset($_GET['bedrooms']) ? intval($_GET['bedrooms']) : '';
$sortBy = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'created_at';
$sortDir = isset($_GET['dir']) ? sanitize($_GET['dir']) : 'DESC';

// Build filters array
if ($searchTerm) $filters['search'] = $searchTerm;
if ($categoryFilter) $filters['category'] = $categoryFilter;
if ($typeFilter) $filters['type'] = $typeFilter;
if ($priceMin) $filters['price_min'] = $priceMin;
if ($priceMax) $filters['price_max'] = $priceMax;
if ($bedrooms) $filters['bedrooms'] = $bedrooms;

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Get properties
$properties = $property->getAll($filters, $perPage, $offset, $sortBy, $sortDir);

// Get total count
$totalProperties = $property->count($filters);
$totalPages = ceil($totalProperties / $perPage);

// View mode (grid or list)
$viewMode = isset($_GET['view']) && $_GET['view'] === 'list' ? 'list' : 'grid';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Properties - LIFETIME Real Estate</title>
    <meta name="description" content="Browse our extensive collection of properties for sale and rent. Find your dream home today!">
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/media.css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,regular,500,600,700,800,900" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Properties Page Specific Styles */
        .properties-page {
            width: 100%;
            margin-top: 12vh;
            padding: 2rem 0;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .container {
            width: 85%;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            background: linear-gradient(135deg, #ff0000, #000000);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }
        
        .page-header p {
            color: #666;
            font-size: 1.1rem;
        }
        
        /* Filters Section */
        .filters-wrapper {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        
        .filters-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .filters-header h3 {
            margin: 0;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .toggle-filters {
            background: #f8f9fa;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            display: none;
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }
        
        .filter-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #ff0000;
            box-shadow: 0 0 0 3px rgba(255,0,0,0.1);
        }
        
        .filter-actions {
            display: flex;
            gap: 1rem;
            grid-column: 1 / -1;
            margin-top: 1rem;
        }
        
        .btn-filter {
            padding: 0.8rem 2rem;
            background: linear-gradient(135deg, #ff0000, #cc0000);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,0,0,0.3);
        }
        
        .btn-clear {
            padding: 0.8rem 2rem;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-clear:hover {
            background: #5a6268;
        }
        
        /* Results Header */
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .results-info {
            color: #666;
            font-size: 1.1rem;
        }
        
        .results-info strong {
            color: #ff0000;
            font-size: 1.3rem;
        }
        
        .results-controls {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .view-toggle {
            display: flex;
            gap: 0.5rem;
        }
        
        .view-btn {
            padding: 0.6rem 1rem;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #666;
        }
        
        .view-btn.active {
            background: #ff0000;
            color: white;
            border-color: #ff0000;
        }
        
        .sort-select {
            padding: 0.6rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        
        /* Properties Grid */
        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .properties-grid.list-view {
            grid-template-columns: 1fr;
        }
        
        .property-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        .properties-grid.list-view .property-card {
            flex-direction: row;
            height: 250px;
        }
        
        .property-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .property-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            position: relative;
        }
        
        .properties-grid.list-view .property-image {
            width: 350px;
            height: 100%;
        }
        
        .property-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .property-badges {
            position: absolute;
            top: 15px;
            left: 15px;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .badge {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
            background: rgba(255,255,255,0.95);
        }
        
        .badge.sale {
            background: rgba(40, 167, 69, 0.95);
            color: white;
        }
        
        .badge.rent {
            background: rgba(0, 123, 255, 0.95);
            color: white;
        }
        
        .badge.featured {
            background: rgba(255, 193, 7, 0.95);
            color: #333;
        }
        
        .property-content {
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .property-category {
            color: #ff0000;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .property-title {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 0.8rem;
            font-weight: 700;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .property-location {
            color: #666;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .property-features {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .feature {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            color: #666;
            font-size: 0.9rem;
            background: #f8f9fa;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
        }
        
        .feature i {
            color: #ff0000;
        }
        
        .property-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 1rem;
            border-top: 2px solid #f0f0f0;
        }
        
        .property-price {
            font-size: 1.8rem;
            font-weight: 800;
            color: #ff0000;
        }
        
        .property-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-view {
            padding: 0.6rem 1.5rem;
            background: linear-gradient(135deg, #ff0000, #cc0000);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,0,0,0.3);
        }
        
        .btn-favorite {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 8px;
            color: #ff0000;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-favorite:hover {
            background: #ff0000;
            color: white;
            transform: scale(1.1);
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin: 3rem 0;
        }
        
        .pagination a,
        .pagination span {
            padding: 0.8rem 1.2rem;
            border-radius: 10px;
            text-decoration: none;
            color: #333;
            background: white;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
            font-weight: 600;
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
        }
        
        .pagination .disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .empty-state i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 2rem;
        }
        
        .empty-state h3 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .empty-state p {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                width: 90%;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .properties-grid {
                grid-template-columns: 1fr;
            }
            
            .properties-grid.list-view .property-card {
                flex-direction: column;
                height: auto;
            }
            
            .properties-grid.list-view .property-image {
                width: 100%;
                height: 250px;
            }
            
            .results-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .toggle-filters {
                display: block;
            }
            
            .filters-content {
                display: none;
            }
            
            .filters-content.active {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav>
            <div class="logo">LIFETIME</div>
            <ul id="navMenu">
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php#about">About</a></li>
                <li><a href="properties.php">Properties</a></li>
                <li><a href="index.php#categories">Categories</a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="admin/dashboard.php">Admin Panel</a></li>
                <?php endif; ?>
            </ul>
            <div class="buttons">
                <?php if (isLoggedIn()): ?>
                    <span style="color: white; margin-right: 1rem;">Welcome, <?php echo $_SESSION['username']; ?></span>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
            <div class="menutoggler" id="menuToggle">
                <i class="fa fa-bars"></i>
            </div>
        </nav>
    </header>

    <div class="properties-page">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1><i class="fas fa-home"></i> Browse Properties</h1>
                <p>Find your perfect property from our extensive collection</p>
            </div>

            <!-- Filters Section -->
            <div class="filters-wrapper">
                <div class="filters-header">
                    <h3><i class="fas fa-filter"></i> Filter Properties</h3>
                    <button class="toggle-filters" onclick="toggleFilters()">
                        <i class="fas fa-sliders-h"></i> Toggle Filters
                    </button>
                </div>
                
                <form method="GET" action="" class="filters-content active">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label><i class="fas fa-search"></i> Search</label>
                            <input 
                                type="text" 
                                name="search" 
                                placeholder="Search properties..."
                                value="<?php echo htmlspecialchars($searchTerm); ?>"
                            >
                        </div>

                        <div class="filter-group">
                            <label><i class="fas fa-tag"></i> Category</label>
                            <select name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo $categoryFilter == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label><i class="fas fa-key"></i> Property Type</label>
                            <select name="type">
                                <option value="">Sale & Rent</option>
                                <option value="sale" <?php echo $typeFilter === 'sale' ? 'selected' : ''; ?>>For Sale</option>
                                <option value="rent" <?php echo $typeFilter === 'rent' ? 'selected' : ''; ?>>For Rent</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label><i class="fas fa-dollar-sign"></i> Min Price</label>
                            <input 
                                type="number" 
                                name="price_min" 
                                placeholder="Min Price"
                                value="<?php echo $priceMin; ?>"
                            >
                        </div>

                        <div class="filter-group">
                            <label><i class="fas fa-dollar-sign"></i> Max Price</label>
                            <input 
                                type="number" 
                                name="price_max" 
                                placeholder="Max Price"
                                value="<?php echo $priceMax; ?>"
                            >
                        </div>

                        <div class="filter-group">
                            <label><i class="fas fa-bed"></i> Min Bedrooms</label>
                            <select name="bedrooms">
                                <option value="">Any</option>
                                <option value="1" <?php echo $bedrooms == 1 ? 'selected' : ''; ?>>1+</option>
                                <option value="2" <?php echo $bedrooms == 2 ? 'selected' : ''; ?>>2+</option>
                                <option value="3" <?php echo $bedrooms == 3 ? 'selected' : ''; ?>>3+</option>
                                <option value="4" <?php echo $bedrooms == 4 ? 'selected' : ''; ?>>4+</option>
                                <option value="5" <?php echo $bedrooms == 5 ? 'selected' : ''; ?>>5+</option>
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn-filter">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                            <?php if ($searchTerm || $categoryFilter || $typeFilter || $priceMin || $priceMax || $bedrooms): ?>
                                <a href="properties.php" class="btn-clear">
                                    <i class="fas fa-times"></i> Clear All
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Results Header -->
            <div class="results-header">
                <div class="results-info">
                    Showing <strong><?php echo count($properties); ?></strong> of 
                    <strong><?php echo $totalProperties; ?></strong> properties
                </div>
                
                <div class="results-controls">
                    <div class="view-toggle">
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['view' => 'grid'])); ?>" 
                           class="view-btn <?php echo $viewMode === 'grid' ? 'active' : ''; ?>">
                            <i class="fas fa-th"></i>
                        </a>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['view' => 'list'])); ?>" 
                           class="view-btn <?php echo $viewMode === 'list' ? 'active' : ''; ?>">
                            <i class="fas fa-list"></i>
                        </a>
                    </div>
                    
                    <select class="sort-select" onchange="window.location.href='?<?php echo http_build_query(array_merge($_GET, ['sort' => ''])); ?>'+this.value">
                        <option value="created_at&dir=DESC" <?php echo $sortBy === 'created_at' && $sortDir === 'DESC' ? 'selected' : ''; ?>>
                            Newest First
                        </option>
                        <option value="price&dir=ASC" <?php echo $sortBy === 'price' && $sortDir === 'ASC' ? 'selected' : ''; ?>>
                            Price: Low to High
                        </option>
                        <option value="price&dir=DESC" <?php echo $sortBy === 'price' && $sortDir === 'DESC' ? 'selected' : ''; ?>>
                            Price: High to Low
                        </option>
                        <option value="title&dir=ASC" <?php echo $sortBy === 'title' && $sortDir === 'ASC' ? 'selected' : ''; ?>>
                            Title: A-Z
                        </option>
                    </select>
                </div>
            </div>

            <!-- Properties Grid/List -->
            <?php if (!empty($properties)): ?>
                <div class="properties-grid <?php echo $viewMode; ?>-view">
                    <?php foreach ($properties as $prop): ?>
                        <div class="property-card">
                            <div class="property-image">
                                <img 
                                    src="assets/uploads/properties/<?php echo $prop['image'] ?: 'default.jpg'; ?>" 
                                    alt="<?php echo htmlspecialchars($prop['title']); ?>"
                                    onerror="this.src='img/default-property.jpg'"
                                >
                                <div class="property-badges">
                                    <span class="badge <?php echo $prop['property_type']; ?>">
                                        For <?php echo ucfirst($prop['property_type']); ?>
                                    </span>
                                    <?php if ($prop['featured']): ?>
                                        <span class="badge featured">
                                            <i class="fas fa-star"></i> Featured
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="property-content">
                                <div class="property-category">
                                    <i class="fas fa-tag"></i>
                                    <?php echo htmlspecialchars($prop['category_name']); ?>
                                </div>
                                
                                <h3 class="property-title">
                                    <?php echo htmlspecialchars($prop['title']); ?>
                                </h3>
                                
                                <div class="property-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($prop['location']); ?>
                                </div>
                                
                                <div class="property-features">
                                    <?php if ($prop['bedrooms']): ?>
                                        <span class="feature">
                                            <i class="fas fa-bed"></i>
                                            <?php echo $prop['bedrooms']; ?> Beds
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($prop['bathrooms']): ?>
                                        <span class="feature">
                                            <i class="fas fa-bath"></i>
                                            <?php echo $prop['bathrooms']; ?> Baths
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($prop['area']): ?>
                                        <span class="feature">
                                            <i class="fas fa-ruler-combined"></i>
                                            <?php echo $prop['area']; ?> m²
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="property-footer">
                                    <div class="property-price">
                                        $<?php echo number_format($prop['price'], 2); ?>
                                    </div>
                                    
                                    <div class="property-actions">
                                        <a href="property-detail.php?id=<?php echo $prop['id']; ?>" class="btn-view">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <?php if (isLoggedIn()): ?>
                                            <a href="add-favorite.php?id=<?php echo $prop['id']; ?>" class="btn-favorite">
                                                <i class="fas fa-heart"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <i class="fas fa-home"></i>
                    <h3>No Properties Found</h3>
                    <p>We couldn't find any properties matching your criteria. Try adjusting your filters.</p>
                    <a href="properties.php" class="btn-filter">
                        <i class="fas fa-sync"></i> Reset Filters
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-col">
                <h3>LIFETIME Real Estate</h3>
                <p>Your trusted partner in finding the perfect property.</p>
            </div>
            <div class="footer-col">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="properties.php">Properties</a></li>
                    <li><a href="index.php#about">About</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Contact</h3>
                <ul>
                    <li><i class="fas fa-phone"></i> +234 810 127 4164</li>
                    <li><i class="fas fa-envelope"></i> info@lifetime.com</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> LIFETIME Real Estate. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        function toggleFilters() {
            const filtersContent = document.querySelector('.filters-content');
            filtersContent.classList.toggle('active');
        }
    </script>
</body>
</html>