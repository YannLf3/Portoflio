-- =============================================================================
-- Migration 2026-10-25 : statistiques d'audience détaillées (heure, navigateur, système, pays, langue,
-- mode Dev/Photo, campagnes UTM, temps passé).
-- Colonnes toutes avec valeur par défaut : les lignes déjà enregistrées restent valides telles quelles.
-- À jouer UNE SEULE FOIS (MySQL n'a pas « ADD COLUMN IF NOT EXISTS ») : une 2e exécution affiche « Duplicate column name ».
-- À jouer AVANT d'envoyer le nouveau code sur le serveur, sinon la mesure n'enregistre plus rien et l'onglet
-- Statistiques affiche un message d'erreur jusqu'à l'exécution de cette migration.
-- Toujours aucune adresse IP stockée. Le pays n'est renseigné que si le site est derrière Cloudflare (en-tête CF-IPCountry).
-- =============================================================================

ALTER TABLE `analytics_events`
    ADD COLUMN `hour`         TINYINT UNSIGNED  NOT NULL DEFAULT 0  AFTER `day`,
    ADD COLUMN `browser`      VARCHAR(10)       NOT NULL DEFAULT '' AFTER `device`,
    ADD COLUMN `os`           VARCHAR(10)       NOT NULL DEFAULT '' AFTER `browser`,
    ADD COLUMN `country`      CHAR(2)           NOT NULL DEFAULT '' AFTER `os`,
    ADD COLUMN `lang`         CHAR(2)           NOT NULL DEFAULT '' AFTER `country`,
    ADD COLUMN `mode`         VARCHAR(5)        NOT NULL DEFAULT '' AFTER `lang`,
    ADD COLUMN `utm_source`   VARCHAR(40)       NOT NULL DEFAULT '' AFTER `mode`,
    ADD COLUMN `utm_campaign` VARCHAR(40)       NOT NULL DEFAULT '' AFTER `utm_source`,
    ADD COLUMN `value`        SMALLINT UNSIGNED NOT NULL DEFAULT 0  AFTER `utm_campaign`;
