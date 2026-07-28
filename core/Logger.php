<?php
/**
 * Global Activity Logging Helper
 */
class Logger {
    public static function logActivity($pdo, $userId, $roleId, $module, $action, $description = null) {
        if (!$pdo) {
            return false;
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO activity_logs 
                (user_id, role_id, module, action, description, ip_address, browser, device, operating_system, user_agent, created_at)
                VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            
            // Simple parsing for browser/os
            $browser = 'Unknown';
            if (strpos($userAgent, 'Firefox') !== false) $browser = 'Firefox';
            elseif (strpos($userAgent, 'Chrome') !== false) $browser = 'Chrome';
            elseif (strpos($userAgent, 'Safari') !== false) $browser = 'Safari';
            elseif (strpos($userAgent, 'Edge') !== false) $browser = 'Edge';

            $os = 'Unknown';
            if (strpos($userAgent, 'Windows') !== false) $os = 'Windows';
            elseif (strpos($userAgent, 'Mac') !== false) $os = 'MacOS';
            elseif (strpos($userAgent, 'Linux') !== false) $os = 'Linux';
            elseif (strpos($userAgent, 'Android') !== false) $os = 'Android';
            elseif (strpos($userAgent, 'iOS') !== false) $os = 'iOS';

            $device = (strpos($userAgent, 'Mobi') !== false) ? 'Mobile' : 'Desktop';

            $stmt->execute([
                $userId,
                $roleId,
                $module,
                $action,
                $description,
                $ip,
                $browser,
                $device,
                $os,
                $userAgent
            ]);

            return true;
        } catch (PDOException $e) {
            // Silently fail logging in production rather than breaking the application
            error_log("Activity Logging Failed: " . $e->getMessage());
            return false;
        }
    }
}
