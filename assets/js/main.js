/**
 * Main JavaScript File
 * 
 * Handles all frontend interactions including:
 * - Mobile menu toggle
 * - Smooth scrolling
 * - Form validation
 * - Dynamic UI updates
 */

// Wait for DOM to be fully loaded before executing scripts
document.addEventListener('DOMContentLoaded', function() {
    
    /**
     * MOBILE MENU TOGGLE
     * 
     * Shows/hides navigation menu on mobile devices
     * Toggles between hamburger and close icon
     */
    const menuToggle = document.getElementById('menuToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function() {
            // Toggle 'active' class to show/hide menu
            navMenu.classList.toggle('active');
            
            // Get the icon element
            const icon = this.querySelector('i');
            
            // Toggle between bars (hamburger) and times (close) icon
            if (navMenu.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });

        // Close menu when clicking on a nav link (for smooth navigation)
        const navLinks = navMenu.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                // Only close if menu is open (mobile view)
                if (navMenu.classList.contains('active')) {
                    navMenu.classList.remove('active');
                    menuToggle.querySelector('i').classList.remove('fa-times');
                    menuToggle.querySelector('i').classList.add('fa-bars');
                }
            });
        });
    }

    /**
     * SMOOTH SCROLLING FOR ANCHOR LINKS
     * 
     * Adds smooth scrolling behavior when clicking on anchor links
     */
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            // Get the target element
            const targetId = this.getAttribute('href');
            
            // Skip if href is just "#"
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                e.preventDefault();
                
                // Calculate offset for fixed header
                const headerHeight = document.querySelector('header').offsetHeight;
                const targetPosition = targetElement.offsetTop - headerHeight - 20;
                
                // Smooth scroll to target
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    /**
     * HEADER SHADOW ON SCROLL
     * 
     * Adds shadow to header when user scrolls down
     */
    const header = document.querySelector('header');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    /**
     * SEARCH FORM VALIDATION
     * 
     * Validates search form before submission
     */
    const searchForm = document.querySelector('.search-form');
    
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="search"]');
            const categorySelect = this.querySelector('select[name="category"]');
            const typeSelect = this.querySelector('select[name="type"]');
            const priceMin = this.querySelector('input[name="price_min"]');
            const priceMax = this.querySelector('input[name="price_max"]');
            
            // Check if at least one field is filled
            const hasInput = searchInput.value.trim() !== '' || 
                           categorySelect.value !== '' || 
                           typeSelect.value !== '' ||
                           priceMin.value !== '' ||
                           priceMax.value !== '';
            
            if (!hasInput) {
                e.preventDefault();
                alert('Please enter at least one search criteria');
                return false;
            }

            // Validate price range
            if (priceMin.value && priceMax.value) {
                const min = parseFloat(priceMin.value);
                const max = parseFloat(priceMax.value);
                
                if (min > max) {
                    e.preventDefault();
                    alert('Minimum price cannot be greater than maximum price');
                    return false;
                }
            }

            // Validate price values are positive
            if (priceMin.value && parseFloat(priceMin.value) < 0) {
                e.preventDefault();
                alert('Price cannot be negative');
                return false;
            }

            if (priceMax.value && parseFloat(priceMax.value) < 0) {
                e.preventDefault();
                alert('Price cannot be negative');
                return false;
            }
        });
    }

    /**
     * FAVORITE BUTTON FUNCTIONALITY
     * 
     * Handles adding/removing properties from favorites
     */
    const favoriteButtons = document.querySelectorAll('.btn-favorite');
    
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get property ID from URL
            const url = this.getAttribute('href');
            const propertyId = new URL(url, window.location.origin).searchParams.get('id');
            
            // Send AJAX request to add/remove favorite
            fetch('add-favorite.php?id=' + propertyId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Toggle heart icon color
                        const icon = this.querySelector('i');
                        
                        if (data.action === 'added') {
                            icon.style.color = 'red';
                            showNotification('Added to favorites!', 'success');
                        } else {
                            icon.style.color = '';
                            showNotification('Removed from favorites!', 'info');
                        }
                    } else {
                        showNotification(data.message || 'Error occurred', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred', 'error');
                });
        });
    });

    /**
     * IMAGE LAZY LOADING
     * 
     * Loads images only when they're about to be visible
     * Improves page load performance
     */
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));

    /**
     * ANIMATION ON SCROLL
     * 
     * Adds fade-in animation to elements as they come into view
     */
    const animateElements = document.querySelectorAll('.property-card, .feature-card, .category-card, .testimonial-card');
    
    const animationObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                animationObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1 // Trigger when 10% of element is visible
    });
    
    animateElements.forEach(element => animationObserver.observe(element));

    /**
     * PRICE RANGE SLIDER (if you want to add later)
     * 
     * This function can be used to create an interactive price range slider
     */
    function initPriceSlider() {
        const priceMinInput = document.querySelector('input[name="price_min"]');
        const priceMaxInput = document.querySelector('input[name="price_max"]');
        
        if (priceMinInput && priceMaxInput) {
            // Format numbers with commas
            [priceMinInput, priceMaxInput].forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value) {
                        const value = parseFloat(this.value);
                        this.value = value.toLocaleString('en-US');
                    }
                });
                
                input.addEventListener('focus', function() {
                    this.value = this.value.replace(/,/g, '');
                });
            });
        }
    }

    // Initialize price slider if elements exist
    initPriceSlider();
});

/**
 * NOTIFICATION SYSTEM
 * 
 * Shows temporary notification messages to users
 * 
 * @param {string} message - Message to display
 * @param {string} type - Type of notification (success, error, info, warning)
 */
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

/**
 * FORM VALIDATION HELPER
 * 
 * Validates email format
 * 
 * @param {string} email - Email address to validate
 * @return {boolean} True if valid, false otherwise
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * PHONE NUMBER VALIDATION
 * 
 * Validates phone number format (basic validation)
 * 
 * @param {string} phone - Phone number to validate
 * @return {boolean} True if valid, false otherwise
 */
function isValidPhone(phone) {
    const phoneRegex = /^[\d\s\-\+\(\)]+$/;
    return phoneRegex.test(phone) && phone.replace(/\D/g, '').length >= 10;
}

/**
 * DEBOUNCE FUNCTION
 * 
 * Limits how often a function can be called
 * Useful for search inputs and scroll events
 * 
 * @param {function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @return {function} Debounced function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * PROPERTY IMAGE PREVIEW
 * 
 * Shows preview of selected image before upload
 * Used in admin panel for adding/editing properties
 * 
 * @param {HTMLInputElement} input - File input element
 * @param {HTMLElement} preview - Preview container element
 */
function previewImage(input, preview) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.style.backgroundImage = `url('${e.target.result}')`;
            preview.style.backgroundSize = 'cover';
            preview.style.backgroundPosition = 'center';
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * NUMBER FORMATTING
 * 
 * Formats numbers with commas for better readability
 * Example: 1000000 -> 1,000,000
 * 
 * @param {number} num - Number to format
 * @return {string} Formatted number string
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * CALCULATE MORTGAGE (Optional feature)
 * 
 * Calculates monthly mortgage payment
 * 
 * @param {number} principal - Loan amount
 * @param {number} annualRate - Annual interest rate (as percentage)
 * @param {number} years - Loan term in years
 * @return {number} Monthly payment amount
 */
function calculateMortgage(principal, annualRate, years) {
    const monthlyRate = (annualRate / 100) / 12;
    const numPayments = years * 12;
    
    const monthlyPayment = principal * 
        (monthlyRate * Math.pow(1 + monthlyRate, numPayments)) / 
        (Math.pow(1 + monthlyRate, numPayments) - 1);
    
    return monthlyPayment;
}