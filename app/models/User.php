<?php
require_once __DIR__ . '/../config/database.php';

class User
{
    public static function findByEmail(string $email)
    {
        $db = getPDOConnection();
        // Assure-toi que la colonne s'appelle bien 'email' dans ta table MySQL
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }
}