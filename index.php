<?php
// Entry Point
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper to get base path for links and redirects
$scriptName = $_SERVER['SCRIPT_NAME']; // e.g., /NGO system landing and login/index.php
$baseDir = dirname($scriptName); // e.g., /NGO system landing and login
$baseDir = str_replace('\\', '/', $baseDir);
$basePath = rtrim($baseDir, '/') . '/';

// Parse current request path
$requestUri = $_SERVER['REQUEST_URI'];
// Remove query string
$parsedUrl = parse_url($requestUri);
$path = urldecode($parsedUrl['path']);

// Remove base path to get relative route
if (strpos($path, $basePath) === 0) {
    $path = '/' . substr($path, strlen($basePath));
}

// Normalize path (remove duplicate slashes, trailing slashes)
$path = '/' . trim($path, '/');

// Define routes
switch ($path) {
    case '/':
    case '/landing':
    case '/landing-page':
        require_once __DIR__ . '/modules/landing-page/index.php';
        break;

    case '/login':
        require_once __DIR__ . '/modules/role-based-login/login.php';
        break;

    case '/forgot-password':
        require_once __DIR__ . '/modules/role-based-login/forgot-password.php';
        break;

    case '/logout':
        unset($_SESSION['user']);
        session_destroy();
        header('Location: ' . $basePath . 'login');
        exit;

    case '/dashboard/super-admin':
        checkRole('super_admin', $basePath);
        require_once __DIR__ . '/modules/super-admin-dashboard/dashboard.php';
        break;

    case '/dashboard/ngo-admin':
        checkRole('ngo_admin', $basePath);
        require_once __DIR__ . '/modules/ngo-admin-dashboard/dashboard.php';
        break;

    case '/dashboard/volunteer':
        checkRole('volunteer', $basePath);
        require_once __DIR__ . '/modules/volunteer-dashboard/dashboard.php';
        break;

    case '/dashboard/donor':
        checkRole('donor', $basePath);
        require_once __DIR__ . '/modules/donor-dashboard/dashboard.php';
        break;

    case '/dashboard/event-coordinator':
        checkRole('event_coordinator', $basePath);
        require_once __DIR__ . '/modules/event-coordinator-dashboard/dashboard.php';
        break;

    default:
        // Handle API or assets or 404
        if (strpos($path, '/api/') === 0) {
            require_once __DIR__ . '/api/api.php';
        } else {
            // Default fallback
            require_once __DIR__ . '/modules/landing-page/index.php';
        }
        break;
}

// Helper to check user authorization
function checkRole($role, $basePath) {
    if (!isset($_SESSION['user'])) {
        header('Location: ' . $basePath . 'login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    if ($_SESSION['user']['role'] !== $role) {
        // Redirect to their own dashboard if they have a different role
        $ownDashboard = getDashboardPath($_SESSION['user']['role']);
        header('Location: ' . $basePath . $ownDashboard);
        exit;
    }
}

function getDashboardPath($role) {
    switch ($role) {
        case 'super_admin':
            return 'dashboard/super-admin';
        case 'ngo_admin':
            return 'dashboard/ngo-admin';
        case 'volunteer':
            return 'dashboard/volunteer';
        case 'donor':
            return 'dashboard/donor';
        case 'event_coordinator':
            return 'dashboard/event-coordinator';
        default:
            return '';
    }
}
