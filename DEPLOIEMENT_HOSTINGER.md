# 🚀 Déploiement Hostinger — yannleflohic.fr

Procédure de mise en production du portfolio, à suivre **dans l'ordre, case par case** :

- rebranding **Graphite × Lime Spark** ;
- animations ;
- **double univers Dev / Photo** ;
- glitch du logo, terminal CLI, API JSON ;
- sécurité du back-office.

**Méthode :** transfert **fichier par fichier**, dans un ordre qui garde le site fonctionnel à chaque instant, avec un **test après chaque phase**.

**Mise à jour :** 05/10/2026 · **Stack :** PHP 8 MVC · Tailwind CSS 4 · Apache/LiteSpeed (Hostinger)

> 📘 Le fonctionnement technique est documenté dans **[`README.md`](README.md)**. Ce fichier ne traite **que** de la mise en ligne.

---

## 📋 Sommaire

1. Ce qui part en production
2. Prérequis serveur
3. Pourquoi cet ordre de transfert
4. Ce qui ne part jamais sur le serveur
5. Préparation — serveur (bloquant)
6. Préparation — local
7. Sauvegarde du site en ligne
8. Comment transférer UN fichier
9. Transfert — 39 fichiers en 9 phases
10. Contrôle des fichiers inchangés
11. Finalisation
12. Check-list APRÈS la mise en ligne
13. Encarts de diagnostic
14. Retour arrière (rollback)
15. Récapitulatif express

---

## 1. Ce qui part en production

| Chantier | Contenu |
| --- | --- |
| Rebranding | Polices Space Grotesk / DM Sans, tokens Graphite `#23262F` / Lime Spark `#B6FF2E` |
| SEO | Titres, descriptions et URL canoniques par page, `noindex` des Mentions légales, Open Graph, favicons, `robots.txt`, `sitemap.xml` |
| Animations | Particules WebGL, curseur magnétique, boutons Glow, révélation des titres, cartes en cascade + tilt 3D |
| Double univers Dev / Photo | Interrupteur mémorisé, View Transition, mode Photo « noir cinéma » + Fraunces, galerie magazine, visionneuse, comparateur avant / après |
| Glitch, terminal, API | Logo « YANN LE FLOHIC » glitché, terminal ⌘K / Ctrl+K, `GET /api/terminal` |
| Sécurité | Jeton CSRF, liste blanche des contenus, uploads validés, **identifiants BDD uniquement dans le `.env`**, HSTS, dossier `uploads/` durci |

**39 fichiers à transférer** (16 nouveaux et 23 modifiés depuis le commit `a241d1e`), plus **16 fichiers inchangés** dont il suffit de contrôler la présence en ligne (§ 10).

**Base de données : aucune migration SQL.** Les nouveaux contenus (comparateur avant / après) sont des clés de la table `settings`, créées automatiquement au premier enregistrement depuis le back-office.

---

## 2. Prérequis serveur

| Élément | Exigence | Où vérifier |
| --- | --- | --- |
| Version PHP | **≥ 8.1** (8.3 recommandé). En dessous : **erreur 500 sur tout le site** | hPanel → Avancé → **Configuration PHP** |
| Extensions PHP | `pdo_mysql`, `fileinfo`, `mbstring` (actives par défaut chez Hostinger) | Configuration PHP → Extensions |
| HTTPS | Certificat SSL actif (cookie de session `Secure`) | hPanel → Sécurité → SSL |
| Structure | `public_html/` contient `app/`, `public/`, `.htaccess` et `.env` | Gestionnaire de fichiers |
| **`.env` du serveur** | **Les 4 clés `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.** Le code n'a plus aucune valeur de repli | `public_html/.env` (§ 5.1) |

> 📁 **Règle de chemin :** chaque fichier va **au même chemin** sous `public_html/` que dans le projet local.
> Exemple : le fichier local `app/core/Csrf.php` va dans `public_html/app/core/Csrf.php`.

---

## 3. Pourquoi cet ordre de transfert

Pendant le transfert, le site reste en ligne avec un mélange d'ancien et de nouveau code. L'ordre du § 9 évite toute panne, en respectant ces dépendances (vérifiées dans le code) :

| Dépendance | Conséquence si l'ordre n'est pas respecté |
| --- | --- |
| `public/index.php` charge **les 4 contrôleurs à chaque requête** ; `AuthController` / `AdminController` chargent `app/core/Csrf.php` et `ImageUploader.php` ; `ApiController` charge `app/core/Response.php` | Contrôleur envoyé avant `app/core/` = **erreur 500 sur tout le site** |
| `app/config/database.php` lit **uniquement** le `.env` | `.env` incomplet = **erreur 500 sur tout le site** |
| Le nouveau `header.php` masque les titres (classe `.fx`) ; seul `animations.js`, chargé par le nouveau `footer.php`, les révèle | Header envoyé avant le footer = **titres invisibles** |
| L'ancien `theme-toggle.js` cherche des boutons que le nouveau header n'a plus (et inversement) | Bascule Dev / Photo inopérante tant que la paire n'est pas complète |
| `home.php` inclut `partials/compare.php` | `home.php` envoyé seul = **erreur 500 sur l'accueil** |
| `login.php` / `dashboard.php` appellent `Csrf::field()`, chargé par les nouveaux contrôleurs | Vue envoyée avant son contrôleur = **erreur 500 sur `/login` ou `/admin`** |

Principes qui en découlent :

1. **Les fichiers « dormants » d'abord** : les nouveaux fichiers qu'aucun ancien fichier n'appelle. Les envoyer est sans effet visible.
2. Puis les fichiers **critiques un par un**, avec un test immédiat.
3. Les **paires liées** sont envoyées l'une juste après l'autre.
4. Le `.htaccess` passe **en dernier** : s'il pose problème, il est le seul suspect.

> ⏱️ Entre deux fichiers d'une même phase, des écarts d'affichage peuvent apparaître quelques minutes (styles en transition). C'est normal : seules les **erreurs** (page blanche, erreur 500) doivent t'arrêter.

---

## 4. Ce qui ne part jamais sur le serveur

| Fichier / dossier | Raison |
| --- | --- |
| `.env` (local) | Identifiants **Docker** : l'envoyer **casserait la connexion BDD** |
| `.htaccess` à la **racine** du projet | Non modifié ; celui du serveur peut contenir des règles Hostinger (« Forcer HTTPS », cache) |
| Images de `public/assets/uploads/` (local) | Fichiers de test locaux ; ceux du serveur sont les vrais (seul `uploads/.htaccess` est envoyé) |
| `src/`, `node_modules/`, `package*.json`, `tailwind.config.js` | Outillage de build |
| `Dockerfile`, `docker-compose.yml`, `database/` | Environnement local |
| `.git/`, `.gitignore`, `.claude/`, `CLAUDE.md`, `.env.example`, `*.md` | Développement / documentation |
| `.DS_Store` | Fichiers système macOS |

---

## 5. Préparation — serveur (bloquant)

> ⛔ Ne rien transférer tant que cette section n'est pas entièrement cochée.

### 5.1 Le `.env` du serveur

- [ ] hPanel → **Gestionnaire de fichiers** → `public_html/` → ⚙️ *Afficher les fichiers cachés*, puis clic droit sur **`.env`** → *Modifier*.
- [ ] Vérifier la présence des **4 clés** (modèle : `.env.example`) :

```dotenv
DB_HOST=localhost
DB_NAME=u740108753_…
DB_USER=u740108753_…
DB_PASS=<mot de passe de la base>
```

- [ ] **Si une clé manque (le plus probable : `DB_PASS`), l'ajouter maintenant et enregistrer.** Jusqu'ici, un mot de passe écrit dans `database.php` masquait ce manque ; il a été supprimé.
  - Les valeurs exactes se trouvent dans hPanel → **Bases de données** → *Gestion*.
  - Mot de passe inconnu : bouton *Changer le mot de passe*, puis reporter le nouveau dans `DB_PASS`.
- [ ] 🔑 **Recommandé :** changer le mot de passe MySQL de toute façon, car il a figuré en clair dans le code de travail. Reporter immédiatement le nouveau mot de passe dans `DB_PASS`.

### 5.2 La structure de `public_html/`

- [ ] `public_html/` contient bien **`app/`**, **`public/`**, **`.htaccess`** et **`.env`**.

> 🩺 **Symptôme → Diagnostic → Solution**
>
> - **Symptôme :** `public_html/` contient directement `index.php`, `css/`, `js/`… sans dossier `app/`.
> - **Diagnostic :** le site a été déployé avec une autre structure.
> - **Solution :** **s'arrêter là**. Les chemins de ce document ne correspondraient pas.

### 5.3 Le `.htaccess` de `public/`

- [ ] Ouvrir **`public_html/public/.htaccess`** en ligne et le comparer au fichier local `public/.htaccess`.
- [ ] S'il contient des blocs **absents de la version locale** (par exemple `# BEGIN LSCACHE`, des règles ajoutées par Hostinger), les copier dans un fichier texte de côté : ils seront recollés en phase 10.

### 5.4 PHP

- [ ] hPanel → **Configuration PHP** : version **≥ 8.1** ; extensions `pdo_mysql`, `fileinfo`, `mbstring` cochées.

---

## 6. Préparation — local

### 6.1 Build du CSS de production

- [ ] Arrêter le watcher `npm run dev` (`Ctrl + C`), sinon il réécrit un CSS non minifié à la prochaine modification.
- [ ] Compiler :

```bash
npm run build
```

- [ ] Vérifier le build (chaque ligne doit afficher **au moins `1`** ; un `0` signale un build obsolète) :

```bash
for c in "Space Grotesk" Fraunces btn-glow reveal-title tilt-card fx-particles glitch-channel-a cli-launcher cli__line mode-switch__thumb gallery__caption lightbox__btn compare__handle film-grain; do printf "%s: " "$c"; grep -c "$c" public/css/style.css; done
```

### 6.2 Vérifications de syntaxe et test local (Docker démarré)

```bash
# Syntaxe JavaScript
for f in public/js/*.js; do node --check "$f" || echo "ERREUR : $f"; done
# Syntaxe PHP (tous les dossiers déployés)
for f in $(find app public -name "*.php"); do php -l "$f" | grep -v "^No syntax errors"; done
# Pages et API (toutes doivent répondre 200)
for p in / /lab /login /mentions-legales /api/terminal; do curl -s -o /dev/null -w "$p %{http_code}\n" http://localhost:8000$p; done
```

- [ ] Aucune ligne « ERREUR » ni « Parse error », toutes les pages en `200`.

### 6.3 Ouvrir le dossier du projet

- [ ] Garder le dossier local du projet ouvert (Finder) : c'est la source des 39 fichiers du § 9.

---

## 7. Sauvegarde du site en ligne

- [ ] hPanel → **Fichiers** → **Sauvegardes** : générer une sauvegarde des fichiers **et** de la base de données (ou vérifier qu'une sauvegarde automatique récente existe).
- [ ] En complément, dans le Gestionnaire de fichiers : télécharger une copie de **chaque fichier existant** avant de le remplacer (clic droit → *Télécharger*), ou d'un coup les dossiers `public_html/app` et `public_html/public` (*Compresser* → *Télécharger*). C'est ce qui permet d'annuler **un seul fichier** en cas de problème (§ 14).

---

## 8. Comment transférer UN fichier

### Option A — Gestionnaire de fichiers (hPanel)

1. Ouvrir le dossier de destination dans `public_html/` (même chemin que le fichier local).
2. Si le dossier n'existe pas (cas de `app/core/`) : bouton **Nouveau dossier**.
3. Bouton **Upload** (icône ⬆) → *Fichier* → sélectionner le fichier local.
4. Si le fichier existe déjà : confirmer **Remplacer**.
5. Vérifier que la date de modification du fichier en ligne est celle d'aujourd'hui.

### Option B — FTP (FileZilla)

1. hPanel → **Fichiers** → **Comptes FTP** : hôte, utilisateur, mot de passe, port 21.
2. Panneau local : le dossier du projet ; panneau distant : `public_html/`, puis le **même sous-dossier** des deux côtés.
3. Glisser **le** fichier vers le panneau distant ; à la question « fichier existant » : **Écraser** (sans cocher « Toujours »).
4. Vérifier l'onglet *Transferts réussis*.

---

## 9. Transfert — 39 fichiers en 9 phases

> Cocher chaque fichier **dès qu'il est transféré**. Faire le **test** de fin de phase avant de passer à la suivante. Si un test échoue : § 13 (diagnostic), puis § 14 (annuler le dernier fichier envoyé).
>
> Ouvrir https://yannleflohic.fr/ dans un onglet de **navigation privée**, à recharger après chaque test (`Cmd + Shift + R`).

### Phase 1 — Nouveaux fichiers « dormants » (aucun effet visible)

Aucun fichier actuellement en ligne ne les appelle : les envoyer ne change rien au site.

- [ ] **1.** `app/core/Response.php` — *créer d'abord le dossier `app/core/`*
- [ ] **2.** `app/core/Csrf.php`
- [ ] **3.** `app/core/ImageUploader.php`
- [ ] **4.** `app/controllers/ApiController.php`
- [ ] **5.** `app/views/front/partials/compare.php`
- [ ] **6.** `public/js/animations.js`
- [ ] **7.** `public/js/terminal.js`
- [ ] **8.** `public/js/photo-mode.js`
- [ ] **9.** `public/assets/icons/favicon.svg` — *créer le dossier `icons/` s'il n'existe pas*
- [ ] **10.** `public/assets/icons/favicon-48.png`
- [ ] **11.** `public/assets/icons/favicon-180.png`
- [ ] **12.** `public/assets/images/og-image.jpg`
- [ ] **13.** `public/assets/before-video.webp`
- [ ] **14.** `public/robots.txt`
- [ ] **15.** `public/sitemap.xml`
- [ ] **16.** `public/assets/uploads/.htaccess` — *fichier caché ; remplace l'éventuelle version existante*

**✅ Test phase 1**

- [ ] https://yannleflohic.fr/ s'affiche **comme avant**.
- [ ] https://yannleflohic.fr/js/animations.js affiche du code JavaScript.
- [ ] Une **image de projet ou de galerie** s'affiche toujours (contrôle du `uploads/.htaccess`).
- [ ] https://yannleflohic.fr/robots.txt s'affiche.

### Phase 2 — Connexion à la base (point critique)

- [ ] **17.** `app/config/database.php`

**✅ Test phase 2 (immédiat)**

- [ ] https://yannleflohic.fr/ s'affiche.
- [ ] ⛔ Si la page affiche « Erreur interne du serveur. » : le `.env` est incomplet. **Ré-envoyer tout de suite l'ancien `database.php`** (sauvegarde § 7), puis corriger le `.env` (§ 5.1) avant de recommencer.

### Phase 3 — Modèles

- [ ] **18.** `app/models/settings.php`
- [ ] **19.** `app/models/Project.php`

**✅ Test phase 3**

- [ ] https://yannleflohic.fr/ s'affiche, avec ses textes habituels (ils viennent de la base).

### Phase 4 — Routeur

- [ ] **20.** `public/index.php` — *possible grâce aux fichiers 1 et 4, déjà en ligne*

**✅ Test phase 4**

- [ ] https://yannleflohic.fr/ s'affiche.
- [ ] https://yannleflohic.fr/api/terminal affiche du **JSON** (`{"name":"Yann Le Flohic",…}`).

### Phase 5 — Styles et scripts communs

- [ ] **21.** `public/css/style.css` — *celui compilé au § 6.1*
- [ ] **22.** `public/js/main.js`
- [ ] **23.** `public/js/admin.js`

**✅ Test phase 5**

- [ ] https://yannleflohic.fr/ s'affiche (des différences d'affichage sont **normales** à ce stade : les vues ne sont pas encore à jour).

### Phase 6 — Gabarit commun (enchaîner les 3 fichiers, dans CET ordre)

- [ ] **24.** `app/views/layouts/footer.php` — *en premier : il charge `animations.js`, qui révèle les titres*
- [ ] **25.** `public/js/theme-toggle.js`
- [ ] **26.** `app/views/layouts/header.php` — *juste après le 25*

**✅ Test phase 6**

- [ ] Header : logo « YANN LE FLOHIC » (glitch au survol), interrupteur **Développeur / Photographe**.
- [ ] L'interrupteur bascule bien en mode Photo, puis en mode Dev.
- [ ] Le titre principal apparaît (pas de titres invisibles), et le curseur Lime suit la souris.
- [ ] ⌘K / Ctrl+K ouvre le terminal ; la commande `help` répond.

### Phase 7 — Pages publiques

- [ ] **27.** `app/views/front/partials/about.php`
- [ ] **28.** `app/views/front/partials/skills.php`
- [ ] **29.** `app/views/front/partials/projects.php`
- [ ] **30.** `app/views/front/partials/gallery.php`
- [ ] **31.** `app/views/front/partials/contact.php`
- [ ] **32.** `app/views/front/home.php` — *après le 5 (`compare.php`), déjà en ligne*
- [ ] **33.** `app/views/front/lab.php`
- [ ] **34.** `app/controllers/HomeController.php`

**✅ Test phase 7**

- [ ] https://yannleflohic.fr/ : toutes les sections s'affichent ; en mode Photo, galerie magazine et section « Post-traitement ».
- [ ] Clic sur une photo → visionneuse plein écran.
- [ ] https://yannleflohic.fr/lab et https://yannleflohic.fr/mentions-legales s'affichent.

### Phase 8 — Back-office (2 paires : contrôleur PUIS vue, sans pause)

- [ ] **35.** `app/controllers/AuthController.php`
- [ ] **36.** `app/views/admin/login.php` — *juste après le 35*
- [ ] **37.** `app/controllers/AdminController.php`
- [ ] **38.** `app/views/admin/dashboard.php` — *juste après le 37*

**✅ Test phase 8**

- [ ] https://yannleflohic.fr/login → connexion OK, arrivée sur le tableau de bord.
- [ ] Modifier un texte puis **Enregistrer** → bandeau vert « Modifications enregistrées ».
- [ ] Remettre le texte d'origine et enregistrer de nouveau.

### Phase 9 — `.htaccess` de `public/` (en dernier, isolé)

- [ ] **39.** `public/.htaccess` — *fichier caché*
- [ ] Si des blocs ont été mis de côté au § 5.3 : les recoller **à la fin** de `public_html/public/.htaccess` et enregistrer.

**✅ Test phase 9**

- [ ] https://yannleflohic.fr/ s'affiche.
- [ ] https://www.yannleflohic.fr/ redirige vers `https://yannleflohic.fr/`.
- [ ] ⛔ Si une erreur 500 apparaît sur tout le site : ré-envoyer l'ancien `public/.htaccess` (sauvegarde § 7), puis voir § 13.

**✅ Fin du transfert :** les **39 cases** de ce § 9 sont cochées.

---

## 10. Contrôle des fichiers inchangés

Ces 16 fichiers n'ont pas changé depuis le commit `a241d1e`. Il suffit de **vérifier qu'ils existent en ligne**, et de les envoyer seulement s'ils manquent.

| Fichier | Vérification rapide |
| --- | --- |
| `app/models/User.php` | Présent dans `public_html/app/models/` (sinon `/login` ne fonctionne pas) |
| `app/views/front/legal.php` | https://yannleflohic.fr/mentions-legales s'affiche |
| `public/assets/images/gallery/gallery-1.webp` → `gallery-6.webp` (6 fichiers) | Présents dans `public_html/public/assets/images/gallery/` |
| `public/assets/images/projects/` : `barcelona.webp`, `landingpageCinema.webp`, `portfolioV1.webp`, `sae105.webp`, `sae202.webp`, `spaceInvaders.webp` | Présents dans `public_html/public/assets/images/projects/` |
| `public/assets/videos/animoji.mov`, `animoji.webm` | L'avatar animé du hero se lance |

- [ ] Les 16 fichiers sont présents en ligne (ceux qui manquaient ont été envoyés).

---

## 11. Finalisation

- [ ] Supprimer **`public_html/public/test.php`** s'il existe (ancien fichier de débogage qui exposerait la configuration du serveur).
- [ ] hPanel → **Avancé** → **Cache LiteSpeed** (ou *Performance* → *Cache*) → **Purger tout**.
- [ ] Fermer et rouvrir la fenêtre de navigation privée.

---

## 12. ✅ Check-list APRÈS la mise en ligne

**Fonctionnement général**

- [ ] https://yannleflohic.fr/ s'affiche : loader, puis titre révélé avec son trait Lime
- [ ] https://yannleflohic.fr/api/terminal → JSON
- [ ] Console DevTools (F12) sans erreur ; onglet Réseau : polices chargées depuis `fonts.gstatic.com`

**Mode Dev**

- [ ] Survol du logo « YANN LE FLOHIC » → glitch de 0,5 s, puis texte stable
- [ ] Curseur Lime, particules réactives, cartes en cascade + tilt, boutons Glow
- [ ] ⌘K / Ctrl+K : `help`, `skills`, `contact`, `sudo hire-yann` fonctionnent

**Mode Photo**

- [ ] Interrupteur → révélation circulaire ; fond noir cinéma, titres en serif, galerie magazine avec grain, section « Post-traitement »
- [ ] Recharger la page : le mode Photo est conservé
- [ ] Visionneuse : ← / → et Échap fonctionnent
- [ ] Comparateur avant / après glissable (souris, doigt, clavier)
- [ ] Le terminal reste Graphite / Lime

**Back-office et sécurité**

- [ ] Connexion admin, enregistrement d'un texte OK
- [ ] Envoi d'une image JPEG / PNG → acceptée (nom du type `proj_<timestamp>_<aléatoire>.jpg`)
- [ ] Les images déjà en ligne s'affichent toujours
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
- [ ] `/robots.txt` et `/sitemap.xml` s'affichent

**Mobile**

- [ ] Pas de défilement horizontal, interrupteur « Dev / Photo » et bouton `>_ terminal` visibles

---

## 13. 🩺 Encarts de diagnostic

| Symptôme | Diagnostic | Solution |
| --- | --- | --- |
| « Erreur interne du serveur. » (texte brut) sur tout le site, juste après le fichier 17 | Clé manquante dans le `.env` du serveur (souvent `DB_PASS`) | Ré-envoyer l'ancien `database.php`, compléter le `.env` (§ 5.1), puis renvoyer le fichier 17. Le journal PHP (hPanel → Avancé → Journaux) indique « Configuration BDD incomplète : … » |
| Même message, `.env` complet | Identifiants incorrects ou base indisponible | Vérifier les valeurs dans hPanel → Bases de données ; journal « Erreur PDO : … » |
| Page d'erreur 500 sur **tout** le site après un contrôleur ou `index.php` | Un fichier de `app/core/` manque (phase 1), ou PHP < 8.1 | Vérifier les fichiers 1 à 4 dans `public_html/app/` ; PHP ≥ 8.1 (§ 5.4) |
| Erreur 500 sur tout le site après le fichier 39 | Directive du nouveau `public/.htaccess`, ou bloc recollé incorrect | Ré-envoyer l'ancien `public/.htaccess` (§ 14) ; vérifier le bloc recollé |
| Erreur 500 sur l'accueil uniquement | `home.php` envoyé sans `partials/compare.php` | Vérifier le fichier 5 |
| Erreur 500 sur `/login` ou `/admin` uniquement | Vue admin envoyée avant son contrôleur (ou `Csrf.php` absent) | Envoyer les fichiers 35 et 37 (et vérifier le 2) |
| Erreur 500 sur les images de projets / galerie uniquement | Directive du `uploads/.htaccess` refusée par le serveur | Vérifier que la version en ligne est la nouvelle (`php_flag` dans des `<IfModule>`) ; à défaut, commenter (`#`) la ligne `Options -ExecCGI -Indexes`. **Garder** le bloc `FilesMatch`, qui est la vraie protection |
| Titres invisibles après la phase 6 | `header.php` en ligne sans le nouveau `footer.php`, ou `animations.js` absent | Vérifier les fichiers 24 et 6 |
| L'interrupteur ne fait rien | `theme-toggle.js` et `header.php` pas tous les deux à jour | Vérifier les fichiers 25 et 26, puis purger le cache |
| Le site n'est plus forcé en HTTPS | Règle « Forcer HTTPS » perdue | hPanel → Sécurité → SSL → réactiver **Forcer HTTPS** |
| Login : « Session expirée » à chaque essai | `login.php` ancien avec le nouveau contrôleur, ou cookies bloqués | Vérifier le fichier 36 ; autoriser les cookies |
| Back-office : « Jeton de sécurité invalide » systématique | `dashboard.php` ancien avec le nouveau contrôleur | Vérifier le fichier 38 |
| Ancien design ou ancien comportement visible | Cache LiteSpeed / navigateur | Purger le cache (§ 11) + navigation privée |
| Mode Photo sans serif ni galerie magazine | `style.css` ancien ou en cache | Vérifier le fichier 21 ; purger le cache |
| Clic sur une photo sans visionneuse | `photo-mode.js` absent ou `gallery.php` ancien | Vérifier les fichiers 8 et 30 |
| Terminal : « Impossible de joindre /api/terminal » | API en erreur (503 BDD) ou `index.php` ancien | Ouvrir `/api/terminal` ; vérifier les fichiers 4 et 20 |
| Image refusée : « type non autorisé » | Format HEIC, SVG, AVIF… (comportement voulu) | Convertir en JPEG, PNG, WebP ou GIF |
| Connexion admin qui « ne tient pas » | Cookie de session rejeté (domaine / HTTPS) | Le site doit être servi en **HTTPS** sur `yannleflohic.fr` (sans `www`) |

---

## 14. Retour arrière (rollback)

**Annuler un seul fichier** (cas le plus fréquent) : ré-envoyer sa version d'origine, issue de la sauvegarde du § 7, au même emplacement. Puis purger le cache.

**Tout annuler :**

1. hPanel → **Sauvegardes** : restaurer la sauvegarde **des fichiers** du § 7.
2. **Ne pas** restaurer la base de données : le déploiement n'y touche pas.
3. Garder le `.env` complété au § 5.1 : il reste compatible avec l'ancien code.
4. Purger le cache LiteSpeed.

> ⚠️ Restaurer l'ancien `app/config/database.php` remet un mot de passe en clair dans le code en ligne. C'est une raison de plus pour changer ce mot de passe MySQL (§ 5.1).

---

## 15. ✅ Récapitulatif express

```
PRÉPARATION
 1. public_html/.env : DB_HOST, DB_NAME, DB_USER, DB_PASS → sinon les ajouter MAINTENANT
 2. public_html contient app/, public/, .htaccess, .env
 3. Noter les blocs propres au serveur dans public/.htaccess (s'il y en a)
 4. PHP ≥ 8.1 + pdo_mysql, fileinfo, mbstring
 5. Ctrl + C sur npm run dev → npm run build → vérifications des § 6.1 et § 6.2
 6. Sauvegarde hPanel (fichiers + BDD) + copie des fichiers à remplacer

TRANSFERT (un fichier à la fois, test après chaque phase)
 Phase 1  fichiers 1-16   nouveaux « dormants » (app/core, ApiController, compare.php, 3 JS, assets, SEO, uploads/.htaccess)
 Phase 2  fichier 17      database.php → tester l'accueil TOUT DE SUITE
 Phase 3  fichiers 18-19  modèles
 Phase 4  fichier 20      public/index.php → tester /api/terminal
 Phase 5  fichiers 21-23  style.css, main.js, admin.js
 Phase 6  fichiers 24-26  footer.php → theme-toggle.js → header.php (enchaîner)
 Phase 7  fichiers 27-34  partials, home.php, lab.php, HomeController
 Phase 8  fichiers 35-38  AuthController → login.php ; AdminController → dashboard.php
 Phase 9  fichier 39      public/.htaccess (+ recoller les blocs notés)

FIN
 7. Contrôler les 16 fichiers inchangés (§ 10)
 8. Supprimer public/test.php s'il existe ; purger le cache LiteSpeed
 9. Dérouler la check-list du § 12
```
