<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/UrlGuard.php';

/**
 * Projet du portfolio, stocké dans la table `projects` (une ligne par projet, ordre d'affichage = position).
 *
 * Sécurité : seuls les champs de FIELDS sont lus depuis le formulaire du back-office (liste blanche),
 * et toutes les requêtes sont préparées.
 */
final class Project
{
    /** Champs texte éditables dans le back-office. */
    private const FIELDS = [
        'title', 'meta', 'description', 'image', 'link', 'category', 'tags',
        'year', 'role', 'context', 'solution', 'result', 'repo_link', 'gallery',
    ];

    private ?int $id = null;
    private int $position = 0;
    private string $title = '';
    private string $meta = '';
    private string $description = '';
    private string $image = '';
    private string $link = '';
    private string $category = '';
    private string $tags = '';
    private string $year = '';
    private string $role = '';
    private string $context = '';
    private string $solution = '';
    private string $result = '';
    private string $repoLink = '';
    /** Images de la galerie du projet : une URL par ligne. */
    private string $gallery = '';

    /**
     * Lit tous les projets, dans l'ordre d'affichage.
     *
     * @return list<Project>
     */
    public static function findAll(): array
    {
        $stmt = getPDOConnection()->prepare('
            SELECT id, position, title, meta, description, image, link, category, tags,
                   year, role, context, solution, result, repo_link, gallery
            FROM projects
            ORDER BY position ASC, id ASC
        ');
        $stmt->execute();

        return array_map([self::class, 'fromRow'], $stmt->fetchAll());
    }

    /**
     * Lit un projet par son identifiant.
     *
     * @param int $id Identifiant BDD.
     * @return Project|null Le projet, ou null s'il n'existe pas.
     */
    public static function findById(int $id): ?Project
    {
        $stmt = getPDOConnection()->prepare('
            SELECT id, position, title, meta, description, image, link, category, tags,
                   year, role, context, solution, result, repo_link, gallery
            FROM projects
            WHERE id = :id
        ');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return is_array($row) ? self::fromRow($row) : null;
    }

    /**
     * Construit les projets saisis dans le back-office, dans l'ordre du formulaire.
     * Les lignes non conformes (non tableau) sont ignorées ; un champ absent ou non texte devient vide.
     *
     * @param array<int|string, mixed> $rows Lignes « projects[i][champ] » du formulaire.
     * @return list<Project>
     */
    public static function fromInput(array $rows): array
    {
        $validRows = array_values(array_filter($rows, 'is_array'));

        return array_map(
            static fn(array $row, int $position): Project => self::fromRow(array_merge(
                self::sanitize($row),
                ['id' => $row['id'] ?? null, 'position' => $position]
            )),
            $validRows,
            array_keys($validRows)
        );
    }

    /**
     * Remplace la liste des projets par celle du back-office :
     * supprime les projets retirés, puis enregistre chaque projet à sa position.
     * Sans transaction propre : l'appelant l'englobe dans runInTransaction().
     *
     * @param PDO           $db       Connexion de la transaction en cours.
     * @param list<Project> $projects Projets dans l'ordre d'affichage.
     * @return void
     */
    public static function replaceAll(PDO $db, array $projects): void
    {
        $keptIds = array_values(array_filter(array_map(
            static fn(Project $project): ?int => $project->getId(),
            $projects
        )));
        self::deleteAllExcept($db, $keptIds);

        array_walk($projects, static fn(Project $project): Project => $project->save($db));
    }

    /**
     * Insère le projet, ou le met à jour s'il possède déjà un identifiant.
     *
     * @param PDO $db Connexion à utiliser.
     * @return static
     */
    public function save(PDO $db): static
    {
        $stmt = $db->prepare('
            INSERT INTO projects (id, position, title, meta, description, image, link, category, tags,
                                  year, role, context, solution, result, repo_link, gallery)
            VALUES (:id, :position, :title, :meta, :description, :image, :link, :category, :tags,
                    :year, :role, :context, :solution, :result, :repo_link, :gallery)
            ON DUPLICATE KEY UPDATE
                position = VALUES(position), title = VALUES(title), meta = VALUES(meta),
                description = VALUES(description), image = VALUES(image), link = VALUES(link),
                category = VALUES(category), tags = VALUES(tags),
                year = VALUES(year), role = VALUES(role), context = VALUES(context),
                solution = VALUES(solution), result = VALUES(result),
                repo_link = VALUES(repo_link), gallery = VALUES(gallery)
        ');
        $stmt->execute([
            'id' => $this->id,
            'position' => $this->position,
            'title' => $this->title,
            'meta' => $this->meta,
            'description' => $this->description,
            'image' => $this->image,
            'link' => $this->link,
            'category' => $this->category,
            'tags' => $this->tags,
            'year' => $this->year,
            'role' => $this->role,
            'context' => $this->context,
            'solution' => $this->solution,
            'result' => $this->result,
            'repo_link' => $this->repoLink,
            'gallery' => $this->gallery,
        ]);

        // Nouveau projet : l'identifiant attribué par MySQL est conservé dans l'objet
        if ($this->id === null) {
            $this->id = (int)$db->lastInsertId();
        }
        return $this;
    }

    /**
     * Données publiques du projet (format attendu par les vues et l'API).
     *
     * @return array<string, int|string|null>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'meta' => $this->meta,
            'description' => $this->description,
            'image' => $this->image,
            'link' => $this->link,
            'category' => $this->category,
            'tags' => $this->tags,
            'year' => $this->year,
            'role' => $this->role,
            'context' => $this->context,
            'solution' => $this->solution,
            'result' => $this->result,
            'repo_link' => $this->repoLink,
            'gallery' => $this->gallery,
        ];
    }

    /** @return int|null Identifiant BDD (null tant que le projet n'est pas enregistré). */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id Identifiant BDD ; une valeur nulle ou négative signifie « nouveau projet ».
     * @return static
     */
    public function setId(?int $id): static
    {
        $this->id = $id !== null && $id > 0 ? $id : null;
        return $this;
    }

    /** @return int Position d'affichage (0 = premier). */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position Position d'affichage (0 = premier).
     * @return static
     */
    public function setPosition(int $position): static
    {
        $this->position = max(0, $position);
        return $this;
    }

    /** @return string */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return static
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    /** @return string Ligne d'information (ex : « Client · Full-Stack »). */
    public function getMeta(): string
    {
        return $this->meta;
    }

    /**
     * @param string $meta
     * @return static
     */
    public function setMeta(string $meta): static
    {
        $this->meta = $meta;
        return $this;
    }

    /** @return string */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return static
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    /** @return string URL de l'image (chemin local ou adresse externe). */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @param string $image
     * @return static
     */
    public function setImage(string $image): static
    {
        $this->image = $image;
        return $this;
    }

    /** @return string URL du projet. */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $link
     * @return static
     */
    public function setLink(string $link): static
    {
        $this->link = $link;
        return $this;
    }

    /** @return string Catégorie utilisée par les filtres de la page d'accueil. */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param string $category
     * @return static
     */
    public function setCategory(string $category): static
    {
        $this->category = $category;
        return $this;
    }

    /** @return string Tags techniques séparés par des virgules. */
    public function getTags(): string
    {
        return $this->tags;
    }

    /**
     * @param string $tags
     * @return static
     */
    public function setTags(string $tags): static
    {
        $this->tags = $tags;
        return $this;
    }

    /** @return string Année de réalisation. */
    public function getYear(): string
    {
        return $this->year;
    }

    /**
     * @param string $year
     * @return static
     */
    public function setYear(string $year): static
    {
        $this->year = $year;
        return $this;
    }

    /** @return string Rôle tenu sur le projet. */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @param string $role
     * @return static
     */
    public function setRole(string $role): static
    {
        $this->role = $role;
        return $this;
    }

    /** @return string Contexte et objectifs. */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * @param string $context
     * @return static
     */
    public function setContext(string $context): static
    {
        $this->context = $context;
        return $this;
    }

    /** @return string Solution apportée. */
    public function getSolution(): string
    {
        return $this->solution;
    }

    /**
     * @param string $solution
     * @return static
     */
    public function setSolution(string $solution): static
    {
        $this->solution = $solution;
        return $this;
    }

    /** @return string Résultat obtenu. */
    public function getResult(): string
    {
        return $this->result;
    }

    /**
     * @param string $result
     * @return static
     */
    public function setResult(string $result): static
    {
        $this->result = $result;
        return $this;
    }

    /** @return string URL du dépôt de code (optionnelle). */
    public function getRepoLink(): string
    {
        return $this->repoLink;
    }

    /**
     * @param string $repoLink
     * @return static
     */
    public function setRepoLink(string $repoLink): static
    {
        $this->repoLink = $repoLink;
        return $this;
    }

    /** @return string Images de la galerie, une URL par ligne. */
    public function getGallery(): string
    {
        return $this->gallery;
    }

    /**
     * @param string $gallery
     * @return static
     */
    public function setGallery(string $gallery): static
    {
        $this->gallery = $gallery;
        return $this;
    }

    /**
     * Images de la galerie sous forme de liste d'URL sûres (lignes vides et schémas dangereux écartés).
     *
     * @return list<string>
     */
    public function getGalleryList(): array
    {
        $urls = array_map(
            static fn(string $line): string => UrlGuard::safe(trim($line)),
            preg_split('/\R/', $this->gallery) ?: []
        );

        return array_values(array_filter($urls, static fn(string $url): bool => $url !== ''));
    }

    /**
     * Hydrate un projet depuis une ligne de BDD (ou une ligne de formulaire déjà filtrée).
     *
     * @param array<string, mixed> $row
     * @return Project
     */
    private static function fromRow(array $row): Project
    {
        // Identifiant : entier positif uniquement (champ caché vide = nouveau projet)
        $id = filter_var($row['id'] ?? null, FILTER_VALIDATE_INT);

        return (new self())
            ->setId($id === false ? null : $id)
            ->setPosition((int)($row['position'] ?? 0))
            ->setTitle((string)($row['title'] ?? ''))
            ->setMeta((string)($row['meta'] ?? ''))
            ->setDescription((string)($row['description'] ?? ''))
            // Image et lien : schémas dangereux (« javascript: »…) supprimés
            ->setImage(UrlGuard::safe($row['image'] ?? ''))
            ->setLink(UrlGuard::safe($row['link'] ?? ''))
            ->setCategory((string)($row['category'] ?? ''))
            ->setTags((string)($row['tags'] ?? ''))
            ->setYear((string)($row['year'] ?? ''))
            ->setRole((string)($row['role'] ?? ''))
            ->setContext((string)($row['context'] ?? ''))
            ->setSolution((string)($row['solution'] ?? ''))
            ->setResult((string)($row['result'] ?? ''))
            // Dépôt : schémas dangereux supprimés ; galerie nettoyée à la lecture (getGalleryList)
            ->setRepoLink(UrlGuard::safe($row['repo_link'] ?? ''))
            ->setGallery((string)($row['gallery'] ?? ''));
    }

    /**
     * Ne conserve que les champs autorisés d'une ligne de formulaire, convertis en chaînes.
     *
     * @param array<string, mixed> $row Ligne brute du formulaire.
     * @return array<string, string>
     */
    private static function sanitize(array $row): array
    {
        return array_combine(self::FIELDS, array_map(
            // Champ absent ou non scalaire (tableau injecté) → chaîne vide
            static fn(string $field): string => is_scalar($row[$field] ?? '') ? (string)($row[$field] ?? '') : '',
            self::FIELDS
        ));
    }

    /**
     * Supprime les projets dont l'identifiant ne figure pas dans la liste conservée.
     *
     * @param PDO       $db      Connexion à utiliser.
     * @param list<int> $keptIds Identifiants à conserver (liste vide = tout supprimer).
     * @return void
     */
    private static function deleteAllExcept(PDO $db, array $keptIds): void
    {
        if ($keptIds === []) {
            $db->prepare('DELETE FROM projects')->execute();
            return;
        }

        // Un marqueur « ? » par identifiant conservé : la requête reste préparée quelle que soit la taille de la liste
        $placeholders = implode(', ', array_fill(0, count($keptIds), '?'));
        $db->prepare("DELETE FROM projects WHERE id NOT IN ($placeholders)")->execute($keptIds);
    }
}
