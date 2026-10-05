<?php

/**
 * Retourne la connexion PDO partagée (créée au premier appel, puis réutilisée).
 *
 * Les identifiants proviennent uniquement du fichier .env situé à la racine du
 * projet (clés DB_HOST, DB_NAME, DB_USER, DB_PASS — modèle : .env.example).
 * En cas de configuration incomplète ou de connexion impossible, le détail est
 * journalisé côté serveur et une erreur 500 générique est renvoyée.
 *
 * @return PDO
 */
function getPDOConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        // 1. Lecture du fichier .env s'il existe à la racine du projet
        $envFile = __DIR__ . '/../../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                // Ignore les commentaires (#)
                if (strpos(trim($line), '#') === 0) continue;

                // Découpe la ligne nom=valeur
                if (strpos($line, '=') !== false) {
                    list($name, $value) = explode('=', $line, 2);
                    $name = trim($name);
                    $value = trim($value, " \t\n\r\0\x0B\"'"); // Retire les guillemets éventuels
                    $_ENV[$name] = $value;
                }
            }
        }

        // 2. Identifiants : lus EXCLUSIVEMENT dans le .env (aucune valeur de repli dans le code).
        //    Un identifiant écrit en dur finirait tôt ou tard versionné ou partagé ;
        //    ici, si une variable manque, on échoue clairement plutôt que de deviner.
        //    DB_PASS doit exister mais peut être vide (ex : MySQL local sans mot de passe).
        $missing = [];
        foreach (['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'] as $key) {
            $isMissing = !array_key_exists($key, $_ENV) || ($key !== 'DB_PASS' && $_ENV[$key] === '');
            if ($isMissing) {
                $missing[] = $key;
            }
        }
        if ($missing !== []) {
            // Le nom des variables manquantes va dans le log serveur, jamais dans la réponse HTTP
            error_log('Configuration BDD incomplète : ' . implode(', ', $missing) . ' absent(s) du fichier .env');
            http_response_code(500);
            exit('Erreur interne du serveur.');
        }

        $host = $_ENV['DB_HOST']; // 'db' sous Docker, 'localhost' chez Hostinger
        $db   = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];

        // 3. Connexion PDO sécurisée
        try {
            $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log("Erreur PDO : " . $e->getMessage());
            http_response_code(500);
            exit("Erreur interne du serveur.");
        }
    }

    return $pdo;
}