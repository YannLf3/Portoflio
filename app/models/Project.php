<?php
require_once __DIR__ . '/../config/database.php';

class Project {
    public static function getAll() {
        $pdo = getPDOConnection();
        $stmt = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
}