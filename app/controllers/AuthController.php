<?php
require_once __DIR__ . '/../models/User.php';

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
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $user = User::findByEmail($email);
        $dbPassword = is_array($user) ? $user['password'] : ($user->password ?? null);

        if ($user && $dbPassword && password_verify($password, $dbPassword)) {
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

    // Déconnexion
    public function logout()
    {
        unset($_SESSION['admin_logged_in']);
        session_destroy();
        header('Location: /login');
        exit;
    }
}