<?php

/**
 * Admin Dashboard
 * 
 * Main admin panel showing statistics and quick links
 * Only accessible by users with 'admin' role
 */

require_once '../config/database.php';
// require_once '../classes/Property.php';
require_once '../classes/User.php';
require_once '../classes/Category.php';

// Check if user is logged in and is admin
// if (!isLoggedIn() || !isAdmin()) {
//     redirect('../login.php');
// }

// Initialize classes
// $property = new Property();
$user = new User();
$category = new Category();

// Get statistics
// $totalProperties = $property->count();
$totalUsers = $user->total();
$totalCategories = count($category->getAll());

// Get recent properties
// $recentProperties = $property->getAll([], 5, 0);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LIFETIME Real Estate</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Admin Dashboard Styles */
        body {
            background: #f5f5f5;
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
            margin-top: 10vh;
        }

        /* Sidebar Navigation */
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

        .admin-sidebar ul li {
            margin: 0;
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

        .admin-sidebar ul li a i {
            width: 20px;
        }

        /* Main Content Area */
        .admin-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
        }

        .admin-header {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-header h1 {
            color: #333;
            margin: 0;
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .admin-user span {
            color: #666;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            font-size: 2rem;
        }

        .stat-icon.properties {
            background: rgba(255, 0, 0, 0.1);
            color: #ff0000;
        }

        .stat-icon.users {
            background: rgba(0, 123, 255, 0.1);
            color: #007bff;
        }

        .stat-icon.categories {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .stat-info h3 {
            margin: 0;
            color: #999;
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-info p {
            margin: 0.5rem 0 0;
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
        }

        /* Quick Actions */
        .quick-actions {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .quick-actions h2 {
            margin-bottom: 1.5rem;
            color: #333;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .action-btn {
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #ff0000, #cc0000);
            color: white;
            text-align: center;
            border-radius: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 0, 0, 0.3);
        }

        /* Recent Properties Table */
        .recent-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .recent-section h2 {
            margin-bottom: 1.5rem;
            color: #333;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #f8f9fa;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }

        table td {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            color: #666;
        }

        table tr:hover {
            background: #f9f9f9;
        }

        .property-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        .badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
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

        .table-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-icon {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: rgba(0, 123, 255, 0.1);
            color: #007bff;
        }

        .btn-edit:hover {
            background: #007bff;
            color: white;
        }

        .btn-delete {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .btn-delete:hover {
            background: #dc3545;
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 200px;
            }

            .admin-content {
                margin-left: 200px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header>
        <nav>
            <div class="logo">LIFETIME ADMIN</div>
            <ul>
                <li><a href="../index.php" target="_blank">View Site</a></li>
            </ul>
            <div class="buttons">
                <span style="color: white; margin-right: 1rem;">
                    <?php echo $_SESSION['full_name']; ?>
                </span>
                <a href="../logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <div class="admin-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar">
            <h2><i class="fas fa-cog"></i> Admin Panel</h2>
            <ul>
                <li>
                    <a href="dashboard.php" class="active">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="properties/list.php">
                        <i class="fas fa-home"></i> Properties
                    </a>
                </li>
                <li>
                    <a href="properties/add.php">
                        <i class="fas fa-plus-circle"></i> Add Property
                    </a>
                </li>
                <li>
                    <a href="categories/manage.php">
                        <i class="fas fa-tags"></i> Categories
                    </a>
                </li>
                <li>
                    <a href="users/manage.php">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                <li>
                    <a href="../index.php" target="_blank">
                        <i class="fas fa-globe"></i> View Website
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <!-- Header -->
            <div class="admin-header">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <div class="admin-user">
                    <span>Welcome back, <strong><?php //echo $_SESSION['full_name']; ?></strong></span>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <!-- Total Properties -->
                <div class="stat-card">
                    <div class="stat-icon properties">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Properties</h3>
                        <p>
                            <?php //echo $totalProperties; ?>
                    </p>
                    </div>
                </div>

                <!-- Total Users -->
                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Users</h3>
                        <p><?php echo $totalUsers; ?></p>
                    </div>
                </div>

                <!-- Total Categories -->
                <div class="stat-card">
                    <div class="stat-icon categories">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Categories</h3>
                        <p><?php echo $totalCategories; ?></p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                <div class="action-buttons">
                    <a href="properties/add.php" class="action-btn">
                        <i class="fas fa-plus"></i> Add New Property
                    </a>
                    <a href="categories/add.php" class="action-btn">
                        <i class="fas fa-tag"></i> Add Category
                    </a>
                    <a href="properties/list.php" class="action-btn">
                        <i class="fas fa-list"></i> View All Properties
                    </a>
                    <a href="users/manage.php" class="action-btn">
                        <i class="fas fa-user-cog"></i> Manage Users
                    </a>
                </div>
            </div>

        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>

</html>