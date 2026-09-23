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

-- 2. Table des projets
CREATE TABLE IF NOT EXISTS `projects`
(
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `title`       VARCHAR(255) NOT NULL,
    `slug`        VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT         NULL,
    `image`       VARCHAR(255) NULL,
    `link`        VARCHAR(255) NULL,
    `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
    `type`        VARCHAR(255) NULL,
    `category`    VARCHAR(255) NULL,
    `tag`         VARCHAR(255) NULL
) ENGINE = InnoDB;

-- Insertion d'un utilisateur administrateur de test
-- Le mot de passe ici est en clair : "admin123"
-- Mais dans le INSERT ci-dessous, il est déjà HASHÉ avec BCRYPT !
INSERT INTO `users` (`email`, `password`)
VALUES ('admin@portfolio.fr', 'admin');

CREATE TABLE IF NOT EXISTS `settings`
(
    `setting_key`   VARCHAR(100) PRIMARY KEY,
    `setting_value` LONGTEXT NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;