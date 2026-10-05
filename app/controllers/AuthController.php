<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../core/Csrf.php';

class AuthController
{
    // Affiche le formulaire de login
    public function showLogin()
    {
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            header('Location: /admin');
            exit;
        }
        require_once __DIR__ . '/../views/admin/login.php';
    }

// Traite la soumission du formulaire
    public function login(): void
    {
        // Protection CSRF : empêche un site tiers de soumettre ce formulaire à l'insu du visiteur
        // (login CSRF = connecter la victime sur un compte choisi par l'attaquant)
        if (!Csrf::isValid($_POST[Csrf::FIELD] ?? null)) {
            $error = "Session expirée : rechargez la page puis reconnectez-vous.";
            require_once __DIR__ . '/../views/admin/login.php';
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $user = User::findByEmail($email);
        $dbPassword = is_array($user) ? $user['password'] : ($user->password ?? null);

        if ($user && $dbPassword && password_verify($password, $dbPassword)) {
            // Protection anti-usurpation : régénère l'ID de session et détruit l'ancien
            session_regenerate_id(true);
            // Élévation de privilège : nouveau jeton CSRF pour la session authentifiée
            Csrf::rotate();

            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = is_array($user) ? $user['id'] : $user->id;
            $_SESSION['admin_email'] = is_array($user) ? $user['email'] : $user->email;

            header('Location: /admin');
            exit;
        } else {
            $error = "Identifiants incorrects.";
            require_once __DIR__ . '/../views/admin/login.php';
        }
    }

// Déconnexion sécurisée
    public function logout(): void
    {
        // 1. Vider toutes les variables de session
        $_SESSION = [];

        // 2. Supprimer le cookie de session côté navigateur
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000, // Date passée pour forcer l'expiration comme vu en cours
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // 3. Détruire la session côté serveur
        session_destroy();

        // 4. Redirection vers la page de connexion
        header('Location: /login');
        exit;
    }
}