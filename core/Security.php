<?php
/**
 * Security and Hashing utilities
 */
class Security {
    
    // Hash password
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // Verify password
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    // Generate CSRF Token
    public static function generateCSRF() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Verify CSRF Token
    public static function verifyCSRF($token) {
        if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
            return true;
        }
        return false;
    }

    // Create a signed token for Remember Me (No DB modification needed)
    public static function generateRememberToken($userId, $passwordHash) {
        $data = $userId . '|' . $passwordHash;
        $signature = hash_hmac('sha256', $data, APP_SECRET);
        return base64_encode($data . '|' . $signature);
    }

    // Verify the signed token
    public static function verifyRememberToken($token) {
        $decoded = base64_decode($token);
        if (!$decoded) return false;
        
        $parts = explode('|', $decoded);
        if (count($parts) !== 3) return false;
        
        $userId = $parts[0];
        $passwordHash = $parts[1];
        $signature = $parts[2];
        
        $data = $userId . '|' . $passwordHash;
        $expectedSignature = hash_hmac('sha256', $data, APP_SECRET);
        
        if (hash_equals($expectedSignature, $signature)) {
            return ['user_id' => $userId, 'password_hash' => $passwordHash];
        }
        return false;
    }
    
    // Sanitize output
    public static function sanitize($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
