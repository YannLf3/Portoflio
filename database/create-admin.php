<?php
declare(strict_types=1);

/**
 * Crée (ou remplace le mot de passe d') un compte administrateur depuis la ligne de commande.
 *
 * Usage (à la racine du projet, .env renseigné) :  php database/create-admin.php
 * L'e-mail et le mot de passe sont demandés à l'écran : rien dans l'historique du shell ni dans le dépôt.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../app/config/database.php';

/**
 * Lit une ligne saisie au clavier.
 *
 * @param string $question Question affichée.
 * @param bool   $hidden   true : la saisie n'est pas affichée (mot de passe, terminaux Unix).
 * @return string Saisie sans retour à la ligne.
 */
function ask(string $question, bool $hidden = false): string
{
    fwrite(STDOUT, $question);
    if ($hidden) {
        shell_exec('stty -echo');
    }
    $answer = trim((string)fgets(STDIN));
    if ($hidden) {
        shell_exec('stty echo');
        fwrite(STDOUT, PHP_EOL);
    }
    return $answer;
}

$email = ask('E-mail administrateur : ');
$password = ask('Mot de passe (12 caractères minimum) : ', true);

if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || mb_strlen($password) < 12) {
    fwrite(STDERR, "E-mail invalide ou mot de passe trop court.\n");
    exit(1);
}

// Une seule ligne par e-mail (colonne UNIQUE) : relancer le script remplace le mot de passe
getPDOConnection()->prepare(
    'INSERT INTO users (email, password) VALUES (:email, :password)
     ON DUPLICATE KEY UPDATE password = :password_update'
)->execute([
    'email' => $email,
    'password' => $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
    'password_update' => $hash,
]);

fwrite(STDOUT, "Compte « $email » enregistré.\n");
