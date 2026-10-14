# 🧱 Mise en production — Restructuration MVC (octobre 2026)

> Procédure pour mettre en ligne la restructuration MVC (branche `V3-Restructuration`) sur Hostinger.
> **Aucun changement visuel** pour les visiteurs. Le seul changement de fond : **les projets quittent la table `settings` (JSON) pour leur propre table `projects`**, d'où une migration SQL à exécuter **avant** le transfert des fichiers.
>
> Pour les gestes de base (transférer un fichier, purger le cache, diagnostic), voir [DEPLOIEMENT_HOSTINGER.md](DEPLOIEMENT_HOSTINGER.md) (§ 8, § 11 et § 13).

---

## 📋 Sommaire

1. [Ce qui change pour toi](#1-ce-qui-change-pour-toi)
2. [Pourquoi cet ordre](#2-pourquoi-cet-ordre)
3. [Phase 0 — Sauvegarde](#3-phase-0--sauvegarde)
4. [Phase 1 — Migration SQL](#4-phase-1--migration-sql)
5. [Phase 2 — Fichiers compatibles](#5-phase-2--fichiers-compatibles-aucun-effet-visible)
6. [Phase 3 — Bascule](#6-phase-3--bascule-tous-les-fichiers-en-une-fois)
7. [Phase 4 — Nettoyage](#7-phase-4--nettoyage)
8. [Check-list après mise en ligne](#8-check-list-après-mise-en-ligne)
9. [Retour arrière](#9-retour-arrière)
10. [Récapitulatif express](#10-récapitulatif-express)

---

## 1. Ce qui change pour toi

| Sujet | Action de ta part |
| --- | --- |
| **Base de données** | ✅ **Obligatoire** : exécuter une fois `database/migrations/2026-10-14_projects_table.sql` dans phpMyAdmin (§ 4) |
| **Fichier renommé** | ✅ **Obligatoire** : supprimer `app/models/settings.php` du serveur **après** la bascule (§ 7). Le serveur Linux distingue `settings.php` et `Setting.php` : l'ancien fichier ne serait jamais écrasé |
| **Fichiers** | 32 fichiers à transférer (4 nouveaux, 28 modifiés), dans l'ordre des phases 2 et 3 |
| `.env` | Aucun changement |
| `public/css/style.css` | Aucun changement (pas besoin de `npm run build`, ni de le renvoyer) |
| Version de PHP | Aucun changement (≥ 8.1) |
| Version de MySQL | Vérifier : **MySQL ≥ 8.0** ou **MariaDB ≥ 10.6** (fonction `JSON_TABLE` utilisée par la migration). phpMyAdmin l'affiche sur sa page d'accueil (« Version du serveur ») |
| Cache navigateur | Rien à faire : les scripts sont appelés avec `?v=20261014`, les visiteurs reçoivent automatiquement les nouvelles versions |
| Back-office | S'utilise exactement comme avant. Les projets ont désormais un identifiant (champ caché) : c'est normal |

---

## 2. Pourquoi cet ordre

- **La migration d'abord** : elle crée la table `projects` et y copie tes projets. L'ancien code ne lit pas cette table, donc le site en ligne n'est pas affecté. Le nouveau code, lui, en a besoin dès son arrivée.
- **Les fichiers compatibles ensuite** : nouveaux fichiers qu'aucun code en ligne n'appelle encore, ou fichiers modifiés qui fonctionnent aussi avec l'ancien code (même interface, même DOM).
- **La bascule en une seule fois** : `public/index.php` charge **les quatre contrôleurs à chaque requête**. Les anciens chargent `settings.php`, les nouveaux `Setting.php`, et ces deux fichiers déclarent la même classe `Setting`. Un mélange d'anciens et de nouveaux contrôleurs provoque une **erreur fatale** (« Cannot declare class Setting »). Les contrôleurs et les vues qui vont avec doivent donc arriver ensemble.

---

## 3. Phase 0 — Sauvegarde

- [ ] hPanel → **Fichiers** → **Sauvegardes** : sauvegarde des fichiers **et** de la base de données.
- [ ] phpMyAdmin → base du site → onglet **Exporter** → *Rapide*, format SQL → **Exécuter**. Garder ce fichier : c'est ta copie de la table `settings` avant migration.
- [ ] Gestionnaire de fichiers : *Compresser* puis *Télécharger* les dossiers `public_html/app` et `public_html/public`.
- [ ] Noter le **nombre de projets** affichés actuellement sur https://yannleflohic.fr/#projects : il servira de contrôle au § 4.

---

## 4. Phase 1 — Migration SQL

1. phpMyAdmin → sélectionner la base du site (colonne de gauche).
2. Onglet **SQL** : exécuter d'abord cette requête de contrôle :

   ```sql
   SHOW TABLES LIKE 'projects';
   ```

   - **Aucun résultat** : passer à l'étape 3.
   - **Une ligne `projects`** : c'est l'ancienne table de `schema.sql` (colonnes `slug`, `type`, `tag`…), qui n'a jamais été utilisée par le site. La mettre de côté :

     ```sql
     RENAME TABLE `projects` TO `projects_legacy`;
     ```

3. Onglet **Importer** → choisir le fichier local `database/migrations/2026-10-14_projects_table.sql` → **Exécuter**.
   *(Ou copier-coller son contenu dans l'onglet **SQL**.)*
4. Le dernier résultat affiche `projets_migres` : ce nombre doit être **égal au nombre de projets noté au § 3**.
5. Contrôle visuel : onglet **Parcourir** de la table `projects`. Les projets sont dans l'ordre du site (`position` 0, 1, 2…), avec titres, images et liens.

- [ ] Migration exécutée, nombre de projets conforme.
- [ ] Le site en ligne fonctionne toujours (il n'utilise pas encore cette table).

> 🛟 **Si `JSON_TABLE` provoque une erreur** (version de MySQL / MariaDB trop ancienne) : **ne pas continuer**. Le site en ligne n'est pas affecté. La table `projects` a pu être créée vide : la supprimer (`DROP TABLE projects;`), puis vérifier la version du serveur SQL avec Hostinger.
>
> La migration est **ré-exécutable** sans risque : ses insertions ne s'appliquent que si la table `projects` est vide.

---

## 5. Phase 2 — Fichiers compatibles (aucun effet visible)

Un fichier à la fois, comme d'habitude. Ces fichiers fonctionnent avec le code actuellement en ligne.

**Nouveaux fichiers, appelés par personne pour l'instant :**

- [ ] **1.** `app/controllers/Controller.php`
- [ ] **2.** `app/models/Setting.php` — *nouveau nom ; l'ancien `settings.php` reste en place pour l'instant*
- [ ] **3.** `public/js/lab.js`
- [ ] **4.** `public/js/projects-filter.js`

**Fichiers modifiés, compatibles avec l'ancien code :**

- [ ] **5.** `app/models/Project.php` — *n'était utilisé par aucun code*
- [ ] **6.** `app/config/database.php` — *même fonction `getPDOConnection()`*
- [ ] **7.** `app/core/ImageUploader.php` — *mêmes méthodes*
- [ ] **8.** `public/js/main.js`
- [ ] **9.** `public/js/theme-toggle.js`
- [ ] **10.** `public/js/animations.js`
- [ ] **11.** `public/js/terminal.js`
- [ ] **12.** `public/js/photo-mode.js`
- [ ] **13.** `app/views/layouts/footer.php` — *après les fichiers 8 à 12 : il appelle leurs nouvelles versions (`?v=20261014`)*
- [ ] **14.** `app/views/layouts/header.php` — *juste après le 13*
- [ ] **15.** `app/views/front/legal.php`

**Test de fin de phase** (navigation privée, `Cmd + Shift + R`) : accueil, Lab, mentions légales et back-office fonctionnent **comme avant**. Loader, bascule Dev/Photo, terminal (⌘K), visionneuse et comparateur répondent.

---

## 6. Phase 3 — Bascule (tous les fichiers en une fois)

Ces **17 fichiers** dépendent les uns des autres : ils doivent arriver **ensemble**, en un seul transfert.

```text
public/index.php
public/js/admin.js
app/controllers/HomeController.php
app/controllers/ApiController.php
app/controllers/AuthController.php
app/controllers/AdminController.php
app/models/User.php
app/views/front/home.php
app/views/front/lab.php
app/views/front/partials/about.php
app/views/front/partials/skills.php
app/views/front/partials/projects.php
app/views/front/partials/gallery.php
app/views/front/partials/compare.php
app/views/front/partials/contact.php
app/views/admin/login.php
app/views/admin/dashboard.php
```

**Méthode recommandée : FileZilla (FTP).** Le transfert prend quelques secondes :

1. Panneau local : dossier du projet ; panneau distant : `public_html/`.
2. Transférer les fichiers ci-dessus **dossier par dossier, en enchaînant sans pause** : sélection multiple (`Cmd` + clic) dans chaque dossier, glisser vers le même dossier distant, puis répondre **Écraser** et cocher *« Toujours utiliser cette action »* **pour cette file d'attente uniquement**.
3. Vérifier l'onglet *Transferts réussis* : **17 fichiers**.

> Avec le Gestionnaire de fichiers d'hPanel, l'envoi fichier par fichier laisse le site en erreur pendant toute la durée du transfert. Si c'est la seule option, préparer tous les fichiers à l'avance et les envoyer aussi vite que possible.

- [ ] 17 fichiers transférés.
- [ ] **Test immédiat** : https://yannleflohic.fr/ s'affiche, ainsi que la section Projets, `/lab`, `/api/terminal` (JSON) et `/login`.

Si une page affiche une erreur 500 : vérifier dans *Transferts réussis* qu'aucun fichier de la liste ne manque (le cas typique est un contrôleur oublié). Sinon, voir le § 9.

---

## 7. Phase 4 — Nettoyage

- [ ] Gestionnaire de fichiers → `public_html/app/models/` → **supprimer `settings.php`** (minuscule).
  *Plus aucun fichier ne l'appelle ; il ne doit rester que `Setting.php`, `Project.php` et `User.php`.*
- [ ] hPanel → **Avancé** → **Cache** (LiteSpeed) → **Purger tout**.
- [ ] Optionnel, **après une semaine sans souci** : supprimer l'ancienne copie JSON des projets, que le site n'utilise plus :

  ```sql
  DELETE FROM `settings` WHERE `setting_key` = 'projects';
  ```

  *(et `DROP TABLE projects_legacy;` si tu l'avais créée au § 4.)* Tant qu'elle existe, elle permet un retour arrière complet (§ 9).

---

## 8. Check-list après mise en ligne

**Site public** (navigation privée) :

- [ ] Accueil : loader au premier chargement, titres révélés au scroll, fond de particules, curseur magnétique.
- [ ] Projets : mêmes cartes, **même ordre**, mêmes images et liens. Les filtres masquent et réaffichent les cartes.
- [ ] Bascule Dev ↔ Photo : transition, galerie magazine, visionneuse (← / →, balayage, fermeture), comparateur avant / après (souris, doigt, flèches du clavier).
- [ ] Terminal : ⌘K / Ctrl+K, `help`, `whoami`, `projects` (liste identique à la page), `theme photo`, `exit`.
- [ ] Lab : « Voir le code » (onglets HTML / CSS / JS), « Lire l'article », fermeture par ✕, clic sur le fond et Échap.

**Back-office** :

- [ ] Connexion, puis passage d'un onglet à l'autre.
- [ ] Onglet Projets : les projets sont listés. Modifier un titre puis **Enregistrer** : message vert, changement visible sur l'accueil.
- [ ] Ajouter un projet avec une image, enregistrer : il apparaît en dernier sur l'accueil, avec son image.
- [ ] Supprimer un projet, enregistrer : il disparaît de l'accueil et de la commande `projects` du terminal.
- [ ] Supprimer une ligne (ex. une langue), en ajouter une autre, enregistrer : les deux opérations sont prises en compte (c'est la correction du bug d'index).
- [ ] Déconnexion.

---

## 9. Retour arrière

**Tout annuler :**

1. Restaurer les dossiers `app/` et `public/` sauvegardés au § 3, **y compris l'ancien `app/models/settings.php`**.
2. Purger le cache LiteSpeed.
3. Base de données : **rien à restaurer**. L'ancien code ignore la table `projects`, et la clé `projects` de `settings` contient toujours les projets d'avant la migration.

> ⚠️ Les projets modifiés dans le back-office **après** la bascule ne sont enregistrés que dans la table `projects`. Après un retour arrière, l'ancien site affiche ceux d'avant la migration : il faudrait refaire ces modifications.

---

## 10. Récapitulatif express

```
PHASE 0  Sauvegardes hPanel (fichiers + BDD) + export SQL phpMyAdmin + noter le nombre de projets
PHASE 1  phpMyAdmin : SHOW TABLES LIKE 'projects' (→ RENAME en projects_legacy si présente)
         Importer database/migrations/2026-10-14_projects_table.sql → projets_migres = nombre noté
PHASE 2  15 fichiers compatibles, un par un (Controller.php, Setting.php, lab.js, projects-filter.js,
         Project.php, database.php, ImageUploader.php, 5 JS, footer.php, header.php, legal.php) → test
PHASE 3  17 fichiers EN UNE FOIS par FTP (index.php, admin.js, 4 contrôleurs, User.php, 10 vues) → test immédiat
PHASE 4  Supprimer app/models/settings.php → purger le cache LiteSpeed → check-list § 8
```
