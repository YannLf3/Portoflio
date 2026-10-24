<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/ClientIp.php';

/**
 * Mesure d'audience maison (table `analytics_events`), alimentée uniquement après consentement du visiteur.
 *
 * Vie privée : aucune adresse IP ni identifiant persistant n'est stocké. Les visiteurs uniques sont comptés avec
 * une empreinte tronquée de (jour + IP + navigateur + sel secret) : elle change chaque jour, donc impossible
 * de suivre une personne d'un jour à l'autre. Les lignes de plus de 13 mois sont supprimées.
 */
final class AnalyticsEvent
{
    /** Actions que le navigateur a le droit d'envoyer (liste blanche). */
    public const EVENT_NAMES = ['contact_click', 'contact_sent', 'project_open', 'project_link', 'repo_link', 'mode_switch'];

    /** Types d'appareil enregistrés. */
    private const DEVICES = ['mobile', 'tablet', 'desktop'];

    /** Durée de conservation des données (mois). */
    private const RETENTION_MONTHS = 13;

    /** Plages d'affichage autorisées dans le back-office (jours). */
    public const RANGES = [7, 30, 90];

    /**
     * Indique si l'appareil/navigateur annonce un robot (aucune mesure pour eux).
     *
     * @param string $userAgent En-tête User-Agent.
     * @return bool
     */
    public static function isBot(string $userAgent): bool
    {
        return $userAgent === '' || preg_match('/bot|crawl|spider|slurp|headless|lighthouse|preview|curl|wget|python|monitor/i', $userAgent) === 1;
    }

    /**
     * Classe l'appareil d'après le User-Agent.
     *
     * @param string $userAgent En-tête User-Agent.
     * @return string 'mobile', 'tablet' ou 'desktop'.
     */
    public static function deviceFrom(string $userAgent): string
    {
        return match (true) {
            preg_match('/iPad|Tablet/i', $userAgent) === 1 => 'tablet',
            preg_match('/Mobi|Android|iPhone/i', $userAgent) === 1 => 'mobile',
            default => 'desktop',
        };
    }

    /**
     * Empreinte anonyme du jour : 16 premiers caractères d'un HMAC (jour, IP, User-Agent) avec un sel secret.
     *
     * @param string $ip        Adresse IP du visiteur (jamais stockée).
     * @param string $userAgent En-tête User-Agent.
     * @return string 16 caractères hexadécimaux.
     */
    public static function visitorHash(string $ip, string $userAgent): string
    {
        return substr(hash_hmac('sha256', date('Y-m-d') . '|' . $ip . '|' . $userAgent, ClientIp::salt()), 0, 16);
    }

    /**
     * Enregistre une page vue ou une action.
     *
     * @param string $type     'pageview' ou 'event'.
     * @param string $name     Nom de l'action (vide pour une page vue).
     * @param string $path     Chemin de la page (sans paramètres).
     * @param string $referrer Domaine d'origine (vide = accès direct).
     * @param string $device   'mobile', 'tablet' ou 'desktop'.
     * @param string $visitor  Empreinte issue de visitorHash().
     * @return void
     */
    public static function record(string $type, string $name, string $path, string $referrer, string $device, string $visitor): void
    {
        $stmt = getPDOConnection()->prepare('
            INSERT INTO analytics_events (day, type, name, path, referrer, device, visitor)
            VALUES (CURDATE(), :type, :name, :path, :referrer, :device, :visitor)
        ');
        $stmt->execute([
            'type' => $type === 'event' ? 'event' : 'pageview',
            'name' => mb_substr($name, 0, 40),
            'path' => mb_substr($path, 0, 255),
            'referrer' => mb_substr($referrer, 0, 120),
            'device' => in_array($device, self::DEVICES, true) ? $device : 'desktop',
            'visitor' => $visitor,
        ]);
    }

    /**
     * Supprime les données plus anciennes que la durée de conservation.
     *
     * @return void
     */
    public static function purgeOld(): void
    {
        getPDOConnection()
            ->prepare('DELETE FROM analytics_events WHERE day < (CURDATE() - INTERVAL ' . self::RETENTION_MONTHS . ' MONTH)')
            ->execute();
    }

    /**
     * Statistiques de la période pour le back-office.
     *
     * @param int $days Taille de la fenêtre en jours (7, 30 ou 90).
     * @return array<string, mixed> views, visitors, direct, days (zéros inclus), pages, referrers, devices, events.
     */
    public static function summary(int $days): array
    {
        $days = in_array($days, self::RANGES, true) ? $days : 30;

        // Nombre de jours en arrière à inclure (la fenêtre contient aujourd'hui)
        $since = $days - 1;
        $query = static function (string $sql) use ($since): array {
            $stmt = getPDOConnection()->prepare($sql);
            $stmt->bindValue(':since', $since, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        };
        $window = "day >= (CURDATE() - INTERVAL :since DAY)";

        $totals = $query("SELECT COUNT(*) AS views, COUNT(DISTINCT visitor) AS visitors,
                                 SUM(referrer = '') AS direct
                          FROM analytics_events WHERE type = 'pageview' AND $window")[0] ?? [];

        $perDay = array_column(
            $query("SELECT day, COUNT(*) AS views, COUNT(DISTINCT visitor) AS visitors
                    FROM analytics_events WHERE type = 'pageview' AND $window GROUP BY day"),
            null,
            'day'
        );

        // Un point par jour de la fenêtre, y compris les jours sans visite
        $dayList = array_map(
            static function (int $offset) use ($perDay, $since): array {
                $day = date('Y-m-d', strtotime('-' . ($since - $offset) . ' day'));
                return ['day' => $day, 'views' => (int)($perDay[$day]['views'] ?? 0), 'visitors' => (int)($perDay[$day]['visitors'] ?? 0)];
            },
            range(0, $since)
        );

        return [
            'days' => $dayList,
            'views' => (int)($totals['views'] ?? 0),
            'visitors' => (int)($totals['visitors'] ?? 0),
            'direct' => (int)($totals['direct'] ?? 0),
            'pages' => $query("SELECT path AS label, COUNT(*) AS total, COUNT(DISTINCT visitor) AS visitors
                               FROM analytics_events WHERE type = 'pageview' AND $window
                               GROUP BY path ORDER BY total DESC LIMIT 10"),
            'referrers' => $query("SELECT referrer AS label, COUNT(*) AS total
                                   FROM analytics_events WHERE type = 'pageview' AND referrer <> '' AND $window
                                   GROUP BY referrer ORDER BY total DESC LIMIT 10"),
            'devices' => $query("SELECT device AS label, COUNT(*) AS total
                                 FROM analytics_events WHERE type = 'pageview' AND $window
                                 GROUP BY device ORDER BY total DESC"),
            'events' => $query("SELECT name AS label, COUNT(*) AS total
                                FROM analytics_events WHERE type = 'event' AND $window
                                GROUP BY name ORDER BY total DESC"),
        ];
    }
}
