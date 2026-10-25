<?php
declare(strict_types=1);

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Csrf.php';
require_once __DIR__ . '/../core/ClientIp.php';
require_once __DIR__ . '/../models/AnalyticsEvent.php';
require_once __DIR__ . '/../models/ContactMessage.php';
require_once __DIR__ . '/../models/RateLimit.php';

/**
 * Endpoints JSON publics, en lecture seule.
 *
 * Sécurité : seules les clés listées explicitement ici sont exposées (liste blanche).
 * La table `settings` peut contenir d'autres réglages : ils ne quittent jamais le serveur.
 */
final class ApiController extends Controller
{
    /**
     * Contacts de repli de l'API : ceux du site, SANS le numéro de téléphone
     * (le terminal n'expose volontairement aucune donnée téléphonique).
     */
    private const DEFAULT_CONTACTS = [
        ['label' => 'EMAIL', 'value' => 'yann.lfhc@icloud.com', 'href' => 'mailto:yann.lfhc@icloud.com'],
        ['label' => 'GITHUB', 'value' => 'github.com/YannLf3', 'href' => 'https://github.com/YannLf3'],
        ['label' => 'LINKEDIN', 'value' => 'in/yann-le-flohic', 'href' => 'https://www.linkedin.com/in/yannlf3/'],
        ['label' => 'MALT', 'value' => 'Mon lien Malt', 'href' => 'https://www.malt.fr/profile/yannleflohic1'],
    ];

    /** Schémas d'URL autorisés dans les liens renvoyés (bloque javascript:, data:…). */
    private const ALLOWED_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    /** Destinataire des messages du formulaire de contact (adresse déjà publique dans les mentions légales). */
    private const CONTACT_RECIPIENT = 'yann.lfhc@icloud.com';

    /** Anti-spam : délai minimal entre deux envois (s) et nombre maximal d'envois par heure et par adresse IP. */
    private const CONTACT_MIN_DELAY = 30;
    private const CONTACT_MAX_PER_HOUR = 3;

    /** Mesure d'audience : nombre maximal de mesures par minute et par adresse IP. */
    private const TRACK_MAX_PER_MINUTE = 60;

    /** Pages publiques à chemin fixe, seules mesurables avec les fiches projet et les articles du Lab. */
    private const TRACKABLE_PATHS = ['/', '/lab', '/mentions-legales', '/politique-de-confidentialite', '/cgv'];

    /** Longueurs maximales acceptées (identiques aux attributs maxlength du formulaire). */
    private const CONTACT_MAX_NAME = 100;
    private const CONTACT_MAX_EMAIL = 150;
    private const CONTACT_MAX_MESSAGE = 3000;

    /** Durée de cache navigateur de la réponse (s) : le contenu ne change qu'au gré du back-office. */
    private const CACHE_SECONDS = 300;

    /**
     * GET /api/terminal — données affichées par le terminal CLI (public/js/terminal.js).
     *
     * @return never
     */
    public function terminal(): never
    {
        try {
            $settings = Setting::load();
            $projects = Project::findAll();
            $articles = self::rows($settings->getList('articles'));
        } catch (Throwable $e) {
            // Erreur SQL : détail en log serveur uniquement, message générique côté client
            error_log('ApiController::terminal — ' . $e->getMessage());
            Response::json(['error' => 'Service momentanément indisponible.'], 503);
        }

        Response::json([
            'name' => $this->text($settings->get('hero_name')),
            'status' => $this->text($settings->get('dispo_text')),
            'location' => $this->text($settings->get('location')),
            'school' => $this->text($settings->get('status')),
            'skills' => $this->splitWords($settings->get('skills_tech')),
            'tools' => $this->splitWords($settings->get('skills_tools')),
            'interests' => $this->splitWords($settings->get('interests')),
            'languages' => $this->publicLanguages($settings->get('langues')),
            'contacts' => $this->publicContacts($settings->getRaw('contacts') ?? self::DEFAULT_CONTACTS),
            'projects' => array_map([$this, 'publicProject'], $projects),
            'articles' => array_values(array_map([$this, 'publicArticle'], $articles)),
        ], 200, self::CACHE_SECONDS);
    }

    /**
     * POST /api/contact : validation du formulaire de contact, enregistrement en base puis envoi par e-mail.
     *
     * Protections : jeton CSRF, champ piège (honeypot), limitation par adresse IP (en base), validation côté serveur,
     * neutralisation des retours à la ligne dans les en-têtes du mail (anti injection d'en-têtes).
     * Le message est enregistré AVANT mail() : un échec d'envoi ne le perd jamais (colonne mail_sent).
     *
     * @return never
     */
    public function contact(): never
    {
        if (!Csrf::isValid($_POST[Csrf::FIELD] ?? null)) {
            Response::json(['error' => 'Session expirée : rechargez la page puis réessayez.'], 403);
        }

        // Champ piège rempli = robot : on répond « succès » sans rien envoyer
        if (trim((string)($_POST['website'] ?? '')) !== '') {
            Response::json(['ok' => true]);
        }

        $name = $this->singleLine($_POST['name'] ?? '');
        $email = $this->singleLine($_POST['email'] ?? '');
        $message = is_scalar($_POST['message'] ?? null) ? trim((string)$_POST['message']) : '';

        // Erreurs par champ : seuls les champs invalides figurent dans le tableau
        $errors = array_filter([
            'name' => $name === '' ? 'Indiquez votre nom.' : (mb_strlen($name) > self::CONTACT_MAX_NAME ? 'Ce nom est trop long.' : null),
            'email' => filter_var($email, FILTER_VALIDATE_EMAIL) === false || mb_strlen($email) > self::CONTACT_MAX_EMAIL
                ? 'Indiquez une adresse e-mail valide, par exemple nom@exemple.fr.' : null,
            'message' => mb_strlen($message) < 10
                ? 'Votre message doit contenir au moins 10 caractères.'
                : (mb_strlen($message) > self::CONTACT_MAX_MESSAGE ? 'Ce message est trop long (3000 caractères maximum).' : null),
        ]);
        if ($errors !== []) {
            Response::json(['error' => 'Le formulaire contient des erreurs.', 'fields' => $errors], 422);
        }

        // Limite par IP puis enregistrement du message : si la base est en panne, rien n'est perdu en silence
        try {
            $clientIp = ClientIp::get();
            $limited = RateLimit::count('contact', $clientIp, 3600) >= self::CONTACT_MAX_PER_HOUR
                || RateLimit::count('contact', $clientIp, self::CONTACT_MIN_DELAY) > 0;
            $messageId = $limited ? null : ContactMessage::create($name, $email, $message);

            if ($messageId !== null) {
                RateLimit::record('contact', $clientIp);
                // Purge occasionnelle (1 message sur 200) : données personnelles conservées 12 mois au plus
                if (random_int(1, 200) === 1) {
                    ContactMessage::purgeOld();
                }
            }
        } catch (Throwable $e) {
            error_log('ApiController::contact : ' . $e->getMessage());
            Response::json(['error' => 'Service momentanément indisponible. Écrivez-moi directement par e-mail.'], 503);
        }

        if ($messageId === null) {
            Response::json(['error' => 'Trop de messages envoyés. Réessayez dans quelques minutes ou écrivez-moi directement par e-mail.'], 429);
        }

        $headers = implode("\r\n", [
            'From: Portfolio <no-reply@yannleflohic.fr>',
            'Reply-To: ' . $name . ' <' . $email . '>',
            'Content-Type: text/plain; charset=UTF-8',
        ]);
        $body = "Nom : $name\nE-mail : $email\n\n$message\n";
        $sent = mail(self::CONTACT_RECIPIENT, mb_encode_mimeheader('Contact portfolio : ' . $name, 'UTF-8'), $body, $headers);

        if ($sent) {
            try {
                ContactMessage::markSent($messageId);
            } catch (Throwable $e) {
                error_log('ApiController::contact : markSent impossible : ' . $e->getMessage());
            }
        } else {
            // Message conservé en base avec mail_sent = 0 : le visiteur n'a rien à refaire
            error_log('ApiController::contact : échec de mail(), message n°' . $messageId . ' conservé en base');
        }

        Response::json(['ok' => true]);
    }

    /**
     * POST /api/track : enregistre une page vue ou une action (mesure d'audience, après consentement du visiteur).
     *
     * Garde-fous : requête même origine uniquement, signal « Do Not Track » respecté, robots et administrateur
     * connecté ignorés, noms d'action en liste blanche, 60 mesures par minute et par adresse IP (en base),
     * chemin limité aux pages qui existent réellement (une URL inventée ne pollue pas les statistiques).
     * Les champs facultatifs (langue, mode, campagne UTM, durée) sont revalidés ici : le navigateur n'est jamais cru sur parole.
     * Réponse 204 sans contenu (rien à renvoyer au navigateur).
     *
     * @return never
     */
    public function track(): never
    {
        // Même origine : l'en-tête Origin (ou à défaut Referer) doit viser notre propre domaine
        $ownHost = (string)strtok((string)($_SERVER['HTTP_HOST'] ?? ''), ':');
        $source = (string)($_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '');
        if ($ownHost === '' || parse_url($source, PHP_URL_HOST) !== $ownHost) {
            http_response_code(403);
            exit;
        }

        $userAgent = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
        $path = (string)parse_url((string)($_POST['path'] ?? ''), PHP_URL_PATH);
        $type = ($_POST['type'] ?? '') === 'event' ? 'event' : 'pageview';
        $name = is_scalar($_POST['name'] ?? null) ? (string)$_POST['name'] : '';

        // Cas où rien n'est enregistré (réponse 204 identique : le navigateur n'a pas à le savoir)
        $ignored = ($_SERVER['HTTP_DNT'] ?? '') === '1'
            || ($_SERVER['HTTP_SEC_GPC'] ?? '') === '1'
            || AnalyticsEvent::isBot($userAgent)
            || $this->isAdmin()
            || preg_match('#^/(?!/)[A-Za-z0-9/_\-.~%]{0,254}$#', $path) !== 1
            || preg_match('#^/(admin|login|logout|api)(/|$)#', $path) === 1
            || ($type === 'event' && !in_array($name, AnalyticsEvent::EVENT_NAMES, true))
            || ($type === 'event' && $name === 'time_spent' && AnalyticsEvent::cleanDuration($_POST['value'] ?? 0) === 0);

        if (!$ignored) {
            try {
                $clientIp = ClientIp::get();

                // Limite de débit d'abord (peu coûteuse), puis vérification du chemin (peut interroger la base)
                if (RateLimit::attempt('track', $clientIp, self::TRACK_MAX_PER_MINUTE, 60) && $this->isKnownPath($path)) {
                    // Domaine d'origine uniquement (jamais l'URL complète), et seulement s'il est externe
                    $referrerHost = (string)parse_url((string)($_POST['referrer'] ?? ''), PHP_URL_HOST);

                    // Champs facultatifs : chacun est nettoyé par le modèle (liste blanche ou format strict), sinon vide
                    $field = static fn(string $key): string => is_scalar($_POST[$key] ?? null) ? (string)$_POST[$key] : '';

                    AnalyticsEvent::record([
                        'type' => $type,
                        'name' => $type === 'event' ? $name : '',
                        'path' => $path,
                        'referrer' => $referrerHost === $ownHost ? '' : preg_replace('/^www\./', '', $referrerHost),
                        'device' => AnalyticsEvent::deviceFrom($userAgent),
                        'browser' => AnalyticsEvent::browserFrom($userAgent),
                        'os' => AnalyticsEvent::osFrom($userAgent),
                        'country' => AnalyticsEvent::countryFrom((string)($_SERVER['HTTP_CF_IPCOUNTRY'] ?? '')),
                        'lang' => AnalyticsEvent::cleanLang($field('lang')),
                        'mode' => AnalyticsEvent::cleanMode($field('mode')),
                        'utm_source' => $type === 'pageview' ? AnalyticsEvent::cleanTag($field('utm_source')) : '',
                        'utm_campaign' => $type === 'pageview' ? AnalyticsEvent::cleanTag($field('utm_campaign')) : '',
                        'value' => $type === 'event' && $name === 'time_spent' ? AnalyticsEvent::cleanDuration($_POST['value'] ?? 0) : 0,
                        'visitor' => AnalyticsEvent::visitorHash($clientIp, $userAgent),
                    ]);
                    // Purge occasionnelle (1 requête sur 200) : la table ne dépasse jamais 13 mois de données
                    if (random_int(1, 200) === 1) {
                        AnalyticsEvent::purgeOld();
                    }
                }
            } catch (Throwable $e) {
                error_log('ApiController::track : ' . $e->getMessage());
            }
        }

        http_response_code(204);
        exit;
    }

    /**
     * Indique si le chemin désigne une page publique qui existe : page à chemin fixe, fiche projet
     * (identifiant ET adresse canonique exacts) ou article du Lab.
     *
     * @param string $path Chemin déjà validé par expression régulière.
     * @return bool
     */
    private function isKnownPath(string $path): bool
    {
        if (in_array($path, self::TRACKABLE_PATHS, true)) {
            return true;
        }

        if (preg_match('#^/projets/(\d+)-[a-z0-9-]+$#', $path, $matches) === 1) {
            $project = Project::findById((int)$matches[1]);
            return $project !== null && self::projectUrl($project) === $path;
        }

        if (preg_match('#^/lab/([a-z0-9-]+)$#', $path, $matches) === 1) {
            // Mêmes identifiants que HomeController::articleView() : slug du titre, « article » à défaut
            $slugs = array_map(
                static fn(array $article): string => self::slugify((string)($article['title'] ?? '')) ?: 'article',
                self::rows(Setting::load()->getList('articles'))
            );
            return in_array($matches[1], $slugs, true);
        }

        return false;
    }

    /**
     * Valeur scalaire ramenée à une seule ligne (retours à la ligne et caractères de contrôle supprimés).
     *
     * @param mixed $value Valeur brute du formulaire.
     * @return string
     */
    private function singleLine(mixed $value): string
    {
        return is_scalar($value) ? trim((string)preg_replace('/[\x00-\x1F\x7F]+/', ' ', (string)$value)) : '';
    }

    /**
     * Route appelée avec une méthode HTTP non prévue : réponse 405.
     *
     * @param string $allowed Méthode autorisée (en-tête Allow).
     * @return never
     */
    public function methodNotAllowed(string $allowed = 'GET'): never
    {
        header('Allow: ' . $allowed);
        Response::json(['error' => 'Méthode non autorisée.'], 405);
    }

    /**
     * Normalise une valeur scalaire en texte d'une ligne.
     *
     * @param mixed $value Valeur brute issue de la BDD.
     * @return string
     */
    private function text(mixed $value): string
    {
        return is_scalar($value) ? trim((string)$value) : '';
    }

    /**
     * Découpe une liste « a, b, c » en tableau JSON de chaînes non vides (ré-indexé).
     *
     * @param mixed $raw Chaîne séparée par des virgules.
     * @return list<string>
     */
    private function splitWords(mixed $raw): array
    {
        if (!is_string($raw)) {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode(',', $raw)), static fn(string $item): bool => $item !== ''));
    }

    /**
     * Valide une URL : retourne une chaîne vide si son schéma n'est pas autorisé.
     *
     * @param mixed $url URL brute.
     * @return string URL sûre ou chaîne vide.
     */
    private function safeUrl(mixed $url): string
    {
        $url = $this->text($url);
        $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
        return in_array($scheme, self::ALLOWED_SCHEMES, true) ? $url : '';
    }

    /**
     * Ne conserve que les champs publics des langues, pourcentage borné entre 0 et 100.
     *
     * @param mixed $raw Tableau issu de la BDD.
     * @return list<array{name: string, level: string, percent: int}>
     */
    private function publicLanguages(mixed $raw): array
    {
        return array_values(array_map(
            fn(array $language): array => [
                'name' => $this->text($language['name'] ?? ''),
                'level' => $this->text($language['level'] ?? ''),
                'percent' => max(0, min(100, (int)($language['percent'] ?? 0))),
            ],
            self::rows($raw)
        ));
    }

    /**
     * Ne conserve que les champs publics des contacts (liens filtrés par schéma).
     *
     * @param mixed $raw Tableau issu de la BDD.
     * @return list<array{label: string, value: string, href: string}>
     */
    private function publicContacts(mixed $raw): array
    {
        return array_values(array_map(
            fn(array $contact): array => [
                'label' => $this->text($contact['label'] ?? ''),
                'value' => $this->text($contact['value'] ?? ''),
                'href' => $this->safeUrl($contact['href'] ?? ''),
            ],
            self::rows($raw)
        ));
    }

    /**
     * Ne conserve que les champs publics d'un projet (liens filtrés par schéma).
     *
     * @param Project $project
     * @return array<string, mixed>
     */
    private function publicProject(Project $project): array
    {
        return [
            'title' => $this->text($project->getTitle()),
            'meta' => $this->text($project->getMeta()),
            'description' => $this->text($project->getDescription()),
            'category' => $this->text($project->getCategory()),
            'tags' => $this->splitWords($project->getTags()),
            'year' => $this->text($project->getYear()),
            'role' => $this->text($project->getRole()),
            'link' => $this->safeUrl($project->getLink()),
            'repo' => $this->safeUrl($project->getRepoLink()),
            // Page détail interne du site
            'url' => self::projectUrl($project),
        ];
    }

    /**
     * Ne conserve que les champs publics d'un article du Lab.
     *
     * @param array<string, mixed> $article Ligne issue de la BDD.
     * @return array{title: string, category: string, url: string}
     */
    private function publicArticle(array $article): array
    {
        $title = $this->text($article['title'] ?? '');

        return [
            'title' => $title,
            'category' => trim(explode('—', $this->text($article['meta'] ?? ''), 2)[0]),
            'url' => '/lab/' . (self::slugify($title) ?: 'article'),
        ];
    }
}
