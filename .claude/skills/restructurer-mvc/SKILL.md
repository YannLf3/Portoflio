---
name: restructurer-mvc
description: Guide la restructuration complète de l'architecture MVC globale (Back-End PHP & Front-End JS Vanilla) du portfolio pour un code propre, lisible, modulaire et maintenable, avec validation obligatoire d'un plan préalable et garantie d'isofonctionnalité.
---

# Restructuration MVC Globale (Portfolio Yann Le Flohic)

Ce skill formalise le processus complet de restructuration architecturale du portfolio `yannleflohic.fr`. 
Son rôle est de transformer le code existant en un ensemble élégant, lisible, sécurisé et maintenable, qui respecte rigoureusement la patte de développement définie dans `AGENTS.md`, tout en **garantissant une isofonctionnalité totale à 100%**.

---

## 📋 Protocole d'Exécution en 5 Phases

### Phase 1 : Audit & Présentation du Plan d'Action (Validation Préalable Obligatoire)

Avant de modifier la moindre ligne de code, l'agent doit dresser un audit de l'état actuel et soumettre un plan qui range chaque élément à sa place :

1. **Plan Front-End Vanilla JS (MVC Strict) :**
   - **M :** Données et fonctions qui les filtrent ou les transforment (aucun accès au DOM) ;
   - **V :** Lecture des templates, construction du HTML, écoute des événements (dans `V.init`) ;
   - **C :** Gestionnaires d'événements (`C.handler_...`) qui interrogent M puis appellent V.

2. **Plan Back-End PHP :**
   - **M (Back) - Modèles :** Organisation des entités/modèles (`Setting`, `Project`, `User`), getters/setters avec fluent interface, requêtes préparées PDO, encapsulation stricte (`private`/`protected`).
   - **C (Back) - Contrôleurs :** Simplification et clarification des flux (`AdminController`, `ApiController`, `HomeController`, `AuthController`), validation de session, sécurité CSRF et retours standardisés via `Response::json()`.
   - **V (Back) - Vues & Partials :** Découpage des composants dans `app/views/`, hiérarchie sémantique HTML5 W3C, conformité SEO & Accessibilité.

3. **Point d'arrêt obligatoire :**
   > ⛔ **Attendre la validation avant d'écrire.** Ne jamais commencer à modifier le code tant que l'utilisateur n'a pas validé ou ajusté le plan.

---

### Phase 2 : Restructuration Back-End PHP (MVC OO)

Une fois le plan validé, refactoriser le code PHP selon ces exigences strictes :

- **Typage Strict & PHP 8+ :** Déclarer `declare(strict_types=1);` sur chaque fichier PHP. Typer toutes les propriétés, paramètres et retours de méthodes.
- **🚫 Interdiction Absolue de `foreach` :** Remplacer systématiquement par `array_map`, `array_filter`, `array_reduce`, `array_walk` ou des itérateurs natifs.
- **Modèles (`app/models/`) :** Encapsulation stricte, requêtes préparées PDO systématiques, méthodes chaînables (`return $this;`).
- **Contrôleurs (`app/controllers/`) :** Les contrôleurs restent légers ; ils orchestrent les modèles et délèguent le rendu ou la réponse JSON (`Response::json()`).
- **Sécurité :** Vérification CSRF sur chaque action de mutation (`Csrf::isValid()`), validation de session stricte (`session_regenerate_id(true)` aux logins), aucune donnée sensible dans les cookies clients.

---

### Phase 3 : Restructuration Front-End JavaScript (Pattern M/V/C Strict)

Chaque script sous `/public/js/` doit être aligné sur l'architecture en 3 objets globaux :

- **Règle d'écriture obligatoire :**
  Une fonction par propriété (`M.xxx = function(...)`, `V.xxx = function(...)`, `C.handler_xxx = function(...)`), précédée d'un commentaire JSDoc qui indique ses paramètres et sa valeur de retour.
- **🧠 M (Modèle Front-End) :**
  - Gère l'état local (`state`), les données, le filtrage, les transformations et les appels asynchrones (`fetch` avec `async/await` vers `ApiController.php`).
  - **Règle d'or :** Aucun accès au DOM (`document`, `window`, sélecteurs, HTML interdits). Retourne toujours des données (tableaux, objets, scalaires).
- **👁️ V (Vue Front-End) :**
  - Lecture des templates, construction du HTML et écoute des événements déclarés par **délégation d'événements dans `V.init()`**.
  - Seul endroit autorisé à manipuler le DOM. Aucune logique métier ou de calcul.
- **🎮 C (Contrôleur Front-End) :**
  - Gestionnaires d'événements (`C.handler_...`) qui interrogent M puis appellent V.
  - L'appel `C.init();` est **l'unique point d'entrée** déclenché au chargement de la page.

---

### Phase 4 : Vues, SEO, Accessibilité & Tailwind v4

- **Sémantique & Structure HTML :**
  - Structure rigoureuse : un unique `<h1>` par page, pas de saut de niveau de titre (`<h2>` avant `<h3>`).
  - Balises de repère (`<main>`, `<article>`, `<section>`, `<aside>`, `<nav>`) conformes au validateur W3C.
  - Balises canoniques `<link rel="canonical">` et données structurées JSON-LD (`<script type="application/ld+json">`).
- **Médias & Web Vitals :**
  - Attributs natifs `width` et `height` obligatoires sur toutes les balises `<img>` pour éviter le CLS.
  - Attribut `alt` descriptif systématique.
  - Chargement différé `loading="lazy"` sous la ligne de flottaison, formats WebP/AVIF prioritaires.
- **Accessibilité :** Attributs `aria-label`, `aria-hidden`, `aria-expanded` sur tous les contrôles interactifs non textuels.
- **Tailwind CSS v4 :**
  - Mobile First et respect des tokens sémantiques basés sur `[data-mode='dev ou photo']`.
  - 🚫 **Interdiction formelle de mettre des commentaires dans le HTML ou les classes Tailwind CSS.**

---

### Phase 5 : Contrôle Qualité, Skill `verifier-mvc` & Compte Rendu

1. **Garantie d'Isofonctionnalité :**
   - Vérifier point par point que toutes les fonctionnalités existantes fonctionnent de manière rigoureusement identique :
     - Dashboard admin : affichage, ajout dynamique de lignes, suppression, upload d'images, enregistrement en BDD, messages flash.
     - Client public : terminal interactif et ses commandes, slider comparateur avant/après, animations WebGL/canvas, onglets du Lab, bascule thème dev/photo, liens de contact.
2. **Contrôle automatisé :**
   - **Lancer le skill `verifier-mvc`** pour vérifier l'étanchéité M/V/C, l'absence de DOM dans M, l'absence de foreach en PHP et la présence des commentaires JSDoc/PHPDoc.
3. **Rendre un compte rendu :**
   - Fonctions ajoutées ou modifiées dans M, V et C (en JS et en PHP).
   - Ce que l'utilisateur voit changer à l'écran.
   - Souligner les écarts au plan initial, s'il y en a.
   - **Aucun commit ni push Git** (la validation finale revient exclusivement à l'utilisateur).

---

## 🛑 Contraintes Inviolables

| Périmètre | Règle absolue |
|---|---|
| **Isofonctionnalité** | Le comportement visible et fonctionnel du site doit être 100% identique avant et après restructuration. |
| **Validation du plan** | Interdiction formelle de modifier des fichiers avant d'avoir présenté et fait valider le plan d'action. |
| **PHP - Boucles** | **Zéro `foreach`.** Utiliser exclusivement des fonctions de traitement de tableaux (`array_map`, `array_filter`, etc.). |
| **PHP - Typage** | Typage strict partout (`declare(strict_types=1);`, paramètres, retours, propriétés). |
| **JS - Étanchéité** | `M` ne touche jamais au DOM. `V` ne contient aucune logique métier. `C.init()` est l'unique point d'entrée. |
| **JS - Écriture** | Une fonction par propriété (`M.xxx = function(...)`), précédée d'un commentaire documentant paramètres et retour. |
| **Commentaires** | Autorisés et obligatoires en français sur JS (JSDoc), PHP (PHPDoc) et SQL. **Strictement interdits** dans le HTML et Tailwind CSS. |
| **Git** | Aucun commit, aucun push automatique. |
