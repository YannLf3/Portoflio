# Yann Le Flohic — Portfolio Développeur Web & Photographe 🚀

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-00758F?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/fr/docs/Web/JavaScript)
[![WebGL](https://img.shields.io/badge/WebGL-990000?style=for-the-badge&logo=webgl&logoColor=white)](https://developer.mozilla.org/fr/docs/Web/API/WebGL_API)
[![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com/)
[![Hostinger](https://img.shields.io/badge/Hostinger-673DE6?style=for-the-badge&logo=hostinger&logoColor=white)](https://www.hostinger.fr/)
[![PhpStorm](https://img.shields.io/badge/PhpStorm-000000?style=for-the-badge&logo=phpstorm&logoColor=white)](https://www.jetbrains.com/phpstorm/)

> **« Entre code et image, une même exigence : la précision. »**
> Portfolio personnel et professionnel conçu sur-mesure pour présenter mes projets de développement web full-stack et mes travaux photographiques.
> 🌐 En ligne : **[yannleflohic.fr](https://yannleflohic.fr/)**

> 📦 **Mise en production :** toute la procédure Hostinger (vérifications serveur, paquet ZIP, check-lists, rollback) est dans **[`DEPLOIEMENT_HOSTINGER.md`](DEPLOIEMENT_HOSTINGER.md)**.

---

## 📋 Sommaire

1. [Présentation](#1--présentation)
2. [Stack technique](#2-️-stack-technique)
3. [Fonctionnalités](#3--fonctionnalités)
4. [Architecture du projet](#4--architecture-du-projet)
5. [Conventions de code](#5--conventions-de-code)
6. [Design system « Graphite × Lime Spark »](#6--design-system--graphite--lime-spark-)
7. [Animations & interactions : fonctionnement technique](#7--animations--interactions--fonctionnement-technique)
8. [Double univers Dev / Photo](#8--double-univers-dev--photo)
9. [Glitch typographique du logo](#9--glitch-typographique-du-logo)
10. [Terminal CLI & API `/api/terminal`](#10--terminal-cli--api-apiterminal)
11. [Accessibilité & performances](#11--accessibilité--performances)
12. [Sécurité](#12--sécurité)
13. [Environnement de développement (Docker)](#13--environnement-de-développement-docker)
14. [Installation sans Docker](#14--installation-sans-docker)
15. [Tests & vérifications](#15--tests--vérifications)
16. [Diagnostic (développement)](#16--diagnostic-développement)
17. [Points d'attention & pistes d'amélioration](#17--points-dattention--pistes-damélioration)
18. [Déploiement](#18--déploiement)
19. [Auteur & licence](#19--auteur--licence)

---

## 1. 📌 Présentation

Ce projet est mon site portfolio officiel (**YannLf3**). Je l'ai conçu et développé sur-mesure sous **PhpStorm**, pour la toute première fois. Il associe une interface publique immersive à un back-office qui permet de modifier les contenus sans toucher au code.

| | |
| --- | --- |
| 👨‍💻 **Profil** | Yann Le Flohic : développeur web full-stack freelance et étudiant en 2ᵉ année de BUT MMI (IUT du Limousin, Limoges) |
| 📍 **Localisation** | Châteauroux / Limoges, France |
| 🎯 **Objectifs** | Vitrine professionnelle, démonstrateur technique, contenu administrable en autonomie |
| 🎨 **Identité** | **Double univers** : Mode Dev (Graphite `#23262F` × Lime Spark `#B6FF2E`, Space Grotesk + DM Sans) et Mode Photo (noir cinéma, papier, Fraunces éditoriale) |

---

## 2. 🛠️ Stack technique

| Couche | Technologies | Rôle |
| --- | --- | --- |
| Front-end | HTML5 sémantique, **Tailwind CSS 4** (`@theme inline`) | Mise en page mobile first, design system par tokens |
| Interactions | **JavaScript Vanilla ES6+** (pattern M/V/C), **WebGL 1**, **View Transitions API**, Pointer Events, IntersectionObserver, `<dialog>` | Animations, bascule de mode, curseur, visionneuse, comparateur, terminal ; aucune dépendance externe |
| Back-end | **PHP 8.3** orienté objet, architecture **MVC** maison | Routeur à point d'entrée unique, contrôleurs, vues PHP |
| Données | **MySQL 8 / PDO** (requêtes préparées) | Tables `settings` (contenus), `users` (admin), `projects` |
| API | Endpoint JSON `GET /api/terminal` (`Response::json()`) | Données publiques du terminal CLI |
| Environnement | **Docker** (Apache + PHP 8.3, MySQL 8, phpMyAdmin), npm | Développement local reproductible |
| Production | **Hostinger** (Apache/LiteSpeed, phpMyAdmin) | Hébergement de yannleflohic.fr |
| IDE | JetBrains PhpStorm | Choisi pour gagner en polyvalence sur un nouvel IDE |

---

## 3. ✨ Fonctionnalités

### 3.1 🌐 Interface publique

| Fonctionnalité | Description |
| --- | --- |
| Loader terminal | Séquence de logs façon `zsh` au premier chargement de la session (`sessionStorage`) |
| Double univers Dev / Photo | Interrupteur accessible et mémorisé, transition « View Transition » circulaire : palette, polices des titres, galerie, curseur et sections changent ([§ 8](#8--double-univers-dev--photo)) |
| Hero | Nom révélé au scroll, CTA « Glow », avatar vidéo (Animoji) ou image paramétrable |
| Parcours | Timeline formations / expériences (éditable en back-office) |
| Compétences | Bandeau défilant infini, tags langages / outils, niveaux de langues |
| Projets | Cartes filtrables par catégorie, apparition en cascade, **tilt 3D** à la souris |
| Galerie | Grille bento N&B (Dev) → **magazine asymétrique** couleur, formats 16:9 / 21:9 / 4:5, grain argentique, zoom d'objectif (Photo) + **visionneuse** plein écran |
| Post-traitement | Comparateur **avant / après** glissable (RAW ↔ étalonné), visible en mode Photo |
| Contact | Liens email / téléphone / GitHub / LinkedIn / Malt, centres d'intérêt |
| Lab (`/lab`) | Snippets CSS/JS avec console de code en modale, articles de veille |
| SEO | Titres, descriptions et URL canoniques dynamiques, Open Graph, JSON-LD (SEO local), `robots.txt`, `sitemap.xml` |

### 3.2 ⚡ Expérience immersive

| Fonctionnalité | Résumé | Détail |
| --- | --- | --- |
| Fond spatial WebGL | Particules Lime qui dérivent, s'écartent et se relient autour de la souris | [§ 7.2](#72-particules-webgl) |
| Curseur magnétique | Point Lime qui grossit, devient semi-transparent et attire les éléments `[data-magnetic]` | [§ 7.3](#73-curseur-magnétique) |
| Boutons Glow | Bordure Lime au repos ; fond Lime, texte Graphite et halo intense au survol | [§ 7.4](#74-boutons-glow) |
| Révélation des titres | Glissement depuis un masque + trait Lime de gauche à droite | [§ 7.5](#75-révélation-typographique) |
| Cartes 3D | Apparition en cascade + inclinaison suivant la souris | [§ 7.6](#76-cartes-portfolio--cascade--tilt-3d) |
| Bascule de mode | Révélation circulaire depuis l'interrupteur + morphing des photos vers la mise en page magazine | [§ 8.3](#83-transition--choc-visuel--view-transitions-api) |
| Curseur Photo | Cercle fin + point lumineux, grand cercle « Voir » / « Glisser » sur les images | [§ 8.5](#85-curseur-du-mode-photo) |
| Visionneuse & comparateur | Lightbox plein écran ; slider avant / après souris, doigt, clavier | [§ 8.6](#86-visionneuse-lightbox), [§ 8.7](#87-comparateur-avant--après-post-traitement) |
| Glitch du logo | Aberration chromatique de 0,5 s sur « YANN LE FLOHIC » | [§ 9](#9--glitch-typographique-du-logo) |
| Terminal CLI | ⌘K / Ctrl+K : commandes `help`, `skills`, `contact`, `sudo hire-yann`… | [§ 10](#10--terminal-cli--api-apiterminal) |

### 3.3 🔒 Back-office (`/login` → `/admin`)

| Fonctionnalité | Implémentation réelle |
| --- | --- |
| Authentification | Email + mot de passe vérifié par `password_verify()`, **jeton CSRF**, `session_regenerate_id(true)` + renouvellement du jeton à la connexion |
| Session | Cookie `HttpOnly`, `SameSite=Lax`, `Secure` + domaine en production (`public/index.php`) |
| Édition des contenus | Formulaire unique : hero, disponibilité, parcours, compétences, langues, projets, photos, contacts, centres d'intérêt, snippets et articles du Lab, enregistrés dans `settings` (clé / valeur, JSON pour les listes) **en une transaction**, après **filtrage par liste blanche** |
| Uploads | Avatar, images de projets et de galerie vers `public/assets/uploads/`. **Validation stricte** : type réel, taille ≤ 8 Mo, nom aléatoire (voir [§ 12.4](#124-uploads-dimages-sécurisés)) |
| Galerie & comparateur | Format `panorama` (21:9) en plus de `portrait` / `landscape` ; images, titre, texte et outils du comparateur avant / après |
| Retours utilisateur | **Messages flash** après chaque enregistrement : succès, avertissement (images refusées, avec le motif) ou erreur (jeton expiré, envoi trop lourd, échec BDD) |

---

## 4. 📂 Architecture du projet

### 4.1 Arborescence

```text
Portoflio/
├── .htaccess                     # Redirige toute requête vers public/ (hébergement mutualisé)
├── .env                          # Identifiants BDD (non versionné, jamais déployé) — 4 clés obligatoires
├── .env.example                  # Modèle documenté du .env (sans aucune vraie valeur)
├── app/
│   ├── config/
│   │   └── database.php          # getPDOConnection() : identifiants lus UNIQUEMENT dans le .env
│   ├── core/
│   │   ├── Response.php          # Response::json() : sortie JSON centralisée
│   │   ├── Csrf.php              # Jeton anti-CSRF (session) : token(), field(), isValid(), rotate()
│   │   └── ImageUploader.php     # Validation et enregistrement sécurisés des images
│   ├── controllers/
│   │   ├── HomeController.php    # Accueil, Lab, Mentions légales
│   │   ├── AuthController.php    # Connexion (CSRF) / déconnexion
│   │   ├── AdminController.php   # Back-office : CSRF, uploads, messages flash (accès protégé dès le constructeur)
│   │   └── ApiController.php     # GET /api/terminal
│   ├── models/
│   │   ├── settings.php          # Setting::getAll() / filterInput() (liste blanche) / updateAll() (transaction)
│   │   ├── User.php              # User::findByEmail()
│   │   └── Project.php
│   └── views/
│       ├── layouts/              # header.php (head, loader, canvas, nav), footer.php (curseur, terminal, scripts)
│       ├── front/                # home.php, lab.php, legal.php
│       │   └── partials/         # about, skills, projects, gallery (+ visionneuse), compare (avant / après), contact
│       └── admin/                # login.php, dashboard.php
├── public/                       # DocumentRoot
│   ├── index.php                 # Front controller : en-têtes de sécurité, session, routeur
│   ├── .htaccess                 # HSTS, redirection www → sans www, réécriture vers index.php
│   ├── css/style.css             # CSS COMPILÉ par Tailwind (ne pas éditer à la main)
│   ├── js/
│   │   ├── main.js               # Loader terminal + signal app:ready
│   │   ├── theme-toggle.js       # Bascule Dev / Photo (M/V/C) : interrupteur, mémorisation, View Transition
│   │   ├── animations.js         # WebGL, curseur, révélations, tilt (M/V/C)
│   │   ├── terminal.js           # Terminal CLI (M/V/C)
│   │   ├── photo-mode.js         # Visionneuse de la galerie + comparateur avant / après (M/V/C)
│   │   └── admin.js              # Scripts du back-office
│   ├── assets/                   # icons/, images/, videos/, uploads/ (+ .htaccess anti-exécution)
│   ├── robots.txt
│   └── sitemap.xml
├── src/css/input.css             # SOURCE Tailwind : tokens, @theme inline, composants
├── database/schema.sql           # Schéma importé automatiquement par Docker
├── Dockerfile                    # php:8.3-apache, pdo_mysql, rewrite, headers, DocumentRoot = public/
├── docker-compose.yml            # web (8000), db MySQL (3306), phpMyAdmin (8080)
├── package.json                  # Scripts npm run dev / build (Tailwind CLI)
├── CLAUDE.md                     # Directives de développement de l'assistant IA
├── DEPLOIEMENT_HOSTINGER.md      # Procédure de mise en production
└── README.md                     # Ce document
```

### 4.2 Routes

| Route | Méthode | Contrôleur | Rôle |
| --- | --- | --- | --- |
| `/` | GET | `HomeController::index()` | Accueil |
| `/lab` | GET | `HomeController::lab()` | Laboratoire |
| `/mentions-legales` | GET | `HomeController::legal()` | Mentions légales (`noindex`) |
| `/api/terminal` | GET (sinon 405) | `ApiController::terminal()` | Données JSON du terminal |
| `/login` | GET / POST | `AuthController` | Formulaire / traitement de connexion (jeton CSRF exigé en POST) |
| `/logout` | GET | `AuthController::logout()` | Déconnexion |
| `/admin` | GET | `AdminController::index()` | Tableau de bord (session requise) |
| `/admin/save` | POST | `AdminController::save()` | Enregistrement des contenus (CSRF → uploads → liste blanche → redirection + message flash) |
| autre | — | — | 404 propre, sans fuite d'information |

### 4.3 Cycle d'une requête

```
Navigateur ─► .htaccess racine ─► public/.htaccess ─► public/index.php
                                                       │ en-têtes sécurité + session_start()
                                                       │ normalisation de l'URL
                                                       ▼
                                              switch ($url) ─► Controller ─► Model (PDO) ─► View (PHP + Tailwind)
                                                                    └─► ApiController ─► Response::json()
```

---

## 5. 🧭 Conventions de code

Les directives complètes sont dans [`CLAUDE.md`](CLAUDE.md). Points clés appliqués dans le projet :

| Domaine | Règle |
| --- | --- |
| MVC (PHP **et** JS) | Le Modèle gère les données, la Vue l'affichage et la déclaration des événements, le Contrôleur les handlers. Le Modèle ne parle jamais à la Vue |
| JavaScript | Vanilla ES6+, chaque script est une IIFE `M` / `V` / `C` terminée par `C.init()`. Délégation d'événements (un écouteur sur un ancêtre commun). Templating par `<template>` + remplacement de chaînes, HTML accumulé puis injecté en une fois. `fetch` en `async/await` dans le Modèle |
| CSS | Tailwind 4 mobile first. Tokens **primitifs** séparés des **sémantiques**. Composants **BEM** avec variables **privées** `--_*` : les modifiers ne font que réassigner ces variables |
| PHP | `declare(strict_types=1)` sur le nouveau code, typage des paramètres et retours, encapsulation, requêtes préparées, réponses JSON via `Response::json()` |
| Documentation | JSDoc / PHPDoc sur toutes les fonctions et classes, commentaires sur l'**intention** des lignes non triviales |

Exemple de composant à variables privées :

```css
.btn-glow            { --_bg: transparent; --_fg: var(--accent); background-color: var(--_bg); color: var(--_fg); }
.btn-glow:hover      { --_bg: var(--accent); --_fg: var(--accent-contrast); --_shadow: 0 0 42px …; }
.btn-glow--sm        { --_pad-y: 0.4rem; --_pad-x: 1rem; }
```

---

## 6. 🎨 Design system « Graphite × Lime Spark »

Toute la configuration se trouve dans **`src/css/input.css`** (Tailwind 4, `@theme inline`).

### 6.1 Tokens primitifs (valeurs brutes)

| Token | Valeur | Rôle |
| --- | --- | --- |
| `--clr-graphite-950` | `#16181E` | Loader, fenêtre du terminal |
| `--clr-graphite-900` | `#1B1D24` | `--background` |
| `--clr-graphite-800` | `#23262F` | **Couleur de marque** · `--background-2` (fond de page) |
| `--clr-graphite-700` | `#2C303A` | `--card` |
| `--clr-graphite-600` | `#363A46` | Surfaces surélevées |
| `--clr-lime-spark` | `#B6FF2E` | **Couleur de marque** · accent, boutons |
| `--clr-lime-spark-hover` | `#A3EB1A` | Survol |
| `--clr-lime-spark-light` | `#D9FF94` | `--accent-2` |
| `--clr-lime-spark-soft` | Lime à 13 % | `--accent-soft` |
| `--clr-lime-deep` / `-hover` | `#3F6212` / `#365314` | Accent du thème clair (lisible sur blanc) |
| `--clr-text-light` / `--clr-text-muted` | `#F4F5F0` / `#A1A6B4` | Textes sur Graphite |
| `--clr-glitch-magenta` | `#FF2E9A` | Canal complémentaire du glitch (usage furtif uniquement) |
| `--clr-photo-ground` / `-surface` / `-card` | `#050505` / `#0B0B0B` / `#141414` | **Noir cinéma** du mode Photo |
| `--clr-photo-paper` / `-paper-muted` | `#F3EFE7` / `#A39E94` | Textes « papier » du mode Photo (18:1 et 7,4:1, AAA) |
| `--clr-photo-accent` | `oklch(0.82 0.14 62)` | Ambre (accent du mode Photo) |
| `--font-editorial` | Fraunces, Georgia, serif | Titres du mode Photo |
| `--ease-out-expo`, `--ease-in-out-quart`, `--duration-*` | Courbes et durées | Tokens de mouvement |

### 6.2 Tokens sémantiques (intention UI)

| Token | Utilitaire Tailwind | Mode Dev (défaut) | Thème `.light` | Mode Photo |
| --- | --- | --- | --- | --- |
| `--background` / `--background-2` | `bg-background` / `bg-background-2` | Graphite 900 / 800 | Clair | Noir cinéma `#0B0B0B` / `#050505` |
| `--card` | `bg-card`, `bg-background-card` | Graphite 700 | Blanc | `#141414` |
| `--border` / `--border-strong` | `border-border`, `border-border-strong` | Blanc 9 % / 18 % | Noir 10 % / 20 % | Blanc 7 % / 14 % |
| `--font-display` | `font-display` (+ tous les `h1`→`h6`) | Space Grotesk | Space Grotesk | **Fraunces** |
| `--foreground` / `--muted-foreground` | `text-foreground` / `text-muted-foreground` | `#F4F5F0` / `#A1A6B4` | Graphite 800 / gris | Papier `#F3EFE7` / `#A39E94` |
| `--accent` | `text-accent`, `bg-accent`, `border-accent` | Lime Spark | Lime Deep | Ambre |
| `--accent-contrast` | `text-accent-contrast` | Graphite 800 | Blanc | Brun foncé |
| `--btn`, `--btn-hover`, `--btn-foreground` | `bg-btn`, `hover:bg-btn-hover`, `text-btn-foreground` | Alias de `--accent*` | idem | idem |
| `--glitch-a` / `--glitch-b` | — | Accent / magenta | idem | Ambre / magenta |
| `--cli-bg`, `--cli-bar`, `--cli-text`, `--cli-muted`, `--cli-error`, `--cli-accent`, `--cli-border` | — | Terminal **toujours Graphite / Lime** | idem | idem |

> 💡 Les thèmes fonctionnent par **simple interversion des sémantiques** : aucun composant ne référence une couleur brute. Le mode Photo passe donc les fonds, textes, bordures, accents, boutons et **la police des titres** en une seule règle `[data-mode='photo']`. Seule exception volontaire : le terminal garde des tokens dédiés fixes (Graphite / Lime).

### 6.3 Contrastes vérifiés (WCAG 2.1)

| Combinaison | Ratio | Niveau |
| --- | --- | --- |
| Graphite 800 sur Lime Spark (boutons) | 12,5 : 1 | ✅ AAA |
| Lime Spark sur Graphite 800 | 12,5 : 1 | ✅ AAA |
| `#F4F5F0` sur Graphite 800 | 13,8 : 1 | ✅ AAA |
| `#A1A6B4` sur Graphite 700 | 5,4 : 1 | ✅ AA |
| Blanc sur Lime Deep (thème clair) | 7,1 : 1 | ✅ AAA |
| ~~Lime Spark sur blanc~~ | ~1,2 : 1 | ❌ Interdit → Lime Deep en `.light` |

### 6.4 Typographie

| Élément | Police | Mécanisme |
| --- | --- | --- |
| `h1` → `h6`, logo — mode Dev | **Space Grotesk** (300 → 700) | `@layer base` + `font-display` (`--font-display`) |
| `h1` → `h6`, logo — mode Photo | **Fraunces** (300 → 700, axe optique, **italique réel**) | Même mécanisme : `--font-display` réassigné à `--font-editorial` sous `[data-mode='photo']` |
| Corps de texte | **DM Sans** (300 → 700, italique réel, axe optique) | `@layer base` + `font-body` sur `<body>` |
| Tags, navigation, terminal | JetBrains Mono | `font-mono` |

Les quatre familles sont chargées par **une seule requête** Google Fonts (`@import` en tête de `input.css`, `display=swap`).

> ⚠️ Space Grotesk n'a pas de variante italique : en mode Dev, les fragments de titres en `italic` sont en oblique synthétisé. En mode Photo, Fraunces fournit un **vrai italique**.

---

## 7. 🎬 Animations & interactions : fonctionnement technique

### 7.1 Vue d'ensemble

| Fonctionnalité | Logique JS | Style (`input.css`) | Vues |
| --- | --- | --- | --- |
| Particules WebGL | `animations.js` → `M.particles`, `V.particles` | `.fx-particles` | `layouts/header.php` |
| Curseur magnétique | `M.cursor`, `V.cursor`, `V.magnet` | `.cursor` + bloc hors `@layer` | `layouts/footer.php`, `[data-magnetic]` |
| Boutons Glow | — (100 % CSS) | `.btn-glow` | `home.php`, `partials/projects.php` |
| Révélation des titres | `V.reveal`, `C.handleTitleIntersect` | `.reveal-title` | `home.php`, `lab.php`, 4 partials |
| Cartes : cascade + tilt | `M.tilt`, `V.tilt`, `C.handleCardIntersect` | `.tilt-card` | `partials/projects.php` |

Une **seule boucle `requestAnimationFrame`** fait tourner à la fois les particules et le curseur. Les écouteurs `mousemove` / `mouseout` / `mousedown` / `mouseup` / `resize` sont posés **une seule fois** sur `window` et partagés.

**Synchronisation avec le loader :** `main.js` émet l'événement `app:ready` (et l'attribut `data-app-ready`) quand le loader remonte. Les révélations attendent ce signal, avec un délai de secours de 6 s.

**Amélioration progressive :** un script inline du `<head>` pose la classe `.fx` sur `<html>`. Les états masqués n'existent que sous `.fx` : sans JS, rien n'est caché.

### 7.2 Particules WebGL

| Élément | Détail |
| --- | --- |
| Calque | `<canvas id="fx-particles">`, `position: fixed; z-index: -1; pointer-events: none` |
| Densité | 1 particule / 12 000 px² (de 40 à 160) |
| Rendu | 1 programme GLSL : `gl.POINTS` (disques doux via `gl_PointCoord`) + `gl.LINES`, alpha prémultiplié |
| Couleur | `--accent` lu puis converti en RGB par un canvas 2D d'1 px (gère hex **et** `oklch`). Mis à jour au changement de mode (`MutationObserver`) |

**Interaction :**

- **Répulsion.** Force ∝ (1 − d/R)² sur 140 px, appliquée à un **ressort amorti** : la particule s'écarte puis revient sur sa trajectoire.
- **Connexions.** Les particules à moins de 190 px de la souris, distantes entre elles de moins de 100 px, sont reliées par un trait. Le nombre de candidates est plafonné à 48 pour borner le coût O(n²).
- **Scintillement.** L'alpha est modulé par un sinus propre à chaque particule.

**Robustesse :** DPR plafonné à 1,75, buffers pré-alloués (aucune allocation dans la boucle), `dt` ≤ 50 ms, perte de contexte WebGL gérée. Sans WebGL, le canvas est retiré proprement.

### 7.3 Curseur magnétique

| Élément | Détail |
| --- | --- |
| Activation | Uniquement si `(hover: hover) and (pointer: fine)` : classe `.has-custom-cursor` sur `<html>` |
| Suivi | Lissage exponentiel indépendant du framerate, `transform: translate3d` uniquement |
| États BEM | `.cursor--hover` (×4, 28 %), `.cursor--magnetic` (×5,2, 20 %), `.cursor--view` (zones `[data-cursor]`), `.cursor--pressed`. En mode Photo : cercle fin + point lumineux ([§ 8.5](#85-curseur-du-mode-photo)) |
| Magnétisme | `[data-magnetic]` attiré de 30 % (14 px max) via la propriété CSS `translate`, qui n'écrase pas les `scale` Tailwind. Le centre « au repos » est recalculé sans le décalage appliqué, pour éviter toute oscillation |
| Curseur natif | `cursor: none !important` hors `@layer` (pour battre `cursor-pointer`). Il est rétabli dans les champs, les modales du Lab et le terminal |

Éléments magnétiques : logo, liens de navigation, bascule Dev/Photo, CTA du hero, filtres de projets, bouton du terminal. Pour en ajouter un, l'attribut `data-magnetic` suffit.

### 7.4 Boutons Glow

| État | `--_bg` | `--_fg` | `--_shadow` |
| --- | --- | --- | --- |
| Repos | transparent | Lime | aucune (bordure Lime seule) |
| `:hover` / `:focus-visible` | Lime | Graphite | 4 couches : liseré, halo 14 px, halo 42 px, lueur 80 px |
| `[aria-pressed="true"]` | Lime | Graphite | aucune (filtre actif) |
| `.btn-glow--sm` | — | — | Padding compact |

### 7.5 Révélation typographique

```html
<h2 class="reveal-title">
  <span class="reveal-title__mask">                    <!-- overflow: hidden -->
    <span class="reveal-title__inner">Titre</span>     <!-- translateY(110%) → 0 en 0,9 s -->
  </span>
</h2>                                                   <!-- ::after : trait Lime scaleX(0 → 1) après 0,55 s -->
```

- **Déclenchement :** IntersectionObserver (seuil 35 %), animation jouée une fois.
- **Variante :** `.reveal-title--short` limite le trait à une amorce de 5 rem.
- **Rognage :** le masque a une marge de sécurité, pour ne pas couper les jambages ni les italiques.

### 7.6 Cartes portfolio : cascade + tilt 3D

1. **Cascade.** Les cartes d'un même lot reçoivent `--_delay = index × 110 ms`. Ce délai est retiré au `transitionend`, sinon le tilt en hériterait.
2. **Tilt.** Le transform vaut `perspective(1000px) translate3d(…) rotateX(--_rx) rotateY(--_ry) scale(--_scale)`, avec ±7° maximum. Le calcul est limité à un par frame et un reflet radial Lime suit la souris (`::after`).
3. **Séparation des rôles.** Le JS **n'écrit que des variables** ; la composition du `transform` reste en CSS.

---

## 8. 📸 Double univers Dev / Photo

Le portfolio présente **deux univers complets** sur les mêmes pages : un « Mode Dev » technique et un « Mode Photo » de galerie d'art. La bascule se fait d'un geste, sans rechargement, avec une transition qui marque franchement le changement d'univers.

### 8.1 Principe : un seul attribut pilote tout

Le mode est porté par **`data-mode="dev|photo"` sur `<html>`**. Aucun composant ne teste le mode en JS pour se styler : il suffit que les **tokens sémantiques** soient réassignés sous `[data-mode='photo']` (voir [§ 6.2](#62-tokens-sémantiques-intention-ui)) pour que tout le site change.

| Aspect | Mode Dev | Mode Photo |
| --- | --- | --- |
| Fond | Graphite `#23262F` / `#1B1D24` + grille technique | **Noir cinéma** `#050505` / `#0B0B0B`, sans grille |
| Texte | `#F4F5F0` | Papier `#F3EFE7` (chaud) |
| Accent | Lime Spark `#B6FF2E` | Ambre `oklch(0.82 0.14 62)` |
| Titres (`h1`→`h6`, `.font-display`) | **Space Grotesk** | **Fraunces** (serif éditoriale, italique réel) |
| Corps de texte | DM Sans | DM Sans |
| Fond animé | Particules WebGL | Masqué, rendu WebGL **suspendu** (aucun calcul inutile) |
| Galerie | Grille bento, **noir & blanc**, légende au survol | **Magazine** asymétrique, couleur, formats cinéma, grain argentique, légende éditoriale |
| Curseur | Point Lime magnétique | **Cercle fin + point lumineux**, grand cercle « Voir » / « Glisser » sur les images |
| Section Post-traitement (avant / après) | Masquée | **Visible** |
| Contenus textuels | Versions « dev » | Versions « photo » (classes `[html[data-mode='photo']_&]:…` existantes) |
| Terminal CLI | Graphite / Lime | **Inchangé** (Graphite / Lime, volontairement) |

### 8.2 Interrupteur et mémorisation

```html
<button id="mode-switch" class="mode-switch" role="switch" aria-checked="false" aria-label="Mode photographe">
  <span class="mode-switch__thumb"></span>                      <!-- pouce qui coulisse (--_thumb-x) -->
  <span class="mode-switch__option mode-switch__option--dev">Développeur</span>
  <span class="mode-switch__option mode-switch__option--photo">Photographe</span>
</button>
```

| Point | Implémentation |
| --- | --- |
| Accessibilité | `role="switch"` + `aria-checked` (`true` = Photo). Espace / Entrée natifs (c'est un `<button>`), libellé vocal « Mode photographe, activé / désactivé » |
| Style | Composant BEM `.mode-switch` : `[aria-checked='true']` ne fait que réassigner `--_thumb-x: 100%` ; libellés courts « Dev / Photo » sur mobile |
| Mémorisation | `localStorage['portfolio-mode']`, lu par un **script inline du `<head>`** qui pose `data-mode` **avant le premier rendu** (aucun flash du mauvais univers) |
| Logique | `public/js/theme-toggle.js` (M/V/C) : synchronise l'interrupteur, change le mode, enregistre, choisit la transition |
| API pour les autres scripts | Événement `mode:request`, utilisé par la commande `theme` du terminal : |

```js
document.dispatchEvent(new CustomEvent('mode:request', {detail: {mode: 'photo'}}));
```

### 8.3 Transition « choc visuel » (View Transitions API)

1. `document.startViewTransition(update)` : le navigateur capture l'écran actuel, applique `data-mode`, puis capture le nouvel écran.
2. Le nouvel univers est **révélé par un cercle** qui grandit depuis le centre de l'interrupteur. `clip-path` est animé sur `::view-transition-new(root)` en 750 ms, avec `cubic-bezier(0.76, 0, 0.24, 1)`.
3. Chaque photo de la galerie porte un `view-transition-name` unique (`gallery-photo-N`) et la classe `gallery-photo`. Elle **se déplace et se redimensionne seule** de la grille bento vers sa place dans la mise en page magazine.

| Contexte | Comportement |
| --- | --- |
| View Transitions disponibles (Chrome, Edge, Safari 18+, Firefox récents) | Révélation circulaire + morphing des photos |
| Indisponibles | **Repli** : onde colorée `#theme-transition-overlay`, partant elle aussi de l'interrupteur (`--ripple-x/y`) |
| `prefers-reduced-motion` | Changement **instantané**, sans aucune animation |

### 8.4 Galerie : de la grille bento au magazine

Composant `.gallery` (`input.css` § 10), vue `partials/gallery.php`.

| Format (back-office) | Mode Dev | Mode Photo |
| --- | --- | --- |
| `portrait` | 3:4, deux rangées (grand écran) | **4:5** |
| `landscape` | 16:10 | **16:9** |
| `panorama` (nouveau) | 16:10 | **21:9**, traverse toutes les colonnes (`column-span: all`) |

**Mise en page.**

- **Mode Dev :** CSS Grid sur 1, 2 ou 3 colonnes.
- **Mode Photo :** multi-colonnes de type masonry (1 colonne sur mobile, 2 dès 768 px), avec de grandes marges (`clamp(2.5rem, 7vw, 6.5rem)`).
- **Asymétrie :** des retraits alternés (`:nth-child(3n+2)` à gauche, `:nth-child(4n+3)` à droite) donnent le rythme « pleine page » d'un magazine.

**Effets en mode Photo.**

| Effet | Implémentation |
| --- | --- |
| Zoom d'objectif | `scale(1.08)` sur 1,8 s avec `cubic-bezier(0.16, 1, 0.3, 1)` : lent et doux, comme une mise au point |
| Grain argentique | `.grain::after` : bruit SVG (`feTurbulence`) en `mix-blend-mode: overlay`, opacité 0,22. Animé par sauts (`steps(6)`) pour imiter un film qui défile. Statique en mouvement réduit |
| Couleur | Les photos sont en noir & blanc en mode Dev (`--_filter: grayscale(1)`) et **en couleur en mode Photo** |
| Légende | Sous l'image, en Fraunces italique, précédée de « N° 01 » (en surimpression au survol en mode Dev) |

### 8.5 Curseur du mode Photo

| État | Rendu | Déclencheur |
| --- | --- | --- |
| Repos | Cercle de 30 px (trait 1 px) + point lumineux central avec halo | — |
| Lien / bouton | Cercle de 46 px | `a`, `button`, `[data-magnetic]`… |
| **Visionneuse** | **Cercle de 96 px + libellé** (« Voir », « Glisser »), le point s'efface | Tout élément `[data-cursor]` (libellé = `data-cursor-label`) |
| Clic | Légère compression | `mousedown` |

**Détails de rendu.**

- Le cercle change de **taille** (largeur / hauteur), et non d'échelle : un `scale` épaissirait le trait.
- `mix-blend-mode: difference` le garde lisible sur une photo claire comme sur le fond noir.
- Au **défilement sans mouvement de souris**, l'élément sous le pointeur est réévalué (une fois par frame), donc le libellé reste juste.

Pour ajouter une zone visionneuse :

```html
<div data-cursor="view" data-cursor-label="Voir">…</div>
```

### 8.6 Visionneuse (lightbox)

| Point | Implémentation (`public/js/photo-mode.js`) |
| --- | --- |
| Ouverture | Clic sur une photo (délégation sur `#gallery-grid`), `<dialog id="lightbox">` plein écran via `showModal()` |
| Navigation | Boutons ← / →, flèches du clavier, **balayage** au doigt ou à la souris (≥ 50 px). Navigation en boucle |
| Fermeture | Échap (natif), bouton ✕, clic dans le vide autour de la photo |
| Fluidité | Photos voisines **préchargées** ; fondu pendant le chargement |
| Accessibilité | Chaque photo est un `<button>` libellé (« Agrandir la photo : … ») ; à la fermeture, le focus revient sur la photo d'origine |

### 8.7 Comparateur avant / après (post-traitement)

Section `#post-traitement` (`partials/compare.php`), **visible en mode Photo uniquement**.

```
┌──────────────── .compare (aspect 16:9, --_pos) ────────────────┐
│ .compare__before (clip-path: inset(0 calc(100% - --_pos) 0 0)) │  ← image brute, à gauche
│                         ┃ .compare__handle (role="slider")     │
│ img finale (dessous)    ┃                                      │  ← image étalonnée, à droite
└────────────────────────────────────────────────────────────────┘
```

| Interaction | Implémentation |
| --- | --- |
| Souris / doigt | Pointer Events délégués au niveau du document. La position saute sous le pointeur, puis suit le glissement (`setPointerCapture`). `touch-action: pan-y` : le défilement vertical reste natif au doigt |
| Clavier | Poignée `role="slider"` : ← → ↑ ↓ (±2 %, Maj : ±10 %), Page ↑ / ↓ (±10 %), Début / Fin. `aria-valuenow` et `aria-valuetext` à jour |
| Amorce | Au premier affichage en mode Photo, un aller-retour amorti (50 → ~64 → ~36 → 50 %) suggère l'interaction. Il s'arrête dès que l'utilisateur prend la main ; absent en mouvement réduit |
| Curseur | Grand cercle « Glisser » |

**Contenus du comparateur** (back-office, onglet *Galerie → Comparateur Avant / Après*) :

- `compare_before` et `compare_after` (URL ou upload, validés par `ImageUploader`) ;
- `compare_title`, `compare_caption`, `compare_tools`.

> ⚠️ **Sans image brute fournie**, le « RAW » est **simulé** en CSS à partir de l'image finale (`saturate(0.45) contrast(0.78) brightness(1.08)`). L'interface l'indique explicitement : étiquette « RAW · simulé » et mention sous le comparateur. Pour une vraie démonstration, exporter le RAW non retouché en JPEG / WebP, **avec le même cadrage** que l'image finale.

### 8.8 Fichiers impliqués

| Fichier | Rôle |
| --- | --- |
| `src/css/input.css` | Tokens Photo (noir cinéma, papier), `--font-editorial`, sémantiques `[data-mode='photo']`, composants `.mode-switch`, `.grain`, `.gallery`, `.lightbox`, `.compare`, curseur Photo, règles View Transitions |
| `app/views/layouts/header.php` | Script inline (mode mémorisé avant rendu), interrupteur `#mode-switch` |
| `public/js/theme-toggle.js` | Bascule M/V/C, mémorisation, View Transition / repli, événement `mode:request` |
| `app/views/front/partials/gallery.php` | Galerie BEM, formats, noms de transition, `<dialog id="lightbox">` |
| `app/views/front/partials/compare.php` | Section Post-traitement + comparateur |
| `public/js/photo-mode.js` | Visionneuse + comparateur (M/V/C) |
| `public/js/animations.js` | État « view » du curseur, libellé, réévaluation au défilement, pause des particules en mode Photo |
| `public/js/terminal.js` | Commande `theme` via `mode:request` |
| `app/views/admin/dashboard.php`, `public/js/admin.js` | Format `panorama`, champs du comparateur |
| `app/models/settings.php`, `app/controllers/AdminController.php` | Clés `compare_*` en liste blanche, upload des images du comparateur |

---

## 9. ⚡ Glitch typographique du logo

**Portée :** uniquement le logo du header, « YANN LE FLOHIC ». C'est le seul élément portant la classe `.glitch`. Le logo suit la police des titres : Space Grotesk en mode Dev, Fraunces en mode Photo. Le glitch fonctionne dans les deux cas.

```html
<a href="/" class="glitch …" data-glitch="Yann Le Flohic">
    <span class="glitch__text">Yann Le Flohic</span>
</a>
```

| Calque | Rôle |
| --- | --- |
| `::before` (`--glitch-a`, accent) | Copie du texte (`content: attr(data-glitch)`), découpée en tranches (`clip-path: inset()`), décalée vers la gauche |
| `::after` (`--glitch-b`, magenta) | Autres tranches, décalage opposé : c'est l'**aberration chromatique** |
| `.glitch__text` | Micro-secousses et cisaillement (`skewX`) |

**Fonctionnement :**

- **100 % CSS.** L'animation se déclenche au `:hover` et au `:focus-visible`.
- **Durée.** `--_duration: 0.5s`, jouée **une seule fois** par survol. Le dernier keyframe ramène l'opacité des calques à 0 : le texte se **restabilise** même si la souris reste dessus.
- **Rendu numérique.** `steps(1, end)` fait sauter chaque étape au lieu de la fondre.
- **Couleurs.** `mix-blend-mode: screen` additionne les canaux comme un vrai décalage RVB.

> ⚠️ `data-glitch` doit reprendre **exactement** le texte affiché.

---

## 10. 💻 Terminal CLI & API `/api/terminal`

### 10.1 Utilisation

| Action | Mécanisme |
| --- | --- |
| Ouvrir | Bouton `>_ terminal` (bas droite) ou **⌘K** (macOS) / **Ctrl+K**. Le libellé s'adapte à la plateforme |
| Fenêtre | `<dialog id="cli" closedby="any">` ouvert par `showModal()` : top layer, focus piégé, défilement de la page verrouillé |
| Fermer | Échap, ✕, `exit`, raccourci, clic hors de la fenêtre (repli JS pour Safari, qui ne supporte pas encore `closedby`) |
| Saisie | <kbd>Tab</kbd> autocomplétion · <kbd>↑</kbd> / <kbd>↓</kbd> historique · <kbd>Ctrl</kbd>+<kbd>L</kbd> effacer. Commandes affichées **cliquables** (utile au tactile) |

### 10.2 Commandes

| Commande | Résultat | Utilise l'API |
| --- | --- | --- |
| `help` | Liste des commandes | — |
| `whoami` | Nom, rôle, ville, études, disponibilité, centres d'intérêt | ✅ |
| `skills` | Langages, outils, langues avec barres ASCII | ✅ |
| `projects` | Projets numérotés, titres cliquables | ✅ |
| `contact` | Email, téléphone, GitHub, LinkedIn, Malt | ✅ |
| `ls` / `goto <section>` | Sections du site / défilement (`about`, `skills`, `projects`, `contact`) ou ouverture de `lab` | — |
| `theme dev\|photo` | Bascule le mode via l'événement `mode:request` (même transition et même mémorisation que l'interrupteur) | — |
| `history`, `date`, `echo`, `clear`, `exit` | Utilitaires | — |
| **`sudo hire-yann`** | Séquence animée : authentification, « compilation » du profil, art ASCII **HIRED**, disponibilité, liens de contact | ✅ |
| Cachées | `hire-yann`, `rm`, `vim`, `sudo <autre>` | — |

### 10.3 Flux technique

```
saisie ─► C.handleSubmit ─► M.execute(raw, {currentMode})
                               │  needsData ? ─► await M.loadData() ─► fetch('/api/terminal')
                               ▼
                    {lines, effect?}   ← données pures, jamais de HTML dans le Modèle
                               │
            V.renderLines(lines)   +   C.applyEffect(effect)
            (<template> + escape)      clear | close | theme | goto | sequence
```

**Rendu.**

- Les gabarits `<template id="tpl-cli-line|link|chip">` sont remplis en **une seule passe** par `V.fill()`, avec une fonction de remplacement.
- Ainsi, les motifs `$&`, `$'` ou `{{…}}` contenus dans une donnée ne sont jamais réinterprétés.
- Toute donnée est échappée avant injection.

**Données.**

- Elles sont chargées à la première ouverture, puis gardées en mémoire. Les requêtes simultanées sont mutualisées.
- En cas d'échec, un message d'erreur s'affiche et une nouvelle tentative a lieu à la commande suivante.

### 10.4 Référence de l'API

```http
GET /api/terminal
Accept: application/json
```

| Code | Cas | Corps |
| --- | --- | --- |
| `200` | Succès (`Cache-Control: public, max-age=300`) | `{name, status, location, school, skills[], tools[], interests[], languages[{name, level, percent}], contacts[{label, value, href}], projects[{title, meta, category, link}]}` |
| `405` | Méthode autre que GET (`Allow: GET`) | `{"error": "Méthode non autorisée."}` |
| `503` | Erreur BDD (détail en `error_log` uniquement) | `{"error": "Service momentanément indisponible."}` |

**Garanties :**

- **Liste blanche stricte.** Seules ces clés quittent le serveur.
- **Liens filtrés.** Seuls les schémas `http`, `https`, `mailto` et `tel` passent (bloque `javascript:`). Les pourcentages sont bornés entre 0 et 100.
- **Encodage.** `JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR`, en-tête `X-Content-Type-Options: nosniff`.
- **Prérequis.** **PHP ≥ 8.1**, pour le type de retour `never`.

---

## 11. ♿ Accessibilité & performances

| Situation | Comportement |
| --- | --- |
| `prefers-reduced-motion: reduce` | Titres et cartes visibles d'emblée, particules figées (1 image), ni tilt, ni magnétisme, ni glitch, ni animation d'ouverture du terminal. Séquence `sudo hire-yann` instantanée. Mode Photo : bascule **instantanée** (pas de View Transition), grain statique, pas de zoom d'objectif ni d'amorce du comparateur |
| Écran tactile | Pas de curseur custom ni de tilt. Particules sans interaction. Terminal utilisable (commandes cliquables, champ en 16 px pour éviter le zoom iOS) |
| Sans JavaScript | Aucun contenu masqué. Le glitch (CSS pur) fonctionne |
| Clavier | `:focus-visible` stylé (boutons, logo). Terminal : focus piégé, Échap, focus rendu au déclencheur |
| Interrupteur de mode | `role="switch"` + `aria-checked`, libellé « Mode photographe », focus visible |
| Comparateur | Poignée `role="slider"` (`aria-valuenow` / `aria-valuetext`), pilotable au clavier |
| Visionneuse | `<dialog>` modal (focus piégé, Échap), photos déclenchées par des `<button>` libellés, focus rendu à la fermeture |
| Lecteurs d'écran | Terminal en `role="log"` + `aria-live="polite"`, `aria-expanded` sur le bouton, `aria-pressed` sur les filtres, `aria-hidden` sur le décor |
| Performances | Une seule boucle rAF, buffers WebGL pré-alloués, tilt limité à un calcul par frame, données du terminal chargées à la demande, `?v=` de cache-busting sur CSS/JS |

---

## 12. 🔐 Sécurité

### 12.1 Vue d'ensemble des protections

| Menace | Protection | Où |
| --- | --- | --- |
| Clickjacking, MIME sniffing, fuite de Referrer | `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy` | `public/index.php` |
| Interception (HTTP en clair) | HSTS + cookie de session `Secure` en production | `public/.htaccess`, `public/index.php` |
| Vol de session par JavaScript (XSS) | Cookie `HttpOnly`, `SameSite=Lax` | `public/index.php` |
| Fixation de session | `session_regenerate_id(true)` à la connexion | `AuthController::login()` |
| **Requêtes forgées (CSRF)** | **Jeton synchronisé en session** sur le login et le back-office | `app/core/Csrf.php` → [§ 12.2](#122-protection-csrf) |
| **Mass assignment** (champs ajoutés à la main dans la requête) | **Liste blanche** des clés et sous-champs enregistrables | `Setting::filterInput()` → [§ 12.3](#123-liste-blanche-des-contenus-enregistrés) |
| **Upload malveillant** (script déguisé en image) | **Type réel + décodage image + nom aléatoire** ; `.htaccess` anti-exécution en seconde barrière | `app/core/ImageUploader.php` → [§ 12.4](#124-uploads-dimages-sécurisés) |
| **Fuite d'identifiants** | **Aucun secret dans le code** : `.env` obligatoire, non versionné | `app/config/database.php` → [§ 12.5](#125-identifiants-et-secrets) |
| Injection SQL | Requêtes préparées PDO, `ATTR_EMULATE_PREPARES = false` | Modèles |
| XSS en sortie | `htmlspecialchars()` dans les vues ; `escape()` + gabarits en une passe côté JS | Vues, `terminal.js` |
| Mots de passe | Hachage `password_hash` / `password_verify` | Table `users` |
| Exposition de données par l'API | Liste blanche + filtrage des schémas d'URL | `ApiController` |
| Fuite d'informations | 404 neutre, erreurs détaillées uniquement dans le log serveur | `public/index.php`, contrôleurs |
| Écriture partielle en BDD | Enregistrement en **transaction** (tout ou rien) | `Setting::updateAll()` |

### 12.2 Protection CSRF

**Le risque.** Un site tiers peut faire soumettre à ton navigateur un formulaire vers `/admin/save`. Le cookie de session part automatiquement avec la requête, donc sans protection, le contenu du site pourrait être modifié à ton insu pendant que tu es connecté.

**La parade.** Un jeton secret est stocké en session et inséré dans chaque formulaire sensible. Le site tiers ne peut pas lire ce jeton, donc toute requête sans jeton valide est refusée.

| Étape | Code |
| --- | --- |
| Génération (64 caractères hex, `random_bytes(32)`) | `Csrf::token()`, appelé par `Csrf::field()` |
| Insertion dans les formulaires | `<?= Csrf::field() ?>` dans `admin/login.php` et `admin/dashboard.php` |
| Vérification en temps constant (`hash_equals`) | `Csrf::isValid($_POST['csrf_token'])`, **avant tout traitement** dans `AuthController::login()` et `AdminController::save()` |
| Renouvellement à la connexion (élévation de privilège) | `Csrf::rotate()` juste après `session_regenerate_id(true)` |
| Jeton invalide | Login : message « Session expirée ». Back-office : redirection + message d'erreur, **aucune donnée écrite**, tentative journalisée (`error_log`, avec l'IP) |

### 12.3 Liste blanche des contenus enregistrés

Avant cette correction, `Setting::updateAll($_POST)` enregistrait **n'importe quelle clé** reçue. Désormais, `Setting::filterInput()` applique les règles suivantes :

| Règle | Détail |
| --- | --- |
| Clés simples autorisées (16) | `hero_name`, `hero_dev`, `hero_photo`, `hero_avatar`, `dispo_text`, `location`, `status`, `parcours_intro`, `skills_tech`, `skills_tools`, `interests`, `contact_title`, `contact_subtitle`, `project_cat_1` → `3` |
| Listes autorisées (7) et leurs sous-champs | `parcours` (period, type, title, detail) · `langues` (name, level, percent) · `projects` (id, title, meta, description, image, link, category, tags) · `photos` (url, unsplash_id, legend, alt, format) · `contacts` (label, value, href) · `snippets` (title, category, badge, html, css, js) · `articles` (title, meta, content) |
| Valeurs | Converties en chaînes ; un tableau injecté à la place d'un texte devient une chaîne vide |
| Listes | Ré-indexées (0, 1, 2…) |
| Clés absentes | **Non modifiées** (jamais vidées par erreur) |
| Enregistrement | En **transaction** : en cas d'erreur, `rollBack()` et rien n'est modifié |

> 💡 **Ajouter un nouveau réglage au back-office** se fait en 3 temps : ajouter le champ dans `dashboard.php` (et `admin.js` si la ligne est dynamique), **puis déclarer la clé dans `Setting::SCALAR_KEYS` ou `Setting::LIST_SCHEMAS`**. Sans cette dernière étape, le champ est ignoré silencieusement.

### 12.4 Uploads d'images sécurisés

Avant cette correction, le fichier était enregistré **sous son nom d'origine, sans aucune vérification** ; seul le `.htaccess` d'`uploads/` empêchait l'exécution d'un script. Désormais, `ImageUploader::store()` impose toutes les vérifications suivantes :

| # | Vérification | Pourquoi |
| --- | --- | --- |
| 1 | `error === UPLOAD_ERR_OK` et `is_uploaded_file()` | Le fichier provient bien d'un envoi HTTP (pas d'un chemin forgé) |
| 2 | Taille ≤ **8 Mo** | Limite le stockage abusif (les limites PHP `upload_max_filesize` / `post_max_size` s'appliquent aussi) |
| 3 | Type MIME **réel** lu dans le contenu (`finfo`), parmi `image/jpeg`, `image/png`, `image/webp`, `image/gif` | Le type envoyé par le navigateur (`$_FILES['type']`) est contrôlé par le client, donc non fiable |
| 4 | `getimagesize()` réussit | Le fichier doit vraiment se décoder comme une image |
| 5 | Nom **régénéré** : `{avatar\|proj\|photo}_{timestamp}_{16 hex aléatoires}.{ext}`, extension déduite du type détecté | Le nom d'origine (`photo.php.jpg`, `../../x`…) n'est jamais réutilisé |
| 6 | Droits `0644` sur le fichier, `0755` sur le dossier (au lieu de `0777`) | Ni exécutable, ni modifiable par les autres utilisateurs du serveur |

**Comportement en cas de refus.**

- L'image est ignorée et l'ancienne URL est conservée.
- Le reste du formulaire est **quand même enregistré**.
- Un avertissement liste chaque fichier refusé avec son motif, par exemple : « « evil.php.jpg » ignoré : type non autorisé ».

Le SVG est volontairement exclu : c'est du XML qui peut embarquer du JavaScript.

Le `.htaccess` de `public/assets/uploads/` reste en place comme **seconde barrière**.

**Envoi trop volumineux** (au-delà de `post_max_size`) : PHP vide alors `$_POST`. Ce cas est détecté et expliqué, au lieu d'afficher un faux « jeton invalide ».

### 12.5 Identifiants et secrets

Avant cette correction, `app/config/database.php` contenait des **valeurs de repli de production écrites en clair**, dont le mot de passe MySQL. Désormais :

| Point | Détail |
| --- | --- |
| Source unique | Les identifiants sont lus **uniquement** dans le `.env` à la racine : `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` |
| Obligatoire | Variable manquante : erreur 500 générique pour le visiteur, nom des variables manquantes dans le log serveur. `DB_PASS` doit exister mais peut être vide (MySQL local sans mot de passe) |
| Modèle | `.env.example` (versionné) documente les clés, avec des valeurs factices |
| Versionnement | `.env` est dans `.gitignore`. Vérification faite : le mot de passe n'apparaît dans **aucun** commit de l'historique |
| Déploiement | Le `.env` **du serveur** doit contenir les 4 clés **avant** d'envoyer le nouveau `database.php` (voir `DEPLOIEMENT_HOSTINGER.md` § 5.1) |

> 🔑 **Recommandation :** ce mot de passe a figuré en clair dans le code de travail. Le changer dans hPanel (Bases de données → Changer le mot de passe), puis mettre à jour `DB_PASS` dans le `.env` du serveur.

## 13. 🐳 Environnement de développement (Docker)

Le conteneur `web` (PHP 8.3 + Apache, `DocumentRoot` = `public/`) **sert déjà le site sur le port 8000**. Il n'y a **pas** besoin de lancer `php -S` en plus : ce serveur entrerait en conflit sur le même port.

### 13.1 Démarrage rapide (2 terminaux)

```
┌───────────────────────────────────────────┐   ┌───────────────────────────────────────────┐
│ TERMINAL 1 — 🐳 Docker                    │   │ TERMINAL 2 — 🎨 Tailwind (laisser tourner)│
│ $ docker compose up -d                    │   │ $ npm run dev                             │
└───────────────────────────────────────────┘   └───────────────────────────────────────────┘
```

1. Ouvrir **Docker Desktop**.
2. À la racine du projet : `docker compose up -d` (au tout premier lancement, ou après une modification du `Dockerfile` : `docker compose up -d --build`).
3. Dans un second terminal : `npm install` (une seule fois), puis `npm run dev`, à laisser ouvert pour recompiler `public/css/style.css` à chaque modification.
4. Ouvrir http://localhost:8000.

> 💡 Au premier démarrage, MySQL importe automatiquement `database/schema.sql`. Le volume `db_data` conserve ensuite les données entre deux sessions.

### 13.2 Services

| Service | Conteneur | URL / Port |
| --- | --- | --- |
| 🏠 Site public | `portfolio_app` | http://localhost:8000/ |
| 🔐 Connexion back-office | `portfolio_app` | http://localhost:8000/login |
| ⚙️ Administration | `portfolio_app` | http://localhost:8000/admin |
| 🔌 API terminal | `portfolio_app` | http://localhost:8000/api/terminal |
| 🗄️ phpMyAdmin | `portfolio_phpmyadmin` | http://localhost:8080 (utilisateur `root` / mot de passe `root`) |
| 🐬 MySQL | `portfolio_mysql` | `localhost:3306` (hôte `db` depuis le conteneur web) |

### 13.3 Fichier `.env` (local)

Créer le fichier à partir du modèle : `cp .env.example .env`. Les **4 clés sont obligatoires**, car le code n'a plus aucune valeur de repli (voir [§ 12.5](#125-identifiants-et-secrets)).

| Clé | Valeur Docker |
| --- | --- |
| `DB_HOST` | `db` |
| `DB_NAME` | `portfolio_db` |
| `DB_USER` | `root` |
| `DB_PASS` | `root` |

> ⚠️ Ce fichier n'est **jamais** versionné ni déployé : celui du serveur Hostinger contient ses propres identifiants.

### 13.4 Commandes utiles

| Besoin | Commande |
| --- | --- |
| État des conteneurs | `docker compose ps` |
| Logs PHP (erreurs) | `docker exec portfolio_app tail -f /var/log/apache2/php_error.log` |
| Logs d'un service | `docker compose logs -f web` (ou `db`) |
| Reconstruire l'image | `docker compose up -d --build` |
| Compiler le CSS (dev, watcher) | `npm run dev` |
| Compiler le CSS (production, minifié) | `npm run build` |

### 13.5 Arrêter l'environnement

1. Dans le terminal Tailwind : `Ctrl + C`.
2. `docker compose stop` (arrête sans supprimer ; relancer avec `docker compose up -d`).

### 13.6 ✅ Check-list quotidienne

- [ ] 🐳 Docker Desktop lancé, `docker compose ps` affiche les 3 conteneurs actifs
- [ ] 🎨 `npm run dev` tourne
- [ ] 🌐 http://localhost:8000 répond
- [ ] 🗄️ phpMyAdmin accessible si besoin

---

## 14. 🧰 Installation sans Docker

| Prérequis | Version |
| --- | --- |
| PHP | **≥ 8.1** (8.3 recommandé), extension `pdo_mysql` |
| MySQL | ≥ 8.0 |
| Node.js / npm | Pour la compilation Tailwind |
| Apache (MAMP…) ou serveur PHP intégré | — |

1. **Cloner le dépôt :**

   ```bash
   git clone https://github.com/YannLf3/<nom-du-repo>.git
   cd <nom-du-repo>
   ```

2. **Préparer la base de données :** créer `portfolio_db`, puis importer `database/schema.sql`.

3. **Créer le `.env`** (`cp .env.example .env`), puis renseigner `DB_HOST=127.0.0.1` et les identifiants MySQL locaux. Les 4 clés sont obligatoires ; `DB_PASS` peut rester vide.

4. **Compiler le CSS :**

   ```bash
   npm install
   npm run dev        # équivaut à : tailwindcss -i ./src/css/input.css -o ./public/css/style.css --watch
   ```

5. **Lancer le serveur :** pointer un virtual host Apache vers `public/` (recommandé, car les `.htaccess` y sont lus), ou :

   ```bash
   php -S localhost:8000 -t public
   ```

   > Le serveur intégré ignore les `.htaccess` (pas de HSTS ni de redirection `www`). Il sert les fichiers existants (CSS, JS, images) et renvoie toutes les autres URL (`/lab`, `/api/terminal`…) vers `public/index.php`, donc le routage fonctionne.

---

## 15. 🧪 Tests & vérifications

### 15.1 Vérifications automatiques rapides

```bash
# Syntaxe JS et PHP
node --check public/js/animations.js && node --check public/js/terminal.js && node --check public/js/photo-mode.js && node --check public/js/theme-toggle.js && node --check public/js/main.js
for f in app/core/*.php app/controllers/*.php public/index.php app/views/layouts/*.php app/views/front/*.php app/views/front/partials/*.php; do php -l "$f"; done

# Pages et API
for p in / /lab /login /mentions-legales /api/terminal; do curl -s -o /dev/null -w "$p %{http_code}\n" http://localhost:8000$p; done
curl -s -o /dev/null -w "POST /api/terminal %{http_code}\n" -X POST http://localhost:8000/api/terminal   # attendu : 405

# Le CSS compilé contient les composants (chaque ligne ≥ 1)
for c in btn-glow reveal-title tilt-card fx-particles glitch-channel-a cli-launcher cli__line mode-switch__thumb gallery__caption lightbox__btn compare__handle film-grain Fraunces; do printf "%s: " $c; grep -c "$c" public/css/style.css; done
```

### 15.2 Recette réalisée (Chrome, Docker, 05/10/2026)

- [x] Aucune erreur console sur `/`, `/lab`, `/mentions-legales`
- [x] Hero : titre révélé + trait, bouton Glow avec halo, connexions de particules autour de la souris
- [x] Cartes : cascade 0 / 110 / 220 ms, délai nettoyé, tilt appliqué puis remis à plat
- [x] Filtres : `aria-pressed` correct, filtrage inchangé
- [x] Mode Photo : accent ambre (`oklch`) repris par les particules, curseur en collimateur
- [x] Glitch : canaux Lime et magenta visibles, stabilisation après 0,5 s
- [x] Terminal : ⌘K / Ctrl+K, `help`, `whoami`, `skills`, `projects`, Tab, ↑, `sudo hire-yann`, `theme photo`, `goto contact`, clic extérieur
- [x] Injection `echo <b>$& test</b>` affichée littéralement
- [x] `prefers-reduced-motion` : tout est visible, rien n'est animé
- [x] Mobile 375 / 390 px : pas de défilement horizontal, terminal sans débordement

### 15.3 Recette sécurité du back-office (HTTP réel, Docker, 05/10/2026)

Les tests ont été réalisés avec un compte admin **temporaire**, supprimé ensuite. La base locale a été restaurée à l'identique.

| # | Scénario | Résultat attendu | Obtenu |
| --- | --- | --- | --- |
| 1 | `POST /login` sans jeton | « Session expirée », pas de connexion | ✅ |
| 2 | `POST /login` avec jeton + bons identifiants | 302 → `/admin` | ✅ |
| 3 | Jeton après connexion | Différent de celui du login (`Csrf::rotate()`) | ✅ |
| 4 | `POST /admin/save` sans jeton (`location=HACK`, `evil_key=pwned`) | Message d'erreur, **rien** d'écrit en base | ✅ |
| 5 | `POST /admin/save` avec jeton + `evil_key` | « Modifications enregistrées », `evil_key` **absente** de la base | ✅ |
| 6 | Upload d'un script PHP renommé `evil.php.jpg` | Refusé : « type non autorisé », avertissement affiché | ✅ |
| 7 | Upload d'un vrai PNG | Accepté, renommé `avatar_<timestamp>_<aléatoire>.png` | ✅ |
| 8 | `.env` sans `DB_USER` ni `DB_PASS` | « Erreur interne du serveur. » + log « DB_USER, DB_PASS absent(s) » | ✅ |

Pour un contrôle rapide depuis le terminal (sans session ni jeton, la requête doit être rejetée sans rien écrire) :

```bash
curl -s -o /dev/null -w "%{http_code} → %{redirect_url}\n" -X POST -d "location=HACK" http://localhost:8000/admin/save
# attendu : 302 → http://localhost:8000/login
```

Connecté au back-office, la même requête sans jeton redirige vers `/admin`, avec le message « Jeton de sécurité invalide ».

---

### 15.4 Recette du double univers (Chrome, Docker, 05/10/2026)

| # | Scénario | Résultat |
| --- | --- | --- |
| 1 | Clic sur l'interrupteur | `data-mode="photo"`, `aria-checked="true"`, `localStorage` = `photo` ✅ |
| 2 | Mode Photo appliqué | Fond `#050505`, titres en Fraunces, galerie en 2 colonnes magazine, grain 0,22, particules masquées, section Post-traitement visible ✅ |
| 3 | Rechargement | Le mode Photo est restauré **avant** le rendu, l'interrupteur est synchronisé ✅ |
| 4 | View Transition | Révélation circulaire depuis l'interrupteur, photos déplacées vers la mise en page magazine ✅ |
| 5 | Curseur sur une photo / sur le comparateur | Cercle 96 px « Voir » / « Glisser » ; le libellé se met à jour au défilement molette sans bouger la souris ✅ |
| 6 | Visionneuse | Ouverture sur « 02 / 05 », → 03, ← ← 01 (boucle), fermeture par clic dans le vide, focus rendu à la photo ✅ |
| 7 | Comparateur | Amorce animée, glissement à 20 %, `aria-valuenow` synchronisé, clavier Fin puis ← = 98 ✅ |
| 8 | Commande `theme dev` du terminal | Retour en mode Dev via `mode:request`, mémorisé ✅ |
| 9 | Terminal en mode Photo | Reste Lime `rgb(182, 255, 46)` ✅ |
| 10 | Mobile 375 px (mode Photo) | Pas de défilement horizontal, interrupteur « Dev / Photo » (150 px), galerie 1 colonne ✅ |
| 11 | Mouvement réduit | Bascule instantanée, grain non animé ✅ |
| 12 | `/lab`, `/mentions-legales` | Interrupteur présent, mode partagé, aucune erreur console ✅ |

## 16. 🩺 Diagnostic (développement)

| Symptôme | Diagnostic | Solution |
| --- | --- | --- |
| `localhost:8000` ne répond pas | Conteneur `web` arrêté | `docker compose ps`, puis `docker compose up -d` |
| « Address already in use » au lancement de `php -S` | Docker occupe déjà le port 8000 | Ne pas lancer `php -S` : le site est servi par Docker |
| « Erreur interne du serveur. » (texte brut) | Connexion PDO échouée (`.env` incorrect ou MySQL arrêté) | Vérifier `.env` (`DB_HOST=db` sous Docker) et le conteneur `portfolio_mysql` |
| Nouvelles classes Tailwind sans effet | Watcher arrêté ou CSS non recompilé | Relancer `npm run dev` ; vérifier la date de `public/css/style.css` |
| Titres / cartes invisibles | `animations.js` en erreur ou absent | Console DevTools ; `node --check public/js/animations.js` |
| Le terminal affiche « Impossible de joindre /api/terminal » | API en erreur (BDD ou PHP) | Ouvrir `/api/terminal` dans le navigateur, consulter les logs PHP (§ 13.4) |
| Pas de curseur custom | Écran tactile ou pointeur grossier détecté (comportement voulu) | Tester avec une souris / un trackpad |
| Animations absentes | Option « réduire les animations » activée sur le système (comportement voulu) | La désactiver pour tester |
| Bascule de mode sans cercle, avec un fondu coloré | Navigateur sans View Transitions : c'est le repli prévu | Aucune action (tester dans un Chrome / Safari récent pour la version complète) |
| Le site revient toujours en mode Dev | `localStorage` bloqué (navigation privée stricte, cookies refusés) | Comportement de repli : le mode reste actif pour la page en cours |
| Bref flash du mode Dev au chargement | Script inline du `<head>` absent ou modifié | Vérifier qu'il précède la feuille de style dans `header.php` et lit bien `portfolio-mode` |
| Titres pas en serif en mode Photo | Fraunces non chargée (requête Google Fonts bloquée) ou `style.css` ancien | Onglet Réseau ; recompiler le CSS |
| La section Avant / Après n'apparaît pas | Mode Dev actif (section réservée au mode Photo) | Basculer en mode Photo |
| « RAW · simulé » sur le comparateur | Aucune image brute renseignée | Back-office → Galerie → Comparateur : ajouter l'image AVANT |
| Ancien rendu malgré les modifications | Cache navigateur | `Cmd + Shift + R` ; les `?v=` se changent à chaque déploiement |
| « Erreur interne du serveur. » juste après une mise à jour | `.env` incomplet : une des 4 clés manque (plus de valeur de repli) | Comparer le `.env` à `.env.example` ; le log PHP nomme la clé manquante |
| « Session expirée » à la connexion | Jeton CSRF absent ou périmé (page ouverte avant un redémarrage de session, cookies bloqués) | Recharger `/login` puis se reconnecter ; vérifier que les cookies sont autorisés |
| « Jeton de sécurité invalide ou expiré » au back-office | Session expirée entre l'ouverture du dashboard et l'enregistrement, ou formulaire sans `Csrf::field()` | Recharger `/admin` et recommencer ; vérifier la présence du champ caché `csrf_token` |
| « Envoi trop volumineux » | Requête au-delà de `post_max_size` (souvent 8 Mo en local) | Envoyer moins d'images à la fois, ou augmenter `post_max_size` / `upload_max_filesize` |
| Image « ignorée : type non autorisé » | Fichier HEIC, SVG, AVIF, PDF… ou extension maquillée | Convertir en JPEG, PNG, WebP ou GIF |
| Un nouveau champ du back-office ne s'enregistre pas | Clé absente de la liste blanche | La déclarer dans `Setting::SCALAR_KEYS` ou `Setting::LIST_SCHEMAS` (§ 12.3) |

---

## 17. 🚧 Points d'attention & pistes d'amélioration

| Sujet | Constat | Piste |
| --- | --- | --- |
| ✅ ~~Uploads du back-office~~ | **Corrigé** : type réel, décodage image, taille, nom aléatoire | [§ 12.4](#124-uploads-dimages-sécurisés) |
| ✅ ~~Formulaire d'administration sans CSRF ni filtrage~~ | **Corrigé** : jeton CSRF (admin + login), liste blanche, transaction | [§ 12.2](#122-protection-csrf), [§ 12.3](#123-liste-blanche-des-contenus-enregistrés) |
| ✅ ~~Mot de passe BDD dans `database.php`~~ | **Corrigé** : `.env` obligatoire, aucune valeur de repli | [§ 12.5](#125-identifiants-et-secrets) ; changer le mot de passe MySQL par précaution |
| Connexion | Pas de limitation des tentatives | Compteur en session / BDD avec temporisation progressive |
| Déconnexion | `/logout` accepte un simple GET (un lien externe peut te déconnecter ; impact faible) | Passer par un formulaire POST avec `Csrf::field()` |
| Articles du Lab | Leur contenu HTML est injecté tel quel dans la modale (`innerHTML`). C'est acceptable tant que seul l'admin les rédige | Assainir le HTML (liste blanche de balises) si plusieurs rédacteurs |
| Images | Pas de redimensionnement automatique à l'upload | Génération de variantes WebP côté serveur |
| Italiques des titres (mode Dev) | Oblique synthétisé (Space Grotesk sans italique). En mode Photo, Fraunces fournit un vrai italique | Retirer `italic` en mode Dev si gênant |
| Poids de Fraunces | Chargée sur toutes les pages (une famille de plus dans la requête Google Fonts), même si le visiteur reste en mode Dev | Charger Fraunces à la demande (première bascule en Photo) si les performances l'exigent |
| Comparateur avant / après | Rendu brut **simulé** tant qu'aucun vrai RAW n'est fourni | Ajouter une paire RAW / étalonné réelle depuis le back-office |
| Ordre de lecture de la galerie Photo | Le multi-colonnes remplit une colonne avant la suivante (N° 01, 02 à gauche…) | Acceptable pour une galerie ; sinon passer à une grille CSS à placement explicite |
| Thème `.light` | Tokens prêts, mais aucun script ne pose la classe | À brancher si un thème clair est souhaité |
| Pastille « status: 200 OK » | Élément fixe en bas à gauche, peut chevaucher le contenu | À repositionner si gênant |
| Graphe de compétences SVG | Implémenté puis **retiré volontairement** (rendu jugé inadapté) | — |

---

## 18. 📦 Déploiement

La mise en production sur Hostinger est entièrement décrite dans **[`DEPLOIEMENT_HOSTINGER.md`](DEPLOIEMENT_HOSTINGER.md)**. Elle couvre les prérequis (PHP ≥ 8.1), les vérifications serveur bloquantes, le build, le paquet ZIP, la sauvegarde, le transfert, les check-lists, le diagnostic et le retour arrière.

En résumé :

1. **Vérifier que le `.env` du serveur contient les 4 clés BDD** (étape bloquante depuis la suppression des valeurs de repli).
2. `npm run build`, puis construire le paquet `app/` + `public/` (commande `rsync` + `zip`, sans `.env`, sans `.htaccess` racine, sans les images d'`uploads/`).
3. Sauvegarder le site, puis extraire le paquet dans `public_html/`.
4. Purger le cache LiteSpeed et dérouler la check-list.

### 18.1 Commit et push

- `.gitignore` exclut le `.env`, `node_modules/`, les réglages locaux (`.claude/settings.local.json`) et les **images** de `public/assets/uploads/` (données de production). Le `.htaccess` de ce dossier, lui, reste versionné.
- `public/css/style.css` est versionné : lancer `npm run build` avant de commiter pour versionner le CSS de production (minifié).
- Aucun identifiant n'est présent dans le code : seul `.env.example` (valeurs factices) est commité.

---

## 19. 👤 Auteur & licence

**Yann Le Flohic (YannLf3)**

- **Rôle :** développeur web full-stack & photographe
- **Formation :** BUT MMI (Métiers du Multimédia et de l'Internet), parcours Développement web et dispositifs interactifs (IUT du Limousin)
- **GitHub :** [github.com/YannLf3](https://github.com/YannLf3)
- **LinkedIn :** [linkedin.com/in/yannlf3](https://www.linkedin.com/in/yannlf3/)
- **Site :** [yannleflohic.fr](https://yannleflohic.fr/)

**Licence :** projet sous licence personnelle. Tous droits réservés sur le code sur-mesure, les visuels et les photographies présentées.
