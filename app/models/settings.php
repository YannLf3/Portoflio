<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Contenus éditables du site, stockés en clé / valeur dans la table `settings`.
 * Les listes (parcours, projets…) sont sérialisées en JSON.
 *
 * Sécurité : updateAll() n'enregistre QUE les clés et sous-champs déclarés dans
 * SCALAR_KEYS / LIST_SCHEMAS (liste blanche). Un champ ajouté à la main dans la
 * requête (mass assignment) est ignoré au lieu de créer ou d'écraser un réglage.
 */
class Setting
{
    /** Réglages texte simples (une valeur par clé). */
    private const SCALAR_KEYS = [
        'hero_name',
        'hero_dev',
        'hero_photo',
        'hero_avatar',
        'dispo_text',
        'location',
        'status',
        'parcours_intro',
        'skills_tech',
        'skills_tools',
        'interests',
        'contact_title',
        'contact_subtitle',
        'project_cat_1',
        'project_cat_2',
        'project_cat_3',
        // Comparateur avant / après (section Post-traitement du mode Photo)
        'compare_before',
        'compare_after',
        'compare_title',
        'compare_caption',
        'compare_tools',
    ];

    /** Réglages en liste : clé → sous-champs autorisés pour chaque ligne. */
    private const LIST_SCHEMAS = [
        'parcours' => ['period', 'type', 'title', 'detail'],
        'langues' => ['name', 'level', 'percent'],
        'projects' => ['id', 'title', 'meta', 'description', 'image', 'link', 'category', 'tags'],
        'photos' => ['url', 'unsplash_id', 'legend', 'alt', 'format'],
        'contacts' => ['label', 'value', 'href'],
        'snippets' => ['title', 'category', 'badge', 'html', 'css', 'js'],
        'articles' => ['title', 'meta', 'content'],
    ];

    /**
     * Lit tous les réglages ; les valeurs JSON valides sont décodées en tableaux.
     *
     * @return array<string, mixed>
     */
    public static function getAll(): array
    {
        $db = getPDOConnection();
        $stmt = $db->query('SELECT setting_key, setting_value FROM settings');
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

    /**
     * Ne conserve que les clés et sous-champs autorisés, convertis en chaînes.
     * Seules les clés PRÉSENTES dans la saisie sont retournées : une clé absente
     * n'est donc jamais vidée par erreur.
     *
     * @param array<string, mixed> $input Données brutes du formulaire.
     * @return array<string, string|list<array<string, string>>> Données sûres à enregistrer.
     */
    public static function filterInput(array $input): array
    {
        $clean = [];

        foreach (self::SCALAR_KEYS as $key) {
            if (array_key_exists($key, $input) && is_scalar($input[$key])) {
                $clean[$key] = (string)$input[$key];
            }
        }

        foreach (self::LIST_SCHEMAS as $key => $fields) {
            if (!array_key_exists($key, $input) || !is_array($input[$key])) {
                continue;
            }

            $rows = [];
            foreach ($input[$key] as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $item = [];
                foreach ($fields as $field) {
                    // Sous-champ absent ou non scalaire (tableau injecté) → chaîne vide
                    $value = $row[$field] ?? '';
                    $item[$field] = is_scalar($value) ? (string)$value : '';
                }
                $rows[] = $item;
            }
            // Liste ré-indexée (0, 1, 2…) : les lignes supprimées dans l'admin ne laissent pas de « trous »
            $clean[$key] = $rows;
        }

        return $clean;
    }

    /**
     * Enregistre les réglages autorisés en une seule transaction (tout ou rien).
     *
     * @param array<string, mixed> $data Données du formulaire (filtrées ici par liste blanche).
     * @return void
     * @throws PDOException|JsonException En cas d'échec, la transaction est annulée puis l'erreur relancée.
     */
    public static function updateAll(array $data): void
    {
        $clean = self::filterInput($data);
        if ($clean === []) {
            return;
        }

        $db = getPDOConnection();

        // Marqueur distinct (:value_update) pour la partie UPDATE : PDO n'autorise pas deux fois le même nom
        $stmt = $db->prepare('
            INSERT INTO settings (setting_key, setting_value)
            VALUES (:key, :value)
            ON DUPLICATE KEY UPDATE setting_value = :value_update
        ');

        $db->beginTransaction();
        try {
            foreach ($clean as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
                }
                $stmt->execute([
                    'key' => $key,
                    'value' => $value,
                    'value_update' => $value,
                ]);
            }
            $db->commit();
        } catch (Throwable $e) {
            // Aucune écriture partielle : la BDD reste dans l'état précédent
            $db->rollBack();
            throw $e;
        }
    }
}
