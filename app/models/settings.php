<?php
require_once __DIR__ . '/../config/database.php';

class Setting
{

    public static function getAll(): array
    {
        $db = getPDOConnection();
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        $results = $stmt->fetchAll();

        $settings = [];
        foreach ($results as $row) {
            $key = $row['setting_key'];
            $val = $row['setting_value'];

            // Décodage JSON si la valeur commence par '[' ou '{'
            if (is_string($val) && (str_starts_with(trim($val), '[') || str_starts_with(trim($val), '{'))) {
                $decoded = json_decode($val, true);
                $settings[$key] = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : $val;
            } else {
                $settings[$key] = $val;
            }
        }
        return $settings;
    }

    public static function updateAll(array $data): void
    {
        $db = getPDOConnection();

        $stmt = $db->prepare("
        INSERT INTO settings (setting_key, setting_value) 
        VALUES (:key, :value) 
        ON DUPLICATE KEY UPDATE setting_value = :value
    ");

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Re-indexation du tableau + encodage propre du JSON pour la BDD
                $value = json_encode(array_values($value), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            $stmt->execute([
                'key' => $key,
                'value' => (string)$value
            ]);
        }
    }
}