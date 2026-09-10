<?php
require_once __DIR__ . '/../config/database.php';

class User {
    public static function findByEmail($email) {
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }
}