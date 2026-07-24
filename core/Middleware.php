<?php
/**
 * Middleware for route protection and role checks
 */
require_once __DIR__ . '/Session.php';

class Middleware {
    
    /**
     * Centralized dashboard routing helper based on role ID
     */
    public static function getDashboardUrl($roleId) {
        switch ((int)$roleId) {
            case 1: return APP_URL . "/admin_dashboard.php";
            case 2: return APP_URL . "/ngo_dashboard.php";
            case 3: return APP_URL . "/donor_dashboard.php";
            case 4: return APP_URL . "/volunteer_dashboard.php";
            case 5: return APP_URL . "/coordinator_dashboard.php";
            default: return APP_URL . "/index.php"; // Fallback
        }
    }

    /**
     * Protects pages from unauthorized (unauthenticated) users
     */
    public static function auth() {
        self::startSessionSafe();
        self::preventCaching();
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . APP_URL . "/login.php");
            exit();
        }
    }

    private static function startSessionSafe() {
        if (session_status() === PHP_SESSION_NONE) {
            Session::start();
        }
    }

    /**
     * Prevents browser from caching protected pages
     */
    private static function preventCaching() {
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    /**
     * Protects guest pages (login/register) from authenticated users
     */
    public static function guest() {
        self::startSessionSafe();
        if (isset($_SESSION['user_id'])) {
            // Redirect logged-in users away from guest pages to THEIR OWN dashboard
            $roleId = $_SESSION['role_id'] ?? 0;
            header("Location: " . self::getDashboardUrl($roleId));
            exit();
        }
    }

    /**
     * Strict Role-Based Access Control (RBAC) using Role IDs
     * Redirects to the user's correct dashboard if they try to access another role's dashboard.
     */
    public static function role($allowedRoleIds) {
        self::startSessionSafe();
        self::preventCaching();
        
        // Exact requirement: Every dashboard must verify before checking the role.
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . APP_URL . "/login.php");
            exit();
        }
        
        $currentRoleId = (int)($_SESSION['role_id'] ?? 0);
        
        if (!in_array($currentRoleId, $allowedRoleIds)) {
            // User does not have permission. Route them back to their own dashboard.
            header("Location: " . self::getDashboardUrl($currentRoleId));
            exit();
        }
    }
}
