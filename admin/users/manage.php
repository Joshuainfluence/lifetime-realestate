<?php
/**
 * Admin Users Management Page
 * 
 * Features:
 * - View all registered users
 * - Search and filter users
 * - Change user roles
 * - View user statistics
 * - Pagination
 */

require_once '../../config/database.php';
require_once '../../classes/User.php';

// Check authentication
// if (!isLoggedIn() || !isAdmin()) {
//     redirect('../../login.php');
// }

// Initialize User class
$userClass = new User();

$message = '';
$messageType = '';

// Handle role change
if (isset($_POST['change_role'])) {
    $userId = intval($_POST['user_id']);
    $newRole = sanitize($_POST['new_role']);
    
    if ($userClass->changeRole($userId, $newRole)) {
        $message = 'User role updated successfully';
        $messageType = 'success';
    } else {
        $message = 'Failed to update user role';
        $messageType = 'error';
    }
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $userId = intval($_GET['delete']);
    
    // Prevent deleting yourself
    // if ($userId == $_SESSION['user_id']) {


    //     $message = 'You cannot delete your own account';
    //     $messageType = 'error';
    // } else {
        if ($userClass->delete($userId)) {
            $message = 'User deleted successfully';
            $messageType = 'success';
        } else {
            $message = 'Failed to delete user';
            $messageType = 'error';
        }
    // }
}

// Get filter parameters
$searchTerm = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$roleFilter = isset($_GET['role']) ? sanitize($_GET['role']) : '';

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Get all users
$users = $userClass->getAll($perPage, $offset);

// Filter users if search term exists
if ($searchTerm || $roleFilter) {
    $users = array_filter($users, function($user) use ($searchTerm, $roleFilter) {
        $matchesSearch = empty($searchTerm) || 
            stripos($user['username'], $searchTerm) !== false ||
            stripos($user['email'], $searchTerm) !== false ||
            stripos($user['full_name'], $searchTerm) !== false;
        
        $matchesRole = empty($roleFilter) || $user['role'] === $roleFilter;
        
        return $matchesSearch && $matchesRole;
    });
}

// Get total count for pagination
$totalUsers = $userClass->total();
$totalPages = ceil($totalUsers / $perPage);

// Get user statistics by role
$adminCount = 0;
$userCount = 0;
foreach ($users as $u) {
    if ($u['role'] === 'admin') {
        $adminCount++;
    } else {
        $userCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f5f6fa;
        }
        
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
            margin-top: 10vh;
        }
        
        /* Sidebar */
        .admin-sidebar {
            width: 260px;
            background: linear-gradient(180deg, #1a1a1a 0%, #2d2d2d 100%);
            color: white;
            position: fixed;
            height: calc(100vh - 10vh);
            overflow-y: auto;
            padding: 2rem 0;
        }
        
        .sidebar-header {
            padding: 0 1.5rem 2rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h2 {
            color: #ff0000;
            margin: 0;
        }
        
        .admin-sidebar ul {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0 0;
            display: flex;
            flex-direction: column;
        }
        
        .admin-sidebar ul li a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            color: rgba(255, 255, 255, 0.7);
            transition: all 0.3s ease;
        }
        
        .admin-sidebar ul li a:hover,
        .admin-sidebar ul li a.active {
            background: rgba(255, 0, 0, 0.1);
            color: white;
        }
        
        .admin-sidebar ul li a i {
            width: 24px;
        }
        
        /* Main Content */
        .admin-content {
            flex: 1;
            margin-left: 260px;
            padding: 2rem;
        }
        
        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            margin: 0 0 0.5rem 0;
            color: #333;
        }
        
        .page-header p {
            margin: 0;
            color: #666;
        }
        
        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.8rem;
        }
        
        .stat-icon.blue {
            background: rgba(0, 123, 255, 0.1);
            color: #007bff;
        }
        
        .stat-icon.red {
            background: rgba(255, 0, 0, 0.1);
            color: #ff0000;
        }
        
        .stat-icon.green {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .stat-info h3 {
            margin: 0;
            color: #999;
            font-size: 0.85rem;
            text-transform: uppercase;
        }
        
        .stat-info p {
            margin: 0.3rem 0 0;
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }
        
        /* Search and Filter */
        .filters-section {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }
        
        .filters-form {
            display: grid;
            grid-template-columns: 2fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }
        
        .filter-group label {
            display: block;
            font-weight: 600;
            color: #666;
            margin-bottom: 0.3rem;
            font-size: 0.9rem;
        }
        
        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 0.7rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #ff0000;
        }
        
        .btn {
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #ff0000, #cc0000);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 0, 0, 0.3);
        }
        
        /* Users Table */
        .users-table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table thead {
            background: #f8f9fa;
        }
        
        table th {
            padding: 1.2rem 1rem;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }
        
        table td {
            padding: 1.2rem 1rem;
            border-bottom: 1px solid #f0f0f0;
            color: #666;
        }
        
        table tr:hover {
            background: #f9f9f9;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff0000, #cc0000);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        
        .user-details h4 {
            margin: 0 0 0.2rem 0;
            color: #333;
            font-weight: 600;
        }
        
        .user-details p {
            margin: 0;
            font-size: 0.85rem;
            color: #999;
        }
        
        .badge {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .badge-admin {
            background: rgba(255, 0, 0, 0.1);
            color: #ff0000;
        }
        
        .badge-user {
            background: rgba(0, 123, 255, 0.1);
            color: #007bff;
        }
        
        /* Action Buttons */
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
            border: none;
            cursor: pointer;
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
        
        /* Role Change Form */
        .role-form {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .role-form select {
            padding: 0.4rem 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.9rem;
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
            <div class="sidebar-header">
                <h2><i class="fas fa-cog"></i> Admin Panel</h2>
            </div>
            <ul>
                <li>
                    <a href="../dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="../properties/list.php">
                        <i class="fas fa-home"></i> Properties
                    </a>
                </li>
                <li>
                    <a href="../properties/add.php">
                        <i class="fas fa-plus-circle"></i> Add Property
                    </a>
                </li>
                <li>
                    <a href="../categories/manage.php">
                        <i class="fas fa-tags"></i> Categories
                    </a>
                </li>
                <li>
                    <a href="manage.php" class="active">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1><i class="fas fa-users"></i> Manage Users</h1>
                <p>View and manage all registered users</p>
            </div>

            <!-- Alert Messages -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Users</h3>
                        <p><?php echo $totalUsers; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Administrators</h3>
                        <p><?php echo $adminCount; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Regular Users</h3>
                        <p><?php echo $userCount; ?></p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-section">
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <label>Search Users</label>
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Search by name, username, email..."
                            value="<?php echo htmlspecialchars($searchTerm); ?>"
                        >
                    </div>
                    
                    <div class="filter-group">
                        <label>Filter by Role</label>
                        <select name="role">
                            <option value="">All Roles</option>
                            <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="user" <?php echo $roleFilter === 'user' ? 'selected' : ''; ?>>User</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="users-table-container">
                <?php if (!empty($users)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Change Role</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <?php echo strtoupper(substr($u['full_name'] ?: $u['username'], 0, 1)); ?>
                                            </div>
                                            <div class="user-details">
                                                <h4><?php echo htmlspecialchars($u['full_name'] ?: 'N/A'); ?></h4>
                                                <p>@<?php echo htmlspecialchars($u['username']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo htmlspecialchars($u['phone'] ?: 'N/A'); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $u['role']; ?>">
                                            <?php echo ucfirst($u['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if( isset($u['id'])): //if ($u['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" class="role-form">
                                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                <select name="new_role">
                                                    <option value="user" <?php echo $u['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                                    <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                </select>
                                                <button type="submit" name="change_role" class="btn-icon btn-edit" title="Update Role">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: #999; font-size: 0.9rem;">Your Account</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                    <td>
                                        <div class="actions-cell">
                                            <?php if(isset($u['id'])): //if ($u['id'] != $_SESSION['user_id']): ?>
                                                <a href="?delete=<?php echo $u['id']; ?>" 
                                                   class="btn-icon btn-delete" 
                                                   title="Delete User"
                                                   onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <span style="color: #999;">-</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($searchTerm); ?>&role=<?php echo urlencode($roleFilter); ?>">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="current"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($searchTerm); ?>&role=<?php echo urlencode($roleFilter); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($searchTerm); ?>&role=<?php echo urlencode($roleFilter); ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>No Users Found</h3>
                        <p>No users match your search criteria.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Confirm role change
        document.querySelectorAll('.role-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const select = this.querySelector('select[name="new_role"]');
                if (!confirm(`Are you sure you want to change this user's role to ${select.value}?`)) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>