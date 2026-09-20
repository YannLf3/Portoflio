<?php
require_once __DIR__ . '/../models/Project.php';

class HomeController
{
    public function index(): void
    {
        // Optionnel : Récupérer des données de la BDD
        // $projects = Project::getAll();

        // Titre de la page
        $title = "Yann | Développeur Web & Créatif";

        // Charger les vues (layouts + page)
        require_once __DIR__ . '/../views/layouts/header.php';
        require_once __DIR__ . '/../views/front/home.php';
        require_once __DIR__ . '/../views/layouts/footer.php';
    }

    public function legal(): void
    {
        require_once __DIR__ . '/../views/front/legal.php';
    }

    // app/controllers/HomeController.php

    public function lab(): void
    {
        $title = "Lab & Experiments | Yann Le Flohic";
        require_once __DIR__ . '/../views/front/lab.php';
    }
}