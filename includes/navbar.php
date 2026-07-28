<?php
/**
 * Premium Navbar Component
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(__DIR__)); }
?>
<nav class="navbar landing-navbar">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="/" class="navbar-brand d-flex align-items-center" style="gap: 12px; text-decoration: none;">
            <img src="assets/images/logo/arohan-logo.jpeg" alt="Arohan Foundation Logo" class="brand-logo-img">
            <span class="brand-text" style="font-family: var(--font-heading); font-weight: 800; font-size: 22px; color: var(--text-dark);">Arohan Foundation</span>
        </a>
        <button class="mobile-menu-btn"><i class="fas fa-bars"></i></button>
        <div class="navbar-menu d-flex align-items-center">
            <ul class="nav-links d-flex">
                <li><a href="#home" class="nav-link active">Home</a></li>
                <li><a href="#about" class="nav-link">About</a></li>
                <li><a href="#campaigns" class="nav-link">Campaigns</a></li>
                <li><a href="#events" class="nav-link">Events</a></li>
                <li><a href="#contact" class="nav-link">Contact</a></li>
            </ul>
            <div class="nav-actions d-flex align-items-center">
                <a href="login.php" class="nav-link login-link">Log In</a>
                <a href="register.php" class="btn btn-premium">Donate Now</a>
            </div>
        </div>
    </div>
</nav>
