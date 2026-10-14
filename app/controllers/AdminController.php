<?php
declare(strict_types=1);

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../core/Csrf.php';
require_once __DIR__ . '/../core/ImageUploader.php';

/**
 * Back-office : affichage et enregistrement des contenus du site.
 * Toutes les actions exigent une session administrateur (vérifiée dès le constructeur).
 */
final class AdminController extends Controller
{
    /** Dossier physique des images envoyées et préfixe d'URL correspondant. */
    private const UPLOAD_DIR = __DIR__ . '/../../public/assets/uploads/';
    private const UPLOAD_URL = '/assets/uploads/';

    /** Clé du message flash (affiché une seule fois sur le dashboard). */
    private const FLASH_KEY = 'admin_flash';

    /** Couleur du bandeau flash selon la nature du message. */
    private const FLASH_STYLES = [
        'success' => 'border-accent/40 bg-accent/10 text-accent',
        'warning' => 'border-amber-400/40 bg-amber-400/10 text-amber-300',
        'error' => 'border-red-500/40 bg-red-500/10 text-red-300',
    ];

    /** Champs texte du formulaire, avec leur valeur proposée tant que rien n'est enregistré. */
    private const FORM_FIELDS = [
        'dispo_text' => 'Disponible pour un stage · 8-12 semaines',
        'hero_name' => 'Yann Le Flohic',
        'hero_avatar' => '',
        'hero_dev' => 'Développeur web full-stack — je conçois et code des interfaces sur-mesure, du front au back, jusqu\'à l\'hébergement.',
        'hero_photo' => 'Photographe — je capture la lumière, la matière et l\'instant, entre portraits, rue et paysages cinématographiques.',
        'location' => 'Châteauroux — FR',
        'status' => '2° année MMI',
        'parcours_intro' => 'Étudiant en deuxième année de BUT MMI et développeur freelance, je navigue entre le développement web sur-mesure et la photographie.',
        'skills_tech' => 'HTML / CSS, JavaScript, React, PHP, SQL, Python, Sass, BEM CSS, Tailwind, Bootstrap, Git / Github',
        'skills_tools' => 'VS Code, Figma, Shell, Adobe, Affinity, Apache, Mac',
        'project_cat_1' => 'Scolaires',
        'project_cat_2' => 'Personnels',
        'project_cat_3' => 'Professionnels',
        'compare_before' => '',
        'compare_after' => '',
        'compare_title' => '',
        'compare_caption' => '',
        'compare_tools' => 'Lightroom Classic, DaVinci Resolve',
        'contact_title' => 'Un projet web ou photo ?',
        'contact_subtitle' => 'Parlons-en.',
        'interests' => 'Open Source, Développement web, Photographie, Gaming, Badminton, Apple',
    ];

    /** Lignes d'exemple proposées dans le formulaire quand une liste est vide. */
    private const FORM_STARTERS = [
        'parcours' => [
            ['period' => '2025 — Actuel', 'type' => 'Formation', 'title' => 'BUT Métiers du Multimédia et de l\'Internet (MMI)', 'detail' => 'Parcours Web et dispositifs interactifs · IUT du Limousin, Limoges'],
            ['period' => '2025 — Actuel', 'type' => 'Expérience', 'title' => 'Freelance développeur Full-stack', 'detail' => 'Développement web sur-mesure, gestion de projets clients et intégration UI/UX.'],
        ],
        'langues' => [
            ['name' => 'Français', 'level' => 'Natif', 'percent' => 100],
            ['name' => 'Espagnol', 'level' => 'B2+ / C1', 'percent' => 82],
        ],
        'photos' => [
            ['url' => '/assets/images/gallery/gallery-6.webp', 'unsplash_id' => '', 'legend' => 'FLEURS ROSES · LUMIÈRE NATURELLE', 'alt' => 'Fleurs du jardin roses', 'format' => 'portrait'],
            ['url' => '/assets/images/gallery/gallery-1.webp', 'unsplash_id' => '', 'legend' => 'PAYSAGE · MARMOTTES', 'alt' => 'Paysage Marmottes', 'format' => 'landscape'],
        ],
        'contacts' => [
            ['label' => 'Email', 'value' => 'yann.lfhc@icloud.com', 'href' => 'mailto:yann.lfhc@icloud.com'],
            ['label' => 'Téléphone', 'value' => '+33 6 46 13 31 35', 'href' => 'tel:+33646133135'],
            ['label' => 'GitHub', 'value' => 'github.com/YannLf3', 'href' => 'https://github.com/YannLf3'],
            ['label' => 'LinkedIn', 'value' => 'in/yann-le-flohic', 'href' => 'https://linkedin.com/in/yann-le-flohic'],
        ],
        'snippets' => [
            [
                'title' => 'Glow Effect & Dynamic Border',
                'category' => 'CSS Modern',
                'badge' => 'Tested on Chrome / Safari',
                'html' => '<button class="glow-button">Survole-moi !</button>',
                'css' => ".glow-button {\n  background: #18181b;\n  border: 1px solid transparent;\n}",
                'js' => '// Pas de JavaScript requis',
            ],
        ],
        'articles' => [
            [
                'title' => "Passer d'Illustrator à Affinity V3 : Mon retour d'expérience en vectoriel",
                'meta' => 'AFFINITY DESIGNER — 12 SEP 2026',
                'content' => "Après plusieurs mois d'utilisation intensive d'Affinity Designer V3 dans mes projets web...",
            ],
        ],
    ];

    /** Champs de chaque ligne de liste, avec leur valeur affichée s'ils sont absents. */
    private const ROW_FIELDS = [
        'parcours' => ['period' => '', 'type' => '', 'title' => '', 'detail' => ''],
        'langues' => ['name' => '', 'level' => '', 'percent' => 80],
        'photos' => ['url' => '', 'unsplash_id' => '', 'legend' => '', 'alt' => '', 'format' => ''],
        'contacts' => ['label' => '', 'value' => '', 'href' => ''],
        'snippets' => ['title' => '', 'category' => '', 'badge' => '', 'html' => '', 'css' => '', 'js' => ''],
        'articles' => ['title' => '', 'meta' => '', 'content' => ''],
    ];

    /**
     * Refuse l'accès à toute personne non authentifiée.
     */
    public function __construct()
    {
        if (!$this->isAdmin()) {
            $this->redirect('/login');
        }
    }

    /**
     * Affiche le dashboard avec les contenus de la BDD et l'éventuel message flash.
     *
     * @return void
     */
    public function index(): void
    {
        $settings = Setting::load();

        // Message flash : lu puis supprimé (pattern Post/Redirect/Get)
        $flash = $_SESSION[self::FLASH_KEY] ?? null;
        unset($_SESSION[self::FLASH_KEY]);

        $this->render('admin/dashboard', array_merge(
            [
                'title' => 'Back-Office Admin',
                'flash' => $this->flashView($flash),
                'fields' => $this->formFields($settings),
                'projects' => array_map(static fn(Project $project): array => $project->toArray(), Project::findAll()),
            ],
            $this->formLists($settings)
        ));
    }

    /**
     * Enregistre le formulaire du back-office.
     * Ordre : taille de requête → jeton CSRF → images → liste blanche (modèles) → transaction → redirection.
     *
     * @return never
     */
    public function save(): never
    {
        // Requête plus lourde que post_max_size : PHP vide alors $_POST ET $_FILES.
        // On l'explique clairement au lieu d'afficher un faux « jeton invalide ».
        if ($_POST === [] && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            $this->redirectWithFlash('error', 'Envoi trop volumineux : réduisez le poids ou le nombre d\'images envoyées en une fois.');
        }

        // Protection CSRF : sans jeton valide, AUCUNE donnée n'est traitée
        if (!Csrf::isValid($_POST[Csrf::FIELD] ?? null)) {
            error_log('AdminController::save — jeton CSRF invalide (IP ' . ($_SERVER['REMOTE_ADDR'] ?? '?') . ')');
            $this->redirectWithFlash('error', 'Jeton de sécurité invalide ou expiré : rechargez la page puis recommencez. Aucune modification n\'a été enregistrée.');
        }

        $data = $_POST;
        unset($data[Csrf::FIELD]);

        // Images : validation stricte (type réel, taille, nom aléatoire) avant d'écrire leur URL dans les données
        $uploader = new ImageUploader(self::UPLOAD_DIR, self::UPLOAD_URL);
        $data = $this->attachUploads($uploader, $data);

        // Les modèles ne retiennent que les clés de leur liste blanche.
        // Les projets ne sont remplacés que s'ils figurent dans l'envoi (une clé absente n'efface rien).
        $settings = Setting::fromInput($data);
        $projects = isset($data['projects']) && is_array($data['projects']) ? Project::fromInput($data['projects']) : null;

        try {
            // Réglages et projets enregistrés ensemble : tout ou rien
            runInTransaction(static function (PDO $db) use ($settings, $projects): void {
                $settings->save($db);
                if ($projects !== null) {
                    Project::replaceAll($db, $projects);
                }
            });
        } catch (Throwable $e) {
            error_log('AdminController::save — ' . $e->getMessage());
            $this->redirectWithFlash('error', 'Erreur lors de l\'enregistrement : aucune modification n\'a été appliquée.');
        }

        $uploadErrors = $uploader->getErrors();
        if ($uploadErrors !== []) {
            $this->redirectWithFlash('warning', 'Contenus enregistrés, mais certaines images ont été refusées :', $uploadErrors);
        }
        $this->redirectWithFlash('success', 'Modifications enregistrées.');
    }

    /**
     * Enregistre les images envoyées et reporte leur URL dans les données du formulaire
     * (un fichier envoyé remplace l'URL saisie dans le champ texte correspondant).
     *
     * @param ImageUploader        $uploader Service d'upload.
     * @param array<string, mixed> $data     Données du formulaire.
     * @return array<string, mixed> Données complétées.
     */
    private function attachUploads(ImageUploader $uploader, array $data): array
    {
        $avatar = $uploader->store($_FILES['hero_avatar_file'] ?? [], 'avatar');
        if ($avatar !== null) {
            $data['hero_avatar'] = $avatar;
        }

        $projectImages = $uploader->storeIndexed($_FILES['projects_image_file'] ?? [], 'proj');
        array_walk($projectImages, static function (string $url, int|string $index) use (&$data): void {
            $data['projects'][$index]['image'] = $url;
        });

        $photoImages = $uploader->storeIndexed($_FILES['photos_url_file'] ?? [], 'photo');
        array_walk($photoImages, static function (string $url, int|string $index) use (&$data): void {
            $data['photos'][$index]['url'] = $url;
        });

        // Comparateur avant / après
        $compareImages = array_map(
            static fn(string $key): ?string => $uploader->store($_FILES[$key . '_file'] ?? [], 'compare'),
            ['compare_before' => 'compare_before', 'compare_after' => 'compare_after']
        );

        return array_merge($data, array_filter($compareImages, static fn(?string $url): bool => $url !== null));
    }

    /**
     * Valeurs des champs texte du formulaire : valeur enregistrée, sinon valeur proposée.
     *
     * @param Setting $settings
     * @return array<string, mixed>
     */
    private function formFields(Setting $settings): array
    {
        return array_combine(
            array_keys(self::FORM_FIELDS),
            array_map(
                static fn(string $key, string $default): mixed => $settings->getRaw($key) ?? $default,
                array_keys(self::FORM_FIELDS),
                self::FORM_FIELDS
            )
        );
    }

    /**
     * Lignes des listes répétables (parcours, langues, photos, contacts, snippets, articles).
     * Une liste vide affiche ses lignes d'exemple ; chaque champ absent prend sa valeur d'affichage.
     *
     * @param Setting $settings
     * @return array<string, list<array<string, mixed>>|array<int|string, array<string, mixed>>>
     */
    private function formLists(Setting $settings): array
    {
        return array_combine(
            array_keys(self::ROW_FIELDS),
            array_map(
                static function (string $key, array $fields) use ($settings): array {
                    $list = $settings->getRaw($key);
                    $rows = is_array($list) && $list !== [] ? $list : self::FORM_STARTERS[$key];
                    return array_map(static fn(array $row): array => self::fillRow($row, $fields), self::rows($rows));
                },
                array_keys(self::ROW_FIELDS),
                self::ROW_FIELDS
            )
        );
    }

    /**
     * Prépare l'affichage du message flash (rôle ARIA, couleur, détails).
     *
     * @param mixed $flash Message lu en session.
     * @return array{role: string, class: string, message: string, details: list<string>}|null
     */
    private function flashView(mixed $flash): ?array
    {
        if (empty($flash) || !is_array($flash)) {
            return null;
        }

        $details = !empty($flash['details']) && is_array($flash['details']) ? $flash['details'] : [];

        return [
            'role' => ($flash['type'] ?? '') === 'error' ? 'alert' : 'status',
            'class' => self::FLASH_STYLES[$flash['type'] ?? 'error'] ?? self::FLASH_STYLES['error'],
            'message' => $flash['message'] ?? '',
            'details' => array_map('strval', $details),
        ];
    }

    /**
     * Mémorise un message pour le prochain affichage du dashboard puis redirige (Post/Redirect/Get).
     *
     * @param 'success'|'warning'|'error' $type    Nature du message.
     * @param string                      $message Message principal.
     * @param list<string>                $details Détails optionnels (ex : images refusées).
     * @return never
     */
    private function redirectWithFlash(string $type, string $message, array $details = []): never
    {
        $_SESSION[self::FLASH_KEY] = ['type' => $type, 'message' => $message, 'details' => $details];
        $this->redirect('/admin');
    }
}
