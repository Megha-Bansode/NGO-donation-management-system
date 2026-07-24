<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Middleware.php';
require_once __DIR__ . '/core/Security.php';
require_once __DIR__ . '/core/Validator.php';

Middleware::guest();
$csrfToken = Security::generateCSRF();
$message = null;
$error = false;

// Mock token verification for architecture demonstration
$token = $_GET['token'] ?? null;
if (!$token) {
    die("Invalid or missing password reset token.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (Security::verifyCSRF($_POST['csrf_token'] ?? '')) {
        $password = $_POST['password'] ?? '';
        if (Validator::strongPassword($password)) {
            $message = "Your password has been successfully reset. You can now login.";
        } else {
            $error = true;
            $message = "Password must be at least 8 characters, contain an uppercase, lowercase, and a number.";
        }
    } else {
        $error = true;
        $message = "Invalid security token.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password | <?php echo APP_NAME; ?></title>
    <!-- Core Design System -->
    <link rel="stylesheet" href="assets/css/landing.css">
    <!-- Premium Auth UI -->
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="auth-layout">
    
    <!-- LEFT SIDE: HERO -->
    <div class="auth-hero-wrapper">
        <div class="auth-hero"></div>
        <div class="auth-hero-overlay"></div>
        <div class="auth-noise"></div>
        <div class="auth-light"></div>

        <div class="particles">
            <div class="particle"></div><div class="particle"></div>
            <div class="particle"></div><div class="particle"></div><div class="particle"></div>
        </div>

        <div class="hero-content">
            <div class="hero-logo-container">
                <a href="index.php"><img src="assets/images/logo/arohan-logo.jpeg" alt="<?php echo APP_NAME; ?> Logo"></a>
                <div class="hero-brand-text">
                    <h2>Arohan Foundation</h2>
                    <span>Care • Heal • Empower • Uplift</span>
                </div>
            </div>
            
            <h1 class="hero-quote">
                Your journey towards impact continues.<br>
                <span>Secure your account.</span>
            </h1>
            
            <p class="hero-mission">
                Please create a strong new password to secure your account. We recommend a mix of uppercase letters, lowercase letters, numbers, and symbols.
            </p>

            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-value">12,000+</span>
                    <span class="stat-label">Lives Changed</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">250+</span>
                    <span class="stat-label">Campaigns</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">150+</span>
                    <span class="stat-label">Volunteers</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">95%</span>
                    <span class="stat-label">Transparency</span>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT SIDE: FORM -->
    <div class="auth-panel">
        <div class="auth-panel-deco"></div>
        <div class="auth-card">
            
            <div class="auth-header">
                <h2>New Password</h2>
                <p>Create a new, strong password.</p>
            </div>

            <?php if ($message): ?>
                <div class="alert-message <?php echo $error ? 'alert-error' : 'alert-success'; ?>">
                    <i class="fas <?php echo $error ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>" style="margin-right: 8px;"></i> <?php echo htmlspecialchars($message); ?>
                    <?php if(!$error): ?>
                        <div style="margin-top: 15px; width: 100%;">
                            <a href="login.php" class="btn-auth" style="text-align: center; display: block; text-decoration: none; padding: 12px;">Proceed to Login</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!$message || $error): ?>
            <form method="POST" action="" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <i class="fas fa-lock form-icon"></i>
                    <input type="password" name="password" id="pwd-input" class="form-control" required placeholder=" " autofocus>
                    <label class="floating-label">New Password</label>
                    <i class="fas fa-eye toggle-password" title="Toggle Password Visibility"></i>
                </div>
                
                <div class="pwd-strength-container" style="animation: fadeUpStagger 0.8s 2.2s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; opacity: 0;">
                    <div id="pwd-strength" class="pwd-strength">
                        <div id="pwd-bar" class="pwd-bar weak"></div>
                    </div>
                    <span id="pwd-text" class="pwd-text"></span>
                </div>
                
                <div class="btn-wrapper">
                    <button type="submit" class="btn-auth">Reset Password</button>
                </div>
            </form>
            <?php endif; ?>
            
            <div class="auth-footer">
                <a href="login.php"><i class="fas fa-arrow-left" style="margin-right: 5px;"></i> Back to Login</a>
            </div>
        </div>
    </div>

</div>

<script src="assets/js/auth.js"></script>
</body>
</html>
