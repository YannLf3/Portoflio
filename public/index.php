<?php
declare(strict_types=1);

// 1. En-têtes de sécurité HTTP (Protection contre Clickjacking, MIME-sniffing & Referrer leak)
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// 2. Configuration sécurisée des cookies de session PHP
// En local (Docker, http://localhost:8000) : cookie sans domaine ni flag Secure, sinon le navigateur le rejette
// et la session (connexion admin) ne persiste pas. En production : domaine fixé + HTTPS obligatoire.
$isDev = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', 'localhost:8000'], true) || getenv('ENVIRONMENT') === 'dev';
session_set_cookie_params([
    'lifetime' => 0,                                 // Le cookie s'efface à la fermeture du navigateur
    'path' => '/',
    'domain' => $isDev ? '' : 'yannleflohic.fr',
    'secure' => !$isDev,                             // Exige le HTTPS en production (empêche l'envoi en HTTP clair)
    'httponly' => true,                              // Interdit l'accès au cookie via JavaScript (protection XSS)
    'samesite' => 'Lax',                             // Protège contre les attaques CSRF
]);

session_start();

require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/controllers/ApiController.php';

/**
 * Normalise le chemin demandé : décodage, slashes multiples fusionnés, slash final retiré.
 * Exemple : « //admin/ » → « /admin » ; une chaîne vide devient « / ».
 *
 * @param string $uri URI brute ($_SERVER['REQUEST_URI']).
 * @return string Chemin normalisé.
 */
function normalizePath(string $uri): string
{
    $path = rawurldecode((string)parse_url($uri, PHP_URL_PATH));
    $path = rtrim((string)preg_replace('#/+#', '/', $path), '/');
    return $path === '' ? '/' : $path;
}

/**
 * Réponse 404 propre (sans fuite d'informations système).
 *
 * @return void
 */
function notFound(): void
{
    http_response_code(404);
    if (file_exists(__DIR__ . '/../app/views/404.php')) {
        require_once __DIR__ . '/../app/views/404.php';
        return;
    }
    echo '404 - Page introuvable';
}

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

// 3. Table de routes : chemin → action. Le contrôleur n'est instancié que si sa route est demandée.
$routes = [
    // Pages publiques
    '/' => static fn() => (new HomeController())->index(),
    '/index.php' => static fn() => (new HomeController())->index(),
    '/mentions-legales' => static fn() => (new HomeController())->legal(),
    '/lab' => static fn() => (new HomeController())->lab(),

    // API JSON publique en lecture seule (données du terminal CLI)
    '/api/terminal' => static fn() => $_SERVER['REQUEST_METHOD'] === 'GET'
        ? (new ApiController())->terminal()
        : (new ApiController())->methodNotAllowed(),

    // Authentification
    '/login' => static fn() => $isPost ? (new AuthController())->login() : (new AuthController())->showLogin(),
    '/logout' => static fn() => (new AuthController())->logout(),

    // Back-office : un GET sur /admin/save renvoie simplement vers le dashboard
    '/admin' => static fn() => (new AdminController())->index(),
    '/admin/save' => static function () use ($isPost): void {
        if (!$isPost) {
            header('Location: /admin');
            exit;
        }
        (new AdminController())->save();
    },
];

// 4. Exécution de la route demandée (404 si inconnue)
($routes[normalizePath($_SERVER['REQUEST_URI'] ?? '/')] ?? 'notFound')();
