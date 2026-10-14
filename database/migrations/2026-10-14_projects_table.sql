-- =============================================================================
-- Migration 2026-10-14 : les projets passent de `settings` (JSON) à leur propre table `projects`.
-- Compatible MySQL 8.0+ et MariaDB 10.6+ (JSON_TABLE). Ré-exécutable sans risque :
-- chaque INSERT ne s'applique que si la table `projects` est encore vide.
--
-- AVANT D'EXÉCUTER : si une ANCIENNE table `projects` existe déjà (colonnes slug, type, tag…,
-- jamais utilisée par le site), la renommer : RENAME TABLE `projects` TO `projects_legacy`;
-- =============================================================================

-- 1. Table des projets (ordre d'affichage = position croissante)
CREATE TABLE IF NOT EXISTS `projects`
(
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `position`    INT UNSIGNED NOT NULL DEFAULT 0,
    `title`       TEXT         NOT NULL,
    `meta`        TEXT         NOT NULL,
    `description` TEXT         NOT NULL,
    `image`       TEXT         NOT NULL,
    `link`        TEXT         NOT NULL,
    `category`    TEXT         NOT NULL,
    `tags`        TEXT         NOT NULL,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_projects_position` (`position`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- 2. Import des projets déjà saisis dans le back-office (clé `projects` de la table `settings`),
--    dans le même ordre (FOR ORDINALITY numérote les lignes du tableau JSON à partir de 1)
INSERT INTO `projects` (`position`, `title`, `meta`, `description`, `image`, `link`, `category`, `tags`)
SELECT jt.ordinal - 1,
       COALESCE(jt.title, ''),
       COALESCE(jt.meta, ''),
       COALESCE(jt.description, ''),
       COALESCE(jt.image, ''),
       COALESCE(jt.link, ''),
       COALESCE(jt.category, ''),
       COALESCE(jt.tags, '')
FROM `settings` AS s,
     -- Valeur JSON invalide : remplacée par une liste vide (aucune ligne importée, aucune erreur)
     JSON_TABLE(IF(JSON_VALID(s.`setting_value`), s.`setting_value`, '[]'), '$[*]' COLUMNS (
         ordinal FOR ORDINALITY,
         title LONGTEXT PATH '$.title',
         meta LONGTEXT PATH '$.meta',
         description LONGTEXT PATH '$.description',
         image LONGTEXT PATH '$.image',
         link LONGTEXT PATH '$.link',
         category LONGTEXT PATH '$.category',
         tags LONGTEXT PATH '$.tags'
         )) AS jt
WHERE s.`setting_key` = 'projects'
  AND NOT EXISTS (SELECT 1 FROM `projects`);

-- 3. Site jamais configuré (aucune clé `projects` dans `settings`) : projets affichés par défaut jusqu'ici
INSERT INTO `projects` (`position`, `title`, `meta`, `description`, `image`, `link`, `category`, `tags`)
SELECT d.*
FROM (SELECT 0                                                                                                                                   AS `position`,
             'Barcelona Feel Good Tours'                                                                                                         AS `title`,
             'Client · Full-Stack'                                                                                                               AS `meta`,
             'Création d''une plateforme web multilingue avec espace d''administration sur-mesure pour les modifications du site partie client.' AS `description`,
             '/assets/images/projects/barcelona.webp'                                                                                            AS `image`,
             'https://barcelonafeelgoodtours.eu/'                                                                                                AS `link`,
             'Professionnel'                                                                                                                     AS `category`,
             'PHP / MYSQL, HTML / CSS / JS'                                                                                                      AS `tags`
      UNION ALL
      SELECT 1, 'Design cinéma landing page', 'Scolaire · BUT1',
             'Conception d''un wireframe haute fidélité pour une landing page de cinéma sous Figma.',
             '/assets/images/projects/landingpageCinema.webp',
             'https://www.figma.com/design/qxHqC61TU3nQUqv73qYdEd/LE-FLOHIC-YANN---Science-Fiction?node-id=0-1&t=wcLPSjIyGeyXmyKf-1',
             'Scolaire', 'Figma Design, Auto-layout - Nommage propre - Styles'
      UNION ALL
      SELECT 2, 'Design d''une première version de mon portfolio', 'Scolaire · BUT1',
             'Développement de maquettes d''une première version de mon portfolio dans le cadre des situations d''apprentissage et d''évaluation du BUT MMI.',
             '/assets/images/projects/portfolioV1.webp',
             'https://www.figma.com/design/EBKSSO41uKu8MKjqOK7Fgk/LE-FLOHIC-Yann?node-id=0-1&t=ajspR0CKdX7v391r-1',
             'Scolaire', 'Figma Design, Création numérique'
      UNION ALL
      SELECT 3, 'Intégration HTML CSS d''une maquette', 'Scolaire · BUT1',
             'Intégration web HTML CSS JS avec templating d''une maquette Figma. Architecture et nomenclature propre avec BEM CSS.',
             '/assets/images/projects/sae105.webp',
             'https://leflohic-sae105.mmi-limoges.fr/',
             'Scolaire', 'Développement web, HTML / CSS / JS'
      UNION ALL
      SELECT 4, 'Wordpress landing page fictive RockEnSeine', 'Scolaire · BUT1',
             'Création d''une maquette Figma, puis développement d''un site landing page fictif pour la promotion du festival RockEnSeine.',
             '/assets/images/projects/sae202.webp',
             'https://github.com/YannLf3/SAE202_Wordpress',
             'Scolaire', 'Développement web & Création graphique, Wordpress & Figma'
      UNION ALL
      SELECT 5, 'Space Invaders Python (NSI)', 'Scolaire · NSI Terminal',
             'Création d''un jeu type Space Invaders en Python.',
             '/assets/images/projects/spaceInvaders.webp',
             'https://github.com/YannLf3/NSI_SpaceInvaders-game',
             'Scolaire', 'Développement Python, Space Invaders') AS d
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `setting_key` = 'projects')
  AND NOT EXISTS (SELECT 1 FROM `projects`);

-- 4. Vérification : nombre de projets désormais en table (doit correspondre à ceux visibles sur le site)
SELECT COUNT(*) AS `projets_migres` FROM `projects`;
