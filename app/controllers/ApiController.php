<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/settings.php';
require_once __DIR__ . '/../core/Response.php';

/**
 * Endpoints JSON publics, en lecture seule.
 *
 * Sécurité : seules les clés listées explicitement ici sont exposées (liste blanche).
 * La table `settings` peut contenir d'autres réglages : ils ne quittent jamais le serveur.
 */
class ApiController
{
    /** Valeurs de repli, identiques à celles des partials, si la clé est absente en BDD. */
    private const DEFAULT_SKILLS = 'HTML / CSS, JavaScript, React, PHP, SQL, Python, Sass, BEM CSS, Tailwind, Bootstrap, Git / Github';
    private const DEFAULT_TOOLS = 'VS CODE, JETBRAINS IDE, FIGMA, SHELL, ADOBE, AFFINITY, APACHE, MAC';
    private const DEFAULT_INTERESTS = 'Open Source, Développement web, Photographie, Gaming, Badminton, Apple';
    private const DEFAULT_LANGUAGES = [
        ['name' => 'Français', 'level' => 'Natif', 'percent' => 100],
        ['name' => 'Espagnol', 'level' => 'B2+ / C1', 'percent' => 85],
        ['name' => 'Anglais', 'level' => 'B1', 'percent' => 60],
    ];
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
            $content = Setting::getAll();
        } catch (Throwable $e) {
            // Erreur SQL : détail en log serveur uniquement, message générique côté client
            error_log('ApiController::terminal — ' . $e->getMessage());
            Response::json(['error' => 'Service momentanément indisponible.'], 503);
        }

        Response::json([
            'name' => $this->text($content['hero_name'] ?? 'Yann Le Flohic'),
            'status' => $this->text($content['dispo_text'] ?? 'Disponible pour un stage · 8-12 semaines'),
            'location' => $this->text($content['location'] ?? 'Châteauroux — FR'),
            'school' => $this->text($content['status'] ?? '2° Année MMI'),
            'skills' => $this->splitList($content['skills_tech'] ?? self::DEFAULT_SKILLS),
            'tools' => $this->splitList($content['skills_tools'] ?? self::DEFAULT_TOOLS),
            'interests' => $this->splitList($content['interests'] ?? self::DEFAULT_INTERESTS),
            'languages' => $this->publicLanguages($content['langues'] ?? self::DEFAULT_LANGUAGES),
            'contacts' => $this->publicContacts($content['contacts'] ?? self::DEFAULT_CONTACTS),
            'projects' => $this->publicProjects($content['projects'] ?? []),
        ], 200, self::CACHE_SECONDS);
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
     * Découpe une liste « a, b, c » en tableau de chaînes non vides.
     *
     * @param mixed $raw Chaîne séparée par des virgules.
     * @return list<string>
     */
    private function splitList(mixed $raw): array
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
        if (!is_array($raw)) {
            return [];
        }
        $languages = [];
        foreach ($raw as $lang) {
            if (!is_array($lang)) {
                continue;
            }
            $languages[] = [
                'name' => $this->text($lang['name'] ?? ''),
                'level' => $this->text($lang['level'] ?? ''),
                'percent' => max(0, min(100, (int)($lang['percent'] ?? 0))),
            ];
        }
        return $languages;
    }

    /**
     * Ne conserve que les champs publics des contacts (liens filtrés par schéma).
     *
     * @param mixed $raw Tableau issu de la BDD.
     * @return list<array{label: string, value: string, href: string}>
     */
    private function publicContacts(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }
        $contacts = [];
        foreach ($raw as $contact) {
            if (!is_array($contact)) {
                continue;
            }
            $contacts[] = [
                'label' => $this->text($contact['label'] ?? ''),
                'value' => $this->text($contact['value'] ?? ''),
                'href' => $this->safeUrl($contact['href'] ?? ''),
            ];
        }
        return $contacts;
    }

    /**
     * Ne conserve que les champs publics des projets (titre, méta, catégorie, lien filtré).
     *
     * @param mixed $raw Tableau issu de la BDD.
     * @return list<array{title: string, meta: string, category: string, link: string}>
     */
    private function publicProjects(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }
        $projects = [];
        foreach ($raw as $project) {
            if (!is_array($project)) {
                continue;
            }
            $projects[] = [
                'title' => $this->text($project['title'] ?? ''),
                'meta' => $this->text($project['meta'] ?? ''),
                'category' => $this->text($project['category'] ?? ''),
                'link' => $this->safeUrl($project['link'] ?? ''),
            ];
        }
        return $projects;
    }
}
