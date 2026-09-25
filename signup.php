<?php 
// FarmTrack Signup Page
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
    <title>FarmTrack - Sign Up</title>
    <!-- Google Fonts for modern typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Link to external CSS -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        .login-card { max-height: 90vh; overflow-y: auto; }
        .form-section { padding-top: 1rem; padding-bottom: 1rem; }
    </style>
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
                    <h2>Create an Account</h2>
                    <p>Join the FarmTrack network</p>
                </div>
                
                <form action="actions/auth.php" method="post" id="signupForm" class="auth-form">
                    <input type="hidden" name="action" value="signup">
                    
                    <?php if (isset($_GET['error'])) { ?>
                        <div class="error-alert">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <span><?=htmlspecialchars($_GET['error'])?></span>
                        </div>
                    <?php } ?>
                    <?php if (isset($_GET['success'])) { ?>
                        <div class="error-alert" style="background: #ecfdf5; border-color: #a7f3d0; color: #065f46;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            <span><?=htmlspecialchars($_GET['success'])?></span>
                        </div>
                    <?php } ?>
                    
                    <div class="input-group">
                        <label for="name">Full Name</label>
                        <div class="input-wrapper">
                            <input type="text" name="name" id="name" required placeholder="Enter your full name" value="<?php echo isset($_GET['name']) ? htmlspecialchars($_GET['name']) : ''; ?>">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="username">Username</label>
                        <div class="input-wrapper">
                            <input type="text" name="username" id="username" required placeholder="Choose a username" value="<?php echo isset($_GET['username']) ? htmlspecialchars($_GET['username']) : ''; ?>">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="mobile">Mobile Number</label>
                        <div class="input-wrapper">
                            <input type="tel" name="mobile" id="mobile" required placeholder="Enter your mobile number" value="<?php echo isset($_GET['mobile']) ? htmlspecialchars($_GET['mobile']) : ''; ?>">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="role">Role</label>
                        <div class="input-wrapper">
                            <select name="role" id="role" required>
                                <option value="" disabled <?php echo !isset($_GET['role']) ? 'selected' : ''; ?>>Select your role</option>
                                <option value="farmer" <?php echo (isset($_GET['role']) && $_GET['role'] === 'farmer') ? 'selected' : ''; ?>>Farmer</option>
                                <option value="transporter" <?php echo (isset($_GET['role']) && $_GET['role'] === 'transporter') ? 'selected' : ''; ?>>Transporter</option>
                                <option value="buyer" <?php echo (isset($_GET['role']) && $_GET['role'] === 'buyer') ? 'selected' : ''; ?>>Buyer</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <input type="password" name="password" id="password" required placeholder="Create a password">
                        </div>
                    </div>
                    
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <span>Sign Up</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                    </button>

                    <div style="display: flex; align-items: center; margin: 1.5rem 0;">
                        <hr style="flex-grow: 1; border: none; border-top: 1px solid #e5e7eb;">
                        <span style="padding: 0 10px; color: #6b7280; font-size: 0.875rem;">OR</span>
                        <hr style="flex-grow: 1; border: none; border-top: 1px solid #e5e7eb;">
                    </div>

                    <a href="actions/google.php" class="submit-btn" style="background-color: white; color: #374151; border: 1px solid #d1d5db; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); text-decoration: none; justify-content: center;">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google" style="width: 20px; height: 20px; margin-right: 10px;">
                        <span>Sign up with Google</span>
                    </a>

                    <div class="form-footer" style="margin-top: 1.5rem; text-align: center; color: #6b7280; font-size: 0.875rem;">
                        Already have an account? <a href="index.php" style="color: #4f46e5; text-decoration: none; font-weight: 500;">Sign in</a>
                    </div>
                </form>
            </div>
            
        </div>
    </main>

    <!-- Link to external JS -->
    <script src="js/app.js"></script>
</body>
</html>
