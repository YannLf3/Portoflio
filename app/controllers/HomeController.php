<?php
require_once __DIR__ . '/../models/settings.php';

class HomeController
{
    public function index(): void
    {
        // 1. Récupérer tout le contenu enregistré en BDD
        $content = Setting::getAll();

        // 2. Variables SEO dynamiques pour l'Accueil (Ciblage local)
        $title = "Yann Le Flohic | Développeur Web Freelance à Châteauroux & Limoges";
        $pageDesc = "Développeur web freelance basé entre Châteauroux et Limoges. Création de sites sur-mesure, applications web et intégration moderne.";
        $canonical = "https://yannleflohic.fr/";

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

        // 4. Variables SEO dynamiques pour le Lab
        $title = "Le Lab | Expérimentations Web - YannLf3";
        $pageDesc = "Découvrez mes snippets, expérimentations CSS/JS et tests de développement front-end par Yann Le Flohic.";
        $canonical = "https://yannleflohic.fr/lab";

        require_once __DIR__ . '/../views/front/lab.php';
    }

    public function legal(): void
    {
        // Variables SEO dynamiques pour les Mentions Légales
        $title = "Mentions Légales | Yann Le Flohic";
        $pageDesc = "Mentions légales du site portfolio de Yann Le Flohic.";
        $canonical = "https://yannleflohic.fr/mentions-legales";
        $noindex = true; // Demande à Google de ne pas indexer cette page pour éviter la concurrence avec l'accueil

        require_once __DIR__ . '/../views/front/legal.php';
    }
}