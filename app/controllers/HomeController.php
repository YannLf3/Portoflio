<?php
declare(strict_types=1);

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../core/UrlGuard.php';
require_once __DIR__ . '/../core/Csrf.php';

/**
 * Pages publiques : accueil, fiches projet, Lab, articles du Lab et pages légales.
 * Chaque méthode privée prépare les données d'une section ; les vues se contentent de les afficher.
 */
final class HomeController extends Controller
{
    /**
     * Formats de photo acceptés par la galerie (une valeur inconnue retombe sur « landscape »),
     * avec les dimensions width/height de la balise <img> (ratio du format, anti-CLS).
     */
    private const GALLERY_FORMATS = [
        'portrait' => ['width' => 1200, 'height' => 1600],
        'landscape' => ['width' => 1600, 'height' => 1000],
        'panorama' => ['width' => 2100, 'height' => 900],
    ];

    /** Images de repli. */
    private const FALLBACK_PROJECT_IMAGE = '/assets/images/projects/barcelona.webp';
    private const FALLBACK_GALLERY_IMAGE = '/assets/images/gallery/gallery-1.webp';

    /** Extensions acceptées pour l'avatar (sinon la vidéo animoji est affichée). */
    private const AVATAR_PATTERN = '/\.(png|jpg|jpeg|webp)$/i';

    /**
     * Page d'accueil : toutes les sections du portfolio.
     *
     * @return void
     */
    public function index(): void
    {
        $settings = Setting::load();

        $this->render('front/home', array_merge(
            [
                // SEO : ciblage local
                'title' => 'Yann Le Flohic | Développeur Web Freelance à Châteauroux & Limoges',
                'pageDesc' => 'Développeur web freelance basé entre Châteauroux et Limoges. Création de sites sur-mesure, applications web et intégration moderne.',
                'canonical' => 'https://yannleflohic.fr/',
            ],
            $this->heroData($settings),
            $this->aboutData($settings),
            $this->skillsData($settings),
            $this->projectsData($settings),
            $this->galleryData($settings),
            $this->compareData($settings),
            $this->contactData($settings)
        ));
    }

    /**
     * Page Lab : snippets interactifs et articles.
     *
     * @return void
     */
    public function lab(): void
    {
        $settings = Setting::load();

        $this->render('front/lab', [
            'title' => 'Le Lab | Expérimentations Web - YannLf3',
            'pageDesc' => 'Découvrez mes snippets, expérimentations CSS/JS et tests de développement front-end par Yann Le Flohic.',
            'canonical' => 'https://yannleflohic.fr/lab',
            'snippets' => array_map([$this, 'snippetView'], self::rows($settings->getList('snippets'))),
            'articles' => array_map([$this, 'articleView'], self::rows($settings->getList('articles'))),
        ]);
    }

    /**
     * Fiche d'un projet : /projets/{id}-{slug}. Un slug erroné redirige (301) vers l'adresse canonique.
     *
     * @param int    $id   Identifiant du projet.
     * @param string $slug Slug demandé dans l'URL.
     * @return void
     */
    public function project(int $id, string $slug): void
    {
        $project = Project::findById($id);
        if ($project === null) {
            $this->notFound();
            return;
        }

        $url = self::projectUrl($project);
        if ($url !== '/projets/' . $id . '-' . $slug) {
            http_response_code(301);
            $this->redirect($url);
        }

        $card = $this->projectCardView($project, 0);
        $canonical = 'https://yannleflohic.fr' . $url;
        $description = mb_substr(trim($project->getDescription()), 0, 155);

        $this->render('front/project', [
            'title' => $project->getTitle() . ' | Projet web de Yann Le Flohic',
            'pageDesc' => $description !== '' ? $description : 'Projet réalisé par Yann Le Flohic, développeur web freelance.',
            'canonical' => $canonical,
            'project' => $card,
            'jsonLd' => array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'CreativeWork',
                'name' => $project->getTitle(),
                'description' => $project->getDescription(),
                'image' => str_starts_with($card['image'], 'http') ? $card['image'] : 'https://yannleflohic.fr' . $card['image'],
                'url' => $canonical,
                'keywords' => implode(', ', $card['tags']),
                'copyrightYear' => $card['year'] !== '' ? $card['year'] : null,
                'author' => ['@id' => 'https://yannleflohic.fr/#person'],
            ], static fn(mixed $value): bool => $value !== null),
        ]);
    }

    /**
     * Article du Lab : /lab/{slug}. Le slug est calculé à partir du titre (404 s'il ne correspond à aucun article).
     *
     * @param string $slug Slug demandé dans l'URL.
     * @return void
     */
    public function article(string $slug): void
    {
        $articles = array_map([$this, 'articleView'], self::rows(Setting::load()->getList('articles')));
        $matches = array_values(array_filter($articles, static fn(array $article): bool => $article['slug'] === $slug));

        if ($matches === []) {
            $this->notFound();
            return;
        }

        $article = $matches[0];
        $canonical = 'https://yannleflohic.fr/lab/' . $slug;

        $this->render('front/article', [
            'title' => $article['title'] . ' | Lab de Yann Le Flohic',
            'pageDesc' => mb_substr(trim($article['excerpt']), 0, 155),
            'canonical' => $canonical,
            'article' => $article,
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $article['title'],
                'url' => $canonical,
                'author' => ['@id' => 'https://yannleflohic.fr/#person'],
            ],
        ]);
    }

    /**
     * Plan du site XML généré à la volée : accueil, Lab, une fiche par projet et un article par entrée du Lab.
     * Remplace l'ancien fichier statique, qui ne pouvait pas suivre les contenus du back-office.
     *
     * @return never
     */
    public function sitemap(): never
    {
        $settings = Setting::load();
        $projectUrls = array_map([self::class, 'projectUrl'], Project::findAll());
        $articleUrls = array_map(
            static fn(array $article): string => '/lab/' . (self::slugify((string)($article['title'] ?? '')) ?: 'article'),
            self::rows($settings->getList('articles'))
        );

        $entries = array_merge(
            [['/', 'weekly', '1.0'], ['/lab', 'monthly', '0.8']],
            array_map(static fn(string $url): array => [$url, 'monthly', '0.7'], $projectUrls),
            array_map(static fn(string $url): array => [$url, 'monthly', '0.6'], $articleUrls)
        );

        $xml = implode('', array_map(
            static fn(array $entry): string => sprintf(
                "  <url>\n    <loc>%s</loc>\n    <changefreq>%s</changefreq>\n    <priority>%s</priority>\n  </url>\n",
                htmlspecialchars('https://yannleflohic.fr' . ($entry[0] === '/' ? '/' : $entry[0]), ENT_XML1),
                $entry[1],
                $entry[2]
            ),
            $entries
        ));

        header('Content-Type: application/xml; charset=UTF-8');
        header('Cache-Control: public, max-age=3600');
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n" . $xml . "</urlset>\n";
        exit;
    }

    /**
     * Page des mentions légales (non indexée pour ne pas concurrencer l'accueil).
     *
     * @return void
     */
    public function legal(): void
    {
        $this->render('front/legal', [
            'title' => 'Mentions Légales | Yann Le Flohic',
            'pageDesc' => 'Mentions légales du site portfolio de Yann Le Flohic.',
            'canonical' => 'https://yannleflohic.fr/mentions-legales',
            'noindex' => true,
        ]);
    }

    /**
     * Page de la politique de confidentialité (non indexée, comme les autres pages légales).
     *
     * @return void
     */
    public function privacy(): void
    {
        $this->render('front/privacy', [
            'title' => 'Politique de confidentialité | Yann Le Flohic',
            'pageDesc' => 'Données collectées, durées de conservation, cookies et droits RGPD sur le site de Yann Le Flohic.',
            'canonical' => 'https://yannleflohic.fr/politique-de-confidentialite',
            'noindex' => true,
        ]);
    }

    /**
     * Page des conditions générales de vente (non indexée, comme les autres pages légales).
     *
     * @return void
     */
    public function terms(): void
    {
        $this->render('front/terms', [
            'title' => 'Conditions générales de vente | Yann Le Flohic',
            'pageDesc' => 'Conditions générales de vente des prestations web et photo de Yann Le Flohic (YannLf3).',
            'canonical' => 'https://yannleflohic.fr/cgv',
            'noindex' => true,
        ]);
    }

    /**
     * Page 404 personnalisée : code HTTP 404 et exclusion de l'index des moteurs de recherche.
     *
     * @return void
     */
    public function notFound(): void
    {
        http_response_code(404);

        $this->render('front/404', [
            'title' => 'Page introuvable | Yann Le Flohic',
            'pageDesc' => 'Cette page n\'existe pas ou a été déplacée. Retournez à l\'accueil du portfolio de Yann Le Flohic.',
            'canonical' => 'https://yannleflohic.fr/',
            'noindex' => true,
        ]);
    }

    /**
     * Page d'erreur 500 : affichée par le gestionnaire d'exceptions de public/index.php.
     * Aucune requête BDD ici (la panne peut venir de la base) et aucun détail technique.
     *
     * @return void
     */
    public function serverError(): void
    {
        http_response_code(500);

        $this->render('front/500', [
            'title' => 'Erreur technique | Yann Le Flohic',
            'pageDesc' => 'Une erreur technique est survenue. Réessayez dans quelques instants ou écrivez-moi directement.',
            'canonical' => 'https://yannleflohic.fr/',
            'noindex' => true,
        ]);
    }

    /**
     * En-tête de l'accueil : disponibilité, nom (prénom / nom séparés), accroches, avatar.
     *
     * @param Setting $settings
     * @return array<string, mixed>
     */
    private function heroData(Setting $settings): array
    {
        $nameParts = explode(' ', trim((string)$settings->get('hero_name')), 2);
        $avatar = (string)$settings->get('hero_avatar');

        return [
            'dispoText' => $settings->get('dispo_text'),
            'firstName' => $nameParts[0] ?? 'Yann',
            'lastName' => $nameParts[1] ?? 'Le Flohic',
            'heroDev' => $settings->get('hero_dev'),
            'heroPhoto' => $settings->get('hero_photo'),
            'location' => $settings->get('location'),
            'status' => $settings->get('status'),
            // Image affichée uniquement si un fichier png/jpg/webp est renseigné, sinon la vidéo animoji
            'avatar' => $avatar !== '' && preg_match(self::AVATAR_PATTERN, $avatar) ? $avatar : null,
        ];
    }

    /**
     * Section Parcours : introduction et étapes de la timeline.
     *
     * @param Setting $settings
     * @return array<string, mixed>
     */
    private function aboutData(Setting $settings): array
    {
        return [
            'parcoursIntro' => $settings->get('parcours_intro'),
            'parcours' => array_map(
                static fn(array $step): array => self::fillRow($step, ['period' => '', 'type' => 'FORMATION', 'title' => '', 'detail' => '']),
                self::rows($settings->get('parcours'))
            ),
        ];
    }

    /**
     * Section Compétences : stack technique, outils et langues (pourcentage entier).
     *
     * @param Setting $settings
     * @return array<string, mixed>
     */
    private function skillsData(Setting $settings): array
    {
        return [
            'skillsTech' => self::splitList($settings->get('skills_tech')),
            'skillsTools' => self::splitList($settings->get('skills_tools')),
            'languages' => array_map(
                static fn(array $language): array => [
                    'name' => $language['name'] ?? 'Langue',
                    'level' => $language['level'] ?? '',
                    'percent' => intval($language['percent'] ?? 100),
                ],
                self::rows($settings->get('langues'))
            ),
        ];
    }

    /**
     * Section Projets : boutons de filtre (catégories non vides) et cartes projet.
     *
     * @param Setting $settings
     * @return array<string, mixed>
     */
    private function projectsData(Setting $settings): array
    {
        $projects = Project::findAll();
        $categories = array_filter([
            $settings->get('project_cat_1'),
            $settings->get('project_cat_2'),
            $settings->get('project_cat_3'),
        ]);

        return [
            'projectFilters' => array_map(
                static fn(mixed $category): array => [
                    'value' => mb_strtolower(trim((string)$category)),
                    'label' => trim((string)$category),
                ],
                $categories
            ),
            'projects' => array_map([$this, 'projectCardView'], $projects, array_keys($projects)),
        ];
    }

    /**
     * Données d'affichage d'une carte projet.
     *
     * @param Project $project Projet à afficher.
     * @param int     $index   Position dans la grille (0 = premier).
     * @return array<string, mixed>
     */
    private function projectCardView(Project $project, int $index): array
    {
        // Lien filtré à l'affichage aussi : protège même les lignes enregistrées avant l'ajout du filtre
        $link = UrlGuard::safe($project->getLink()) ?: '#';
        $tags = $project->getTags();

        return [
            'number' => sprintf('%02d', $index + 1),
            'url' => self::projectUrl($project),
            'link' => $link,
            'isExternal' => str_starts_with($link, 'http'),
            'image' => UrlGuard::safe($project->getImage()) ?: self::FALLBACK_PROJECT_IMAGE,
            // Catégorie normalisée pour le filtrage JS (public/js/projects-filter.js)
            'filterCategory' => mb_strtolower(trim($project->getCategory())),
            'category' => $project->getCategory(),
            'hasCategory' => !empty($project->getCategory()),
            'title' => $project->getTitle(),
            'meta' => $project->getMeta(),
            'hasMeta' => !empty($project->getMeta()),
            'description' => $project->getDescription(),
            'hasTags' => !empty($tags),
            'tags' => self::splitList($tags),
            // Page détail : champs optionnels, une chaîne vide = section non affichée
            'year' => trim($project->getYear()),
            'role' => trim($project->getRole()),
            'context' => trim($project->getContext()),
            'solution' => trim($project->getSolution()),
            'result' => trim($project->getResult()),
            'repoLink' => UrlGuard::safe($project->getRepoLink()),
            'gallery' => $project->getGalleryList(),
        ];
    }

    /**
     * Galerie « Regarder autrement » : source, format, légende et numéro de chaque photo.
     *
     * @param Setting $settings
     * @return array<string, mixed>
     */
    private function galleryData(Setting $settings): array
    {
        $photos = $settings->get('photos');
        $photos = is_array($photos) ? array_values($photos) : [];

        // Le numéro suit la position dans la liste d'origine, avant d'écarter les lignes invalides
        $views = array_map(
            static fn(mixed $photo, int $index): ?array => is_array($photo) ? self::photoView($photo, $index) : null,
            $photos,
            array_keys($photos)
        );

        return ['photos' => array_filter($views)];
    }

    /**
     * Données d'affichage d'une photo de la galerie.
     *
     * @param array<string, mixed> $photo Ligne issue de la BDD.
     * @param int                  $index Position dans la galerie (0 = première).
     * @return array<string, mixed>
     */
    private static function photoView(array $photo, int $index): array
    {
        // Source : URL directe, sinon identifiant Unsplash, sinon image par défaut
        $src = match (true) {
            UrlGuard::safe($photo['url'] ?? '') !== '' => UrlGuard::safe($photo['url']),
            !empty($photo['unsplash_id']) => "https://images.unsplash.com/photo-{$photo['unsplash_id']}?auto=format&fit=crop&w=1600&q=80",
            default => self::FALLBACK_GALLERY_IMAGE,
        };

        $format = is_string($photo['format'] ?? null) && isset(self::GALLERY_FORMATS[$photo['format']]) ? $photo['format'] : 'landscape';

        return [
            'index' => $index,
            'src' => $src,
            'format' => $format,
            'width' => self::GALLERY_FORMATS[$format]['width'],
            'height' => self::GALLERY_FORMATS[$format]['height'],
            // Alt vide en base : on retombe sur la légende, puis sur un texte unique par photo (numéro)
            'alt' => self::photoAlt($photo, $index),
            'legend' => $photo['legend'] ?? '',
            'number' => sprintf('N° %02d', $index + 1),
        ];
    }

    /**
     * Texte alternatif d'une photo : alt saisi, sinon légende, sinon libellé numéroté (jamais deux alt identiques).
     *
     * @param array<string, mixed> $photo Ligne issue de la BDD.
     * @param int                  $index Position dans la galerie (0 = première).
     * @return string
     */
    private static function photoAlt(array $photo, int $index): string
    {
        $candidates = array_filter([
            trim((string)($photo['alt'] ?? '')),
            trim((string)($photo['legend'] ?? '')),
        ]);

        return (string)(reset($candidates) ?: sprintf('Photographie de Yann Le Flohic n° %d', $index + 1));
    }

    /**
     * Comparateur avant / après : sans image brute fournie, le « RAW » est simulé à partir de l'image finale.
     *
     * @param Setting $settings
     * @return array<string, mixed>
     */
    private function compareData(Setting $settings): array
    {
        $before = trim((string)($settings->getRaw('compare_before') ?? ''));
        $after = $settings->getText('compare_after');
        $isSimulated = $before === '';

        return [
            'compareAfter' => $after,
            'compareBeforeSrc' => $isSimulated ? $after : $before,
            'isSimulated' => $isSimulated,
            'compareTitle' => $settings->getText('compare_title'),
            'compareCaption' => $settings->getText('compare_caption'),
            'compareTools' => self::splitList($settings->get('compare_tools')),
        ];
    }

    /**
     * Section Contact : titres, liens (externes ouverts dans un nouvel onglet) et centres d'intérêt.
     *
     * @param Setting $settings
     * @return array<string, mixed>
     */
    private function contactData(Setting $settings): array
    {
        return [
            'contactTitle' => $settings->get('contact_title'),
            'contactSubtitle' => $settings->get('contact_subtitle'),
            'contacts' => array_map(
                static fn(array $contact): array => [
                    'href' => UrlGuard::safe($contact['href'] ?? '') ?: '#',
                    'isExternal' => str_starts_with(UrlGuard::safe($contact['href'] ?? ''), 'http'),
                    'label' => $contact['label'] ?? 'CONTACT',
                    'value' => $contact['value'] ?? '',
                    'track' => self::contactTrackName(UrlGuard::safe($contact['href'] ?? '')),
                ],
                self::rows($settings->get('contacts'))
            ),
            'interests' => self::splitList($settings->get('interests')),
        ];
    }

    /**
     * Nom de l'action mesurée pour un lien de la section contact, déduit de son adresse.
     *
     * @param string $href Adresse déjà filtrée par UrlGuard.
     * @return string Un nom de AnalyticsEvent::EVENT_NAMES.
     */
    private static function contactTrackName(string $href): string
    {
        return match (true) {
            preg_match('/\.pdf($|[?#])/i', $href) === 1 => 'cv_download',
            str_contains($href, 'github.com') => 'link_github',
            str_contains($href, 'linkedin.com') => 'link_linkedin',
            str_starts_with($href, 'mailto:') => 'link_email',
            default => 'link_other',
        };
    }

    /**
     * Données d'affichage d'un snippet du Lab (aperçu HTML/CSS/JS + contenu de la modale de code).
     *
     * @param array<string, mixed> $snippet Ligne issue de la BDD.
     * @return array<string, mixed>
     */
    private function snippetView(array $snippet): array
    {
        $js = (string)($snippet['js'] ?? '');

        return [
            'title' => $snippet['title'] ?? 'Expérimentation',
            'modalTitle' => $snippet['title'] ?? 'Console',
            'category' => $snippet['category'] ?? $snippet['tag'] ?? 'Lab',
            'badge' => $snippet['badge'] ?? $snippet['note'] ?? 'Tested on Modern Browsers',
            'html' => $snippet['html'] ?? '',
            'css' => $snippet['css'] ?? '',
            'js' => $snippet['js'] ?? '',
            // Document complet de l'aperçu, rendu dans une <iframe sandbox> (voir lab.php)
            'srcdoc' => $this->snippetDocument(
                (string)($snippet['html'] ?? ''),
                (string)($snippet['css'] ?? ''),
                // Le JS n'est exécuté que s'il existe et n'est pas le texte de remplacement « // Pas de … »
                !empty($js) && !str_contains($js, '// Pas de') ? $js : ''
            ),
        ];
    }

    /**
     * Construit le document HTML autonome d'un aperçu de snippet.
     *
     * Il est affiché dans une iframe sandbox SANS allow-same-origin : son code s'exécute dans une origine
     * isolée et ne peut ni lire les cookies ni toucher à la page parente, même s'il est malveillant.
     * Seul le <script> généré ici porte le nonce CSP : un <script> glissé dans le champ HTML est bloqué.
     *
     * @param string $html Balisage du snippet.
     * @param string $css  Styles du snippet.
     * @param string $js   Script du snippet ('' = aucun).
     * @return string Document HTML complet.
     */
    private function snippetDocument(string $html, string $css, string $js): string
    {
        $script = $js === '' ? '' : sprintf(
            '<script nonce="%s">(function(){try{%s}catch(e){console.error("Erreur JS Snippet:",e);}})();</script>',
            CSP_NONCE,
            $js
        );

        return '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><style>'
            . 'html,body{margin:0;height:100%}body{display:flex;align-items:center;justify-content:center;'
            . 'background:transparent;color:#fff;font-family:system-ui,sans-serif}</style><style>'
            . $css . '</style></head><body>' . $html . $script . '</body></html>';
    }

    /**
     * Données d'affichage d'un article du Lab (catégorie et date extraites de « CATÉGORIE — DATE »).
     *
     * @param array<string, mixed> $article Ligne issue de la BDD.
     * @return array<string, mixed>
     */
    private function articleView(array $article): array
    {
        $metaParts = explode('—', (string)($article['meta'] ?? ''), 2);

        return [
            'slug' => self::slugify((string)($article['title'] ?? '')) ?: 'article',
            'category' => trim($metaParts[0] ?? 'BLOG'),
            'date' => trim($metaParts[1] ?? '2026'),
            'title' => $article['title'] ?? '',
            'excerpt' => strip_tags((string)($article['content'] ?? '')),
            'meta' => $article['meta'] ?? '',
            'content' => $article['content'] ?? '',
        ];
    }
}
