<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/settings.php';
require_once __DIR__ . '/../core/Csrf.php';
require_once __DIR__ . '/../core/ImageUploader.php';

/**
 * Back-office : affichage et enregistrement des contenus du site.
 * Toutes les actions exigent une session administrateur (vérifiée dès le constructeur).
 */
class AdminController
{
    /** Dossier physique des images envoyées et préfixe d'URL correspondant. */
    private const UPLOAD_DIR = __DIR__ . '/../../public/assets/uploads/';
    private const UPLOAD_URL = '/assets/uploads/';

    /** Clé du message flash (affiché une seule fois sur le dashboard). */
    private const FLASH_KEY = 'admin_flash';

    /**
     * Refuse l'accès à toute personne non authentifiée.
     */
    public function __construct()
    {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Affiche le dashboard avec les contenus de la BDD et l'éventuel message flash.
     *
     * @return void
     */
    public function index(): void
    {
        $content = Setting::getAll();

        // Tableaux répétables isolés pour simplifier la vue
        $parcours = $content['parcours'] ?? [];
        $langues = $content['langues'] ?? [];
        $projects = $content['projects'] ?? [];
        $photos = $content['photos'] ?? [];
        $contacts = $content['contacts'] ?? [];
        $snippets = $content['snippets'] ?? [];
        $articles = $content['articles'] ?? [];

        // Message flash : lu puis supprimé (pattern Post/Redirect/Get)
        $flash = $_SESSION[self::FLASH_KEY] ?? null;
        unset($_SESSION[self::FLASH_KEY]);

        $title = 'Back-Office Admin';

        require_once __DIR__ . '/../views/admin/dashboard.php';
    }

    /**
     * Enregistre le formulaire du back-office.
     * Ordre : méthode → taille de requête → jeton CSRF → images → liste blanche (modèle) → redirection.
     *
     * @return never
     */
    public function save(): never
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin');
            exit;
        }

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

        $avatar = $uploader->store($_FILES['hero_avatar_file'] ?? [], 'avatar');
        if ($avatar !== null) {
            $data['hero_avatar'] = $avatar;
        }
        foreach ($uploader->storeIndexed($_FILES['projects_image_file'] ?? [], 'proj') as $index => $url) {
            $data['projects'][$index]['image'] = $url;
        }
        foreach ($uploader->storeIndexed($_FILES['photos_url_file'] ?? [], 'photo') as $index => $url) {
            $data['photos'][$index]['url'] = $url;
        }
        // Comparateur avant / après : un fichier envoyé remplace l'URL saisie
        foreach (['compare_before', 'compare_after'] as $key) {
            $url = $uploader->store($_FILES[$key . '_file'] ?? [], 'compare');
            if ($url !== null) {
                $data[$key] = $url;
            }
        }

        // Le modèle n'enregistre que les clés de sa liste blanche, en une transaction
        try {
            Setting::updateAll($data);
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
        header('Location: /admin');
        exit;
    }
}
