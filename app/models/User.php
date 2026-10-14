<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Compte administrateur du back-office (table `users`).
 * Le mot de passe n'est jamais manipulé en clair : seule son empreinte (bcrypt) est stockée.
 */
final class User
{
    private ?int $id = null;
    private string $email = '';
    private string $passwordHash = '';

    /**
     * Recherche un compte par son adresse e-mail.
     *
     * @param string $email Adresse saisie dans le formulaire de connexion.
     * @return User|null Compte trouvé, ou null s'il n'existe pas.
     */
    public static function findByEmail(string $email): ?User
    {
        $stmt = getPDOConnection()->prepare('SELECT id, email, password FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            return null;
        }

        return (new self())
            ->setId((int)$row['id'])
            ->setEmail((string)$row['email'])
            ->setPasswordHash((string)$row['password']);
    }

    /**
     * Vérifie un mot de passe saisi contre l'empreinte enregistrée.
     *
     * @param string $password Mot de passe en clair (saisie du formulaire).
     * @return bool true si le mot de passe correspond.
     */
    public function verifyPassword(string $password): bool
    {
        return $this->passwordHash !== '' && password_verify($password, $this->passwordHash);
    }

    /** @return int|null Identifiant BDD. */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return static
     */
    public function setId(?int $id): static
    {
        $this->id = $id;
        return $this;
    }

    /** @return string */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return static
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @param string $passwordHash Empreinte bcrypt issue de la BDD.
     * @return static
     */
    public function setPasswordHash(string $passwordHash): static
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }
}
