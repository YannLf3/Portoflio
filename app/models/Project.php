<?php
require_once __DIR__ . '/../config/database.php';

class Project {
    public static function getAll(): array {
    $db = getPDOConnection();
    // Utiliser prepare() même sans paramètre pour respecter les standards de sécurité
    $stmt = $db->prepare("SELECT * FROM projects ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}
}