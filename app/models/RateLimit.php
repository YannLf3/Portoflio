<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/ClientIp.php';

/**
 * Limitation de débit par client et par usage (table `rate_limits`).
 *
 * Le compteur est en BDD, et non en session : un robot qui ne renvoie pas son cookie obtiendrait une
 * session neuve à chaque requête, donc aucune limite. Le client est identifié par une empreinte HMAC
 * de son IP : l'adresse n'est jamais stockée en clair.
 */
final class RateLimit
{
    /** Durée de conservation des lignes (heures) : la plus longue fenêtre utilisée est de 1 h. */
    private const RETENTION_HOURS = 24;

    /**
     * Nombre d'appels d'un client dans la fenêtre donnée.
     *
     * @param string $bucket  Usage limité ('contact', 'track'), 20 caractères maximum.
     * @param string $ip      Adresse IP du client (voir ClientIp::get()).
     * @param int    $seconds Taille de la fenêtre glissante.
     * @return int
     */
    public static function count(string $bucket, string $ip, int $seconds): int
    {
        // L'horloge est celle de MySQL (NOW()) : une seule source de temps, pas de décalage PHP/SQL
        $stmt = getPDOConnection()->prepare(
            'SELECT COUNT(*) FROM rate_limits
             WHERE bucket = :bucket AND client = :client AND hit_at > (NOW() - INTERVAL :seconds SECOND)'
        );
        $stmt->execute(['bucket' => $bucket, 'client' => self::clientKey($ip), 'seconds' => $seconds]);

        return (int)$stmt->fetchColumn();
    }

    /**
     * Enregistre un appel du client, puis purge occasionnellement les lignes périmées.
     *
     * @param string $bucket Usage limité.
     * @param string $ip     Adresse IP du client.
     * @return void
     */
    public static function record(string $bucket, string $ip): void
    {
        $db = getPDOConnection();
        $db->prepare('INSERT INTO rate_limits (bucket, client) VALUES (:bucket, :client)')
            ->execute(['bucket' => $bucket, 'client' => self::clientKey($ip)]);

        // Purge occasionnelle (1 appel sur 100) : la table reste petite sans tâche planifiée
        if (random_int(1, 100) === 1) {
            $db->prepare('DELETE FROM rate_limits WHERE hit_at < (NOW() - INTERVAL :hours HOUR)')
                ->execute(['hours' => self::RETENTION_HOURS]);
        }
    }

    /**
     * Compte l'appel s'il reste de la marge, sinon le refuse.
     *
     * @param string $bucket  Usage limité.
     * @param string $ip      Adresse IP du client.
     * @param int    $max     Nombre d'appels autorisés dans la fenêtre.
     * @param int    $seconds Taille de la fenêtre glissante.
     * @return bool true si l'appel est autorisé (et enregistré), false si la limite est atteinte.
     */
    public static function attempt(string $bucket, string $ip, int $max, int $seconds): bool
    {
        if (self::count($bucket, $ip, $seconds) >= $max) {
            return false;
        }
        self::record($bucket, $ip);

        return true;
    }

    /**
     * Empreinte du client : 40 premiers caractères d'un HMAC de l'IP avec le sel secret.
     *
     * @param string $ip Adresse IP du client.
     * @return string 40 caractères hexadécimaux (colonne CHAR(40)).
     */
    private static function clientKey(string $ip): string
    {
        return substr(hash_hmac('sha256', $ip, ClientIp::salt()), 0, 40);
    }
}
