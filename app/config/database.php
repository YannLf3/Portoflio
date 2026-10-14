<?php
declare(strict_types=1);

/** Variables obligatoires du fichier .env (DB_PASS doit exister mais peut être vide). */
const REQUIRED_ENV_KEYS = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];

/**
 * Charge le fichier .env (lignes « NOM=valeur ») dans $_ENV.
 * Les commentaires (#) et les lignes sans « = » sont ignorés ; les guillemets entourant la valeur sont retirés.
 *
 * @param string $envFile Chemin absolu du fichier .env.
 * @return void
 */
function loadEnvFile(string $envFile): void
{
    if (!file_exists($envFile)) {
        return;
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

    // Ne garde que les lignes utiles : ni commentaire (#), ni ligne sans « = »
    $assignments = array_filter(
        $lines,
        static fn(string $line): bool => !str_starts_with(trim($line), '#') && str_contains($line, '=')
    );

    array_walk($assignments, static function (string $line): void {
        [$name, $value] = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value, " \t\n\r\0\x0B\"'");
    });
}

/**
 * Liste les variables obligatoires absentes (ou vides, sauf DB_PASS) de $_ENV.
 *
 * @return list<string> Noms des variables manquantes.
 */
function findMissingEnvKeys(): array
{
    return array_values(array_filter(
        REQUIRED_ENV_KEYS,
        static fn(string $key): bool => !array_key_exists($key, $_ENV) || ($key !== 'DB_PASS' && $_ENV[$key] === '')
    ));
}

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

    if ($pdo !== null) {
        return $pdo;
    }

    loadEnvFile(__DIR__ . '/../../.env');

    // Identifiants lus EXCLUSIVEMENT dans le .env (aucune valeur de repli dans le code) :
    // si une variable manque, on échoue clairement plutôt que de deviner.
    $missing = findMissingEnvKeys();
    if ($missing !== []) {
        // Le nom des variables manquantes va dans le log serveur, jamais dans la réponse HTTP
        error_log('Configuration BDD incomplète : ' . implode(', ', $missing) . ' absent(s) du fichier .env');
        http_response_code(500);
        exit('Erreur interne du serveur.');
    }

    try {
        // DB_HOST vaut 'db' sous Docker, 'localhost' chez Hostinger
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $_ENV['DB_HOST'], $_ENV['DB_NAME']);
        $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        error_log('Erreur PDO : ' . $e->getMessage());
        http_response_code(500);
        exit('Erreur interne du serveur.');
    }

    return $pdo;
}

/**
 * Exécute un traitement dans une transaction unique : tout est enregistré, ou rien.
 * En cas d'erreur, la transaction est annulée puis l'exception relancée à l'appelant.
 *
 * @param callable(PDO): void $work Traitement à exécuter (reçoit la connexion partagée).
 * @return void
 * @throws Throwable Erreur levée par le traitement, après annulation.
 */
function runInTransaction(callable $work): void
{
    $pdo = getPDOConnection();
    $pdo->beginTransaction();

    try {
        $work($pdo);
        $pdo->commit();
    } catch (Throwable $e) {
        // Aucune écriture partielle : la BDD reste dans l'état précédent
        $pdo->rollBack();
        throw $e;
    }
}
