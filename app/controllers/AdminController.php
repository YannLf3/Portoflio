<?php
require_once __DIR__ . '/../models/settings.php';

class AdminController
{

    public function __construct()
    {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: /login');
            exit;
        }
    }

    // Affiche le Dashboard avec les vraies données
    public function index(): void
    {
        // On charge tout le contenu depuis la BDD
        $content = Setting::getAll();

        // On isole les tableaux répétables pour faciliter la vue
        $parcours = $content['parcours'] ?? [];
        $langues = $content['langues'] ?? [];
        $projects = $content['projects'] ?? [];
        $photos = $content['photos'] ?? [];
        $contacts = $content['contacts'] ?? [];

        // Extraction dynamique pour le Lab
        $snippets = $content['snippets'] ?? [];
        $articles = $content['articles'] ?? [];

        $title = "Back-Office Admin";

        require_once __DIR__ . '/../views/admin/dashboard.php';
    }

    // Méthode pour sauvegarder les données du formulaire
    public function save(): void
    {

        $uploadDir = __DIR__ . '/../../public/assets/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

// 1. Upload Avatar Hero
        if (!empty($_FILES['hero_avatar_file']['tmp_name'])) {
            $filename = time() . '_avatar_' . basename($_FILES['hero_avatar_file']['name']);
            move_uploaded_file($_FILES['hero_avatar_file']['tmp_name'], $uploadDir . $filename);
            $_POST['hero_avatar'] = '/assets/uploads/' . $filename;
        }

// 2. Upload Images Projets
        if (isset($_FILES['projects_image_file'])) {
            foreach ($_FILES['projects_image_file']['tmp_name'] as $idx => $tmpName) {
                if (!empty($tmpName)) {
                    $filename = time() . '_proj_' . basename($_FILES['projects_image_file']['name'][$idx]);
                    move_uploaded_file($tmpName, $uploadDir . $filename);
                    $_POST['projects'][$idx]['image'] = '/assets/uploads/' . $filename;
                }
            }
        }

// 3. Upload Images Galerie
        if (isset($_FILES['photos_url_file'])) {
            foreach ($_FILES['photos_url_file']['tmp_name'] as $idx => $tmpName) {
                if (!empty($tmpName)) {
                    $filename = time() . '_photo_' . basename($_FILES['photos_url_file']['name'][$idx]);
                    move_uploaded_file($tmpName, $uploadDir . $filename);
                    $_POST['photos'][$idx]['url'] = '/assets/uploads/' . $filename;
                }
            }
        }

// Ensuite, le code normal continue :
// Setting::updateAll($_POST);
// header('Location: /admin');
        // On vérifie que la requête est bien en POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // On exclut les éventuels champs de contrôle inutiles en BDD
            $dataToSave = $_POST;

            // On envoie le tableau nettoyé au Modèle pour sauvegarde
            Setting::updateAll($dataToSave);

            // On redirige vers le dashboard (tu pourras ajouter un message flash de succès plus tard)
            header('Location: /admin');
            exit;
        }
    }
}