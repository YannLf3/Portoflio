<?php
declare(strict_types=1);

/**
 * Socle commun des contrôleurs : rendu des vues et préparation des données d'affichage.
 * Les vues ne reçoivent que des variables prêtes à afficher (aucun calcul dans les templates).
 */
require_once __DIR__ . '/../models/Project.php';

abstract class Controller
{
    /** Session administrateur : clé portée uniquement côté serveur ($_SESSION), jamais dans un cookie. */
    protected const SESSION_ADMIN = 'admin_logged_in';

    /**
     * Affiche une vue de app/views/ en lui transmettant ses variables.
     *
     * @param string               $view Chemin de la vue sans extension (ex : 'front/home').
     * @param array<string, mixed> $data Variables du template (ex : ['title' => '…'] devient $title).
     * @return void
     */
    protected function render(string $view, array $data = []): void
    {
        // Chaque clé devient une variable locale du template ; EXTR_SKIP protège $view, $data et $this
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../views/' . $view . '.php';
    }

    /**
     * Indique si la session courante appartient à l'administrateur connecté.
     *
     * @return bool
     */
    protected function isAdmin(): bool
    {
        return isset($_SESSION[self::SESSION_ADMIN]) && $_SESSION[self::SESSION_ADMIN] === true;
    }

    /**
     * Redirige vers une URL interne puis termine le script.
     *
     * @param string $location Chemin de destination (ex : '/admin').
     * @return never
     */
    protected function redirect(string $location): never
    {
        header('Location: ' . $location);
        exit;
    }

    /**
     * Découpe une liste « a, b, c » en éléments débarrassés de leurs espaces ; les éléments vides sont retirés.
     *
     * @param mixed $raw Chaîne séparée par des virgules.
     * @return array<int, string>
     */
    protected static function splitList(mixed $raw): array
    {
        return array_filter(array_map('trim', explode(',', (string)$raw)));
    }

    /**
     * Complète une ligne de liste : chaque champ absent (ou null) prend sa valeur de repli.
     *
     * @param array<string, mixed> $row       Ligne issue de la BDD.
     * @param array<string, mixed> $fallbacks Champ → valeur de repli.
     * @return array<string, mixed>
     */
    protected static function fillRow(array $row, array $fallbacks): array
    {
        return array_merge($row, array_combine(
            array_keys($fallbacks),
            array_map(
                static fn(string $field, mixed $fallback): mixed => $row[$field] ?? $fallback,
                array_keys($fallbacks),
                $fallbacks
            )
        ));
    }

    /**
     * Ne garde que les lignes valides (tableaux) d'une liste issue de la BDD.
     *
     * @param mixed $list Liste décodée (ou valeur invalide).
     * @return array<int|string, array<string, mixed>>
     */
    protected static function rows(mixed $list): array
    {
        return is_array($list) ? array_filter($list, 'is_array') : [];
    }

    /**
     * Transforme un texte en identifiant d'URL : minuscules, accents retirés, tirets entre les mots.
     * Exemple : « Café de l'Été » devient « cafe-de-l-ete ».
     *
     * @param string $text Texte à convertir.
     * @return string Slug (chaîne vide si le texte ne contient aucun caractère exploitable).
     */
    protected static function slugify(string $text): string
    {
        $ascii = strtr(mb_strtolower($text), [
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ö' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'œ' => 'oe', 'æ' => 'ae', 'ÿ' => 'y',
        ]);

        // Toute suite de caractères hors a-z / 0-9 devient un seul tiret ; tirets de bord retirés
        return trim((string)preg_replace('/[^a-z0-9]+/', '-', $ascii), '-');
    }

    /**
     * Adresse interne de la fiche d'un projet : /projets/{id}-{slug du titre}.
     *
     * @param Project $project Projet enregistré (identifiant non nul).
     * @return string Chemin absolu.
     */
    protected static function projectUrl(Project $project): string
    {
        return '/projets/' . (int)$project->getId() . '-' . (self::slugify($project->getTitle()) ?: 'projet');
    }
}
