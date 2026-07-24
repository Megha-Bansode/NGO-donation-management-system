<?php
/**
 * Input validation helpers
 */
class Validator {
    public static function email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function stringRequired($string, $min = 1, $max = 255) {
        $length = strlen(trim($string));
        return $length >= $min && $length <= $max;
    }

    public static function match($val1, $val2) {
        return $val1 === $val2;
    }

    public static function strongPassword($password) {
        // At least 8 chars, 1 uppercase, 1 lowercase, 1 number
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
    }
}
