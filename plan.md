# Plan — Écarts prod Hostinger (`public_html/`) ↔ version locale (`V3-Restructuration`)

> Analyse du 2026-10-14. Rien n'a été modifié dans le code : ce document liste les écarts, à toi de trancher.

## 0. Constat général

| Élément | Résultat |
|---|---|
| Code PHP / JS / CSS / `.htaccess` / `robots.txt` / `sitemap.xml` en prod | **Identique au commit `a700b72` (HEAD)** |
| Vues en prod (`header`, `footer`, `home`, `lab`, 6 partials) | Différentes de HEAD **uniquement par la suppression des commentaires HTML** (aucun changement fonctionnel ni SEO) |
| Conséquence | La prod ne contient **aucune amélioration SEO absente du local**. Tous les écarts vont dans le sens local → prod |
| Fichiers présents en prod seulement | `.env` (identifiants Hostinger), `app/models/settings.php` (ancien nom), 2 uploads `compare_1791270937_*.webp` |

---

## 1. SEO — Écarts prod → local

### 1.1 Identique des deux côtés (rien à reporter)

- `<title>`, `<meta name="description">`, `<meta name="robots">` (noindex sur `/mentions-legales`), `<link rel="canonical">` : mêmes valeurs par page (`HomeController`).
- Open Graph + Twitter Cards, favicons, JSON-LD `Person` + `ProfessionalService` + `WebSite`.
- `robots.txt`, `sitemap.xml`, redirection 301 `www` → sans `www`, HSTS.
- Hiérarchie des titres (un seul `<h1>` par page).

### 1.2 Présent en local, absent en prod (gagné à la mise en ligne)

| # | Amélioration locale | Fichier(s) | Impact |
|---|---|---|---|
| L1 | `<main>` autour du contenu de l'accueil (la prod n'a **aucun** repère `<main>` sur `/`) | `front/home.php` | Sémantique / a11y |
| L2 | `width` / `height` sur l'avatar (1200×1200), les cartes projet (1600×900), la galerie (selon format) et le comparateur (1600×1200) | `home.php`, `projects.php`, `gallery.php`, `compare.php` | CLS (Core Web Vitals) |
| L3 | `aria-hidden="true"` sur les SVG décoratifs (flèches contact / projets) | `contact.php`, `projects.php` | a11y |
| L4 | `type="button"` sur tous les boutons des modales du Lab + `aria-label="Fermer la console"` | `lab.php` | W3C / a11y |
| L5 | Suppression du `src=""` vide de l'image de la lightbox (erreur W3C) | `gallery.php` | Validité HTML |
| L6 | JS inline sorti dans `lab.js` et `projects-filter.js`, chargés en `defer` | `lab.php`, `projects.php` | Performance |
| L7 | Cache-busting `?v=20261014` (prod : `?v=20261005`) | `header.php`, `footer.php` | Fraîcheur CSS/JS (LiteSpeed) |

### 1.3 Point de vigilance (contenu indexable)

| # | Écart | Risque |
|---|---|---|
| V1 | En prod, les vues contiennent des contenus de repli codés en dur (6 projets, parcours, contacts…). En local, ils sont dans `Setting::DEFAULTS`, **sauf les projets** qui viennent de la table `projects` | Si la migration `database/migrations/2026-10-14_projects_table.sql` n'est pas jouée en prod **avant** l'envoi du code, la section Projets s'affiche vide → perte de contenu indexé |

### 1.4 Manquant des deux côtés (propositions — à valider une par une)

| # | Proposition | Fichier concerné | Priorité |
|---|---|---|---|
| P1 | `og-image.jpg` fait 2828×1322 et 947 Ko → recadrer en 1200×630, < 300 Ko | `public/assets/images/` | Haute |
| P2 | Ajouter `og:locale` (`fr_FR`), `og:site_name`, `og:image:width`, `og:image:height`, `og:image:alt` | `layouts/header.php` | Moyenne |
| P3 | Twitter Cards : `property="twitter:*"` → `name="twitter:*"` (attribut attendu par la spec) | `layouts/header.php` | Basse |
| P4 | Second `apple-touch-icon` pointant vers un SVG (ignoré par iOS) → supprimer | `layouts/header.php` | Basse |
| P5 | JSON-LD `CreativeWork` (ou `ItemList`) généré depuis la table `projects` (règle AGENTS.md § 5) | `HomeController` + `home.php` | Haute |
| P6 | JSON-LD `BreadcrumbList` sur `/lab` et `/mentions-legales` | `lab.php`, `legal.php` | Basse |
| P7 | `noindex, nofollow` sur `/login` et `/admin` (aucune balise robots aujourd'hui) | `admin/login.php`, `admin/dashboard.php` | Moyenne |
| P8 | `robots.txt` : `Disallow: /admin/` ne couvre pas `/admin` ni `/login` (préférer P7, un `Disallow` empêche Google de lire le `noindex`) | `public/robots.txt` | Basse |
| P9 | `<lastmod>` dans `sitemap.xml` | `public/sitemap.xml` | Basse |
| P10 | Images sources très lourdes (galerie 3000 à 5700 px, projets ~2900 px) : redimensionner (~1600 px) et/ou `srcset` | `public/assets/images/` | Haute (LCP) |
| P11 | Ratios `width`/`height` codés en dur ≠ ratios réels (ex. `sae202.webp` = 1768×1578, déclaré 16:9). Sans effet sur le CLS si le conteneur impose son ratio, mais à aligner | `HomeController`, `projects.php` | Basse |
| P12 | Pas de vue `app/views/404.php` : la 404 renvoie un texte brut sans `<head>` | `app/views/` | Moyenne |

---

## 2. Connexion — Écarts et identifiants

### 2.1 Comparaison du code d'authentification

| Élément | Prod | Local | Compatible ? |
|---|---|---|---|
| Table / colonnes lues | `SELECT * FROM users WHERE email = :email` | `SELECT id, email, password FROM users …` | ✅ mêmes colonnes |
| Vérification | `password_verify()` direct | `User::verifyPassword()` → `password_verify()` (refuse une empreinte vide) | ✅ même algorithme : le compte admin prod continue de fonctionner sans migration |
| Clé de session | `$_SESSION['admin_logged_in']` | `Controller::SESSION_ADMIN` = `'admin_logged_in'` | ✅ sessions ouvertes conservées au déploiement |
| CSRF (`Csrf.php`) | — | — | ✅ fichier identique |
| `session_regenerate_id(true)` + `Csrf::rotate()` | ✅ | ✅ | ✅ |
| Cookie de session (`index.php`) | domaine `yannleflohic.fr`, Secure, HttpOnly, Lax | identique | ✅ |
| E-mail réaffiché après échec | `$_POST['email']` dans la vue | variable `$email` préparée par le contrôleur | ✅ (échappé des deux côtés) |
| Chargement `.env` | boucle `foreach` | `loadEnvFile()` en `array_filter` / `array_walk` | ✅ même format de `.env` |

**Aucun écart bloquant côté connexion** : le code local se branche sur la table `users` de prod telle quelle.

### 2.2 Inventaire des identifiants

| Identifiant | Où | Stockage | État |
|---|---|---|---|
| BDD prod (`DB_HOST=localhost`, `DB_NAME=u740108753_portfolio_db`, `DB_USER=u740108753_yannlf3`) | `public_html/.env` (serveur) | **Clair** (normal pour un `.env`) | ✅ Jamais commité (vérifié `git log -S`) ; `public_html/.env` est bien ignoré par `.gitignore` |
| `DB_PASS` prod (`Higm…zy3`, valeur complète dans `public_html/.env`) | `public_html/.env` | **Clair** | ⚠️ README / DEPLOIEMENT recommandent de le changer (il a figuré en clair dans le code de travail) |
| Préfixe de compte Hostinger `u740108753` | commit `07b6c4f` + `DEPLOIEMENT_HOSTINGER.md` | Clair | ℹ️ Pas un secret en soi, mais visible dans l'historique |
| BDD locale Docker (`root` / `root`, base `portfolio_db`) | `.env`, `docker-compose.yml`, `README.md` | Clair | ✅ Acceptable en local uniquement |
| phpMyAdmin local (`root` / `root`) | `docker-compose.yml` | Clair | ✅ Local uniquement |
| Admin du back-office — **prod** | table `users` de la BDD Hostinger | Empreinte bcrypt (à confirmer dans phpMyAdmin Hostinger : doit commencer par `$2y$`, 60 caractères) | ❓ Non vérifiable depuis `public_html/` (aucun dump SQL) |
| Admin du back-office — **seed local** (`admin@portfolio.fr`) | `database/schema.sql` | **CLAIR : `'admin'`** | ❌ `password_verify()` échoue toujours sur une valeur non hachée → connexion impossible avec ce seed. Le commentaire (« admin123 », « déjà HASHÉ ») est faux |
| Admin du back-office — BDD Docker actuelle | table `users` locale | Inconnu | ❓ Non vérifié (requête bloquée) : à contrôler dans phpMyAdmin `localhost:8080` |

Requête de contrôle (prod et local) :

```sql
SELECT id, email, LEFT(password, 4) AS prefixe, LENGTH(password) AS longueur FROM users;
-- Attendu : prefixe = '$2y$', longueur = 60
```

Générer une empreinte :

```bash
php -r "echo password_hash('MOT_DE_PASSE', PASSWORD_DEFAULT), PHP_EOL;"
```

---

## 3. À changer dans le code local (liste à valider)

### 3.1 Connexion / sécurité

| # | Action | Fichier | Priorité |
|---|---|---|---|
| C1 | Remplacer le seed `'admin'` par une empreinte bcrypt et corriger le commentaire | `database/schema.sql` | Haute |
| C2 | Limiter les tentatives de connexion (anti-brute-force : compteur en session ou table `login_attempts`, délai après N échecs) — absent en prod comme en local | `AuthController.php` | Haute |
| C3 | Ne plus appliquer `trim()` au mot de passe (un mot de passe avec espaces en bord échoue) — présent en prod comme en local | `AuthController.php` | Basse |
| C4 | `password_needs_rehash()` après connexion réussie (mise à niveau automatique de l'empreinte) | `AuthController.php` + `User.php` | Basse |
| C5 | Changer `DB_PASS` dans hPanel puis dans le `.env` du serveur (recommandation déjà notée dans README) | serveur | Moyenne |
| C6 | `noindex` sur `/login` et `/admin` (= P7) + `?v=` sur la feuille de style de `login.php` | `admin/login.php`, `admin/dashboard.php` | Moyenne |

### 3.2 Déploiement / hygiène

| # | Action | Détail |
|---|---|---|
| D1 | Jouer `2026-10-14_projects_table.sql` en prod **avant** d'envoyer le code (cf. V1) | `MISE_EN_PRODUCTION_RESTRUCTURATION.md` |
| D2 | Supprimer `app/models/settings.php` du serveur au moment d'envoyer `Setting.php` (coexistence = « Cannot declare class Setting ») | serveur |
| D3 | Ne pas écraser le `.env` du serveur | serveur |
| D4 | Rapatrier si besoin les 2 uploads prod `compare_1791270937_*.webp` (référencés par `compare_before` / `compare_after` dans la BDD prod) | `public/assets/uploads/` (non versionné) |
| D5 | Supprimer `public_html/` du projet local avant tout commit (dossier non ignoré, hors `.env`) | racine |
| D6 | Supprimer `plan.md` ou l'ajouter au `.gitignore` s'il ne doit pas être versionné | racine |
