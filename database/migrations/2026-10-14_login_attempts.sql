-- =============================================================================
-- Migration 2026-10-14 : journal des échecs de connexion (anti-brute-force du back-office).
-- Ré-exécutable sans risque (CREATE TABLE IF NOT EXISTS). Aucune donnée existante n'est touchée.
-- À jouer AVANT d'envoyer le nouveau AuthController.php sur le serveur.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `login_attempts`
(
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ip`           VARCHAR(45) NOT NULL,
    `attempted_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_login_attempts_ip_date` (`ip`, `attempted_at`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;
