<?php
declare(strict_types=1);

/**
 * Réception sécurisée du CV (un seul PDF) envoyé depuis le back-office.
 *
 * Le fichier doit passer TOUTES les vérifications, sinon il est ignoré et une erreur lisible
 * est enregistrée (getErrors()) :
 *  1. code d'erreur PHP = UPLOAD_ERR_OK et fichier réellement issu d'un upload HTTP ;
 *  2. taille ≤ MAX_BYTES ;
 *  3. type MIME RÉEL détecté dans le contenu (finfo), jamais celui annoncé par le navigateur ;
 *  4. signature « %PDF- » en tête de fichier ;
 *  5. nom de fichier régénéré aléatoirement, extension imposée .pdf : le nom d'origine n'est jamais réutilisé.
 */
final class PdfUploader
{
    /** Taille maximale du CV (5 Mo). La limite PHP upload_max_filesize s'applique aussi. */
    private const MAX_BYTES = 5 * 1024 * 1024;

    /** Forme exacte d'une adresse produite par store() : sert aussi à contrôler une valeur relue en BDD. */
    public const URL_PATTERN = '#^/assets/uploads/cv_\d+_[a-f0-9]{16}\.pdf$#';

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
     * Valide et enregistre le PDF.
     *
     * @param array<string, mixed> $file Entrée de $_FILES (name, type, tmp_name, error, size).
     * @return string|null URL publique du fichier enregistré, ou null (aucun fichier / fichier refusé).
     */
    public function store(array $file): ?string
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        // Champ laissé vide : ce n'est pas une erreur, le CV existant est conservé
        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $label = mb_strimwidth(basename((string)($file['name'] ?? 'cv.pdf')) ?: 'cv.pdf', 0, 60, '…');

        if ($error !== UPLOAD_ERR_OK) {
            return $this->reject($label, 'fichier trop volumineux pour le serveur ou envoi interrompu');
        }

        $tmpPath = (string)($file['tmp_name'] ?? '');
        // Garantit que le fichier provient bien d'un upload HTTP (et non d'un chemin forgé)
        if (!is_uploaded_file($tmpPath)) {
            return $this->reject($label, 'fichier d\'origine invalide');
        }

        $size = (int)($file['size'] ?? 0);
        if ($size <= 0 || $size > self::MAX_BYTES) {
            return $this->reject($label, 'taille invalide (5 Mo maximum)');
        }

        // Type réel lu dans les octets du fichier ($file['type'] est fourni par le client : non fiable)
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmpPath);
        $signature = (string)file_get_contents($tmpPath, false, null, 0, 5);
        if ($mime !== 'application/pdf' || $signature !== '%PDF-') {
            return $this->reject($label, 'type non autorisé (PDF uniquement)');
        }

        if (!is_dir($this->targetDir) && !mkdir($this->targetDir, 0755, true) && !is_dir($this->targetDir)) {
            return $this->reject($label, 'dossier de destination inaccessible');
        }

        // Nom imprévisible : horodatage + 16 caractères aléatoires (format décrit par URL_PATTERN)
        $filename = sprintf('cv_%d_%s.pdf', time(), bin2hex(random_bytes(8)));

        if (!move_uploaded_file($tmpPath, $this->targetDir . $filename)) {
            return $this->reject($label, 'échec de l\'enregistrement sur le serveur');
        }
        // Lecture seule pour les autres utilisateurs du serveur, jamais exécutable
        @chmod($this->targetDir . $filename, 0644);

        return $this->publicPrefix . $filename;
    }

    /**
     * Supprime un ancien CV du disque, uniquement s'il porte un nom produit par store().
     *
     * @param string $url Adresse publique de l'ancien CV (lue en BDD).
     * @return void
     */
    public function delete(string $url): void
    {
        if (preg_match(self::URL_PATTERN, $url) === 1) {
            @unlink($this->targetDir . basename($url));
        }
    }

    /**
     * Erreurs rencontrées pendant l'envoi.
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
     * @return string|null Toujours null.
     */
    private function reject(string $label, string $reason): ?string
    {
        $this->errors[] = sprintf('« %s » ignoré : %s.', $label, $reason);
        return null;
    }
}
