<?php
namespace Backend\Controllers;

class AuthController {
    
    // Developer Demo Credentials
    private static $mockUsers = [
        [
            'id' => 1,
            'email' => 'superadmin@ngo.org',
            'name' => 'Sarah Connor',
            'password' => 'admin123', // In database, this would be hashed (e.g. password_hash)
            'role' => 'super_admin'
        ],
        [
            'id' => 2,
            'email' => 'ngoadmin@ngo.org',
            'name' => 'Michael Scott',
            'password' => 'ngo123',
            'role' => 'ngo_admin'
        ],
        [
            'id' => 3,
            'email' => 'volunteer@ngo.org',
            'name' => 'Peter Parker',
            'password' => 'volunteer123',
            'role' => 'volunteer'
        ],
        [
            'id' => 4,
            'email' => 'donor@ngo.org',
            'name' => 'Bruce Wayne',
            'password' => 'donor123',
            'role' => 'donor'
        ],
        [
            'id' => 5,
            'email' => 'coordinator@ngo.org',
            'name' => 'Clark Kent',
            'password' => 'coord123',
            'role' => 'event_coordinator'
        ]
    ];

    /**
     * Authenticates user against MySQL DB or fallback mock array
     */
    public static function login($email, $password) {
        $email = trim(strtolower($email));
        
        // 1. Try to authenticate via Database if connection is alive
        $dbUser = self::authenticateViaDatabase($email, $password);
        if ($dbUser !== null) {
            return $dbUser; // Found in database!
        }
        
        // 2. Fallback to Mock Developer Credentials if DB fails/is empty
        foreach (self::$mockUsers as $mockUser) {
            if ($mockUser['email'] === $email && $mockUser['password'] === $password) {
                return [
                    'id' => $mockUser['id'],
                    'email' => $mockUser['email'],
                    'name' => $mockUser['name'],
                    'role' => $mockUser['role']
                ];
            }
        }
        
        return false;
    }

    /**
     * Validates Username/Email for password recovery
     */
    public static function validateResetRequest($identity) {
        $identity = trim(strtolower($identity));
        
        // Check DB first
        $dbValid = self::checkResetIdentityViaDatabase($identity);
        if ($dbValid) {
            return true;
        }

        // Fallback to mock checks
        foreach (self::$mockUsers as $mockUser) {
            if ($mockUser['email'] === $identity) {
                return true;
            }
        }
        return false;
    }

    /**
     * Helper to authenticate via DB (Ready for integration)
     */
    private static function authenticateViaDatabase($email, $password) {
        $dbConfigPath = __DIR__ . '/../config/database.php';
        if (!file_exists($dbConfigPath)) {
            return null;
        }
        
        try {
            // Establish local connection variable without outputting failures
            include $dbConfigPath;
            if (!isset($conn)) {
                return null;
            }
            
            // Check if users table exists
            $tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
            if ($tableCheck->rowCount() === 0) {
                return null;
            }
            
            // Query user
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($user) {
                // Verify password (supports plain text for mock or password_verify for production hashed passwords)
                if (password_verify($password, $user['password']) || $password === $user['password']) {
                    return [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'name' => $user['name'] ?? 'Database User',
                        'role' => $user['role']
                    ];
                }
            }
        } catch (\Exception $e) {
            // Silence exceptions to gracefully trigger mock fallbacks for front-end testing
        }
        
        return null;
    }

    /**
     * Helper to check user existence in DB
     */
    private static function checkResetIdentityViaDatabase($identity) {
        $dbConfigPath = __DIR__ . '/../config/database.php';
        if (!file_exists($dbConfigPath)) {
            return false;
        }
        
        try {
            include $dbConfigPath;
            if (!isset($conn)) {
                return false;
            }
            
            $tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
            if ($tableCheck->rowCount() === 0) {
                return false;
            }
            
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = :identity OR username = :identity LIMIT 1");
            $stmt->execute(['identity' => $identity]);
            return $stmt->fetch() !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
