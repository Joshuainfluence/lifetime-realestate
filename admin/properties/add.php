<?php
/**
 * Admin Add Property Page
 * 
 * Complete form to add new properties with:
 * - Image upload with preview
 * - All property fields
 * - Validation
 * - Success/error messages
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

// Get all categories for dropdown
$categories = $category->getAll();

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $property_type = sanitize($_POST['property_type']);
    $bedrooms = intval($_POST['bedrooms']);
    $bathrooms = intval($_POST['bathrooms']);
    $area = floatval($_POST['area']);
    $location = sanitize($_POST['location']);
    $address = sanitize($_POST['address']);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = sanitize($_POST['status']);
    
    // Validation
    if (empty($title)) {
        $errors[] = 'Property title is required';
    }
    
    if ($price <= 0) {
        $errors[] = 'Please enter a valid price';
    }
    
    if (empty($category_id)) {
        $errors[] = 'Please select a category';
    }
    
    if (empty($location)) {
        $errors[] = 'Location is required';
    }
    
    // Handle image upload
    $imageName = '';
    if (!empty($_FILES['image']['name'])) {
        $uploadResult = $property->uploadImage($_FILES['image']);
        if ($uploadResult) {
            $imageName = $uploadResult;
        } else {
            $errors[] = 'Failed to upload image. Please check file type and size (max 5MB)';
        }
    }
    
    // If no errors, create property
    if (empty($errors)) {
        $data = [
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'category_id' => $category_id,
            'property_type' => $property_type,
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'area' => $area,
            'location' => $location,
            'address' => $address,
            'image' => $imageName,
            'featured' => $featured,
            'status' => $status,
            'created_by' => $_SESSION['user_id']
        ];
        
        $result = $property->create($data);
        
        if ($result) {
            $success = 'Property added successfully!';
            // Clear form by redirecting
            header("Location: add.php?success=1");
            exit();
        } else {
            $errors[] = 'Failed to add property. Please try again.';
        }
    }
}

// Check for success message from redirect
if (isset($_GET['success'])) {
    $success = 'Property added successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property - Admin</title>
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
        
        /* Form Container */
        .form-container {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            max-width: 1000px;
        }
        
        .form-section {
            margin-bottom: 2.5rem;
        }
        
        .form-section:last-child {
            margin-bottom: 0;
        }
        
        .form-section h3 {
            color: #333;
            margin: 0 0 1.5rem 0;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #ff0000;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-row.single {
            grid-template-columns: 1fr;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .required {
            color: #ff0000;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            font-family: 'Nunito', sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ff0000;
            box-shadow: 0 0 0 3px rgba(255, 0, 0, 0.1);
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-group small {
            color: #666;
            font-size: 0.85rem;
            margin-top: 0.3rem;
        }
        
        /* Image Upload Section */
        .image-upload-area {
            border: 3px dashed #e0e0e0;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: #f8f9fa;
        }
        
        .image-upload-area:hover {
            border-color: #ff0000;
            background: #fff;
        }
        
        .image-upload-area.dragover {
            border-color: #ff0000;
            background: rgba(255, 0, 0, 0.05);
        }
        
        .image-upload-area i {
            font-size: 3rem;
            color: #ff0000;
            margin-bottom: 1rem;
        }
        
        .image-upload-area p {
            color: #666;
            margin: 0.5rem 0;
        }
        
        #image {
            display: none;
        }
        
        #imagePreview {
            max-width: 100%;
            max-height: 300px;
            border-radius: 10px;
            margin-top: 1rem;
            display: none;
        }
        
        /* Checkbox styling */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .checkbox-group:hover {
            background: #e9ecef;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .checkbox-group label {
            margin: 0;
            cursor: pointer;
            flex: 1;
        }
        
        /* Buttons */
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #f0f0f0;
        }
        
        .btn {
            padding: 1rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #ff0000, #cc0000);
            color: white;
            flex: 1;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 0, 0, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
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
        
        .alert ul {
            margin: 0;
            padding-left: 1.5rem;
        }
        
        /* Info boxes */
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .info-box i {
            color: #2196f3;
            margin-right: 0.5rem;
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
                    <a href="list.php">
                        <i class="fas fa-home"></i> Properties
                    </a>
                </li>
                <li>
                    <a href="add.php" class="active">
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
                <h1><i class="fas fa-plus-circle"></i> Add New Property</h1>
                <p>Fill in the details below to list a new property</p>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $success; ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>Please fix the following errors:</strong>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <div class="form-container">
                <form method="POST" enctype="multipart/form-data" id="propertyForm">
                    
                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                        
                        <div class="form-row single">
                            <div class="form-group">
                                <label>
                                    Property Title <span class="required">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    name="title" 
                                    placeholder="e.g., Luxury 3-Bedroom Apartment in Downtown"
                                    required
                                    value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                                >
                                <small>Enter a descriptive title for the property</small>
                            </div>
                        </div>

                        <div class="form-row single">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea 
                                    name="description" 
                                    placeholder="Describe the property features, amenities, and highlights..."
                                ><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                <small>Provide detailed information about the property</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>
                                    Category <span class="required">*</span>
                                </label>
                                <select name="category_id" required>
                                    <option value="">-- Select Category --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"
                                            <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>
                                    Property Type <span class="required">*</span>
                                </label>
                                <select name="property_type" required>
                                    <option value="sale" <?php echo (isset($_POST['property_type']) && $_POST['property_type'] == 'sale') ? 'selected' : ''; ?>>For Sale</option>
                                    <option value="rent" <?php echo (isset($_POST['property_type']) && $_POST['property_type'] == 'rent') ? 'selected' : ''; ?>>For Rent</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>
                                    Price ($) <span class="required">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    name="price" 
                                    step="0.01" 
                                    min="0"
                                    placeholder="e.g., 250000"
                                    required
                                    value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>"
                                >
                                <small>Enter the property price in USD</small>
                            </div>

                            <div class="form-group">
                                <label>
                                    Status <span class="required">*</span>
                                </label>
                                <select name="status" required>
                                    <option value="available" <?php echo (isset($_POST['status']) && $_POST['status'] == 'available') ? 'selected' : 'selected'; ?>>Available</option>
                                    <option value="sold" <?php echo (isset($_POST['status']) && $_POST['status'] == 'sold') ? 'selected' : ''; ?>>Sold</option>
                                    <option value="rented" <?php echo (isset($_POST['status']) && $_POST['status'] == 'rented') ? 'selected' : ''; ?>>Rented</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Property Details Section -->
                    <div class="form-section">
                        <h3><i class="fas fa-home"></i> Property Details</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Bedrooms</label>
                                <input 
                                    type="number" 
                                    name="bedrooms" 
                                    min="0"
                                    placeholder="e.g., 3"
                                    value="<?php echo isset($_POST['bedrooms']) ? $_POST['bedrooms'] : '0'; ?>"
                                >
                                <small>Number of bedrooms</small>
                            </div>

                            <div class="form-group">
                                <label>Bathrooms</label>
                                <input 
                                    type="number" 
                                    name="bathrooms" 
                                    min="0"
                                    placeholder="e.g., 2"
                                    value="<?php echo isset($_POST['bathrooms']) ? $_POST['bathrooms'] : '0'; ?>"
                                >
                                <small>Number of bathrooms</small>
                            </div>
                        </div>

                        <div class="form-row single">
                            <div class="form-group">
                                <label>Area (Square Meters)</label>
                                <input 
                                    type="number" 
                                    name="area" 
                                    step="0.01"
                                    min="0"
                                    placeholder="e.g., 120.50"
                                    value="<?php echo isset($_POST['area']) ? $_POST['area'] : ''; ?>"
                                >
                                <small>Total area in square meters</small>
                            </div>
                        </div>
                    </div>

                    <!-- Location Section -->
                    <div class="form-section">
                        <h3><i class="fas fa-map-marker-alt"></i> Location</h3>
                        
                        <div class="form-row single">
                            <div class="form-group">
                                <label>
                                    Location <span class="required">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    name="location" 
                                    placeholder="e.g., Victoria Island, Lagos"
                                    required
                                    value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>"
                                >
                                <small>City, State, or general area</small>
                            </div>
                        </div>

                        <div class="form-row single">
                            <div class="form-group">
                                <label>Full Address</label>
                                <textarea 
                                    name="address" 
                                    rows="3"
                                    placeholder="Enter the complete address..."
                                ><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                                <small>Complete street address (optional)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Image Upload Section -->
                    <div class="form-section">
                        <h3><i class="fas fa-camera"></i> Property Image</h3>
                        
                        <div class="info-box">
                            <i class="fas fa-info-circle"></i>
                            <span>Upload a clear, high-quality image of the property. Accepted formats: JPG, PNG, GIF, WEBP (Max size: 5MB)</span>
                        </div>

                        <div class="image-upload-area" id="imageUploadArea">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <h4>Click to upload or drag and drop</h4>
                            <p>JPG, PNG, GIF, WEBP up to 5MB</p>
                            <input type="file" name="image" id="image" accept="image/*">
                        </div>
                        
                        <img id="imagePreview" alt="Image Preview">
                    </div>

                    <!-- Featured Section -->
                    <div class="form-section">
                        <h3><i class="fas fa-star"></i> Featured Property</h3>
                        
                        <div class="checkbox-group">
                            <input 
                                type="checkbox" 
                                name="featured" 
                                id="featured"
                                <?php echo (isset($_POST['featured'])) ? 'checked' : ''; ?>
                            >
                            <label for="featured">
                                <strong>Mark as Featured Property</strong>
                                <br>
                                <small>Featured properties appear on the homepage and get more visibility</small>
                            </label>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Add Property
                        </button>
                        <a href="list.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Image Upload and Preview
        const imageInput = document.getElementById('image');
        const imagePreview = document.getElementById('imagePreview');
        const uploadArea = document.getElementById('imageUploadArea');

        // Click to upload
        uploadArea.addEventListener('click', () => {
            imageInput.click();
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                imageInput.files = files;
                previewImage(files[0]);
            }
        });

        // Image change event
        imageInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                previewImage(this.files[0]);
            }
        });

        // Preview function
        function previewImage(file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
                uploadArea.style.display = 'none';
            }
            
            reader.readAsDataURL(file);
        }

        // Form validation
        document.getElementById('propertyForm').addEventListener('submit', function(e) {
            const price = document.querySelector('input[name="price"]').value;
            
            if (parseFloat(price) <= 0) {
                e.preventDefault();
                alert('Please enter a valid price greater than 0');
                return false;
            }
        });

        // Auto-save to localStorage (optional feature)
        const formInputs = document.querySelectorAll('#propertyForm input, #propertyForm select, #propertyForm textarea');
        
        formInputs.forEach(input => {
            // Load saved value
            const savedValue = localStorage.getItem('property_' + input.name);
            if (savedValue && !input.value) {
                input.value = savedValue;
            }
            
            // Save on change
            input.addEventListener('change', function() {
                localStorage.setItem('property_' + this.name, this.value);
            });
        });

        // Clear localStorage on successful submission
        <?php if ($success): ?>
            formInputs.forEach(input => {
                localStorage.removeItem('property_' + input.name);
            });
        <?php endif; ?>
    </script>
</body>
</html>