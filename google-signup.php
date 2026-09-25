<?php 
session_start();
if (!isset($_SESSION['temp_google_id'])) {
    header("Location: index.php");
    exit();
}
$suggested_username = explode('@', $_SESSION['temp_email'])[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FarmTrack - Complete Google Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .login-card { max-height: 90vh; overflow-y: auto; }
        .google-badge {
            display: flex; align-items: center; justify-content: center;
            background: #f3f4f6; border-radius: 8px; padding: 12px; margin-bottom: 20px;
        }
        .google-badge img { width: 24px; height: 24px; margin-right: 12px; }
        .google-badge span { font-weight: 500; color: #374151; }
    </style>
</head>
<body>
    <main class="login-wrapper">
        <div class="login-card" style="grid-template-columns: 1fr; max-width: 500px; margin: 0 auto;">
            <div class="form-section">
                <div class="form-header">
                    <h2>Complete Profile</h2>
                    <p>Almost there! Just a few more details.</p>
                </div>
                
                <div class="google-badge">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google">
                    <span>Signed in as <?php echo htmlspecialchars($_SESSION['temp_email']); ?></span>
                </div>

                <form action="actions/auth.php" method="post" class="auth-form">
                    <input type="hidden" name="action" value="google_signup">
                    <?php if (isset($_GET['error'])) { ?>
                        <div class="error-alert">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <span><?=htmlspecialchars($_GET['error'])?></span>
                        </div>
                    <?php } ?>

                    <div class="input-group">
                        <label for="username">Username</label>
                        <div class="input-wrapper">
                            <input type="text" name="username" id="username" required value="<?php echo htmlspecialchars($suggested_username); ?>">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="mobile">Mobile Number</label>
                        <div class="input-wrapper">
                            <input type="tel" name="mobile" id="mobile" required placeholder="Enter your mobile number">
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
                    
                    <button type="submit" class="submit-btn" style="margin-top:20px;">
                        <span>Complete Sign Up</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
