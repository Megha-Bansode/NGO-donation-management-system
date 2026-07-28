<?php
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Logger.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        Session::start();
        $this->userModel = new User();
    }

    public function handleLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return null;
        
        if (!Security::verifyCSRF($_POST['csrf_token'] ?? '')) {
            return ["Invalid security token."];
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        $errors = [];

        if (empty($email)) {
            $errors[] = "Email is required.";
        }
        
        if (empty($password)) {
            $errors[] = "Password is required.";
        }

        if (!empty($errors)) {
            return $errors;
        }

        if (!Validator::email($email)) {
            return ["Invalid email or password."]; // Matches user request for invalid credentials
        }
        
        $user = $this->userModel->findByEmail($email);
        
        if ($user && Security::verifyPassword($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                return ["Your account is currently " . $user['status'] . "."];
            }
            Session::login($user, $remember);
            
            // Log Login Activity
            Logger::logActivity(getDatabase(), $user['id'], $user['role_id'], 'Authentication', 'Login', 'User successfully logged in.');
            
            // Redirect to their specific dashboard based on role ID
            require_once __DIR__ . '/../core/Middleware.php';
            header("Location: " . Middleware::getDashboardUrl($user['role_id']));
            exit();
        }
        
        return ["Invalid email or password."];
    }

    public function handleRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        if (!Security::verifyCSRF($_POST['csrf_token'] ?? '')) {
            return "Invalid security token.";
        }

        $fullName = Security::sanitize($_POST['full_name'] ?? '');
        $email = Security::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleName = Security::sanitize($_POST['role'] ?? '');

        // Strict role enforcement
        if (!in_array($roleName, ['Donor', 'Volunteer'])) {
            return "Invalid role selected.";
        }

        if (!Validator::email($email)) return "Invalid email.";
        if (!Validator::stringRequired($fullName, 2, 100)) return "Name is required (2-100 chars).";
        if (!Validator::strongPassword($password)) return "Password must be at least 8 characters long, contain an uppercase, lowercase, and a number.";

        // Check if email exists
        if ($this->userModel->findByEmail($email)) {
            return "Email is already registered.";
        }

        $roleId = $this->userModel->getRoleByName($roleName);
        if (!$roleId) return "System error: Role not configured.";

        $this->userModel->create([
            'role_id' => $roleId,
            'full_name' => $fullName,
            'email' => $email,
            'password' => Security::hashPassword($password)
        ]);

        $pdo = getDatabase();
        $userId = $pdo->lastInsertId();
        
        // Log Registration
        Logger::logActivity($pdo, $userId, $roleId, 'Authentication', 'Register', 'New ' . $roleName . ' registration.');

        return ["success" => true, "message" => "Registration successful. You can now login."];
    }
}
