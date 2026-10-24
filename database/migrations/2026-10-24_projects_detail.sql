-- =============================================================================
-- Migration 2026-10-24 : champs de la page « détail d'un projet ».
-- Colonnes toutes optionnelles (NULL) : les projets existants restent valides tels quels.
-- À jouer UNE SEULE FOIS (MySQL n'a pas « ADD COLUMN IF NOT EXISTS »).
-- À jouer AVANT d'envoyer le nouveau Project.php sur le serveur, sinon l'accueil plante sur une colonne inconnue.
-- =============================================================================

ALTER TABLE `projects`
    ADD COLUMN `year`      VARCHAR(20)  NULL AFTER `tags`,
    ADD COLUMN `role`      VARCHAR(255) NULL AFTER `year`,
    ADD COLUMN `context`   TEXT         NULL AFTER `role`,
    ADD COLUMN `solution`  TEXT         NULL AFTER `context`,
    ADD COLUMN `result`    TEXT         NULL AFTER `solution`,
    ADD COLUMN `repo_link` TEXT         NULL AFTER `result`,
    ADD COLUMN `gallery`   TEXT         NULL AFTER `repo_link`;
