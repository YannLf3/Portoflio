-- Création de la base de données si elle n'existe pas
CREATE DATABASE IF NOT EXISTS `portfolio_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `portfolio_db`;

-- 1. Table des utilisateurs (Admin)
CREATE TABLE IF NOT EXISTS `users`
(
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `email`      VARCHAR(180) NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;

-- Aucun compte n'est créé ici : en local, le compte de test vient de database/seed-dev.sql (monté par Docker) ;
-- en production, créer l'administrateur avec « php database/create-admin.php ».

-- 2. Table des contenus éditables (clé / valeur)
CREATE TABLE IF NOT EXISTS `settings`
(
    `setting_key`   VARCHAR(100) PRIMARY KEY,
    `setting_value` LONGTEXT NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- 3. Table des projets (une ligne par projet, ordre d'affichage = position croissante)
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

-- Projets affichés par défaut sur une installation neuve
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


-- 4. Journal des échecs de connexion (anti-brute-force, voir app/models/LoginAttempt.php)
CREATE TABLE IF NOT EXISTS `login_attempts`
(
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ip`           VARCHAR(45) NOT NULL,
    `attempted_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_login_attempts_ip_date` (`ip`, `attempted_at`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;
