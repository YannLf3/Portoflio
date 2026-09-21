<?php
require_once __DIR__ . '/../config/database.php';

class Setting
{

    // Récupère tous les paramètres et les formate en tableau associatif
    public static function getAll(): array
    {
        $db = getPDOConnection();
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        $results = $stmt->fetchAll();

        $settings = [];
        foreach ($results as $row) {
            $val = $row['setting_value'];
            // Si la chaîne ressemble à un tableau JSON (commence par '[' ou '{'), on la décode
            if (is_string($val) && (str_starts_with($val, '[') || str_starts_with($val, '{'))) {
                $decoded = json_decode($val, true);
                $settings[$row['setting_key']] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $val;
            } else {
                $settings[$row['setting_key']] = $val;
            }
        }
        return $settings;
    }

    // Met à jour ou insère les paramètres envoyés par le formulaire
    public static function updateAll(array $data): void
    {
        $db = getPDOConnection();

        // On utilise INSERT ... ON DUPLICATE KEY UPDATE pour créer la clé si elle n'existe pas, ou la mettre à jour
        $stmt = $db->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES (:key, :value) 
            ON DUPLICATE KEY UPDATE setting_value = :value
        ");

        foreach ($data as $key => $value) {
            // Si la valeur est un tableau (ex: Projets, Parcours), on la transforme en texte JSON
            if (is_array($value)) {
                $value = json_encode(array_values($value), JSON_UNESCAPED_UNICODE);
            }

            $stmt->execute([
                'key' => $key,
                'value' => (string)$value
            ]);
        }
    }
}