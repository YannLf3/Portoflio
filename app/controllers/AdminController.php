<?php
require_once __DIR__ . '/../models/Project.php';

class AdminController {

    public function __construct() {
        // Protection globale : Si non connecté, redirection immédiate vers le login
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: /login');
            exit;
        }
    }

    // Tableau de bord principal
    public function index() {
        $projects = Project::getAll();
        $title = "Back-Office Admin";

        require_once __DIR__ . '/../views/admin/dashboard.php';
    }
}