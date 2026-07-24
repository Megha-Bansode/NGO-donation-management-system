<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Session.php';

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Logger.php';

if (isset($_SESSION['user_id'])) {
    Logger::logActivity(getDatabase(), $_SESSION['user_id'], $_SESSION['role_id'] ?? null, 'Authentication', 'Logout', 'User logged out.');
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

header("Location: login.php");
exit;
