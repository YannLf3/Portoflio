<?php
session_start();

// Chargement des contrôleurs
require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

switch ($url) {
    // Routes Publiques
    case '/':
    case '/index.php':
        (new HomeController())->index();
        break;

    case '/mentions-legales':
        (new HomeController())->legal();
        break;

    case '/lab':
        (new HomeController())->lab();
        break;

    // Authentification Admin
    case '/login':
        if ($method === 'POST') {
            (new AuthController())->login();
        } else {
            (new AuthController())->showLogin();
        }
        break;

    case '/logout':
        (new AuthController())->logout();
        break;

    // Espace Back-Office (Protections appliquées dans AdminController)
    case '/admin':
        (new AdminController())->index();
        break;

    // Route de sauvegarde du formulaire
    case '/admin/save':
        if ($method === 'POST') {
            (new AdminController())->save();
        } else {
            // Si on tente d'accéder à l'URL sans valider le formulaire, on redirige vers le dashboard
            header('Location: /admin');
            exit;
        }
        break;

    default:
        http_response_code(404);
        echo "404 - Page non trouvée";
        break;
}