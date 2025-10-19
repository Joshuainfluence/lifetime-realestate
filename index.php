
<?php
/**
 * Homepage - index.php
 * 
 * This is the main landing page of the real estate website
 * It displays featured properties, categories, and search functionality
 */

// Include necessary files
require_once 'config/database.php';
// require_once 'classes/Property.php';
require_once 'classes/Category.php';

// Initialize classes
// $property = new Property();
$category = new Category();

// Get featured properties (limit to 6 for display)
// $featuredProperties = $property->getFeatured(6);

// Get all categories for display
$categories = $category->getAll();

// Get latest properties for pricing section
// $latestProperties = $property->getAll([], 3, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Find your dream home with LIFETIME Real Estate. Browse properties for sale and rent." />
    <meta name="keywords" content="real estate, property, house, apartment, rent, sale" />
    <title>LIFETIME - Real Estate | Find Your Dream Home</title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/css/media.css" />
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,regular,500,600,700,800,900,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic" rel="stylesheet" />
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
    <!-- 
        HEADER SECTION
        Contains navigation bar with logo, menu items, and authentication buttons
    -->
    <header>
        <nav>
            <!-- Logo/Brand Name -->
            <div class="logo">LIFETIME</div>

            <!-- Navigation Menu -->
            <ul id="navMenu">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#pricing">Properties</a></li>
                <li><a href="#categories">Categories</a></li>
                <?php if (isAdmin()): ?>
                    <!-- Show admin link if user is logged in as admin -->
                    <li><a href="admin/dashboard.php">Admin Panel</a></li>
                <?php endif; ?>
            </ul>

            <!-- Authentication Buttons -->
            <div class="buttons">
                <?php if (isLoggedIn()): ?>
                    <!-- If user is logged in, show profile and logout -->
                    <span style="color: white; margin-right: 1rem;">Welcome, <?php echo $_SESSION['username']; ?></span>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <!-- If not logged in, show login and register buttons -->
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Toggle Button -->
            <div class="menutoggler" id="menuToggle">
                <i class="fa fa-bars"></i>
            </div>
        </nav>
    </header>

    <!-- 
        HERO SECTION
        Main landing section with headline and call-to-action
    -->
    <section id="home">
        <div class="col">
            <h1>Find Your Dream Home with Confidence at LIFETIME Real Estate</h1>
            <h3>
                Discover the perfect property with our trusted real estate experts.
                Browse homes, apartments, and land for sale or rent — all in one place.
            </h3>
            <div class="homebutton">
                <a href="#features">See Features</a>
                <a href="#pricing">View Properties</a>
            </div>
        </div>
        <!-- Image column with sliding background animation -->
        <div class="col"></div>
    </section>

    <!-- 
        ABOUT SECTION
        Information about the company
    -->
    <section id="about">
        <div class="col"></div>
        <div class="col">
            <h1>About Us</h1>
            <p>
                Welcome to LIFETIME Real Estate, your trusted partner in real estate. 
                We are dedicated to helping individuals, families, and investors find 
                the perfect property that matches their goals and lifestyle.
            </p>
            <p>
                With years of industry experience, we combine expert market knowledge 
                with personalized service to make every property journey smooth and rewarding. 
                Whether you're buying, selling, or investing, our team is committed to guiding 
                you every step of the way — with transparency, professionalism, and care.
            </p>
            <p>
                At LIFETIME Real Estate, we don't just sell properties — we help you find 
                a place to call home and build lasting value for the future.
            </p>

            <!-- Social Media Links -->
            <div class="media">
                <a href="https://wa.me/+2348101274164" target="_blank" aria-label="WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
                <a href="#" aria-label="Facebook">
                    <i class="fab fa-facebook"></i>
                </a>
                <a href="#" aria-label="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" aria-label="Telegram">
                    <i class="fab fa-telegram"></i>
                </a>
                <a href="#" aria-label="TikTok">
                    <i class="fab fa-tiktok"></i>
                </a>
                <a href="#" aria-label="X (Twitter)">
                    <i class="fab fa-x-twitter"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- 
        FEATURES SECTION
        Highlights key features of the platform
    -->
    <section id="features">
        <h1 class="section-title">Why Choose LIFETIME?</h1>
        <div class="features-grid">
            <!-- Feature 2: Verified Listings -->
            <div class="feature-card">
                <i class="fas fa-shield-alt"></i>
                <h3>Verified Listings</h3>
                <p>All properties are verified by our team to ensure authenticity and quality.</p>
            </div>

            <!-- Feature 3: Expert Agents -->
            <div class="feature-card">
                <i class="fas fa-user-tie"></i>
                <h3>Expert Agents</h3>
                <p>Work with experienced real estate professionals who understand your needs.</p>
            </div>

            <!-- Feature 4: Easy Search -->
            <div class="feature-card">
                <i class="fas fa-search"></i>
                <h3>Advanced Search</h3>
                <p>Filter properties by location, price, size, and amenities to find your perfect match.</p>
            </div>

            <!-- Feature 5: Virtual Tours -->
            <div class="feature-card">
                <i class="fas fa-video"></i>
                <h3>Virtual Tours</h3>
                <p>Explore properties from the comfort of your home with our virtual tour feature.</p>
            </div>

            <!-- Feature 6: Best Prices -->
            <div class="feature-card">
                <i class="fas fa-dollar-sign"></i>
                <h3>Competitive Prices</h3>
                <p>Get the best deals on properties with transparent pricing and no hidden fees.</p>
            </div>
        </div>
    </section>

    <!-- 
        SEARCH SECTION
        Advanced search form to filter properties
    -->
    <section id="search">
        <h1 class="section-title">Find Your Perfect Property</h1>
        <div class="search-container">
            <form action="search.php" method="GET" class="search-form">
                <!-- Search Input -->
                <div class="form-group">
                    <input type="text" name="search" placeholder="Search by location, title..." class="search-input">
                </div>

                <!-- Category Filter -->
                <div class="form-group">
                    <select name="category" class="search-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Property Type Filter -->
                <div class="form-group">
                    <select name="type" class="search-select">
                        <option value="">Sale or Rent</option>
                        <option value="sale">For Sale</option>
                        <option value="rent">For Rent</option>
                    </select>
                </div>

                <!-- Price Range -->
                <div class="form-group">
                    <input type="number" name="price_min" placeholder="Min Price" class="search-input">
                </div>

                <div class="form-group">
                    <input type="number" name="price_max" placeholder="Max Price" class="search-input">
                </div>

                <!-- Search Button -->
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>
    </section>

    <!-- 
        CATEGORIES SECTION
        Display property categories with icons
    -->
    <section id="categories">
        <h1 class="section-title">Browse by Category</h1>
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
                <!-- Each category card -->
                <a href="search.php?category=<?php echo $cat['id']; ?>" class="category-card">
                    <i class="fas <?php echo htmlspecialchars($cat['icon']); ?>"></i>
                    <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                    <p><?php echo htmlspecialchars($cat['description']); ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    

    <!-- 
        TESTIMONIALS SECTION (Optional)
        Customer reviews and feedback
    -->
    <section id="testimonials">
        <h1 class="section-title">What Our Clients Say</h1>
        <div class="testimonials-grid">
            <!-- Testimonial 1 -->
            <div class="testimonial-card">
                <div class="stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p>"LIFETIME Real Estate helped me find my dream home! The process was smooth and the team was very professional."</p>
                <h4>- John Doe</h4>
            </div>

            <!-- Testimonial 2 -->
            <div class="testimonial-card">
                <div class="stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p>"Excellent service! They understood exactly what I was looking for and found the perfect property within my budget."</p>
                <h4>- Jane Smith</h4>
            </div>

            <!-- Testimonial 3 -->
            <div class="testimonial-card">
                <div class="stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p>"Great experience from start to finish. Highly recommend LIFETIME for anyone looking to buy or rent property."</p>
                <h4>- Mike Johnson</h4>
            </div>
        </div>
    </section>

    <!-- 
        FOOTER SECTION
        Company information and links
    -->
    <footer>
        <div class="footer-content">
            <!-- Company Info -->
            <div class="footer-col">
                <h3>LIFETIME Real Estate</h3>
                <p>Your trusted partner in finding the perfect property. We're here to help you every step of the way.</p>
                <div class="social-links">
                    <a href="https://wa.me/+2348101274164" target="_blank"><i class="fab fa-whatsapp"></i></a>
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-telegram"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-col">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="properties.php">Properties</a></li>
                    <li><a href="#categories">Categories</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>

            <!-- Categories -->
            <div class="footer-col">
                <h3>Property Types</h3>
                <ul>
                    <?php foreach (array_slice($categories, 0, 5) as $cat): ?>
                        <li>
                            <a href="search.php?category=<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="footer-col">
                <h3>Contact Us</h3>
                <ul>
                    <li><i class="fas fa-phone"></i> +234 810 127 4164</li>
                    <li><i class="fas fa-envelope"></i> info@lifetime.com</li>
                    <li><i class="fas fa-map-marker-alt"></i> Port Harcourt, Nigeria</li>
                </ul>
            </div>
        </div>

        <!-- Copyright -->
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> LIFETIME Real Estate. All rights reserved.</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
</body>
</html>