<?php
/**
 * Purpose: Razorpay configuration and environment variable loader
 */

// Function to load .env variables if available
if (!function_exists('load_env')) {
    function load_env($filePath) {
        if (file_exists($filePath)) {
            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                    putenv(sprintf('%s=%s', $name, $value));
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }
}

// Load the .env file from the root directory
load_env(__DIR__ . '/../.env');

// Razorpay Configuration
define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID') ?: 'YOUR_RAZORPAY_KEY_ID');
define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET') ?: 'YOUR_RAZORPAY_KEY_SECRET');
define('RAZORPAY_WEBHOOK_SECRET', getenv('RAZORPAY_WEBHOOK_SECRET') ?: 'YOUR_RAZORPAY_WEBHOOK_SECRET');

return [
    'key_id' => RAZORPAY_KEY_ID,
    'key_secret' => RAZORPAY_KEY_SECRET,
    'webhook_secret' => RAZORPAY_WEBHOOK_SECRET
];
