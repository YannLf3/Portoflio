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
    public const EVENT_NAMES = [
        'contact_click', 'contact_sent', 'project_open', 'project_link', 'repo_link', 'mode_switch',
        'link_github', 'link_linkedin', 'link_email', 'link_other', 'cv_download',
        'scroll_50', 'scroll_90', 'time_spent',
    ];

    /** Actions de mesure technique (profondeur de défilement, temps passé) : affichées à part, pas dans « Actions mesurées ». */
    private const TECHNICAL_EVENTS = ['scroll_50', 'scroll_90', 'time_spent'];

    /** Colonnes autorisées pour les classements (liste blanche : jamais de nom de colonne venant du navigateur). */
    private const RANKED_COLUMNS = ['device', 'browser', 'os', 'lang', 'country', 'mode', 'utm_source', 'utm_campaign'];

    /** Durée maximale retenue pour une mesure de temps passé (secondes) et pas d'arrondi. */
    private const MAX_DURATION = 1800;
    private const DURATION_STEP = 5;

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
     * Classe le navigateur d'après le User-Agent (l'ordre compte : Edge et Opera contiennent « Chrome »).
     *
     * @param string $userAgent En-tête User-Agent.
     * @return string 'edge', 'opera', 'firefox', 'chrome', 'safari' ou 'other'.
     */
    public static function browserFrom(string $userAgent): string
    {
        return match (true) {
            preg_match('/Edg(e|A|iOS)?\//', $userAgent) === 1 => 'edge',
            preg_match('/OPR\/|Opera/', $userAgent) === 1 => 'opera',
            preg_match('/Firefox|FxiOS/', $userAgent) === 1 => 'firefox',
            preg_match('/Chrome|CriOS/', $userAgent) === 1 => 'chrome',
            preg_match('/Safari/', $userAgent) === 1 => 'safari',
            default => 'other',
        };
    }

    /**
     * Classe le système d'exploitation d'après le User-Agent (iOS avant macOS, Android avant Linux).
     *
     * @param string $userAgent En-tête User-Agent.
     * @return string 'ios', 'android', 'windows', 'macos', 'linux' ou 'other'.
     */
    public static function osFrom(string $userAgent): string
    {
        return match (true) {
            preg_match('/iPhone|iPad|iPod/', $userAgent) === 1 => 'ios',
            preg_match('/Android/', $userAgent) === 1 => 'android',
            preg_match('/Windows/', $userAgent) === 1 => 'windows',
            preg_match('/Mac OS X|Macintosh/', $userAgent) === 1 => 'macos',
            preg_match('/Linux|X11/', $userAgent) === 1 => 'linux',
            default => 'other',
        };
    }

    /**
     * Pays annoncé par le CDN (en-tête CF-IPCountry de Cloudflare), jamais déduit de l'adresse IP.
     *
     * @param string $header Valeur brute de l'en-tête (vide si le site n'est pas derrière Cloudflare).
     * @return string Code ISO à 2 lettres, ou chaîne vide (absent, inconnu « XX » ou réseau Tor « T1 »).
     */
    public static function countryFrom(string $header): string
    {
        $code = strtoupper(trim($header));
        return preg_match('/^[A-Z]{2}$/', $code) === 1 && $code !== 'XX' ? $code : '';
    }

    /**
     * Langue principale du navigateur : 2 lettres minuscules (« fr-FR » devient « fr »).
     *
     * @param string $value Valeur de navigator.language envoyée par le navigateur.
     * @return string Code à 2 lettres, ou chaîne vide si la valeur est illisible.
     */
    public static function cleanLang(string $value): string
    {
        return preg_match('/^([A-Za-z]{2})(?:[-_]|$)/', $value, $matches) === 1 ? strtolower($matches[1]) : '';
    }

    /**
     * Mode d'affichage du site au moment de la mesure.
     *
     * @param string $value Valeur de data-mode envoyée par le navigateur.
     * @return string 'dev', 'photo' ou chaîne vide.
     */
    public static function cleanMode(string $value): string
    {
        return in_array($value, ['dev', 'photo'], true) ? $value : '';
    }

    /**
     * Paramètre de campagne (utm_source, utm_campaign) : minuscules, caractères simples, 40 au plus.
     *
     * @param string $value Valeur brute lue dans l'adresse de la page.
     * @return string
     */
    public static function cleanTag(string $value): string
    {
        return mb_substr(strtolower(preg_replace('/[^A-Za-z0-9._+\- ]/', '', $value) ?? ''), 0, 40);
    }

    /**
     * Durée de présence en secondes : bornée à 30 minutes et arrondie à 5 s (une durée exacte serait presque une empreinte).
     *
     * @param mixed $value Valeur brute envoyée par le navigateur.
     * @return int 0 si la valeur est absente ou invalide.
     */
    public static function cleanDuration(mixed $value): int
    {
        $seconds = is_numeric($value) ? (int)$value : 0;
        $capped = max(0, min($seconds, self::MAX_DURATION));
        return (int)(round($capped / self::DURATION_STEP) * self::DURATION_STEP);
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
     * Clés lues dans $row (toutes optionnelles sauf path et visitor) : type ('pageview' ou 'event'), name, path,
     * referrer, device, visitor, browser, os, country, lang, mode, utm_source, utm_campaign, value (secondes, temps passé).
     * L'heure est celle de Paris, pour que l'histogramme horaire ne dépende pas du fuseau du serveur.
     *
     * @param array<string, string|int> $row Valeurs déjà nettoyées par les méthodes clean*() et *From().
     * @return void
     */
    public static function record(array $row): void
    {
        $text = static fn(string $key, int $max): string => mb_substr((string)($row[$key] ?? ''), 0, $max);

        $stmt = getPDOConnection()->prepare('
            INSERT INTO analytics_events
                (day, hour, type, name, path, referrer, device, browser, os, country, lang, mode, utm_source, utm_campaign, value, visitor)
            VALUES
                (CURDATE(), :hour, :type, :name, :path, :referrer, :device, :browser, :os, :country, :lang, :mode, :utm_source, :utm_campaign, :value, :visitor)
        ');
        $stmt->execute([
            'hour' => (int)(new DateTimeImmutable('now', new DateTimeZone('Europe/Paris')))->format('G'),
            'type' => ($row['type'] ?? '') === 'event' ? 'event' : 'pageview',
            'name' => $text('name', 40),
            'path' => $text('path', 255),
            'referrer' => $text('referrer', 120),
            'device' => in_array($row['device'] ?? '', self::DEVICES, true) ? $row['device'] : 'desktop',
            'browser' => $text('browser', 10),
            'os' => $text('os', 10),
            'country' => $text('country', 2),
            'lang' => $text('lang', 2),
            'mode' => $text('mode', 5),
            'utm_source' => $text('utm_source', 40),
            'utm_campaign' => $text('utm_campaign', 40),
            'value' => max(0, min((int)($row['value'] ?? 0), self::MAX_DURATION)),
            'visitor' => $text('visitor', 16),
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
     * Un « visiteur » est une empreinte du jour : la même personne revenue un autre jour compte deux fois
     * (c'est le prix de l'anonymat). Toutes les moyennes et tous les taux se calculent sur ces visiteurs-jours.
     *
     * @param int $days Taille de la fenêtre en jours (7, 30 ou 90).
     * @return array<string, mixed> Voir les clés du tableau retourné en fin de méthode.
     */
    public static function summary(int $days): array
    {
        $days = in_array($days, self::RANGES, true) ? $days : 30;

        // Nombre de jours en arrière à inclure (la fenêtre contient aujourd'hui)
        $since = $days - 1;

        // Les requêtes n'emploient qu'un seul marqueur par nom (prepare() natif) : $params les lie tous en entiers
        $query = static function (string $sql, array $params) : array {
            $stmt = getPDOConnection()->prepare($sql);
            array_walk($params, static fn(int $value, string $name): bool => $stmt->bindValue(':' . $name, $value, PDO::PARAM_INT));
            $stmt->execute();
            return $stmt->fetchAll();
        };
        $window = 'day >= (CURDATE() - INTERVAL :since DAY)';
        $current = ['since' => $since];

        $totals = $query("SELECT COUNT(*) AS views, SUM(referrer = '') AS direct
                          FROM analytics_events WHERE type = 'pageview' AND $window", $current)[0] ?? [];

        // Période précédente, de même durée, juste avant la fenêtre : sert aux variations en pourcentage
        $previous = $query("SELECT COALESCE(SUM(views), 0) AS views, COALESCE(SUM(visitors), 0) AS visitors
                            FROM (SELECT COUNT(*) AS views, COUNT(DISTINCT visitor) AS visitors
                                  FROM analytics_events
                                  WHERE type = 'pageview'
                                    AND day >= (CURDATE() - INTERVAL :first DAY)
                                    AND day < (CURDATE() - INTERVAL :last DAY)
                                  GROUP BY day) AS per_day", ['first' => 2 * $days - 1, 'last' => $since])[0] ?? [];

        $perDay = array_column(
            $query("SELECT day, COUNT(*) AS views, COUNT(DISTINCT visitor) AS visitors
                    FROM analytics_events WHERE type = 'pageview' AND $window GROUP BY day", $current),
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

        // Visiteurs-jours qui n'ont consulté qu'une seule page (rebond)
        $visits = $query("SELECT COUNT(*) AS visits, COALESCE(SUM(pages = 1), 0) AS bounces
                          FROM (SELECT COUNT(*) AS pages FROM analytics_events
                                WHERE type = 'pageview' AND $window GROUP BY day, visitor) AS per_visitor", $current)[0] ?? [];

        // Actions : total et nombre de visiteurs distincts par nom (entonnoir, défilement, classement)
        $events = array_column(
            $query("SELECT name AS label, COUNT(*) AS total, COUNT(DISTINCT visitor) AS visitors, SUM(value) AS seconds
                    FROM analytics_events WHERE type = 'event' AND $window
                    GROUP BY name ORDER BY total DESC", $current),
            null,
            'label'
        );
        $reached = static fn(string $name): int => (int)($events[$name]['visitors'] ?? 0);

        $views = (int)($totals['views'] ?? 0);
        $visitors = array_sum(array_column($dayList, 'visitors'));

        // Classement d'une colonne de la liste blanche, avec la part de chaque ligne
        $ranking = static function (string $column, int $limit) use ($query, $window, $current): array {
            $column = in_array($column, self::RANKED_COLUMNS, true) ? $column : 'device';
            $rows = $query("SELECT $column AS label, COUNT(*) AS total
                            FROM analytics_events WHERE type = 'pageview' AND $column <> '' AND $window
                            GROUP BY $column ORDER BY total DESC LIMIT $limit", $current);
            $sum = max(1, array_sum(array_column($rows, 'total')));
            return array_map(static fn(array $row): array => $row + ['percent' => (int)round($row['total'] / $sum * 100)], $rows);
        };

        return [
            'days' => $dayList,
            'views' => $views,
            'visitors' => $visitors,
            'direct' => (int)($totals['direct'] ?? 0),
            'trend' => [
                'views' => self::variation($views, (int)($previous['views'] ?? 0)),
                'visitors' => self::variation($visitors, (int)($previous['visitors'] ?? 0)),
            ],
            'pagesPerVisit' => $visitors > 0 ? round($views / $visitors, 1) : 0.0,
            'bounceRate' => (int)($visits['visits'] ?? 0) > 0 ? (int)round((int)$visits['bounces'] / (int)$visits['visits'] * 100) : null,
            'avgSeconds' => $views > 0 ? (int)round((int)($events['time_spent']['seconds'] ?? 0) / $views) : 0,
            'conversionRate' => $visitors > 0 ? round($reached('contact_sent') / $visitors * 100, 1) : null,
            'funnel' => [
                ['label' => 'visit', 'total' => $visitors],
                ['label' => 'project_open', 'total' => $reached('project_open')],
                ['label' => 'contact_click', 'total' => $reached('contact_click')],
                ['label' => 'contact_sent', 'total' => $reached('contact_sent')],
            ],
            'scroll' => ['visitors' => $visitors, 'half' => $reached('scroll_50'), 'full' => $reached('scroll_90')],
            'weekdays' => self::fillSeries(
                $query("SELECT WEEKDAY(day) AS slot, COUNT(*) AS total
                        FROM analytics_events WHERE type = 'pageview' AND $window GROUP BY slot", $current),
                7
            ),
            'hours' => self::fillSeries(
                $query("SELECT hour AS slot, COUNT(*) AS total
                        FROM analytics_events WHERE type = 'pageview' AND $window GROUP BY slot", $current),
                24
            ),
            'pages' => $query("SELECT path AS label, COUNT(*) AS total, COUNT(DISTINCT visitor) AS visitors
                               FROM analytics_events WHERE type = 'pageview' AND $window
                               GROUP BY path ORDER BY total DESC LIMIT 10", $current),
            'projects' => $query("SELECT path AS label, COUNT(*) AS total, COUNT(DISTINCT visitor) AS visitors
                                  FROM analytics_events WHERE type = 'pageview' AND path LIKE '/projets/%' AND $window
                                  GROUP BY path ORDER BY total DESC LIMIT 10", $current),
            'referrers' => $query("SELECT referrer AS label, COUNT(*) AS total
                                   FROM analytics_events WHERE type = 'pageview' AND referrer <> '' AND $window
                                   GROUP BY referrer ORDER BY total DESC LIMIT 10", $current),
            'devices' => $ranking('device', 3),
            'browsers' => $ranking('browser', 6),
            'systems' => $ranking('os', 6),
            'languages' => $ranking('lang', 10),
            'countries' => $ranking('country', 10),
            'modes' => $ranking('mode', 2),
            'sources' => $ranking('utm_source', 10),
            'campaigns' => $ranking('utm_campaign', 10),
            'events' => array_values(array_filter(
                $events,
                static fn(array $row): bool => !in_array($row['label'], self::TECHNICAL_EVENTS, true)
            )),
        ];
    }

    /**
     * Variation en pourcentage entre deux périodes.
     *
     * @param int $now      Valeur de la période en cours.
     * @param int $previous Valeur de la période précédente.
     * @return int|null null si la période précédente est vide (calcul impossible).
     */
    private static function variation(int $now, int $previous): ?int
    {
        return $previous > 0 ? (int)round(($now - $previous) / $previous * 100) : null;
    }

    /**
     * Série complète de 0 à $size - 1 : les créneaux sans donnée valent 0.
     *
     * @param list<array{slot: int|string, total: int|string}> $rows Lignes SQL (créneau, total).
     * @param int                                              $size Nombre de créneaux (7 jours, 24 heures).
     * @return list<int>
     */
    private static function fillSeries(array $rows, int $size): array
    {
        $bySlot = array_column($rows, 'total', 'slot');
        return array_map(static fn(int $slot): int => (int)($bySlot[$slot] ?? 0), range(0, $size - 1));
    }
}
