<?php 
// FarmTrack Login Page
session_start();

// Redirect if already logged in
if (isset($_SESSION['username']) && isset($_SESSION['id'])) {   
    header("Location: redirect.php");
    exit();
} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FarmTrack - Login</title>
    <!-- Google Fonts for modern typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Link to external CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <main class="login-wrapper">
        <div class="login-card">
            
            <!-- Minimal Branding Side -->
            <div class="brand-section">
                <div class="brand-content">
                    <div class="logo-icon">
                        <!-- Simple Leaf/Farm SVG Icon -->
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                    <h1>FarmTrack</h1>
                    <p>Smart Supply Chain Tracking</p>
                </div>
                <!-- Decorative background elements -->
                <div class="decor-circle circle-1"></div>
                <div class="decor-circle circle-2"></div>
            </div>
            
            <!-- Form Side -->
            <div class="form-section">
                <div class="form-header">
                    <h2>Welcome Back</h2>
                    <p>Sign in to your account</p>
                </div>
                
                <form action="check-login.php" method="post" id="authForm" class="auth-form">
                    
                    <?php if (isset($_GET['error'])) { ?>
                        <div class="error-alert">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <span><?=htmlspecialchars($_GET['error'])?></span>
                        </div>
                    <?php } ?>
                    
                    <div class="input-group">
                        <label for="username">Username</label>
                        <div class="input-wrapper">
                            <input type="text" name="username" id="username" required placeholder="Enter your username">
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <input type="password" name="password" id="password" required placeholder="Enter your password">
                            <button type="button" id="togglePassword" class="icon-btn" aria-label="Toggle password visibility">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label for="role">Role</label>
                        <div class="input-wrapper">
                            <select name="role" id="role" required>
                                <option value="" disabled selected>Select your role</option>
                                <option value="farmer">Farmer</option>
                                <option value="transporter">Transporter</option>
                                <option value="buyer">Buyer</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <span>Sign In</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </button>
                </form>
            </div>
            
        </div>
    </main>

    <!-- Link to external JS -->
    <script src="js/app.js"></script>
</body>
</html>
