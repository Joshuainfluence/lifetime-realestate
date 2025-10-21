/**
 * Main javascript file
 * handles all frontend interactions including:
 * - Mobile menu toggle
 * - Smooth scrolling
 * - Form validation 
 * - Dynamic UI updates
 */

// Wait for DOM to be fully loaded before executing scripts
document.addEventListener('DOMContentLoaded', function() {
    
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

});

