<?php
declare(strict_types=1);

/**
 * Protection CSRF (Cross-Site Request Forgery) par jeton synchronisé en session.
 *
 * Principe : un jeton aléatoire est stocké côté serveur (session PHP) et inséré
 * dans chaque formulaire sensible. Un site tiers peut forcer le navigateur à
 * soumettre un formulaire (le cookie de session part avec), mais il ne peut pas
 * LIRE le jeton : toute requête sans jeton valide est donc refusée.
 *
 * Prérequis : session_start() déjà appelé (public/index.php).
 */
final class Csrf
{
    /** Clé du jeton dans $_SESSION et nom du champ caché des formulaires. */
    public const FIELD = 'csrf_token';

    /** 32 octets aléatoires = 256 bits d'entropie (64 caractères hexadécimaux). */
    private const TOKEN_BYTES = 32;

    /**
     * Retourne le jeton de la session, en le créant au premier appel.
     *
     * @return string Jeton hexadécimal.
     */
    public static function token(): string
    {
        if (empty($_SESSION[self::FIELD]) || !is_string($_SESSION[self::FIELD])) {
            $_SESSION[self::FIELD] = bin2hex(random_bytes(self::TOKEN_BYTES));
        }
        return $_SESSION[self::FIELD];
    }

    /**
     * Génère le champ caché à placer dans un formulaire.
     *
     * @return string Balise <input type="hidden"> échappée.
     */
    public static function field(): string
    {
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            self::FIELD,
            htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Vérifie le jeton reçu avec le formulaire.
     * hash_equals() compare en temps constant : aucune information n'est déduite du temps de réponse.
     *
     * @param mixed $token Valeur reçue ($_POST['csrf_token']).
     * @return bool true si le jeton correspond à celui de la session.
     */
    public static function isValid(mixed $token): bool
    {
        $expected = $_SESSION[self::FIELD] ?? null;
        return is_string($expected) && is_string($token) && $expected !== '' && hash_equals($expected, $token);
    }

    /**
     * Renouvelle le jeton (à appeler lors d'un changement de privilège, ex : connexion réussie).
     *
     * @return void
     */
    public static function rotate(): void
    {
        $_SESSION[self::FIELD] = bin2hex(random_bytes(self::TOKEN_BYTES));
    }
}
