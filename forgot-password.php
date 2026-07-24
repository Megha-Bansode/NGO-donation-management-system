<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Middleware.php';
require_once __DIR__ . '/core/Security.php';

Middleware::guest();
$csrfToken = Security::generateCSRF();
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (Security::verifyCSRF($_POST['csrf_token'] ?? '')) {
        $message = "If an account exists with that email, a password reset link has been sent.";
    } else {
        $message = "Invalid security token.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | <?php echo APP_NAME; ?></title>
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
                Every setback is a setup for a comeback.<br>
                <span>We're here to help.</span>
            </h1>
            
            <p class="hero-mission">
                Don't worry if you've lost access to your account. Enter your email address and we'll guide you through the process of getting back to making a difference.
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
                <h2>Forgot Password</h2>
                <p>Enter your email to receive a reset link.</p>
            </div>

            <?php if ($message && strpos($message, 'Invalid') === false): ?>
                <div class="alert-message alert-success">
                    <i class="fas fa-paper-plane" style="margin-right: 8px;"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php elseif ($message): ?>
                <div class="alert-message alert-error">
                    <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="form-group">
                    <i class="fas fa-envelope form-icon"></i>
                    <input type="email" name="email" class="form-control" required placeholder=" " autofocus>
                    <label class="floating-label">Email Address</label>
                </div>
                
                <div class="btn-wrapper">
                    <button type="submit" class="btn-auth">Send Reset Link</button>
                </div>
            </form>
            
            <div class="auth-footer">
                <a href="login.php"><i class="fas fa-arrow-left" style="margin-right: 5px;"></i> Back to Login</a>
            </div>
        </div>
    </div>

</div>

<script src="assets/js/auth.js"></script>
</body>
</html>
