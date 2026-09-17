# Yann Le Flohic — Portfolio Développeur Web & Photographe 🚀

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-00758F?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/fr/docs/Web/JavaScript)
[![Hostinger](https://img.shields.io/badge/Hostinger-673DE6?style=for-the-badge&logo=hostinger&logoColor=white)](https://www.hostinger.fr/)
[![PhpStorm](https://img.shields.io/badge/PhpStorm-000000?style=for-the-badge&logo=phpstorm&logoColor=white)](https://www.jetbrains.com/phpstorm/)

> **« Entre code et image, une même exigence : la précision. »**  
> Portfolio personnel et professionnel conçu sur-mesure pour présenter mes projets de développement web full-stack et mes travaux photographiques.

---

## 📌 Présentation du Projet

Ce projet est mon site portfolio officiel (**Yannlf3**). Conçu et développé sur-mesure sous **PhpStorm** pour la toute première fois, il allie une interface publique immersive à un back-office d'administration complet pour gérer dynamiquement les contenus, réalisations et photographies.

- 👨‍💻 **Profil :** Yann Le Flohic — Étudiant et futur développeur Web Full-stack (Freelance & Étudiant en 2ᵉ année de BUT MMI à Limoges).
- 📍 **Localisation :** Châteauroux / Limoges, France.
- 🎯 **Objectif :** Vitrine professionnelle, démonstrateur technique et gestionnaire de contenu autonome.

---

## 🛠️ Stack Technique

### **Front-end**
- **HTML5 sémantique** & templating.
- **Tailwind CSS** : Stylisation moderne, responsive, dark mode et animations sur-mesure.
- **JavaScript Vanilla (ES6+)** : Interactions dynamiques légères, transitions soignées, navigation sans dépendances superflues.

### **Back-end & Données**
- **PHP 8 (Architecture MVC)** : Séparation stricte des responsabilités (Modèles, Vues, Contrôleurs), routing propre et sécurisé.
- **MySQL / PDO** : Persistance des données relationnelles (projets, compétences, messages de contact, galeries photo).
- **Session & Auth sécurisées** : Hachage des mots de passe, prévention des injections SQL (requêtes préparées).

### **Environnement & Déploiement**
- **IDE :** JetBrains PhpStorm, dans le but de m'éxercer sur un nouvel IDE pour être plus polyvalent, également connu pour son efficacité.
- **Serveur local :** Apache / PHP / MySQL (Environnement macOS).
- **Hébergement & Production :** [Hostinger](https://www.hostinger.fr/) (serveur web Apache/LiteSpeed, phpMyAdmin, déploiement Git/FTP).

---

## ✨ Fonctionnalités Principales

### 🌐 Interface Publique (App)
- **Hero & Identité :** Typographie soignée, badges de disponibilité (recherche de stage / freelance) et univers visuel sombre élégant.
- **Double univers (Développeur / Photographe) :** Switch fluide entre les projets web et les séries photographiques.
- **Parcours & Timeline :** Présentation du cursus (BUT MMI, Bac Bachibac) et des expériences freelance.
- **Showcase de réalisations :** Fiches projets détaillées avec technologies utilisées, liens GitHub/Live et captures d'écran.

### 🔒 Espace Administration (Back-Office)
- **Authentification sécurisée :** Accès restreint avec sessions protégées et limitation des tentatives de connexion.
- **CRUD Projets :** Ajout, modification, réorganisation et suppression des projets web et galeries.
- **Gestionnaire de médias :** Upload, redimensionnement et optimisation des images pour le web.

---

## 📂 Architecture du Projet (Modèle MVC)

J'essaie de m'éxercer sur une nouvelle architecture, légèrement différente de celle dont j'ai l'habitude pour m'adapter.

```text
├── app/
│   ├── config/             # Configuration BDD, constantes et variables d'environnement
│   ├── controllers/        # Contrôleurs (Frontend & Admin)
│   ├── models/             # Modèles et interactions BDD (PDO)
│   └── views/              # Vues et templates PHP
│       ├── admin/          # Vues du tableau de bord d'administration
│       ├── front/          # Vues publiques (portfolio, projets, photo)
│       └── layouts/        # Headers, navbars, footers partagés
├── public/                 # Racine web exposée publiquement (DocumentRoot)
│   ├── assets/             # Images, icônes, uploads
│   ├── css/                # CSS compilé via Tailwind
│   ├── js/                 # Scripts JavaScript Vanilla
│   └── index.php           # Point d'entrée unique (Front Controller & Routeur)
├── src/                    # Fichiers sources front (Tailwind CSS, JS source)
├── database/               # Scripts SQL (schéma et données initiales)
├── .gitignore
└── README.md
```

---

## 🚀 Installation & Lancement en Local

### Prérequis
- PHP >= 8.1
- MySQL >= 8.0 
- npm (pour la compilation Tailwind) / Docker
- Serveur Apache (MAMP, Docker)

### 1. Cloner le dépôt
```bash
git clone https://github.com/ton-profil/nom-du-repo.git
cd nom-du-repo
```

### 2. Configuration de la base de données
1. Créez une base de données MySQL locale (ex: `portfolio_db`).
2. Importez le fichier de schéma présent dans `database/schema.sql`.
3. Configurez vos identifiants dans le fichier de configuration (ex: `app/config/database.php` ou `.env`) :
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'portfolio_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

### 3. Compilation des assets Tailwind CSS
Installez les dépendances et lancez le watcher Tailwind :
```bash
npm install
npm run dev   # ou: npx tailwindcss -i ./src/input.css -o ./public/css/input.css --watch
```

### 4. Lancer le serveur local
Pointez votre virtual host Apache vers le dossier `public/`, ou lancez le serveur intégré PHP pour tester rapidement :
```bash
php -S localhost:8000 -t public
```
Rendez-vous ensuite sur `http://localhost:8000`.

---

## 🌐 Déploiement sur Hostinger

1. **Configuration du domaine :** Pointer le dossier racine (`DocumentRoot`) vers le répertoire `public/` (ou adapter le fichier `.htaccess` à la racine de l'hébergement).
2. **Base de données :**
  - Créer une base de données MySQL et un utilisateur dédié depuis le **hPanel Hostinger**.
  - Importer la structure via **phpMyAdmin**.
3. **Mise en ligne des fichiers :**
  - Déploiement via le **Git Deployment** automatique d'Hostinger ou transfert SFTP.
  - Mettre à jour les identifiants de production dans votre fichier de config.
4. **Build Front :** Exécuter `npm run build` en local ou via script CI pour générer le CSS minifié avant déploiement.

---

## 👤 Auteur

**Yann Le Flohic (YLF.36)**
- **Rôle :** Développeur Web Full-stack & Photographe
- **Formation :** BUT MMI (Métiers du Multimédia et de l'Internet) — Parcours Web & Dispositifs interactifs (IUT du Limousin)
- **LinkedIn :** [linkedin.com/in/yann-le-flohic](https://fr.linkedin.com/in/yannlf3)
- **Site Web :** [Portfolio](https://yannleflohic.fr/)

---

## 📄 Licence

Ce projet est sous licence personnelle. Tous droits réservés sur le code sur-mesure, les visuels et les photographies présentées.
