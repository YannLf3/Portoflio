<?php
// 1. En-têtes de sécurité HTTP (Protection contre Clickjacking, MIME-sniffing & Referrer leak)
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// 2. Configuration sécurisée des cookies de session PHP
// En local (Docker, http://localhost:8000) : cookie sans domaine ni flag Secure, sinon le navigateur le rejette
// et la session (connexion admin) ne persiste pas. En production : domaine fixé + HTTPS obligatoire.
$isDev = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', 'localhost:8000'], true) || getenv('ENVIRONMENT') === 'dev';
session_set_cookie_params([
    'lifetime' => 0,                                 // Le cookie s'efface à la fermeture du navigateur
    'path'     => '/',
    'domain'   => $isDev ? '' : 'yannleflohic.fr',
    'secure'   => !$isDev,                           // Exige le HTTPS en production (empêche l'envoi en HTTP clair)
    'httponly' => true,                              // Interdit l'accès au cookie via JavaScript (protection XSS)
    'samesite' => 'Lax'                              // Protège contre les attaques CSRF
]);

session_start();

// Chargement des contrôleurs
require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/controllers/ApiController.php';

// 3. Normalisation et nettoyage strict de l'URL
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = rawurldecode($path);                  // Décode les caractères encodés (ex: %2F)
$path = preg_replace('#/+#', '/', $path);     // Supprime les slashes multiples (ex: //admin -> /admin)
$url = rtrim($path, '/');

if (empty($url)) {
    $url = '/';
}

$method = $_SERVER['REQUEST_METHOD'];

// 4. Routage
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

    // API JSON publique en lecture seule (données du terminal CLI)
    case '/api/terminal':
        if ($method !== 'GET') {
            header('Allow: GET');
            Response::json(['error' => 'Méthode non autorisée.'], 405);
        }
        (new ApiController())->terminal();
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

    // Espace Back-Office
    case '/admin':
        (new AdminController())->index();
        break;

    case '/admin/save':
        if ($method === 'POST') {
            (new AdminController())->save();
        } else {
            header('Location: /admin');
            exit;
        }
        break;

    default:
        // 5. Gestion propre de la 404 (sans leaker d'informations système)
        http_response_code(404);
        if (file_exists(__DIR__ . '/../app/views/404.php')) {
            require_once __DIR__ . '/../app/views/404.php';
        } else {
            echo "404 - Page introuvable";
        }
        break;
}