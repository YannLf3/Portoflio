-- Données de TEST pour le développement local UNIQUEMENT (monté par docker-compose.yml, jamais à importer en production).
USE `portfolio_db`;

-- Compte administrateur de test. Mot de passe : "admin123" ; seule son empreinte BCRYPT est stockée ci-dessous.
INSERT INTO `users` (`email`, `password`)
VALUES ('admin@portfolio.fr', '$2y$12$GBh.QoXPFEswV2jMMjSpqetNaIcCN0OcpbJUUIH/68RJtaEIHdMyW');
