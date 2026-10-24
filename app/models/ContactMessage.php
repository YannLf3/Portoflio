<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Messages du formulaire de contact (table `contact_messages`).
 *
 * Le message est enregistré AVANT l'envoi du mail : si mail() échoue ou si le mail est classé en spam,
 * le message reste en base (mail_sent = 0) et peut être relu ou renvoyé.
 * Données personnelles : les lignes de plus de 12 mois sont supprimées.
 */
final class ContactMessage
{
    /** Durée de conservation des messages (mois). */
    private const RETENTION_MONTHS = 12;

    /**
     * Enregistre un message reçu.
     *
     * @param string $name    Nom de l'expéditeur (déjà validé).
     * @param string $email   Adresse e-mail de l'expéditeur (déjà validée).
     * @param string $message Texte du message (déjà validé).
     * @return int Identifiant de la ligne créée.
     */
    public static function create(string $name, string $email, string $message): int
    {
        $db = getPDOConnection();
        $db->prepare('INSERT INTO contact_messages (name, email, message) VALUES (:name, :email, :message)')
            ->execute(['name' => $name, 'email' => $email, 'message' => $message]);

        return (int)$db->lastInsertId();
    }

    /**
     * Marque le message comme transmis par e-mail.
     *
     * @param int $id Identifiant renvoyé par create().
     * @return void
     */
    public static function markSent(int $id): void
    {
        getPDOConnection()
            ->prepare('UPDATE contact_messages SET mail_sent = 1 WHERE id = :id')
            ->execute(['id' => $id]);
    }

    /**
     * Supprime les messages plus anciens que la durée de conservation.
     *
     * @return void
     */
    public static function purgeOld(): void
    {
        getPDOConnection()
            ->prepare('DELETE FROM contact_messages WHERE created_at < (NOW() - INTERVAL ' . self::RETENTION_MONTHS . ' MONTH)')
            ->execute();
    }
}
