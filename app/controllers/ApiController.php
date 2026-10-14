<?php
declare(strict_types=1);

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../core/Response.php';

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
        ], 200, self::CACHE_SECONDS);
    }

    /**
     * GET /api/terminal appelé avec une autre méthode HTTP : réponse 405.
     *
     * @return never
     */
    public function methodNotAllowed(): never
    {
        header('Allow: GET');
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
     * Ne conserve que les champs publics d'un projet (titre, méta, catégorie, lien filtré).
     *
     * @param Project $project
     * @return array{title: string, meta: string, category: string, link: string}
     */
    private function publicProject(Project $project): array
    {
        return [
            'title' => $this->text($project->getTitle()),
            'meta' => $this->text($project->getMeta()),
            'category' => $this->text($project->getCategory()),
            'link' => $this->safeUrl($project->getLink()),
        ];
    }
}
