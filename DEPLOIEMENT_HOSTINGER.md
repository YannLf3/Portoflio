# 🚀 Déploiement Hostinger — yannleflohic.fr

Procédure de mise en production du portfolio, à suivre **dans l'ordre, case par case** :

- rebranding **Graphite × Lime Spark** ;
- animations ;
- **double univers Dev / Photo** ;
- glitch du logo, terminal CLI, API JSON ;
- sécurité du back-office.

**Mise à jour :** 05/10/2026 · **Stack :** PHP 8 MVC · Tailwind CSS 4 · Apache/LiteSpeed (Hostinger)

> 📘 Le fonctionnement technique est documenté dans **[`README.md`](README.md)**. Ce fichier ne traite **que** de la mise en ligne.

---

## 📋 Sommaire

1. Ce qui part en production
2. Prérequis serveur
3. Principe : un paquet complet plutôt que des fichiers à l'unité
4. Inventaire des changements (référence)
5. Étape 1 — Vérifications sur le serveur (bloquant)
6. Étape 2 — Préparation en local
7. Étape 3 — Sauvegarde du site en ligne
8. Étape 4 — Transfert
9. Étape 5 — Finalisation
10. Check-list APRÈS la mise en ligne
11. Encarts de diagnostic
12. Retour arrière (rollback)
13. Récapitulatif express

---

## 1. Ce qui part en production

| Chantier | Contenu | Fichiers clés |
| --- | --- | --- |
| Rebranding | Polices Space Grotesk / DM Sans, tokens Graphite `#23262F` / Lime Spark `#B6FF2E`, boutons `bg-btn` | `style.css`, vues, `theme-toggle.js` |
| SEO | Titres, descriptions, URL canoniques par page, `noindex` des Mentions légales, Open Graph, favicons, `robots.txt`, `sitemap.xml` | `HomeController.php`, `header.php`, assets |
| Animations | Particules WebGL, curseur magnétique, boutons Glow, révélation des titres, cartes en cascade + tilt 3D | `animations.js`, `main.js`, `style.css`, vues |
| Double univers Dev / Photo | Interrupteur mémorisé, View Transition circulaire, mode Photo « noir cinéma » + Fraunces, galerie magazine, grain, zoom d'objectif, curseur visionneuse, lightbox, comparateur avant / après | `theme-toggle.js`, `photo-mode.js`, `gallery.php`, `compare.php`, `header.php`, `footer.php`, `style.css` |
| Glitch | Logo du header « YANN LE FLOHIC » avec aberration chromatique au survol | `header.php`, `style.css` |
| Terminal CLI + API | ⌘K / Ctrl+K, commandes interactives, `sudo hire-yann` ; `GET /api/terminal` en liste blanche | `terminal.js`, `footer.php`, `app/core/Response.php`, `ApiController.php`, `index.php` |
| Sécurité | Jeton CSRF (login + admin), liste blanche des contenus, uploads validés, **identifiants BDD uniquement dans le `.env`**, HSTS sous `mod_headers`, dossier `uploads/` durci | `app/core/*`, contrôleurs, `settings.php`, `database.php`, vues admin, `.htaccess` de `public/` et `uploads/` |

**Base de données : aucune migration SQL à exécuter.** Les nouveaux contenus (comparateur avant / après) sont des clés de la table `settings`, créées automatiquement au premier enregistrement depuis le back-office.

---

## 2. Prérequis serveur

| Élément | Exigence | Où vérifier |
| --- | --- | --- |
| Version PHP | **≥ 8.1** (8.3 recommandé, identique au Docker local). En dessous : **erreur 500 sur tout le site** (type `never`) | hPanel → Avancé → **Configuration PHP** |
| Extensions PHP | `pdo_mysql`, `fileinfo`, `mbstring` (actives par défaut chez Hostinger) | Configuration PHP → Extensions |
| HTTPS | Certificat SSL actif (le cookie de session est `Secure`) | hPanel → Sécurité → SSL |
| Structure | `public_html/` contient `app/`, `public/`, `.htaccess` et `.env` (voir § 5.2) | Gestionnaire de fichiers |
| **`.env` du serveur** | **Les 4 clés `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` présentes.** Le code n'a plus aucune valeur de repli : une clé manquante = **erreur 500 sur tout le site** | `public_html/.env` (voir § 5.1) |

> 📁 Tous les chemins de ce document sont relatifs à `public_html/`.

---

## 3. Principe : un paquet complet plutôt que des fichiers à l'unité

On ne connaît pas avec certitude l'état actuel du serveur : il peut être plus ancien que le dernier commit. Envoyer une liste de fichiers « modifiés » risque d'en oublier un, et un seul oubli suffit à casser une page.

**La méthode retenue renvoie donc les dossiers `app/` et `public/` complets**, sous forme d'un paquet ZIP généré par une commande (§ 6.3). L'extraction ne touche pas à ce qui doit rester propre au serveur :

| Élément du serveur | Effet du paquet |
| --- | --- |
| `public_html/.env` | **Jamais touché** (absent du paquet) |
| `public_html/.htaccess` (racine) | **Jamais touché** : il peut contenir des règles ajoutées par Hostinger (« Forcer HTTPS », cache LiteSpeed) |
| `public/assets/uploads/` (images envoyées via l'admin) | **Jamais touchées** : seul le `.htaccess` de protection du dossier est mis à jour |
| Tout le reste de `app/` et `public/` | Remplacé par la version locale. Les fichiers absents du paquet ne sont pas supprimés |

---

## 4. Inventaire des changements (référence)

Cette liste sert à **comprendre** ce qui change et à **diagnostiquer**. Le paquet du § 6.3 les contient tous : il n'y a pas à les envoyer un par un.

### 4.1 🆕 Fichiers nouveaux

| # | Fichier | Rôle |
| --- | --- | --- |
| 1 | `app/core/Response.php` | Sortie JSON centralisée de l'API |
| 2 | `app/core/Csrf.php` | Jeton anti-CSRF |
| 3 | `app/core/ImageUploader.php` | Validation des images envoyées |
| 4 | `app/controllers/ApiController.php` | API JSON du terminal |
| 5 | `app/views/front/partials/compare.php` | Section « Post-traitement » (mode Photo) |
| 6 | `public/js/animations.js` | WebGL, curseur (Dev + Photo), révélations, tilt |
| 7 | `public/js/terminal.js` | Terminal CLI |
| 8 | `public/js/photo-mode.js` | Visionneuse de la galerie + comparateur avant / après |
| 9 | `public/assets/uploads/.htaccess` | Interdiction d'exécuter des scripts dans `uploads/` (durci) |
| 10 | `public/assets/icons/` (3 fichiers), `public/assets/images/og-image.jpg`, `public/assets/before-video.webp` | Favicons, image Open Graph, poster de la vidéo du hero |
| 11 | `public/robots.txt`, `public/sitemap.xml` | SEO |

### 4.2 ✏️ Fichiers modifiés

| # | Fichier | Changements |
| --- | --- | --- |
| 12 | `public/css/style.css` | **CSS compilé** (régénéré au § 6.2) : tout le design system et les composants |
| 13 | `public/js/main.js` | Loader, signal `app:ready`, ancien curseur retiré |
| 14 | `public/js/theme-toggle.js` | **Réécrit** : interrupteur `role="switch"`, mémorisation, View Transition, événement `mode:request` |
| 15 | `public/js/admin.js` | Format `panorama` |
| 16 | `public/index.php` | Route `/api/terminal`, cookie de session dev / prod |
| 17 | `public/.htaccess` | HSTS dans `<IfModule mod_headers.c>` (voir § 5.3) |
| 18 | `app/config/database.php` | ⚠️ **Plus aucun identifiant dans le code** : lecture du `.env` uniquement |
| 19 | `app/models/settings.php` | Liste blanche des clés, enregistrement en transaction |
| 20 | `app/models/Project.php` | Requête préparée, typage |
| 21 | `app/controllers/HomeController.php` | **SEO dynamique** (titres, descriptions, canoniques, `noindex` des Mentions légales) |
| 22 | `app/controllers/AdminController.php` | CSRF, `ImageUploader`, messages flash, images du comparateur |
| 23 | `app/controllers/AuthController.php` | CSRF au login + `Csrf::rotate()` |
| 24 | `app/views/admin/dashboard.php` | Champ `csrf_token`, messages flash, format `panorama`, champs du comparateur |
| 25 | `app/views/admin/login.php` | Champ `csrf_token`, polices |
| 26 | `app/views/layouts/header.php` | Script inline (mode mémorisé avant rendu), interrupteur `#mode-switch`, logo glitch, canvas WebGL |
| 27 | `app/views/layouts/footer.php` | Curseur, terminal, scripts versionnés (`?v=20261005`) |
| 28 | `app/views/front/home.php` | Titre révélé, boutons Glow, inclusion de `compare.php` |
| 29 | `app/views/front/lab.php` | Titres révélés, polices |
| 30 | `app/views/front/partials/gallery.php` | **Réécrit** : galerie bento → magazine, visionneuse |
| 31 | `app/views/front/partials/about.php`, `projects.php`, `contact.php`, `skills.php` | Titres révélés, filtres Glow, cartes tilt, polices |

### 4.3 🚫 Ce qui ne part JAMAIS sur le serveur

| Fichier / dossier | Raison |
| --- | --- |
| `.env` (local) | Identifiants **Docker** : l'envoyer **casserait la connexion BDD** |
| `.htaccess` racine | Non modifié en local ; celui du serveur peut contenir des règles Hostinger |
| Images de `public/assets/uploads/` (local) | Fichiers de test locaux ; ceux du serveur sont les vrais |
| `src/`, `node_modules/`, `package*.json`, `tailwind.config.js` | Outillage de build |
| `Dockerfile`, `docker-compose.yml`, `database/` | Environnement local |
| `.git/`, `.gitignore`, `.claude/`, `CLAUDE.md`, `.env.example`, `*.md` | Développement / documentation |

### 4.4 🗑️ À supprimer du serveur s'il existe

| Fichier | Raison |
| --- | --- |
| `public/test.php` | Ancien fichier de débogage (`phpinfo()`) : il exposerait toute la configuration du serveur |

---

## 5. Étape 1 — Vérifications sur le serveur (bloquant)

> ⛔ Ne rien transférer tant que cette étape n'est pas entièrement cochée.

### 5.1 Le `.env` du serveur

- [ ] hPanel → **Gestionnaire de fichiers** → `public_html/` : afficher les fichiers cachés (⚙️ → *Afficher les fichiers cachés*), puis ouvrir **`.env`** (clic droit → *Modifier*).
- [ ] Vérifier la présence des **4 clés**, sur ce modèle (voir aussi `.env.example`) :

```dotenv
DB_HOST=localhost
DB_NAME=u740108753_…
DB_USER=u740108753_…
DB_PASS=<mot de passe de la base>
```

- [ ] **Si une clé manque (le plus probable : `DB_PASS`), l'ajouter maintenant et enregistrer.** Jusqu'ici, un mot de passe écrit dans `database.php` masquait ce manque ; il a été supprimé.
  - Les valeurs exactes se trouvent dans hPanel → **Bases de données** → *Gestion*.
  - Mot de passe oublié : bouton *Changer le mot de passe*, puis reporter le nouveau dans `DB_PASS`.
- [ ] 🔑 **Recommandé** : changer le mot de passe MySQL de toute façon, car il a figuré en clair dans le code de travail. Reporter immédiatement le nouveau mot de passe dans `DB_PASS`.

### 5.2 La structure de `public_html/`

- [ ] `public_html/` contient bien **`app/`**, **`public/`**, **`.htaccess`** et **`.env`** (la méthode de ce document suppose cette structure).

> 🩺 **Symptôme → Diagnostic → Solution**
>
> - **Symptôme :** `public_html/` contient directement `index.php`, `css/`, `js/`… mais pas de dossier `app/`.
> - **Diagnostic :** le site a été déployé avec une autre structure (contenu de `public/` à la racine).
> - **Solution :** **s'arrêter là**. Le paquet ne correspondrait pas, il faut d'abord adapter la procédure à cette structure.

### 5.3 Les `.htaccess` du serveur

- [ ] Ouvrir **`public_html/public/.htaccess`** en ligne. S'il contient des blocs **absents de la version locale** (par exemple `# BEGIN LSCACHE`, des règles ajoutées par Hostinger), les copier de côté : il faudra les recoller après l'extraction (§ 9.2).
- [ ] Ne **pas** modifier `public_html/.htaccess` (racine) : le paquet n'y touche pas.

### 5.4 PHP

- [ ] hPanel → **Configuration PHP** : version **≥ 8.1** ; extensions `pdo_mysql`, `fileinfo`, `mbstring` cochées.

---

## 6. Étape 2 — Préparation en local

### 6.1 Code à jour

- [ ] Le travail est commité et poussé sur GitHub (sauvegarde du code). Le paquet est construit depuis le dossier local, quel que soit l'état de git.

### 6.2 Build du CSS de production

- [ ] Arrêter le watcher `npm run dev` (`Ctrl + C` dans son terminal), sinon il réécrit un CSS non minifié à la prochaine modification.
- [ ] Compiler :

```bash
npm run build
```

- [ ] Vérifier le build (chaque ligne doit afficher **au moins `1`** ; un `0` signale un build obsolète) :

```bash
for c in "Space Grotesk" Fraunces btn-glow reveal-title tilt-card fx-particles glitch-channel-a cli-launcher cli__line mode-switch__thumb gallery__caption lightbox__btn compare__handle film-grain; do printf "%s: " "$c"; grep -c "$c" public/css/style.css; done
```

### 6.3 Vérifications de syntaxe et test local (Docker démarré)

```bash
# Syntaxe JavaScript
for f in public/js/*.js; do node --check "$f" || echo "ERREUR : $f"; done
# Syntaxe PHP (tous les dossiers déployés)
for f in $(find app public -name "*.php"); do php -l "$f" | grep -v "^No syntax errors"; done
# Pages et API (toutes doivent répondre 200)
for p in / /lab /login /mentions-legales /api/terminal; do curl -s -o /dev/null -w "$p %{http_code}\n" http://localhost:8000$p; done
```

- [ ] Aucune ligne « ERREUR » ni « Parse error », toutes les pages en `200`.
- [ ] Contrôle visuel rapide : logo qui glitche, ⌘K / Ctrl+K, bascule Dev ↔ Photo, galerie, comparateur.

### 6.4 Construction du paquet de déploiement

Depuis la racine du projet :

```bash
# 1. Dossier de préparation propre, À CÔTÉ du projet (jamais dans le dépôt git)
rm -rf ../deploy-yannleflohic ../deploy-yannleflohic.zip
mkdir ../deploy-yannleflohic

# 2. Copie de app/ et public/ :
#    - sans .DS_Store ;
#    - sans les images de uploads/ (seul le .htaccess de protection est inclus)
rsync -a --exclude='.DS_Store' \
      --include='public/assets/uploads/.htaccess' \
      --exclude='public/assets/uploads/*' \
      app public ../deploy-yannleflohic/

# 3. Archive ZIP
(cd ../deploy-yannleflohic && zip -rq ../deploy-yannleflohic.zip . -x '*.DS_Store')

# 4. Contrôle : uploads/ ne doit contenir QUE .htaccess, et ni .env ni src/ ne doivent apparaître
find ../deploy-yannleflohic/public/assets/uploads -type f
ls -A ../deploy-yannleflohic
```

- [ ] `find` n'affiche **qu'une ligne** (`…/uploads/.htaccess`).
- [ ] `ls -A` n'affiche **que** `app` et `public`.
- [ ] Le fichier `../deploy-yannleflohic.zip` existe (environ 18 Mo).

---

## 7. Étape 3 — Sauvegarde du site en ligne

- [ ] hPanel → **Fichiers** → **Sauvegardes** : générer une sauvegarde des fichiers **et** de la base de données (ou vérifier qu'une sauvegarde automatique récente existe).
- [ ] En complément, depuis le Gestionnaire de fichiers : sélectionner `app` et `public` dans `public_html/`, puis *Compresser* et *Télécharger* l'archive obtenue.

---

## 8. Étape 4 — Transfert

### 8.1 Méthode A (recommandée) — Gestionnaire de fichiers + ZIP

1. hPanel → **Sites web** → *yannleflohic.fr* → **Gestionnaire de fichiers**, puis ouvrir `public_html/`.
2. **Upload** de `deploy-yannleflohic.zip` dans `public_html/`.
3. Clic droit sur l'archive → **Extraire** → destination : **`public_html`** (le dossier courant, *pas* un sous-dossier).
4. Si une confirmation de remplacement apparaît : **Remplacer / Écraser tout**.
5. Vérifier que `public_html/app/core/` existe et contient `Response.php`, `Csrf.php`, `ImageUploader.php` : c'est la preuve que l'extraction a écrit au bon endroit.

> 🩺 **Symptôme → Diagnostic → Solution**
>
> - **Symptôme :** un dossier `public_html/deploy-yannleflohic/` est apparu, ou `app/core/` est absent.
> - **Diagnostic :** extraction dans un sous-dossier.
> - **Solution :** supprimer ce sous-dossier, puis recommencer l'étape 3 en choisissant `public_html` comme destination. À défaut, utiliser la méthode B.

### 8.2 Méthode B — FTP (FileZilla)

1. hPanel → **Fichiers** → **Comptes FTP** : récupérer l'hôte, l'utilisateur, le mot de passe et le port (21).
2. FileZilla : panneau local = dossier **`deploy-yannleflohic/`** (créé au § 6.4) ; panneau distant = `public_html/`.
3. Sélectionner **`app`** et **`public`** dans le panneau local, puis les glisser vers `public_html/`.
4. Fenêtre « fichier cible existant » : choisir **Écraser**, cocher **Toujours utiliser cette action** et **Appliquer uniquement à la file d'attente actuelle**, puis valider.
5. Attendre « File d'attente vide » et vérifier l'onglet *Transferts échoués* (il doit être vide).

---

## 9. Étape 5 — Finalisation

### 9.1 Nettoyage

- [ ] Supprimer **`public_html/deploy-yannleflohic.zip`** (méthode A).
- [ ] Supprimer **`public_html/public/test.php`** s'il existe.

### 9.2 `.htaccess` de `public/`

- [ ] Si des blocs ont été mis de côté au § 5.3, les recoller dans `public_html/public/.htaccess`, **après** le contenu actuel.

### 9.3 Cache

- [ ] hPanel → **Avancé** → **Cache LiteSpeed** (ou *Performance* → *Cache*) → **Purger tout**.
- [ ] Tester ensuite en **navigation privée**.

---

## 10. ✅ Check-list APRÈS la mise en ligne

**Fonctionnement général**

- [ ] https://yannleflohic.fr/ s'affiche (pas d'erreur 500), loader puis titre révélé avec son trait Lime
- [ ] https://yannleflohic.fr/api/terminal → JSON
- [ ] https://yannleflohic.fr/js/photo-mode.js?v=20261005 et `/js/animations.js?v=20261005` → 200 (code JavaScript affiché)
- [ ] Console DevTools (F12) sans erreur ; onglet Réseau : polices chargées depuis `fonts.gstatic.com`

**Mode Dev**

- [ ] Survol du logo « YANN LE FLOHIC » → glitch de 0,5 s, puis texte stable
- [ ] Curseur Lime, particules réactives, cartes en cascade + tilt, boutons Glow
- [ ] ⌘K / Ctrl+K ouvre le terminal ; `help`, `skills`, `contact`, `sudo hire-yann` fonctionnent

**Mode Photo**

- [ ] Interrupteur → révélation circulaire ; fond noir cinéma, titres en serif (Fraunces), galerie magazine avec grain, section « Post-traitement » visible
- [ ] Recharger la page : le mode Photo est conservé
- [ ] Clic sur une photo : visionneuse plein écran, ← / → et Échap fonctionnent
- [ ] Comparateur avant / après glissable (souris, doigt, clavier)
- [ ] Le terminal reste Graphite / Lime

**Back-office et sécurité**

- [ ] https://yannleflohic.fr/login → connexion admin OK
- [ ] Modifier un texte puis **Enregistrer** → bandeau vert « Modifications enregistrées »
- [ ] Envoyer une image JPEG / PNG → acceptée (nom du type `proj_<timestamp>_<aléatoire>.jpg`)
- [ ] Les images déjà en ligne (projets, galerie) s'affichent toujours
- [ ] https://www.yannleflohic.fr/ → redirection vers la version sans `www`, en HTTPS
- [ ] http://yannleflohic.fr/ (sans « s ») → redirection vers **https**
- [ ] En-tête HSTS présent :

```bash
curl -sI https://yannleflohic.fr/ | grep -i strict-transport
```

- [ ] https://yannleflohic.fr/test.php → **404**

**SEO**

- [ ] Code source (Ctrl+U) de `/mentions-legales` : `<meta name="robots" content="noindex, follow">`
- [ ] Code source de `/` : `<link rel="canonical" href="https://yannleflohic.fr/">`
- [ ] https://yannleflohic.fr/robots.txt et `/sitemap.xml` s'affichent

**Mobile**

- [ ] Pas de défilement horizontal, interrupteur « Dev / Photo » et bouton `>_ terminal` visibles

---

## 11. 🩺 Encarts de diagnostic

| Symptôme | Diagnostic | Solution |
| --- | --- | --- |
| « Erreur interne du serveur. » (texte brut) sur tout le site | Clé manquante dans le `.env` du serveur (souvent `DB_PASS`) | Compléter `public_html/.env` (§ 5.1). Le log d'erreurs PHP (hPanel → Avancé → Journaux) indique « Configuration BDD incomplète : … » |
| Même message, `.env` complet | Identifiants incorrects ou base indisponible | Vérifier les valeurs dans hPanel → Bases de données ; log « Erreur PDO : … » |
| Page d'erreur 500 (Hostinger / LiteSpeed) sur tout le site | PHP < 8.1, ou extraction incomplète (`app/core/` manquant) | PHP ≥ 8.1 (§ 5.4) ; vérifier `app/core/` (§ 8.1, étape 5) |
| Le site n'est plus forcé en HTTPS | Règle « Forcer HTTPS » perdue (`.htaccess` racine écrasé par erreur) | hPanel → Sécurité → SSL → réactiver **Forcer HTTPS**, ou restaurer le `.htaccess` racine depuis la sauvegarde |
| Erreur 500 sur les images de projets / galerie uniquement | Une directive du `.htaccess` d'`uploads/` est refusée par le serveur | Vérifier que la version en ligne est bien la nouvelle (`php_flag` dans des `<IfModule>`) ; à défaut, commenter (`#`) la ligne `Options -ExecCGI -Indexes` (section 2). **Garder** la section 1 (`FilesMatch`), qui est la vraie protection |
| Login : « Session expirée » à chaque essai | Cookies bloqués, ou ancien `login.php` | Autoriser les cookies ; vérifier que l'extraction a bien remplacé `app/views/admin/` |
| Back-office : « Jeton de sécurité invalide » systématique | Session expirée, ou version mixte des fichiers admin | Recharger `/admin` ; refaire l'extraction complète |
| Ancien design ou ancien comportement visible | Cache LiteSpeed / navigateur | Purger le cache (§ 9.3) + navigation privée |
| Titres et cartes invisibles | `animations.js` absent (404) : la classe `.fx` masque mais rien ne révèle | Vérifier `/js/animations.js?v=20261005` ; refaire l'extraction |
| L'interrupteur Dev / Photo ne fait rien | `theme-toggle.js` ancien en cache | Purger le cache ; vérifier `/js/theme-toggle.js?v=20261005` |
| Mode Photo sans serif ni galerie magazine | `style.css` ancien (build non refait, ou cache) | `npm run build`, reconstruire le paquet, renvoyer ; purger le cache |
| Clic sur une photo sans visionneuse | `photo-mode.js` absent | Vérifier `/js/photo-mode.js?v=20261005` |
| `/api/terminal` affiche la page 404 du site | Ancien `public/index.php` | Refaire l'extraction |
| Terminal : « Impossible de joindre /api/terminal » | API en erreur (503 BDD) | Ouvrir `/api/terminal` ; consulter les journaux PHP |
| Image refusée : « type non autorisé » | Format HEIC, SVG, AVIF… (comportement voulu) | Convertir en JPEG, PNG, WebP ou GIF |
| Connexion admin qui « ne tient pas » | Cookie de session rejeté (domaine / HTTPS) | Le site doit être servi en **HTTPS** sur `yannleflohic.fr` (sans `www`) |

---

## 12. Retour arrière (rollback)

1. hPanel → **Sauvegardes** : restaurer la sauvegarde **des fichiers** faite au § 7. Sinon, renvoyer l'archive téléchargée et l'extraire dans `public_html/`.
2. **Ne pas** restaurer la base de données, sauf problème de données : le déploiement n'y touche pas.
3. Garder le `.env` complété au § 5.1 : il reste compatible avec l'ancien code.
4. Purger le cache LiteSpeed.

> ⚠️ Restaurer l'ancien `app/config/database.php` remettrait un mot de passe en clair dans le code en ligne. C'est une raison de plus pour changer ce mot de passe MySQL (§ 5.1).

---

## 13. ✅ Récapitulatif express

```
ÉTAPE 1 — SERVEUR (bloquant)
 1. public_html/.env : DB_HOST, DB_NAME, DB_USER, DB_PASS présents → sinon les ajouter MAINTENANT
 2. public_html contient app/, public/, .htaccess, .env
 3. Noter les blocs propres au serveur dans public/.htaccess (s'il y en a)
 4. PHP ≥ 8.1 + pdo_mysql, fileinfo, mbstring

ÉTAPE 2 — LOCAL
 5. Ctrl + C sur npm run dev → npm run build → greps du § 6.2 (aucun 0)
 6. node --check / php -l / curl du § 6.3 → aucun échec
 7. Construire le paquet (§ 6.4) → uploads/ ne contient que .htaccess

ÉTAPE 3 — SAUVEGARDE
 8. hPanel → Sauvegardes (fichiers + BDD)

ÉTAPE 4 — TRANSFERT
 9. Upload deploy-yannleflohic.zip dans public_html → Extraire dans public_html → Écraser
10. Vérifier public_html/app/core/ (3 fichiers)

ÉTAPE 5 — FINALISATION
11. Supprimer le .zip et public/test.php ; recoller les blocs .htaccess notés
12. Purger le cache LiteSpeed → navigation privée
13. Dérouler la check-list du § 10
```
