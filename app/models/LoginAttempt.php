<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Journal des échecs de connexion (table `login_attempts`), indexé par adresse IP.
 * Sert d'anti-brute-force : au-delà de MAX_FAILURES échecs dans la fenêtre WINDOW_MINUTES, la connexion est refusée.
 * Le compteur est en BDD (et non en session) : un attaquant ne peut pas le remettre à zéro en supprimant son cookie.
 */
final class LoginAttempt
{
    /** Nombre d'échecs tolérés dans la fenêtre avant blocage. */
    public const MAX_FAILURES = 5;

    /** Durée (en minutes) de la fenêtre de comptage, et donc du blocage. */
    public const WINDOW_MINUTES = 15;

    /**
     * Indique si cette adresse IP a atteint la limite d'échecs récents.
     *
     * @param string $ip Adresse IP du visiteur.
     * @return bool true si la connexion doit être refusée.
     */
    public static function isBlocked(string $ip): bool
    {
        // Le calcul de la fenêtre est fait par MySQL (NOW()) : une seule horloge, pas de décalage PHP/SQL
        $stmt = getPDOConnection()->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE ip = :ip AND attempted_at > (NOW() - INTERVAL :minutes MINUTE)'
        );
        $stmt->execute(['ip' => $ip, 'minutes' => self::WINDOW_MINUTES]);

        return (int)$stmt->fetchColumn() >= self::MAX_FAILURES;
    }

    /**
     * Enregistre un échec de connexion et purge les entrées devenues inutiles (table toujours petite).
     *
     * @param string $ip Adresse IP du visiteur.
     * @return void
     */
    public static function recordFailure(string $ip): void
    {
        $db = getPDOConnection();

        $db->prepare('INSERT INTO login_attempts (ip) VALUES (:ip)')->execute(['ip' => $ip]);

        $db->prepare('DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL :minutes MINUTE)')
            ->execute(['minutes' => self::WINDOW_MINUTES]);
    }

    /**
     * Efface les échecs d'une adresse IP après une connexion réussie.
     *
     * @param string $ip Adresse IP du visiteur.
     * @return void
     */
    public static function clear(string $ip): void
    {
        getPDOConnection()->prepare('DELETE FROM login_attempts WHERE ip = :ip')->execute(['ip' => $ip]);
    }
}
