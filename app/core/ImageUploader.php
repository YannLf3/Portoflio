<?php
declare(strict_types=1);

/**
 * Réception sécurisée des images envoyées depuis le back-office.
 *
 * Chaque fichier doit passer TOUTES les vérifications, sinon il est ignoré et
 * une erreur lisible est enregistrée (getErrors()) :
 *  1. code d'erreur PHP = UPLOAD_ERR_OK et fichier réellement issu d'un upload HTTP ;
 *  2. taille ≤ MAX_BYTES ;
 *  3. type MIME RÉEL détecté dans le contenu (finfo), jamais celui annoncé par le navigateur ;
 *  4. le contenu se décode comme une image (getimagesize) ;
 *  5. nom de fichier régénéré aléatoirement, extension déduite du type détecté :
 *     le nom d'origine (ex : « photo.php.jpg ») n'est jamais réutilisé.
 * SVG volontairement exclu : c'est du XML qui peut embarquer du JavaScript.
 */
final class ImageUploader
{
    /** Taille maximale d'une image (8 Mo). La limite PHP upload_max_filesize s'applique aussi. */
    private const MAX_BYTES = 8 * 1024 * 1024;

    /** Liste blanche : type MIME détecté → extension enregistrée. */
    private const ALLOWED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    /** @var list<string> Messages d'erreur destinés à l'administrateur. */
    private array $errors = [];

    /**
     * @param string $targetDir    Dossier de destination sur le disque (ex : public/assets/uploads/).
     * @param string $publicPrefix Préfixe d'URL correspondant (ex : /assets/uploads/).
     */
    public function __construct(
        private readonly string $targetDir,
        private readonly string $publicPrefix
    ) {
    }

    /**
     * Valide et enregistre UN fichier.
     *
     * @param array<string, mixed> $file   Entrée de $_FILES (name, type, tmp_name, error, size).
     * @param string               $prefix Préfixe du nom généré (avatar, proj, photo).
     * @return string|null URL publique du fichier enregistré, ou null (aucun fichier / fichier refusé).
     */
    public function store(array $file, string $prefix): ?string
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        // Champ laissé vide : ce n'est pas une erreur, l'image existante est conservée
        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $label = $this->displayName($file);

        if ($error !== UPLOAD_ERR_OK) {
            return $this->reject($label, $this->uploadErrorMessage((int)$error));
        }

        $tmpPath = (string)($file['tmp_name'] ?? '');
        // Garantit que le fichier provient bien d'un upload HTTP (et non d'un chemin forgé)
        if (!is_uploaded_file($tmpPath)) {
            return $this->reject($label, 'fichier d\'origine invalide');
        }

        $size = (int)($file['size'] ?? 0);
        if ($size <= 0 || $size > self::MAX_BYTES) {
            return $this->reject($label, 'taille invalide (8 Mo maximum)');
        }

        // Type réel lu dans les octets du fichier ($file['type'] est fourni par le client : non fiable)
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmpPath);
        if (!is_string($mime) || !isset(self::ALLOWED_TYPES[$mime])) {
            return $this->reject($label, 'type non autorisé (JPEG, PNG, WebP ou GIF uniquement)');
        }

        // Double contrôle : un fichier qui « ressemble » à une image doit aussi se décoder comme telle
        if (@getimagesize($tmpPath) === false) {
            return $this->reject($label, 'le contenu n\'est pas une image valide');
        }

        if (!$this->ensureTargetDir()) {
            return $this->reject($label, 'dossier de destination inaccessible');
        }

        // Nom imprévisible : horodatage (tri chronologique) + 16 caractères aléatoires
        $filename = sprintf('%s_%d_%s.%s', $prefix, time(), bin2hex(random_bytes(8)), self::ALLOWED_TYPES[$mime]);

        if (!move_uploaded_file($tmpPath, $this->targetDir . $filename)) {
            return $this->reject($label, 'échec de l\'enregistrement sur le serveur');
        }
        // Lecture seule pour les autres utilisateurs du serveur, jamais exécutable
        @chmod($this->targetDir . $filename, 0644);

        return $this->publicPrefix . $filename;
    }

    /**
     * Valide et enregistre les fichiers d'un champ multiple (ex : projects_image_file[0], [1]…).
     * PHP range ces champs « par propriété » (name[i], tmp_name[i]…) : on reconstitue chaque fichier.
     *
     * @param array<string, mixed> $field  Entrée de $_FILES pour un champ indexé.
     * @param string               $prefix Préfixe du nom généré.
     * @return array<int|string, string> URL publique de chaque fichier accepté, par index de ligne.
     */
    public function storeIndexed(array $field, string $prefix): array
    {
        if (!isset($field['tmp_name']) || !is_array($field['tmp_name'])) {
            return [];
        }

        $stored = [];
        foreach (array_keys($field['tmp_name']) as $index) {
            $file = [
                'name' => $field['name'][$index] ?? '',
                'tmp_name' => $field['tmp_name'][$index] ?? '',
                'error' => $field['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $field['size'][$index] ?? 0,
            ];
            $url = $this->store($file, $prefix);
            if ($url !== null) {
                $stored[$index] = $url;
            }
        }
        return $stored;
    }

    /**
     * Erreurs rencontrées pendant les envois.
     *
     * @return list<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Enregistre une erreur et signale le refus.
     *
     * @param string $label  Nom affichable du fichier.
     * @param string $reason Motif du refus.
     * @return string|null Toujours null (type ?string pour rester compatible PHP 8.1).
     */
    private function reject(string $label, string $reason): ?string
    {
        $this->errors[] = sprintf('« %s » ignoré : %s.', $label, $reason);
        return null;
    }

    /**
     * Nom d'origine raccourci, uniquement pour les messages (jamais utilisé sur le disque).
     *
     * @param array<string, mixed> $file
     * @return string
     */
    private function displayName(array $file): string
    {
        $name = basename((string)($file['name'] ?? 'fichier'));
        return mb_strimwidth($name !== '' ? $name : 'fichier', 0, 60, '…');
    }

    /**
     * Crée le dossier de destination si besoin (0755 : pas d'écriture pour « les autres »).
     *
     * @return bool true si le dossier existe et est accessible en écriture.
     */
    private function ensureTargetDir(): bool
    {
        if (!is_dir($this->targetDir) && !mkdir($this->targetDir, 0755, true) && !is_dir($this->targetDir)) {
            return false;
        }
        return is_writable($this->targetDir);
    }

    /**
     * Traduit un code d'erreur d'upload PHP en message compréhensible.
     *
     * @param int $code Constante UPLOAD_ERR_*.
     * @return string
     */
    private function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'fichier trop volumineux pour le serveur',
            UPLOAD_ERR_PARTIAL => 'envoi interrompu',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION => 'erreur de configuration du serveur',
            default => 'erreur d\'envoi inconnue',
        };
    }
}
