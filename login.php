<?php
/**
 * Professional Login Page
 * 
 * Features:
 * - Modern, dynamic design
 * - Animated background
 * - Password visibility toggle
 * - Form validation
 * - Responsive layout
 * - Professional UI/UX
 */

session_start();
require_once 'config/database.php';
require_once 'classes/User.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $user = new User();
        $result = $user->login($username, $password);
        
        if ($result) {
            // Check if admin, redirect accordingly
            if ($result['role'] === 'admin') {
                redirect('admin/dashboard.php');
            } else {
                redirect('index.php');
            }
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LIFETIME Real Estate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Nunito', sans-serif;
        }

        body {
            overflow: hidden;
        }

        /* Animated Background */
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
            position: relative;
            overflow: hidden;
        }

        /* Animated gradient overlay */
        .auth-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            opacity: 0;
            animation: gradientShift 10s ease-in-out infinite;
        }

        @keyframes gradientShift {
            0%, 100% { opacity: 0; }
            50% { opacity: 0.7; }
        }

        /* Floating shapes animation */
        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite ease-in-out;
           
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 70%;
            left: 80%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 100px;
            height: 100px;
            top: 40%;
            left: 70%;
            animation-delay: 4s;
        }

        .shape:nth-child(4) {
            width: 60px;
            height: 60px;
            top: 80%;
            left: 20%;
            animation-delay: 6s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            25% {
                transform: translateY(-30px) rotate(90deg);
            }
            50% {
                transform: translateY(-60px) rotate(180deg);
            }
            75% {
                transform: translateY(-30px) rotate(270deg);
            }
        }

        /* Main Content */
        .auth-content {
            position: relative;
            z-index: 10;
            display: flex;
            max-width: 1100px;
            width: 90%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Left Side - Image/Info Section */
        .auth-side {
            flex: 1;
            background: linear-gradient(135deg, #ff0000 0%, #8b0000 100%);
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .auth-side::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 15s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .auth-side-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }

        .logo-large {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .auth-side h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .auth-side p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .features-list {
            text-align: left;
            margin-top: 2rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            animation: fadeInLeft 0.6s ease-out;
            animation-fill-mode: both;
        }

        .feature-item:nth-child(1) { animation-delay: 0.2s; }
        .feature-item:nth-child(2) { animation-delay: 0.4s; }
        .feature-item:nth-child(3) { animation-delay: 0.6s; }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* Right Side - Form Section */
        .auth-box {
            flex: 1;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-header h1 {
            font-size: 2.2rem;
            color: #333;
            margin-bottom: 0.5rem;
            font-weight: 800;
        }

        .auth-header p {
            color: #666;
            font-size: 1rem;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert i {
            font-size: 1.3rem;
        }

        .alert-error {
            background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
            color: white;
            border: none;
        }

        .alert-success {
            background: linear-gradient(135deg, #51cf66, #37b24d);
            color: white;
            border: none;
        }

        /* Form Styling */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.6rem;
            color: #333;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .input-wrapper {
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus {
            outline: none;
            border-color: #ff0000;
            background: white;
            box-shadow: 0 0 0 4px rgba(255, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus + .input-icon {
            color: #ff0000;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 0.5rem;
            transition: all 0.3s ease;
        }

        .password-toggle:hover {
            color: #ff0000;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, #ff0000, #cc0000);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s ease;
            box-shadow: 0 4px 15px rgba(255, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-submit:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 0, 0, 0.4);
        }

        .btn-submit:active {
            transform: translateY(-1px);
        }

        .btn-content {
            position: relative;
            z-index: 1;
        }

        /* Additional Options */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: -0.5rem;
            font-size: 0.9rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .forgot-password {
            color: #ff0000;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .forgot-password:hover {
            color: #cc0000;
            text-decoration: underline;
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
            color: #999;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e0e0e0;
        }

        .divider span {
            padding: 0 1rem;
            font-size: 0.9rem;
        }

        /* Social Login */
        .social-login {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .social-btn {
            padding: 0.9rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            background: white;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .social-btn:hover {
            border-color: #ff0000;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .social-btn i {
            font-size: 1.2rem;
        }

        .social-btn.google {
            color: #ea4335;
        }

        .social-btn.facebook {
            color: #1877f2;
        }

        /* Footer Links */
        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e0e0e0;
        }

        .auth-footer p {
            color: #666;
            margin-bottom: 1rem;
        }

        .auth-footer a {
            color: #ff0000;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .auth-footer a:hover {
            color: #cc0000;
            text-decoration: underline;
        }

        .back-home {
            margin-top: 1rem;
        }

        .back-home a {
            color: #999;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .back-home a:hover {
            color: #ff0000;
        }

        /* Loading State */
        .btn-submit.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .btn-submit.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 968px) {
            .auth-side {
                display: none;
            }

            .auth-content {
                max-width: 500px;
            }
        }

        @media (max-width: 576px) {
            .auth-content {
                width: 95%;
                border-radius: 20px;
            }

            .auth-box {
                padding: 2rem 1.5rem;
            }

            .auth-header h1 {
                font-size: 1.8rem;
            }

            .social-login {
                grid-template-columns: 1fr;
            }
        }

        /* Accessibility */
        .form-group input:focus-visible {
            outline: 3px solid rgba(255, 0, 0, 0.3);
            outline-offset: 2px;
        }

        /* Dark mode support (optional) */
        @media (prefers-color-scheme: dark) {
            .auth-content {
                background: rgba(30, 30, 30, 0.95);
            }

            .auth-box {
                color: #e0e0e0;
            }

            .auth-header h1 {
                color: #f0f0f0;
            }

            .form-group input {
                background: #2a2a2a;
                border-color: #444;
                color: #f0f0f0;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Animated background shapes -->
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>

        <div class="auth-content">
            <!-- Left Side - Branding -->
            <div class="auth-side">
                <div class="auth-side-content">
                    <div class="logo-large">
                        <i class="fas fa-home"></i> LIFETIME
                    </div>
                    <h2>Welcome Back!</h2>
                    <p>Sign in to access your account and explore amazing properties</p>
                    
                    <div class="features-list">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <div>
                                <strong>Smart Search</strong>
                                <p>Find properties easily</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div>
                                <strong>Save Favorites</strong>
                                <p>Keep track of dream homes</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div>
                                <strong>Get Alerts</strong>
                                <p>Stay updated on new listings</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="auth-box">
                <div class="auth-header">
                    <h1>Sign In</h1>
                    <p>Enter your credentials to access your account</p>
                </div>

                <!-- Alert Messages -->
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['registered']) && $_GET['registered'] == 'success'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span>Registration successful! Please sign in.</span>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" action="" class="login-form" id="loginForm">
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i> Username or Email
                        </label>
                        <div class="input-wrapper">
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                required 
                                placeholder="Enter your username or email"
                                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                autocomplete="username"
                            >
                            <i class="fas fa-user input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <div class="input-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                placeholder="Enter your password"
                                autocomplete="current-password"
                            >
                            <i class="fas fa-lock input-icon"></i>
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        <span class="btn-content">
                            <i class="fas fa-sign-in-alt"></i> Sign In
                        </span>
                    </button>
                </form>

                <!-- Social Login (Optional) -->
                <div class="divider">
                    <span>Or continue with</span>
                </div>

                <div class="social-login">
                    <button class="social-btn google">
                        <i class="fab fa-google"></i> Google
                    </button>
                    <button class="social-btn facebook">
                        <i class="fab fa-facebook"></i> Facebook
                    </button>
                </div>

                <!-- Footer -->
                <div class="auth-footer">
                    <p>
                        Don't have an account? 
                        <a href="register.php">Create Account</a>
                    </p>
                    <div class="back-home">
                        <a href="index.php">
                            <i class="fas fa-home"></i> Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });

        // Form submission with loading state
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');

        loginForm.addEventListener('submit', function() {
            submitBtn.classList.add('loading');
            submitBtn.querySelector('.btn-content').innerHTML = '<span>Signing in...</span>';
        });

        // Input validation and feedback
        const inputs = document.querySelectorAll('input[required]');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.style.borderColor = '#ff6b6b';
                } else {
                    this.style.borderColor = '#51cf66';
                }
            });

            input.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    this.style.borderColor = '#e0e0e0';
                }
            });
        });

        // Prevent multiple form submissions
        let isSubmitting = false;
        loginForm.addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                return false;
            }
            isSubmitting = true;
        });

        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.animation = 'slideDown 0.4s ease-out reverse';
                setTimeout(() => alert.remove(), 400);
            }, 5000);
        });

        // Keyboard accessibility
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                const focusedElement = document.activeElement;
                if (focusedElement.tagName === 'INPUT') {
                    e.preventDefault();
                    loginForm.submit();
                }
            }
        });
    </script>
</body>
</html>