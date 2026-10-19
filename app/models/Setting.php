<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/UrlGuard.php';

/**
 * Contenus éditables du site, stockés en clé / valeur dans la table `settings`.
 * Les listes (parcours, photos…) sont sérialisées en JSON. Les projets ont leur propre table (modèle Project).
 *
 * Sécurité : set() n'accepte QUE les clés et sous-champs déclarés dans SCALAR_KEYS / LIST_SCHEMAS
 * (liste blanche). Un champ ajouté à la main dans la requête (mass assignment) est ignoré au lieu
 * de créer ou d'écraser un réglage.
 */
final class Setting
{
    /** Réglages texte simples (une valeur par clé). */
    private const SCALAR_KEYS = [
        'hero_name',
        'hero_dev',
        'hero_photo',
        'hero_avatar',
        'dispo_text',
        'location',
        'status',
        'parcours_intro',
        'skills_tech',
        'skills_tools',
        'interests',
        'contact_title',
        'contact_subtitle',
        'project_cat_1',
        'project_cat_2',
        'project_cat_3',
        // Comparateur avant / après (section Post-traitement du mode Photo)
        'compare_before',
        'compare_after',
        'compare_title',
        'compare_caption',
        'compare_tools',
    ];

    /** Réglages texte qui contiennent une URL : filtrés par UrlGuard (pas de « javascript: »). */
    private const URL_KEYS = ['hero_avatar', 'compare_before', 'compare_after'];

    /** Sous-champs d'URL des listes : clé de la liste → sous-champs filtrés par UrlGuard. */
    private const LIST_URL_FIELDS = [
        'photos' => ['url'],
        'contacts' => ['href'],
    ];

    /** Réglages en liste : clé → sous-champs autorisés pour chaque ligne. */
    private const LIST_SCHEMAS = [
        'parcours' => ['period', 'type', 'title', 'detail'],
        'langues' => ['name', 'level', 'percent'],
        'photos' => ['url', 'unsplash_id', 'legend', 'alt', 'format'],
        'contacts' => ['label', 'value', 'href'],
        'snippets' => ['title', 'category', 'badge', 'html', 'css', 'js'],
        'articles' => ['title', 'meta', 'content'],
    ];

    /** Valeurs affichées sur le site public tant qu'un réglage n'a jamais été enregistré en BDD. */
    public const DEFAULTS = [
        'hero_name' => 'Yann Le Flohic',
        'hero_dev' => "Développeur web full-stack — je conçois et code des interfaces sur-mesure, du front au back, jusqu'à l'hébergement.",
        'hero_photo' => "Photographe — je capture la lumière, la matière et l'instant, entre portraits, rue et paysages cinématographiques.",
        'hero_avatar' => '',
        'dispo_text' => 'Disponible pour un stage · 8-12 semaines',
        'location' => 'Châteauroux — FR',
        'status' => '2° Année MMI',
        'parcours_intro' => 'Étudiant en deuxième année de BUT MMI et développeur freelance, je navigue entre le développement web sur-mesure et la photographie.',
        'parcours' => [
            [
                'period' => '2025 — ACTUEL',
                'type' => 'FORMATION',
                'title' => 'BUT Métiers du Multimédia et de l\'Internet (MMI)',
                'detail' => 'Parcours Développement Web et Dispositifs Interactifs · IUT du Limousin, Limoges',
            ],
            [
                'period' => '2025 — ACTUEL',
                'type' => 'EXPÉRIENCE',
                'title' => 'Freelance développeur & création numérique',
                'detail' => 'Développement web front & back, design d\'interfaces, hébergement sur-mesure',
            ],
            [
                'period' => '2022 — 2025',
                'type' => 'FORMATION',
                'title' => 'Baccalauréat général — Section bi-nationale Bachibac',
                'detail' => 'BAC français et espagnol, mention bien · Lycée, Châteauroux',
            ],
        ],
        'skills_tech' => 'HTML / CSS, JavaScript, React, PHP, SQL, Python, Sass, BEM CSS, Tailwind, Bootstrap, Git / Github',
        'skills_tools' => 'VS CODE, JETBRAINS IDE, FIGMA, SHELL, ADOBE, AFFINITY, APACHE, MAC',
        'langues' => [
            ['name' => 'Français', 'level' => 'Natif', 'percent' => 100],
            ['name' => 'Espagnol', 'level' => 'B2+ / C1', 'percent' => 85],
            ['name' => 'Anglais', 'level' => 'B1', 'percent' => 60],
        ],
        'project_cat_1' => 'Scolaires',
        'project_cat_2' => 'Personnels',
        'project_cat_3' => 'Professionnels',
        'photos' => [
            ['url' => '/assets/images/gallery/gallery-6.webp', 'legend' => 'FLEURS ROSES · LUMIÈRE NATURELLE', 'alt' => 'Fleurs du jardin roses', 'format' => 'portrait'],
            ['url' => '/assets/images/gallery/gallery-1.webp', 'legend' => 'PAYSAGE · MARMOTTES', 'alt' => 'Paysage Marmottes', 'format' => 'landscape'],
            ['url' => '/assets/images/gallery/gallery-2.webp', 'legend' => 'PAYSAGE · MONTAGNE', 'alt' => 'Montagne, dans les dolomites', 'format' => 'landscape'],
            ['url' => '/assets/images/gallery/gallery-5.webp', 'legend' => "PORTRAIT · LUMIÈRE D'AUTOMNE", 'alt' => 'Expression portrait', 'format' => 'landscape'],
            ['url' => '/assets/images/gallery/gallery-3.webp', 'legend' => 'PAYSAGE · MONTAGNE', 'alt' => 'Montagne, dans les dolomites', 'format' => 'landscape'],
        ],
        'compare_after' => '/assets/images/gallery/gallery-2.webp',
        'compare_title' => "Du RAW à l'image finale.",
        'compare_caption' => "Balance des blancs, courbes, séparation des tons et grain : glissez la poignée pour comparer le fichier brut et l'étalonnage final.",
        'compare_tools' => 'Lightroom Classic, DaVinci Resolve',
        'contact_title' => 'Un projet web ou photo ?',
        'contact_subtitle' => 'Parlons-en.',
        'contacts' => [
            ['label' => 'EMAIL', 'value' => 'yann.lfhc@icloud.com', 'href' => 'mailto:yann.lfhc@icloud.com'],
            ['label' => 'TÉLÉPHONE', 'value' => '+33 6 46 13 31 35', 'href' => 'tel:+33646133135'],
            ['label' => 'GITHUB', 'value' => 'github.com/YannLf3', 'href' => 'https://github.com/YannLf3'],
            ['label' => 'LINKEDIN', 'value' => 'in/yann-le-flohic', 'href' => 'https://www.linkedin.com/in/yannlf3/'],
            ['label' => 'MALT', 'value' => 'Mon lien Malt', 'href' => 'https://www.malt.fr/profile/yannleflohic1'],
        ],
        'interests' => 'Open Source, Développement web, Photographie, Gaming, Badminton, Apple',
        'snippets' => [
            [
                'title' => 'Glow Effect & Dynamic Border',
                'category' => 'CSS Modern',
                'badge' => 'Tested on Chrome / Safari',
                'html' => '<button class="glow-button">Survole-moi !</button>',
                'css' => ".glow-button {\n  background: #18181b;\n  border: 1px solid #27272a;\n  color: #fff;\n  padding: 12px 24px;\n  border-radius: 8px;\n  cursor: pointer;\n  transition: all 0.3s;\n}\n.glow-button:hover {\n  border-color: #22c55e;\n  box-shadow: 0 0 15px rgba(34, 197, 94, 0.4);\n}",
                'js' => '// Pas de JavaScript requis',
            ],
        ],
        'articles' => [
            [
                'title' => "Passer d'Illustrator à Affinity V3 : Mon retour d'expérience en vectoriel",
                'meta' => 'AFFINITY DESIGNER — 12 SEP 2026',
                'content' => "Après plusieurs mois d'utilisation intensive d'Affinity Designer V3 dans mes projets web et UI/UX, le passage depuis la suite Adobe Creative Cloud s'est fait sans friction majeure.\n\n### Les points forts :\n- **Paiement unique** : Fin de l'abonnement mensuel Adobe.\n- **Performance** : Temps de chargement instantané sur macOS Apple Silicon.\n- **Export SVG propre** : Les fichiers générés sont légers et directement exploitables en HTML/CSS.\n\nEn résumé, pour le design d'icônes, de maquettes web et d'illustrations vectorielles, Affinity constitue une alternative d'une grande fluidité.",
            ],
            [
                'title' => 'Mon Setup macOS pour coder en PHP MVC sans Docker lourd',
                'meta' => 'MACOS / DEV — 02 SEP 2026',
                'content' => "Pour maintenir une excellente autonomie sur mon MacBook et éviter le surplus de mémoire consommé par Docker sur des projets légers, j'utilise un environnement serveur natif.\n\n### La Stack de développement :\n1. **Homebrew** pour l'installation de PHP 8.3 et MySQL.\n2. **Apache / Nginx** configuré localement avec des hôtes virtuels.\n3. **PhpStorm & VS Code** avec liaisons Git automatisées.\n4. **Raycast** pour lancer mes scripts et requêtes de base de données en un raccourci clavier.",
            ],
            [
                'title' => 'Étalonnage vidéo sur écran Apple Silicon : Retours sur DaVinci Resolve',
                'meta' => 'HARDWARE — 28 AOU 2026',
                'content' => "Travailler l'étalonnage de vidéos automobiles ou de montages réseaux sur DaVinci Resolve demande une grande rigueur sur la chaîne de couleur.\n\n### Le piège du Gamma QuickTime sous macOS :\nLes écrans Apple utilisent l'espace Display P3 avec un profil de restitution spécifique. Lors de l'exportation :\n- Configurer la Timeline sur **Rec.709-DB** ou **Rec.709 A**.\n- Activer la gestion des profils de couleur d'affichage macOS dans les préférences DaVinci Resolve.\n\nCela évite l'effet d'image délavée lors de la relecture sur Safari ou sur smartphone.",
            ],
        ],
    ];

    /** @var array<string, mixed> Réglages chargés (BDD) ou saisis (formulaire), par clé. */
    private array $values = [];

    /**
     * Lit tous les réglages en BDD ; les valeurs JSON valides sont décodées en tableaux.
     *
     * @return self
     */
    public static function load(): self
    {
        $stmt = getPDOConnection()->prepare('SELECT setting_key, setting_value FROM settings');
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $values = array_combine(
            array_column($rows, 'setting_key'),
            array_map([self::class, 'decode'], array_column($rows, 'setting_value'))
        );

        return (new self())->setValues($values);
    }

    /**
     * Construit les réglages à enregistrer depuis la saisie du back-office (filtrée par liste blanche).
     * Seules les clés PRÉSENTES dans la saisie sont retenues : une clé absente n'est jamais vidée par erreur.
     *
     * @param array<string, mixed> $input Données brutes du formulaire.
     * @return self
     */
    public static function fromInput(array $input): self
    {
        $setting = new self();
        array_walk($input, static fn(mixed $value, int|string $key): self => $setting->set((string)$key, $value));
        return $setting;
    }

    /**
     * Valeur d'un réglage, ou sa valeur par défaut (DEFAULTS) s'il n'a jamais été enregistré.
     *
     * @param string $key Clé du réglage.
     * @return mixed Chaîne, liste décodée, ou null si aucune valeur ni défaut.
     */
    public function get(string $key): mixed
    {
        return $this->values[$key] ?? self::DEFAULTS[$key] ?? null;
    }

    /**
     * Valeur brute d'un réglage, sans valeur par défaut.
     *
     * @param string $key Clé du réglage.
     * @return mixed Valeur enregistrée, ou null si absente.
     */
    public function getRaw(string $key): mixed
    {
        return $this->values[$key] ?? null;
    }

    /**
     * Texte d'un réglage débarrassé de ses espaces ; un texte vide retombe sur sa valeur par défaut.
     *
     * @param string $key Clé du réglage.
     * @return string
     */
    public function getText(string $key): string
    {
        return trim((string)($this->values[$key] ?? '')) ?: (string)(self::DEFAULTS[$key] ?? '');
    }

    /**
     * Liste d'un réglage ; une liste absente, invalide ou vide retombe sur sa valeur par défaut.
     *
     * @param string $key Clé du réglage.
     * @return array<int|string, mixed>
     */
    public function getList(string $key): array
    {
        $list = $this->values[$key] ?? null;
        return is_array($list) && $list !== [] ? $list : (self::DEFAULTS[$key] ?? []);
    }

    /**
     * Définit un réglage s'il figure dans la liste blanche (sinon l'appel est sans effet).
     * Texte : converti en chaîne. Liste : chaque ligne est réduite à ses sous-champs autorisés.
     *
     * @param string $key   Clé du réglage.
     * @param mixed  $value Valeur saisie.
     * @return static
     */
    public function set(string $key, mixed $value): static
    {
        if (in_array($key, self::SCALAR_KEYS, true) && is_scalar($value)) {
            // Un réglage d'URL dangereux (ex : « javascript:… ») est remplacé par une chaîne vide
            $this->values[$key] = in_array($key, self::URL_KEYS, true) ? UrlGuard::safe($value) : (string)$value;
        } elseif (isset(self::LIST_SCHEMAS[$key]) && is_array($value)) {
            $this->values[$key] = self::sanitizeRows($value, self::LIST_SCHEMAS[$key], self::LIST_URL_FIELDS[$key] ?? []);
        }
        return $this;
    }

    /**
     * Enregistre les réglages retenus (insertion ou mise à jour), sans ouvrir de transaction :
     * l'appelant l'englobe dans runInTransaction() pour garantir le « tout ou rien ».
     *
     * @param PDO $db Connexion de la transaction en cours.
     * @return void
     * @throws PDOException|JsonException
     */
    public function save(PDO $db): void
    {
        if ($this->values === []) {
            return;
        }

        // Marqueur distinct (:value_update) pour la partie UPDATE : PDO n'autorise pas deux fois le même nom
        $stmt = $db->prepare('
            INSERT INTO settings (setting_key, setting_value)
            VALUES (:key, :value)
            ON DUPLICATE KEY UPDATE setting_value = :value_update
        ');

        array_walk($this->values, static function (mixed $value, string $key) use ($stmt): void {
            $stored = is_array($value)
                ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
                : $value;
            $stmt->execute(['key' => $key, 'value' => $stored, 'value_update' => $stored]);
        });
    }

    /**
     * Remplace l'ensemble des réglages en mémoire.
     *
     * @param array<string, mixed> $values
     * @return static
     */
    private function setValues(array $values): static
    {
        $this->values = $values;
        return $this;
    }

    /**
     * Décode une valeur JSON (commençant par « [ » ou « { ») ; toute autre valeur est retournée telle quelle.
     *
     * @param mixed $value Valeur brute de la colonne setting_value.
     * @return mixed
     */
    private static function decode(mixed $value): mixed
    {
        if (!is_string($value) || !(str_starts_with(trim($value), '[') || str_starts_with(trim($value), '{'))) {
            return $value;
        }
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : $value;
    }

    /**
     * Réduit chaque ligne d'une liste à ses sous-champs autorisés, convertis en chaînes.
     * Liste ré-indexée (0, 1, 2…) : les lignes supprimées dans l'admin ne laissent pas de « trous ».
     *
     * @param array<int|string, mixed> $rows   Lignes saisies.
     * @param list<string>             $fields Sous-champs autorisés.
     * @param list<string>             $urlFields Sous-champs contenant une URL (filtrés par UrlGuard).
     * @return list<array<string, string>>
     */
    private static function sanitizeRows(array $rows, array $fields, array $urlFields = []): array
    {
        return array_values(array_map(
            static fn(array $row): array => array_combine($fields, array_map(
                // Sous-champ absent ou non scalaire (tableau injecté) → chaîne vide ; URL dangereuse → chaîne vide
                static function (string $field) use ($row, $urlFields): string {
                    $text = is_scalar($row[$field] ?? '') ? (string)($row[$field] ?? '') : '';
                    return in_array($field, $urlFields, true) ? UrlGuard::safe($text) : $text;
                },
                $fields
            )),
            array_filter($rows, 'is_array')
        ));
    }
}
