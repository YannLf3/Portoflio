<?php
declare(strict_types=1);

// 1. Nonce CSP : valeur aléatoire propre à CHAQUE requête, recopiée dans les <script> inline autorisés
define('CSP_NONCE', base64_encode(random_bytes(16)));

// 2. En-têtes de sécurité HTTP (Clickjacking, MIME-sniffing, fuite de Referer, XSS, API navigateur)
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()');
header('Cross-Origin-Opener-Policy: same-origin');
// CSP : seuls nos scripts (fichiers /js + <script> portant le nonce) s'exécutent.
// style-src garde 'unsafe-inline' (attributs style="" générés par les vues) ; polices = Google Fonts ;
// images = site + Unsplash (galerie) ; frame-ancestors remplace X-Frame-Options pour les navigateurs récents.
header(
    "Content-Security-Policy: default-src 'self'; "
    . "script-src 'self' 'nonce-" . CSP_NONCE . "'; "
    . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
    . "font-src 'self' https://fonts.gstatic.com; "
    . "img-src 'self' data: https://images.unsplash.com; "
    . "media-src 'self'; connect-src 'self'; object-src 'none'; "
    . "base-uri 'self'; form-action 'self'; frame-ancestors 'none'"
);

// 3. Configuration sécurisée des cookies de session PHP
// En local (Docker) : ENVIRONMENT=dev (docker-compose.yml) → cookie sans domaine ni flag Secure, sinon le navigateur
// le rejette. Le mode ne dépend PAS de l'en-tête Host (falsifiable par le client) : uniquement de la configuration serveur.
// En production : domaine fixé + HTTPS obligatoire.
$isDev = getenv('ENVIRONMENT') === 'dev';
session_set_cookie_params([
    'lifetime' => 0,                                 // Le cookie s'efface à la fermeture du navigateur
    'path' => '/',
    'domain' => $isDev ? '' : 'yannleflohic.fr',
    'secure' => !$isDev,                             // Exige le HTTPS en production (empêche l'envoi en HTTP clair)
    'httponly' => true,                              // Interdit l'accès au cookie via JavaScript (protection XSS)
    'samesite' => 'Lax',                             // Protège contre les attaques CSRF
]);

// Refuse tout identifiant de session que le serveur n'a pas lui-même créé (anti « session fixation »)
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

session_start();

/** Durée d'inactivité (secondes) au-delà de laquelle la session administrateur est fermée. */
const SESSION_IDLE_TIMEOUT = 1800;

/**
 * Ferme la session si elle est restée inactive trop longtemps, sinon renouvelle son horodatage d'activité.
 *
 * @return void
 */
function enforceSessionTimeout(): void
{
    $lastActivity = $_SESSION['last_activity'] ?? null;

    if (is_int($lastActivity) && time() - $lastActivity > SESSION_IDLE_TIMEOUT) {
        // Session expirée : on repart d'une session vierge avec un nouvel identifiant
        $_SESSION = [];
        session_regenerate_id(true);
    }

    $_SESSION['last_activity'] = time();
}

enforceSessionTimeout();

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
 * Réponse 404 propre (sans fuite d'informations système) : la vue est rendue par HomeController.
 *
 * @return void
 */
function notFound(): void
{
    (new HomeController())->notFound();
}

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

// 3. Table de routes : chemin → action. Le contrôleur n'est instancié que si sa route est demandée.
$routes = [
    // Pages publiques
    '/' => static fn() => (new HomeController())->index(),
    '/index.php' => static fn() => (new HomeController())->index(),
    '/mentions-legales' => static fn() => (new HomeController())->legal(),
    '/lab' => static fn() => (new HomeController())->lab(),
    '/politique-de-confidentialite' => static fn() => (new HomeController())->privacy(),
    '/cgv' => static fn() => (new HomeController())->terms(),

    // API JSON publique en lecture seule (données du terminal CLI)
    '/api/terminal' => static fn() => $_SERVER['REQUEST_METHOD'] === 'GET'
        ? (new ApiController())->terminal()
        : (new ApiController())->methodNotAllowed(),

    // Authentification
    '/login' => static fn() => $isPost ? (new AuthController())->login() : (new AuthController())->showLogin(),
    // Déconnexion : POST + jeton CSRF uniquement (un simple lien ou une image ne peut plus déconnecter l'admin)
    '/logout' => static function () use ($isPost): void {
        if (!$isPost) {
            header('Location: /admin');
            exit;
        }
        (new AuthController())->logout();
    },

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
