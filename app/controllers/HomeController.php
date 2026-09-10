<?php
require_once __DIR__ . '/../models/Project.php';

class HomeController {
    public function index() {
        // Optionnel : Récupérer des données de la BDD
        // $projects = Project::getAll();

        // Titre de la page
        $title = "Yann | Développeur Web & Créatif";

        // Charger les vues (layouts + page)
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/front/home.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
}