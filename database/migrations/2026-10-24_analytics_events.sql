-- =============================================================================
-- Migration 2026-10-24 : mesure d'audience maison (onglet « Statistiques » du back-office).
-- Ré-exécutable sans risque (CREATE TABLE IF NOT EXISTS). Aucune donnée existante n'est touchée.
-- À jouer AVANT d'envoyer le nouveau code sur le serveur.
-- Aucune adresse IP n'est stockée : `visitor` est une empreinte tronquée qui change chaque jour.
-- Les lignes de plus de 13 mois sont purgées automatiquement (AnalyticsEvent::purgeOld).
-- =============================================================================

CREATE TABLE IF NOT EXISTS `analytics_events`
(
    `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `day`        DATE         NOT NULL,
    `type`       VARCHAR(10)  NOT NULL,
    `name`       VARCHAR(40)  NOT NULL DEFAULT '',
    `path`       VARCHAR(255) NOT NULL,
    `referrer`   VARCHAR(120) NOT NULL DEFAULT '',
    `device`     VARCHAR(10)  NOT NULL,
    `visitor`    CHAR(16)     NOT NULL,
    KEY `idx_analytics_day_type` (`day`, `type`),
    KEY `idx_analytics_path` (`path`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;
