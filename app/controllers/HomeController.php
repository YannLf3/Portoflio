<?php
require_once __DIR__ . '/../models/settings.php';

class HomeController
{

    public function index(): void
    {
        // 1. Récupérer tout le contenu enregistré en BDD
        $content = Setting::getAll();

        // 2. Transmettre les données à la vue
        $title = "Yann Le Flohic | Développeur Web & Créatif";

        require_once __DIR__ . '/../views/front/home.php';
    }

    public function lab(): void
    {
        // 1. Récupérer tout le contenu enregistré en BDD
        $content = Setting::getAll();

        // 2. Décodage sécurisé des Snippets
        $snippets = $content['snippets'] ?? [];
        if (is_string($snippets)) {
            $snippets = json_decode($snippets, true) ?? [];
        }

        // 3. Décodage sécurisé des Articles
        $articles = $content['articles'] ?? [];
        if (is_string($articles)) {
            $articles = json_decode($articles, true) ?? [];
        }

        $title = "Lab & Veille — Yann Le Flohic";
        require_once __DIR__ . '/../views/front/lab.php';
    }

    public function legal(): void
    {
        $title = "Mentions Légales";
        require_once __DIR__ . '/../views/front/legal.php';
    }
}