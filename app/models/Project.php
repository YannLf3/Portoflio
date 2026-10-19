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
    private const FIELDS = ['title', 'meta', 'description', 'image', 'link', 'category', 'tags'];

    private ?int $id = null;
    private int $position = 0;
    private string $title = '';
    private string $meta = '';
    private string $description = '';
    private string $image = '';
    private string $link = '';
    private string $category = '';
    private string $tags = '';

    /**
     * Lit tous les projets, dans l'ordre d'affichage.
     *
     * @return list<Project>
     */
    public static function findAll(): array
    {
        $stmt = getPDOConnection()->prepare('
            SELECT id, position, title, meta, description, image, link, category, tags
            FROM projects
            ORDER BY position ASC, id ASC
        ');
        $stmt->execute();

        return array_map([self::class, 'fromRow'], $stmt->fetchAll());
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
            INSERT INTO projects (id, position, title, meta, description, image, link, category, tags)
            VALUES (:id, :position, :title, :meta, :description, :image, :link, :category, :tags)
            ON DUPLICATE KEY UPDATE
                position = VALUES(position), title = VALUES(title), meta = VALUES(meta),
                description = VALUES(description), image = VALUES(image), link = VALUES(link),
                category = VALUES(category), tags = VALUES(tags)
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
     * @return array{id: int|null, title: string, meta: string, description: string, image: string, link: string, category: string, tags: string}
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
            ->setTags((string)($row['tags'] ?? ''));
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
