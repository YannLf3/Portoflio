<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Adresse IP du visiteur et sel secret servant à la hacher.
 *
 * Derrière un proxy ou un CDN, REMOTE_ADDR contient l'adresse du proxy : tous les visiteurs partageraient
 * alors la même limite de débit. Si le .env définit TRUSTED_IP_HEADER (ex : HTTP_CF_CONNECTING_IP ou
 * HTTP_X_FORWARDED_FOR), cet en-tête est lu. Sans cette variable, seul REMOTE_ADDR est cru : un en-tête
 * envoyé par le client lui-même est falsifiable, il ne doit jamais être lu sans que le proxy le réécrive.
 */
final class ClientIp
{
    /**
     * Adresse IP du visiteur.
     *
     * @return string Adresse IPv4 ou IPv6 valide, ou chaîne vide si elle est introuvable.
     */
    public static function get(): string
    {
        loadEnvFile(ENV_FILE);

        $remote = (string)($_SERVER['REMOTE_ADDR'] ?? '');
        $header = (string)($_ENV['TRUSTED_IP_HEADER'] ?? '');

        // Nom de variable $_SERVER uniquement (HTTP_XXX) : aucune autre valeur n'est lue
        if (preg_match('/^HTTP_[A-Z0-9_]+$/', $header) !== 1 || !isset($_SERVER[$header])) {
            return $remote;
        }

        // X-Forwarded-For : « client, proxy1, proxy2 ». Le DERNIER élément est celui ajouté par notre proxy,
        // les précédents peuvent être forgés par le client.
        $parts = explode(',', (string)$_SERVER[$header]);
        $candidate = trim($parts[array_key_last($parts)]);

        return filter_var($candidate, FILTER_VALIDATE_IP) !== false ? $candidate : $remote;
    }

    /**
     * Sel secret des empreintes anonymes : variable ANALYTICS_SALT du .env, sinon dérivé des identifiants de base
     * (secrets absents du dépôt).
     *
     * @return string
     */
    public static function salt(): string
    {
        loadEnvFile(ENV_FILE);

        return (string)($_ENV['ANALYTICS_SALT'] ?? hash('sha256', ($_ENV['DB_NAME'] ?? '') . ($_ENV['DB_USER'] ?? '') . ($_ENV['DB_PASS'] ?? '')));
    }
}
