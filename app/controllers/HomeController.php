<?php
declare(strict_types=1);

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/Project.php';

/**
 * Pages publiques : accueil, Lab et mentions légales.
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
        $link = !empty($project->getLink()) ? $project->getLink() : '#';
        $tags = $project->getTags();

        return [
            'number' => sprintf('%02d', $index + 1),
            'link' => $link,
            'isExternal' => str_starts_with($link, 'http'),
            'image' => !empty($project->getImage()) ? $project->getImage() : self::FALLBACK_PROJECT_IMAGE,
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
            !empty($photo['url']) => $photo['url'],
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
            'alt' => $photo['alt'] ?? 'Photographie Yann Le Flohic',
            'legend' => $photo['legend'] ?? '',
            'number' => sprintf('N° %02d', $index + 1),
        ];
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
                    'href' => $contact['href'] ?? '#',
                    'isExternal' => str_starts_with((string)($contact['href'] ?? '#'), 'http'),
                    'label' => $contact['label'] ?? 'CONTACT',
                    'value' => $contact['value'] ?? '',
                ],
                self::rows($settings->get('contacts'))
            ),
            'interests' => self::splitList($settings->get('interests')),
        ];
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
            'hasCss' => !empty($snippet['css']),
            'js' => $snippet['js'] ?? '',
            // Le JS n'est exécuté que s'il existe et n'est pas le texte de remplacement « // Pas de … »
            'runJs' => !empty($js) && !str_contains($js, '// Pas de'),
        ];
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
            'category' => trim($metaParts[0] ?? 'BLOG'),
            'date' => trim($metaParts[1] ?? '2026'),
            'title' => $article['title'] ?? '',
            'excerpt' => strip_tags((string)($article['content'] ?? '')),
            'meta' => $article['meta'] ?? '',
            'content' => $article['content'] ?? '',
        ];
    }
}
