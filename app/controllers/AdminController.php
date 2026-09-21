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