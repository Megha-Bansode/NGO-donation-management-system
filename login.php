<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Middleware.php';
require_once __DIR__ . '/controllers/AuthController.php';

Middleware::guest(); // Only guests can see login

$auth = new AuthController();
$error = $auth->handleLogin();
$csrfToken = Security::generateCSRF();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?php echo APP_NAME; ?></title>
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
                Empowering communities.<br>
                <span>Together.</span>
            </h1>
            
            <p class="hero-mission">
                We believe in the power of collective action. Log in to access your dashboard, track your impact, and continue making a difference in the lives of those who need it most.
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
                <h2>Welcome Back</h2>
                <p>Sign in to continue making an impact.</p>
            </div>

            <?php if (!empty($error) && is_array($error)): ?>
                <div class="alert-message alert-error" style="display: flex; flex-direction: column; gap: 5px;">
                    <?php foreach ($error as $msg): ?>
                        <div><i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i> <?php echo htmlspecialchars($msg); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php elseif (is_string($error)): ?>
                <div class="alert-message alert-error">
                    <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="form-group">
                    <i class="fas fa-envelope form-icon"></i>
                    <input type="email" name="email" class="form-control" placeholder=" " autofocus>
                    <label class="floating-label">Email Address</label>
                </div>
                
                <div class="form-group">
                    <i class="fas fa-lock form-icon"></i>
                    <input type="password" name="password" class="form-control" placeholder=" ">
                    <label class="floating-label">Password</label>
                    <i class="fas fa-eye toggle-password" title="Toggle Password Visibility"></i>
                </div>
                
                <div class="auth-links">
                    <label class="custom-check">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="forgot-password.php">Forgot Password?</a>
                </div>
                
                <div class="btn-wrapper">
                    <button type="submit" class="btn-auth">Sign In</button>
                </div>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Create Account</a></p>
            </div>
        </div>
    </div>

</div>

<script src="assets/js/auth.js"></script>
</body>
</html>
