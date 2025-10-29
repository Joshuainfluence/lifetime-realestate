# 🏠 LIFETIME Real Estate Website

A modern, full-featured real estate website built with PHP OOP, MySQL, HTML, CSS, and JavaScript. This project includes a comprehensive admin panel for property management, user authentication, advanced search functionality, and a responsive design.

![PHP](https://img.shields.io/badge/PHP-7.4+-blue)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-yellow)
![License](https://img.shields.io/badge/License-MIT-green)

## 📋 Table of Contents
- [Features](#features)
- [Screenshots](#screenshots)
- [Installation](#installation)
- [Database Setup](#database-setup)
- [Project Structure](#project-structure)
- [Usage](#usage)
- [Admin Panel](#admin-panel)
- [Technologies Used](#technologies-used)
- [Contributing](#contributing)
- [License](#license)

## ✨ Features

### Frontend Features
- 🏡 **Property Listings**: Browse properties with detailed information
- 🔍 **Advanced Search**: Filter by category, price range, location, and property type
- ❤️ **Favorites/Wishlist**: Save favorite properties (for logged-in users)
- 📱 **Responsive Design**: Works perfectly on desktop, tablet, and mobile
- 🎨 **Modern UI**: Clean, professional interface with smooth animations
- 🏷️ **Category Browsing**: Explore properties by category (Apartments, Houses, Villas, etc.)
- 💬 **Social Media Integration**: Connect via WhatsApp, Facebook, Instagram, etc.

### User Features
- 👤 **User Registration & Login**: Secure authentication system
- 🔐 **Password Hashing**: bcrypt for secure password storage
- 📊 **User Dashboard**: Manage profile and favorites
- 🔔 **Real-time Notifications**: JavaScript-powered notification system

### Admin Features
- 📊 **Dashboard**: Overview of statistics and recent activities
- ➕ **Property Management**: Full CRUD operations (Create, Read, Update, Delete)
- 🏷️ **Category Management**: Add, edit, and delete property categories
- 👥 **User Management**: View and manage registered users
- 🖼️ **Image Upload**: Upload property images with validation
- 📈 **Statistics**: View total properties, users, and categories
- 🎯 **Featured Properties**: Mark properties as featured for homepage display

### Technical Features
- 🎯 **OOP Architecture**: Clean, maintainable PHP code using classes
- 🛡️ **Security**: Prepared statements to prevent SQL injection
- 🔄 **Session Management**: Secure user session handling
- 📝 **Input Validation**: Client and server-side validation
- 🎨 **CSS Animations**: Smooth transitions and hover effects
- 📱 **Mobile Menu**: Touch-friendly navigation for mobile devices

## 📸 Screenshots

### Homepage
![Homepage](screenshots/homepage.png)

### Property Listings
![Properties](screenshots/properties.png)

### Admin Dashboard
![Admin Dashboard](screenshots/admin-dashboard.png)

## 🚀 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Web browser

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/lifetime-realestate.git
   cd lifetime-realestate
   ```

2. **Create database**
   - Open phpMyAdmin or MySQL command line
   - Create a new database named `lifetime_realestate`
   - Import the SQL schema (see [Database Setup](#database-setup))

3. **Configure database connection**
   - Open `config/database.php`
   - Update the following constants with your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'lifetime_realestate');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **Set base URL**
   - In `config/database.php`, update:
   ```php
   define('BASE_URL', 'http://localhost/lifetime-realestate/');
   ```

5. **Create upload directories**
   ```bash
   mkdir -p assets/uploads/properties
   chmod 755 assets/uploads/properties
   ```

6. **Add sample images**
   - Place some property images in the `img/` folder
   - Name them: `1.jpg`, `2.jpg`, `3.jpg`, `4.jpg`, `me.jpg`

7. **Start your web server**
   - If using XAMPP: Start Apache and MySQL
   - Navigate to: `http://localhost/lifetime-realestate/`

## 🗄️ Database Setup

### SQL Schema

Execute the following SQL to create all necessary tables:

```sql
-- Create database
CREATE DATABASE IF NOT EXISTS lifetime_realestate;
USE lifetime_realestate;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Properties table
CREATE TABLE properties (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category_id INT,
    property_type ENUM('sale', 'rent') NOT NULL,
    bedrooms INT,
    bathrooms INT,
    area DECIMAL(10, 2),
    location VARCHAR(200),
    address TEXT,
    image VARCHAR(255),
    featured BOOLEAN DEFAULT FALSE,
    status ENUM('available', 'sold', 'rented') DEFAULT 'available',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Favorites table
CREATE TABLE favorites (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    property_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (property_id) REFERENCES properties(id),
    UNIQUE KEY unique_favorite (user_id, property_id)
);

-- Insert default admin user
-- Username: admin, Password: admin123
INSERT INTO users (username, email, password, full_name, role) 
VALUES ('admin', 'admin@lifetime.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin');

-- Insert sample categories
INSERT INTO categories (name, description, icon) VALUES
('Apartment', 'Modern apartments and flats', 'fa-building'),
('House', 'Single-family houses', 'fa-home'),
('Villa', 'Luxury villas', 'fa-hotel'),
('Land', 'Land and plots', 'fa-map'),
('Commercial', 'Commercial properties', 'fa-store');
```

### Default Admin Credentials
- **Username**: `admin`
- **Password**: `admin123`
- **Important**: Change this password immediately after first login!

## 📁 Project Structure

```
lifetime-realestate/
│
├── assets/
│   ├── css/
│   │   ├── style.css          # Main stylesheet
│   │   ├── media.css          # Responsive styles
│   │   └── admin.css          # Admin panel styles
│   ├── js/
│   │   ├── main.js            # Frontend JavaScript
│   │   └── admin.js           # Admin panel JavaScript
│   └── uploads/
│       └── properties/        # Uploaded property images
│
├── config/
│   ├── database.php           # Database configuration
│   └── config.php             # General settings
│
├── classes/
│   ├── Database.php           # Database connection class
│   ├── Property.php           # Property operations
│   ├── User.php               # User management
│   ├── Category.php           # Category management
│   └── Favorite.php           # Favorites/Wishlist
│
├── admin/
│   ├── dashboard.php          # Admin dashboard
│   ├── properties/
│   │   ├── add.php           # Add new property
│   │   ├── edit.php          # Edit property
│   │   ├── delete.php        # Delete property
│   │   └── list.php          # List all properties
│   ├── categories/
│   │   ├── manage.php        # Manage categories
│   │   └── add.php           # Add category
│   └── users/
│       └── manage.php        # User management
│
├── img/                       # Static images
├── index.php                  # Homepage
├── properties.php             # Property listings
├── property-detail.php        # Single property view
├── search.php                 # Search results
├── login.php                  # User login
├── register.php               # User registration
├── logout.php                 # Logout handler
├── add-favorite.php           # Add to favorites
└── README.md                  # This file
```

## 💻 Usage

### For Regular Users

1. **Browse Properties**
   - Visit the homepage to see featured properties
   - Click on categories to filter
   - Use the search form for specific criteria

2. **Register/Login**
   - Click "Register" to create an account
   - Login to access favorite features

3. **Add to Favorites**
   - Click the heart icon on any property
   - View your favorites in your profile

### For Administrators

1. **Access Admin Panel**
   - Login with admin credentials
   - Click "Admin Panel" in navigation

2. **Add Properties**
   - Go to "Add Property" in admin sidebar
   - Fill in all details
   - Upload property image
   - Submit

3. **Manage Categories**
   - Add new property categories
   - Edit existing categories
   - Organize properties effectively

4. **View Statistics**
   - Dashboard shows overview
   - Total properties, users, categories

## 🎛️ Admin Panel

The admin panel provides comprehensive management features:

- **Dashboard**: View statistics and recent activities
- **Properties**: Add, edit, delete, and manage all properties
- **Categories**: Organize properties into categories
- **Users**: View and manage registered users
- **Media Management**: Upload and manage property images

Access: `/admin/dashboard.php` (requires admin login)

## 🛠️ Technologies Used

- **Backend**: PHP 7.4+ (Object-Oriented Programming)
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Authentication**: PHP Sessions, bcrypt password hashing
- **Security**: PDO Prepared Statements, Input Sanitization
- **Icons**: Font Awesome 6.4.0
- **Fonts**: Google Fonts (Nunito)

## 📚 Code Documentation

All code is extensively commented to help students learn:

```php
/**
 * Example of well-documented code
 * 
 * @param int $userId - The ID of the user
 * @return array - Array of user's favorite properties
 */
public function getUserFavorites($userId) {
    // Implementation with inline comments
}
```

## 🤝 Contributing

Contributions are welcome! Here's how you can help:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Your Name**
- GitHub: [@joshuainfluence](https://github.com/joshuainfluence)
- Email: joshuaonyeuku@gmail.com

## 🙏 Acknowledgments

- Font Awesome for icons
- Google Fonts for typography
- The PHP and MySQL communities
- All contributors and students learning from this project

## 📞 Support

For questions or support:
- Open an issue on GitHub
- Email: support@lifetime.com
- WhatsApp: +234 810 127 4164

## 🔮 Future Enhancements

- [ ] Property comparison feature
- [ ] Advanced filtering options
- [ ] Email notifications
- [ ] Payment integration
- [ ] Virtual tours
- [ ] Mobile app
- [ ] Multi-language support
- [ ] Map integration

---

**⭐ If you find this project helpful, please give it a star!**

Made with ❤️ for learning and education