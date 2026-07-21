<?php
if (!isset($basePath)) {
    // Fallback if accessed directly
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $baseDir = dirname(dirname($scriptName));
    $baseDir = str_replace('\\', '/', $baseDir);
    $basePath = rtrim($baseDir, '/') . '/';
}
$isLoggedIn = isset($_SESSION['user']);
$user = $isLoggedIn ? $_SESSION['user'] : null;

// Helper to determine active link
$currentUri = $_SERVER['REQUEST_URI'];

function getDashboardUrl($role, $basePath) {
    switch ($role) {
        case 'super_admin': return $basePath . 'dashboard/super-admin';
        case 'admin': return $basePath . 'dashboard/admin';
        case 'donor': return $basePath . 'dashboard/donor';
        case 'volunteer': return $basePath . 'dashboard/volunteer';
        default: return $basePath . 'login';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Arohan Foundation - Empowering Lives, Building Sustainable Futures. Donate, volunteer, and transform lives globally.">
    <meta name="theme-color" content="#0d6efd">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . " | Arohan Foundation" : "Arohan Foundation - Donation & Volunteer Portal"; ?></title>
    
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    
    <!-- FontAwesome 6.4 for Modern Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS (Animate On Scroll) CSS -->
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    
    <!-- Custom Design System & Main Stylesheet -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>public/css/style.css">
</head>
<body>

<!-- Sticky Navigation Bar -->
<header class="header-wrapper sticky-top navbar-expand-lg" id="mainHeader">
    <nav class="navbar navbar-expand-lg navbar-light py-2 py-lg-3 transition-all">
        <div class="container">
            <!-- NGO Logo -->
            <a href="<?php echo $basePath; ?>" class="navbar-brand d-flex align-items-center me-4">
                <img src="<?php echo $basePath; ?>assets/images/logo.png" alt="Arohan Foundation Logo" class="brand-logo me-2" style="height: 48px; width: auto; object-fit: contain;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="logo-icon-fallback d-none align-items-center justify-content-center bg-primary text-white rounded-circle me-2" style="width: 42px; height: 42px; font-size: 1.25rem;">
                    <i class="fa-solid fa-hand-holding-heart"></i>
                </div>
                <div class="brand-text">
                    <span class="fw-bold text-dark fs-4 lh-1 d-block">Arohan<span class="text-success">Foundation</span></span>
                    <small class="text-muted d-block uppercase tracking-wider" style="font-size: 0.65rem;">Empowering Lives</small>
                </div>
            </a>
            
            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler border-0 shadow-none px-2" type="button" data-bs-toggle="collapse" data-bs-target="#ngoNavbar" aria-controls="ngoNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon-custom">
                    <i class="fa-solid fa-bars fs-4 text-dark" id="navToggleIcon"></i>
                </span>
            </button>            
            
            <!-- Nav Links -->
            <div class="collapse navbar-collapse" id="ngoNavbar">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 fw-medium gap-lg-1 gap-xl-2">
                    <li class="nav-item">
                        <a class="nav-link px-3 active" href="<?php echo $basePath; ?>#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="<?php echo $basePath; ?>#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="<?php echo $basePath; ?>#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="<?php echo $basePath; ?>#donate">Donate</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="<?php echo $basePath; ?>#volunteers">Volunteers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="<?php echo $basePath; ?>#contact">Contact</a>
                    </li>
                </ul>
                
                <!-- Action CTA Buttons -->
                <div class="nav-actions d-flex align-items-center gap-2 mt-3 mt-lg-0">
                    <?php if ($isLoggedIn): ?>
                        <a href="<?php echo getDashboardUrl($user['role'], $basePath); ?>" class="btn btn-outline-primary rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="fa-solid fa-gauge-high me-1"></i> Dashboard
                        </a>
                        <a href="<?php echo $basePath; ?>logout" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $basePath; ?>#donate" class="btn btn-success rounded-pill px-4 fw-semibold text-white shadow-sm hover-lift">
                            <i class="fa-solid fa-heart me-1"></i> Donate
                        </a>
                        <a href="<?php echo $basePath; ?>login" class="btn btn-outline-primary rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="fa-solid fa-user me-1"></i> Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>

