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

    case '/mentions-legales':
        (new HomeController())->legal();
        break;

    // Espace Back-Office (Protections appliquées dans AdminController)
    case '/admin':
        (new AdminController())->index();
        break;

    default:
        http_response_code(404);
        echo "404 - Page non trouvée";
        break;
}