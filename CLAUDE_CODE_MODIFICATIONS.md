# Synthèse des modifications effectuées par Claude Code

Comparaison effectuée avec l’état Git de référence du dépôt. Ce document décrit les changements constatés dans le dépôt et les opérations locales mentionnées dans le compte rendu collé. Aucun fichier applicatif n’a été modifié pendant cette vérification.

## Vue d’ensemble

- **31 fichiers suivis modifiés**.
- **1 fichier supprimé**.
- **Plusieurs fichiers ajoutés**, dont des fichiers applicatifs, deux migrations SQL, trois scripts JavaScript et des compétences Claude Code.
- Diff Git : **1 685 lignes ajoutées, 450 supprimées**.
- La reconstruction de `public/css/style.css` a été effectuée et les versions de cache des scripts ont été incrémentées en `20261025`.

## Fonctionnalités ajoutées ou modifiées

### Accessibilité et UX

- Ajout d’un lien d’évitement « Aller au contenu » et d’un identifiant `main` sur les pages principales.
- Ajout du `scroll-padding-top` pour éviter que le header fixe masque les ancres.
- Prise en charge de `prefers-reduced-motion` pour le marquee, le loader, les vidéos et les animations.
- Ajout d’un bouton de pause pour le marquee et les vidéos d’arrière-plan.
- Ajout d’un bouton « Passer » au loader, avec masquage de secours si JavaScript est indisponible.
- Remplacement des consoles du lab par des éléments `<dialog>` avec gestion de fermeture, d’onglets et de clavier.
- Ajout des rôles ARIA pour les onglets, les sections et les barres de langue.
- Ajout ou correction de titres de sections accessibles, notamment pour les compétences.
- Augmentation de plusieurs tailles minimales de texte.
- Correction des métadonnées Twitter, avec `name="twitter:*"`.
- Conservation du curseur personnalisé et du glitch du logo, conformément à la décision indiquée dans le texte collé.

### Navigation, contenu et conversion

- Le CTA « Me contacter » devient prioritaire, tandis que « Voir mes projets » devient secondaire.
- Ajout de l’avatar dans la version mobile du hero.
- Ajout d’un lien pour basculer entre les univers Dev et Photo.
- Ajout de la galerie au menu principal.
- Renumérotation du contact et correction de l’intitulé du Lab.
- Transformation des intérêts et compétences en éléments non cliquables visuellement.
- Suppression du lien ADMIN du footer et de la fausse pastille de statut.
- Suppression de la phrase redondante du hero : « Vous avez un projet en tête ? ».
- Correction des textes en vouvoiement.
- Correction des `alt` des images de cartes projet, désormais décoratives lorsqu’un titre adjacent suffit.
- Ajout d’un conteneur à largeur maximale pour les grands écrans.
- Remplacement du chargement des polices via `@import` par des liens avec `preconnect`.
- Suppression de plusieurs effets visuels jugés gênants : grille décorative initiale, canvas de particules du loader et du terminal, tilt, effets magnetic secondaires et flèches rotatives.
- Réintégration ultérieure de la grille et des particules WebGL réactives à la souris, avec leur halo, à la demande de l’utilisateur.

### Formulaire de contact

- Ajout d’un formulaire avec labels visibles, validation et erreurs par champ.
- Ajout des états d’envoi et de confirmation.
- Ajout d’une protection CSRF.
- Ajout d’un champ honeypot anti-spam.
- Ajout d’une limitation à trois envois par heure et d’un délai minimal de trente secondes par session.
- Ajout d’une protection contre l’injection d’en-têtes.
- Ajout de la route API `POST /api/contact`.
- Les essais indiqués dans le compte rendu donnent notamment `405` en GET, `422` en cas d’erreur, `403` sans CSRF et une validation silencieuse du honeypot rempli.

### Pages de projets

- Les cartes de projets conservent leur apparence, mais ouvrent désormais une page détail.
- Ajout des routes `/projets/{id}-{slug}`.
- Ajout des champs optionnels :
  - année ;
  - rôle ;
  - contexte ;
  - solution ;
  - résultat ;
  - lien du projet ;
  - dépôt GitHub ;
  - galerie d’images.
- Ajout des sections détail « Contexte », « Mon rôle », « Solution » et « Résultat », affichées uniquement lorsqu’elles sont renseignées.
- Ajout de liens optionnels vers le projet et le dépôt GitHub.
- Ajout d’une galerie avec visionneuse, navigation, compteur et fermeture au clavier.
- Ajout du balisage JSON-LD `CreativeWork`.
- Ajout de contrôles d’URL, notamment le filtrage des URLs `javascript:`.
- Ajout des champs correspondants dans le back-office, avec une URL de galerie par ligne et gestion des uploads par le contrôle existant.
- Les textes éditoriaux de contexte, solution et résultat ne sont pas inventés lorsqu’ils ne sont pas fournis.

### Articles du Lab

- Ajout des routes `/lab/{slug}`.
- Ajout de pages d’articles indexables et partageables.
- Ajout de pages et liens d’articles dans les vues et contrôleurs concernés.

### Mesure d’audience et consentement

- Ajout d’un bandeau de consentement en bas de l’écran, avec boutons « Refuser » et « Accepter ».
- Ajout d’une réouverture du choix depuis le footer et depuis la commande cookies du terminal.
- Aucun événement n’est envoyé avant acceptation.
- Le choix est conservé dans `localStorage`, sans cookie de suivi.
- Respect de `Do Not Track` et `Global Privacy Control`.
- Exclusion des robots, des sessions administrateur et des routes `/admin`.
- Ajout de mesures pour les pages vues, l’origine, l’appareil et six actions :
  - clic sur « Me contacter » ;
  - ouverture d’un projet ;
  - clic vers le projet ;
  - clic vers le dépôt ;
  - changement de mode ;
  - envoi d’un message.
- Ajout de l’onglet « Statistiques » dans le back-office, avec périodes de 7, 30 ou 90 jours, compteurs, évolution, pages, origines, appareils et actions.
- Mise à jour de la page de confidentialité.
- Les données sont annoncées comme purgées automatiquement après 13 mois.
- Le sel d’empreinte peut être fourni par `ANALYTICS_SALT`. Sans cette variable, un sel de base est utilisé.
- Statistiques détaillées (25 octobre 2026) :
  - collecte : heure (Paris), navigateur, système, langue, mode Dev/Photo, `utm_source`, `utm_campaign`, pays (en-tête `CF-IPCountry`, vide hors Cloudflare), profondeur de défilement (50 % et 90 %), temps passé (arrondi à 5 s), clics GitHub, LinkedIn, e-mail, autre lien et CV ;
  - back-office : variation par rapport à la période précédente, pages par visite, rebond, durée moyenne, taux de conversion, entonnoir, profondeur de lecture, jours de la semaine, heures, fiches projet les plus ouvertes, navigateurs, systèmes, langues, pays, campagnes, modes ;
  - migration : `2026-10-25_analytics_details.sql`, à jouer avant le code.

### Terminal

Ajout de commandes visibles dans l’aide du terminal :

`project`, `open`, `random`, `stack`, `lab`, `tree`, `neofetch`, `man`, `cookies` et `fortune`.

La commande cachée `coffee` est également mentionnée.

### Sitemap et Docker

- Suppression du sitemap statique.
- Ajout d’un sitemap dynamique via `HomeController::sitemap()`.
- Le sitemap dynamique inclut l’accueil, le Lab, les fiches projet et les articles du Lab.
- Le conteneur local a été recréé avec `ENVIRONMENT=dev` pour permettre le fonctionnement local des sessions, du CSRF et de la connexion administrateur en HTTP.
- URLs locales indiquées :
  - site : `http://localhost:8000` ;
  - administration : `http://localhost:8000/login` ;
  - phpMyAdmin : `http://localhost:8080`.

## Fichiers suivis modifiés

### PHP, modèles, contrôleurs et base

- [app/controllers/AdminController.php](app/controllers/AdminController.php)
- [app/controllers/ApiController.php](app/controllers/ApiController.php)
- [app/controllers/Controller.php](app/controllers/Controller.php)
- [app/controllers/HomeController.php](app/controllers/HomeController.php)
- [app/core/ImageUploader.php](app/core/ImageUploader.php)
- [app/models/Project.php](app/models/Project.php)
- [database/schema.sql](database/schema.sql)
- [public/index.php](public/index.php)

### Vues PHP

- [app/views/admin/dashboard.php](app/views/admin/dashboard.php)
- [app/views/front/404.php](app/views/front/404.php)
- [app/views/front/home.php](app/views/front/home.php)
- [app/views/front/lab.php](app/views/front/lab.php)
- [app/views/front/legal.php](app/views/front/legal.php)
- [app/views/front/privacy.php](app/views/front/privacy.php)
- [app/views/front/terms.php](app/views/front/terms.php)
- [app/views/front/partials/about.php](app/views/front/partials/about.php)
- [app/views/front/partials/compare.php](app/views/front/partials/compare.php)
- [app/views/front/partials/contact.php](app/views/front/partials/contact.php)
- [app/views/front/partials/gallery.php](app/views/front/partials/gallery.php)
- [app/views/front/partials/projects.php](app/views/front/partials/projects.php)
- [app/views/front/partials/skills.php](app/views/front/partials/skills.php)
- [app/views/layouts/footer.php](app/views/layouts/footer.php)
- [app/views/layouts/header.php](app/views/layouts/header.php)

### JavaScript et styles

- [public/js/admin.js](public/js/admin.js)
- [public/js/lab.js](public/js/lab.js)
- [public/js/main.js](public/js/main.js)
- [public/js/projects-filter.js](public/js/projects-filter.js)
- [public/js/terminal.js](public/js/terminal.js)
- [public/css/style.css](public/css/style.css)
- [src/css/input.css](src/css/input.css)

## Fichier supprimé

- `public/sitemap.xml`, remplacé par la génération dynamique du sitemap dans `HomeController`.

## Fichiers ajoutés

### Fonctionnalités applicatives

- [app/models/AnalyticsEvent.php](app/models/AnalyticsEvent.php)
- [app/views/front/article.php](app/views/front/article.php)
- [app/views/front/project.php](app/views/front/project.php)
- [database/migrations/2026-10-24_analytics_events.sql](database/migrations/2026-10-24_analytics_events.sql)
- [database/migrations/2026-10-24_projects_detail.sql](database/migrations/2026-10-24_projects_detail.sql)
- [database/migrations/2026-10-25_analytics_details.sql](database/migrations/2026-10-25_analytics_details.sql)
- [public/js/consent.js](public/js/consent.js)
- [public/js/contact.js](public/js/contact.js)
- [public/js/motion-controls.js](public/js/motion-controls.js)

### Compétences Claude Code

Ajout de compétences dans [.claude/skills/](.claude/skills/) pour :

- architecture MVC ;
- design de bannières ;
- identité de marque ;
- design system ;
- design d’interfaces ;
- design UI/UX ;
- présentations.

Ces dossiers contiennent leurs fichiers `SKILL.md` et, selon la compétence, des documents de référence associés.

## Éléments explicitement non réalisés ou non vérifiés

- Aucun bandeau cookies ni outil d’analytics tiers n’a été ajouté. La mesure maison et le bandeau décrits ci-dessus ont bien été ajoutés ensuite.
- Le rendu visuel à 375 px et la navigation complète au clavier n’ont pas été testés dans un navigateur.
- L’envoi réel d’e-mail n’a pas été validé dans Docker, faute de service `sendmail`.
- Les migrations SQL ont été appliquées sur la base Docker locale, mais leur application sur Hostinger reste à faire.
- Le site doit recevoir les migrations avant le déploiement du code :
  - `2026-10-24_projects_detail.sql` ;
  - `2026-10-24_analytics_events.sql` ;
  - `2026-10-25_analytics_details.sql`.
- Le sitemap statique supprimé ne doit pas être réintroduit sur Hostinger.
- Les champs éditoriaux des fiches projet restent vides lorsqu’aucun contenu réel n’a été fourni.
- Les données de contexte, rôle, solution et résultat nécessitent encore un remplissage éditorial projet par projet.
- Le stockage de `ANALYTICS_SALT` dans l’environnement de production est recommandé.

## Nettoyage local mentionné

- Création puis suppression d’un administrateur de test.
- Suppression des tentatives de connexion de test.
- Vidage des événements d’analytics de test.
- Réinitialisation des champs de test du projet 1.
- Conservation du compte administrateur d’origine et des projets existants.
- Les éventuelles traces Docker temporaires ont été indiquées comme nettoyées.
