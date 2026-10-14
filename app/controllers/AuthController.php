<?php
declare(strict_types=1);

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/LoginAttempt.php';
require_once __DIR__ . '/../core/Csrf.php';

/**
 * Connexion et déconnexion de l'administrateur.
 * Seul un indicateur est stocké en session PHP (côté serveur) : aucune donnée de confiance dans un cookie.
 */
final class AuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion (ou renvoie au dashboard si la session est déjà ouverte).
     *
     * @return void
     */
    public function showLogin(): void
    {
        if ($this->isAdmin()) {
            $this->redirect('/admin');
        }
        $this->renderLogin();
    }

    /**
     * Traite la soumission du formulaire de connexion.
     *
     * @return void
     */
    public function login(): void
    {
        // Protection CSRF : empêche un site tiers de soumettre ce formulaire à l'insu du visiteur
        // (login CSRF = connecter la victime sur un compte choisi par l'attaquant)
        if (!Csrf::isValid($_POST[Csrf::FIELD] ?? null)) {
            $this->renderLogin('Session expirée : rechargez la page puis reconnectez-vous.');
            return;
        }

        // Anti-brute-force : trop d'échecs récents depuis cette IP, on refuse sans même tester les identifiants
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (LoginAttempt::isBlocked($ip)) {
            http_response_code(429);
            $this->renderLogin('Trop de tentatives. Réessayez dans ' . LoginAttempt::WINDOW_MINUTES . ' minutes.');
            return;
        }

        $user = User::findByEmail(trim($_POST['email'] ?? ''));

        // Le mot de passe n'est pas passé par trim() : les espaces en début ou fin font partie du secret
        if ($user === null || !$user->verifyPassword((string)($_POST['password'] ?? ''))) {
            LoginAttempt::recordFailure($ip);
            $this->renderLogin('Identifiants incorrects.');
            return;
        }

        LoginAttempt::clear($ip);

        // Protection anti-usurpation : régénère l'ID de session et détruit l'ancien
        session_regenerate_id(true);
        // Élévation de privilège : nouveau jeton CSRF pour la session authentifiée
        Csrf::rotate();

        $_SESSION[self::SESSION_ADMIN] = true;
        $_SESSION['admin_id'] = $user->getId();
        $_SESSION['admin_email'] = $user->getEmail();

        $this->redirect('/admin');
    }

    /**
     * Déconnexion sécurisée : session vidée, cookie expiré, session détruite côté serveur.
     *
     * @return never
     */
    public function logout(): never
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            // Date passée : le navigateur supprime immédiatement le cookie de session
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        $this->redirect('/login');
    }

    /**
     * Affiche la page de connexion, avec l'e-mail déjà saisi et l'éventuel message d'erreur.
     *
     * @param string|null $error Message d'erreur à afficher.
     * @return void
     */
    private function renderLogin(?string $error = null): void
    {
        $this->render('admin/login', [
            'error' => $error,
            'email' => $_POST['email'] ?? '',
        ]);
    }
}
