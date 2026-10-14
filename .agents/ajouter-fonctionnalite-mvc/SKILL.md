---
name: ajouter-fonctionnalite-mvc
description: Ajoute ou modifie une fonctionnalité en respectant la double architecture MVC du projet (MVC Back-End PHP et MVC Front-End Vanilla JS), tout en appliquant les standards SEO, Accessibilité et Tailwind v4.
---

# Ajouter une fonctionnalité (Double MVC PHP / JS)

## Étapes

1. **Présenter un plan structuré qui range chaque ajout à sa place :**
   Avant d'écrire le moindre code, détailler l'intervention selon la grille d'architecture du projet :
   - **Back-End PHP (si concerné) :**
     - **M (Back) - Stockage/BDD :** Quel modèle PHP est touché (`app/models/Setting.php`, `app/models/Project.php` ou modèle dédié).
     - **C (Back) - Traitement/Logique :** Quel contrôleur pilote le flux (`AdminController.php`, `ApiController.php`, `HomeController.php`).
     - **V (Back) - Gabarit HTML :** Quelle vue ou partial PHP est modifiée (`app/views/front/partials/`, `app/views/admin/dashboard.php`).
   - **Front-End Vanilla JS (MVC Strict) :**
     - **M :** Données et fonctions qui les filtrent ou les transforment (aucun accès au DOM) ;
     - **V :** Lecture des templates, construction du HTML, écoute des événements (dans `V.init`) ;
     - **C :** Gestionnaires d'événements (`C.handler_...`) qui interrogent M puis appellent V.
   **Attendre la validation avant d'écrire.**

2. **Écrire le code Back-End (PHP) si applicable :**
   - Implémenter la logique dans le Contrôleur cible (vérification session, CSRF, etc.).
   - Utiliser `Setting.php` (ou le modèle adéquat) pour les interactions PDO.
   - Typage strict obligatoire (`declare(strict_types=1);`).
   - 🚫 **INTERDICTION ABSOLUE d'utiliser la boucle `foreach`** (remplacer par `array_map`, `array_filter`, etc.).
   - *Rappel d'exception :* Le script front de l'admin (`admin.js`) est procédural. Ne pas lui appliquer le pattern M/V/C front-end sauf demande explicite de refactorisation.

3. **Écrire le code Front-End (JavaScript) :**
   - Structurer selon les trois objets globaux `M`, `V`, `C`.
   - **Règle d'écriture :** Une fonction par propriété (`M.xxx = function(...)`, `V.xxx = function(...)`, `C.xxx = function(...)`), précédée d'un commentaire JSDoc qui indique ses paramètres et sa valeur de retour.
   - Identifier le flux d'interaction :
     - *Cas A (Clics d'interface purs - ex: Thème, Slider) :* `V` capte l'événement par délégation → `C` met à jour l'état dans `M` → `C` ordonne à `V` de rafraîchir.
     - *Cas B (Interactions réseau - ex: Terminal CLI) :* `V` capte → `C` ordonne à `M` de faire un `fetch()` vers l'API → `ApiController.php` (Back C) interroge `Setting.php` / `Project.php` (Back M) et renvoie `Response::json()` → `C` met à jour `V`.

4. **Appliquer les directives de Design et de Référencement (SEO/a11y) :**
   - **Tailwind v4 :** Approche Mobile First. Utiliser les variables sémantiques basées sur `[data-mode='dev ou photo']`.
   - **SEO & Média :** Intégrer JSON-LD dynamique, balises canoniques, balises de repère HTML5 (`<main>`, `<section>`), attributs natifs (`alt`, `width`, `height`, `loading="lazy"`) sur chaque image.
   - **Accessibilité :** Ajouter les attributs `aria-*` nécessaires.
   - **Règle absolue :** N'ajouter **AUCUN** commentaire dans les fichiers HTML ou sur les classes Tailwind CSS.

5. **Contrôler la conformité :**
   - Lancer le skill `verifier-mvc` pour valider l'étanchéité M/V/C et le respect des règles de codage.
   - Contrôler le comportement dans le navigateur (rendu Tailwind, interactions JS, validité W3C du HTML généré, Core Web Vitals).

6. **Rendre un compte rendu :**
   - Lister les fonctions ajoutées/modifiées dans M, V et C (Front et Back le cas échéant).
   - Décrire ce que l'utilisateur voit changer à l'écran.
   - Souligner les écarts au plan initial, s'il y en a.

## Contraintes Absolues

- **Étanchéité MVC Front :** Le Modèle ne touche jamais au DOM. La Vue ne contient aucune logique métier. `C.init()` est l'unique point d'entrée.
- **PHP :** Typage strict obligatoire. **INTERDICTION ABSOLUE** d'utiliser la boucle `foreach`.
- **Commentaires :** Obligatoires et exclusifs au JS (JSDoc), PHP (PHPDoc) et SQL. En français. Totalement **interdits** dans le HTML et Tailwind CSS.
- **Dépendances Front :** Vanilla JavaScript uniquement.
- **Gestion de version :** Ne jamais générer de commit ni exécuter de push. La validation finale appartient à l'utilisateur.
