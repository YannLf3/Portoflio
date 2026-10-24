-- =============================================================================
-- Migration 2026-10-24 : limitation de débit en base (formulaire de contact, mesure d'audience)
-- et enregistrement des messages de contact avant l'envoi du mail.
-- Ré-exécutable sans risque (CREATE TABLE IF NOT EXISTS). Aucune donnée existante n'est touchée.
-- À jouer AVANT d'envoyer le nouveau code sur le serveur.
-- =============================================================================

-- `client` est une empreinte HMAC de l'adresse IP (jamais l'IP en clair), purge automatique après 24 h
CREATE TABLE IF NOT EXISTS `rate_limits`
(
    `id`     BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `bucket` VARCHAR(20) NOT NULL,
    `client` CHAR(40)    NOT NULL,
    `hit_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_rate_limits_lookup` (`bucket`, `client`, `hit_at`),
    KEY `idx_rate_limits_hit_at` (`hit_at`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- Messages du formulaire de contact : `mail_sent` = 0 signale un mail à renvoyer à la main
CREATE TABLE IF NOT EXISTS `contact_messages`
(
    `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `name`       VARCHAR(100)  NOT NULL,
    `email`      VARCHAR(150)  NOT NULL,
    `message`    TEXT          NOT NULL,
    `mail_sent`  TINYINT(1)    NOT NULL DEFAULT 0,
    KEY `idx_contact_messages_created` (`created_at`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;
