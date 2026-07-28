<?php
/**
 * User Model - Handles DB queries for Authentication
 */
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = getDatabase();
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.email = :email 
            LIMIT 1
        ");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.id = :id 
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getRoleByName($name) {
        $stmt = $this->db->prepare("SELECT id FROM roles WHERE name = :name LIMIT 1");
        $stmt->execute(['name' => $name]);
        return $stmt->fetchColumn();
    }

    public function create($data) {
        $sql = "INSERT INTO users (role_id, full_name, email, password, status) 
                VALUES (:role_id, :full_name, :email, :password, 'active')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'role_id' => $data['role_id'],
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'password' => $data['password']
        ]);
        return $this->db->lastInsertId();
    }
    
    public function updatePassword($id, $newPasswordHash) {
        $stmt = $this->db->prepare("UPDATE users SET password = :pwd WHERE id = :id");
        return $stmt->execute(['pwd' => $newPasswordHash, 'id' => $id]);
    }
}
