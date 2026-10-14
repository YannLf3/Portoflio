# Comment fonctionne le terminal interactif

Guide pédagogique du terminal (Easter egg) du portfolio : le bouton `>_ terminal` en bas de page et la fenêtre qui s'ouvre avec ⌘K / Ctrl+K.

---

## 0. Vue d'ensemble

### 0.1 Ce que fait le terminal

Un faux terminal `zsh` dans lequel le visiteur tape des commandes (`whoami`, `skills`, `projects`, `goto contact`, `theme photo`, `sudo hire-yann`…). C'est du **théâtre** : il n'exécute rien sur ton serveur. Chaque commande est une fonction JavaScript qui renvoie du texte à afficher.

### 0.2 Les fichiers concernés

| Couche | Fichier | Rôle |
|---|---|---|
| HTML | [footer.php:46-84](app/views/layouts/footer.php#L46-L84) | Bouton lanceur, `<dialog>` du terminal, 3 gabarits `<template>` |
| CSS | [input.css](src/css/input.css) (section « 7. TERMINAL CLI », classes `.cli*`) | Apparence |
| JS | [terminal.js](public/js/terminal.js) | Tout le comportement |
| Route | [index.php:57](public/index.php#L57) | Route `/api/terminal` |
| API PHP | [ApiController.php](app/controllers/ApiController.php) | Renvoie les données du portfolio en JSON |
| Soutien | [theme-toggle.js](public/js/theme-toggle.js) | Reçoit l'événement `mode:request` pour la commande `theme` |

### 0.3 Le chemin d'une commande, en 6 étapes

```
1. L'utilisateur tape "skills" + Entrée
        │
2. C.handleSubmit  (Contrôleur)  intercepte le formulaire
        │
3. C.run           affiche la ligne tapée, l'ajoute à l'historique
        │
4. M.execute       (Modèle)  trouve la commande "skills"
        │            └─ si elle a besoin de données : fetch GET /api/terminal
        │                       └─ ApiController → base de données → JSON
        │
5. commands.skills.run()  renvoie { lines: [...], effect?: ... }   ← des DONNÉES, jamais du HTML
        │
6. V.renderLines   (Vue)  transforme les lignes en HTML et les affiche
   C.applyEffect   exécute l'éventuel effet (clear, close, theme, goto, sequence)
```

### 0.4 Organisation M / V / C

| Objet | Contient | Ne fait jamais |
|---|---|---|
| **M** (Modèle) | Registre des commandes, historique, appel réseau, autocomplétion | Toucher au DOM |
| **V** (Vue) | `<dialog>`, rendu des lignes, écouteurs, échappement HTML | Décider quoi faire |
| **C** (Contrôleur) | Handlers, enchaînement M → V, application des effets | — |

Règle clé : **une commande ne modifie jamais la page elle-même.** Elle renvoie un **effet** (`{type: 'goto', target: 'projects'}`) que le Contrôleur applique. C'est ce qui sépare M et V.

---

## 1. Le HTML ([footer.php](app/views/layouts/footer.php#L46))

### 1.1 Trois éléments

| Élément | Rôle |
|---|---|
| `<button id="cli-launcher">` | Bouton flottant. Contient `>_`, « terminal » et un `<kbd>` du raccourci (le libellé « ⌘ K » ou « Ctrl K » est rempli par le JS selon la plateforme) |
| `<dialog id="cli" closedby="any">` | La fenêtre du terminal |
| `<template id="tpl-cli-*">` ×3 | Gabarits HTML réutilisables pour une ligne, un lien, une « chip » cliquable |

### 1.2 Pourquoi un `<dialog>` ?

`dialog.showModal()` donne **gratuitement** :

| Fonction native | Bénéfice |
|---|---|
| Couche « top layer » | Passe au-dessus de tout, pas de `z-index` à gérer |
| Focus piégé | Tab ne sort pas du terminal |
| Touche Échap | Ferme la fenêtre |
| Retour du focus | Au bouton lanceur à la fermeture |
| `closedby="any"` | Ferme aussi au clic extérieur et au geste retour mobile |

Safari ne gère pas encore `closedby` : le JS fournit un **repli** (`isBackdropClick`, [terminal.js:642](public/js/terminal.js#L642)) qui détecte un clic hors de la boîte.

### 1.3 Structure du `<dialog>`

| Zone | Contenu |
|---|---|
| `.cli__bar` | Les 3 pastilles rouge/jaune/vert, le titre `visiteur@yannleflohic.fr: ~ (zsh)`, bouton ✕ |
| `#cli-output` | Zone de sortie. `role="log" aria-live="polite"` : les lecteurs d'écran annoncent les nouvelles lignes |
| `#cli-form` + `#cli-input` | Invite `visiteur@yannleflohic.fr:~$` et champ de saisie |

---

## 2. L'ouverture du terminal

| Déclencheur | Code |
|---|---|
| Clic sur le bouton | `V.el.launcher` → `C.toggle` ([terminal.js:835](public/js/terminal.js#L835)) |
| ⌘K / Ctrl+K | `document` `keydown` → `C.handleShortcut` ([terminal.js:860](public/js/terminal.js#L860)) |

`handleShortcut` vérifie : touche Cmd ou Ctrl enfoncée, ni Alt ni Maj, touche `k`. Puis `e.preventDefault()` (supprime l'action native du navigateur sur ce raccourci) et `toggle()`. `!e.repeat` empêche l'ouverture/fermeture en rafale si la touche reste enfoncée.

`C.open()` ([terminal.js:844](public/js/terminal.js#L844)) fait 4 choses :

1. `V.open()` → `dialog.showModal()`.
2. **Première ouverture seulement** (`M.state.booted`) : affiche la bannière d'accueil (art ASCII « YLF »).
3. Donne le focus au champ de saisie.
4. **Précharge** les données (`M.loadData()`), pour que la première commande réponde sans attente.

---

## 3. Le Modèle : les commandes

### 3.1 La forme d'une commande

Toutes sont dans l'objet `M.commands` ([terminal.js:157](public/js/terminal.js#L157)) :

```js
whoami: {
    desc: 'Qui est Yann ?',     // texte affiché par "help" (sans desc = commande cachée)
    needsData: true,            // cette commande attend les données de l'API
    run(args, {data}) {         // reçoit les arguments + le contexte
        return { lines: [ ... ], effect: ... };   // effect est optionnel
    }
}
```

### 3.2 La « ligne » : l'unité d'affichage

Une ligne est un objet `{type, segments}`, fabriqué par `M.line(type, ...segments)`.

| Type | Rendu |
|---|---|
| `text` | Texte normal |
| `accent` | Titre / mise en avant (couleur d'accent) |
| `muted` | Texte discret |
| `error` | Erreur |
| `success` | Succès |
| `command` | Écho de ce que l'utilisateur a tapé |
| `ascii` | Art ASCII |

Chaque **segment** de la ligne est de l'un de ces trois types :

| Segment | Fabrique | Rendu |
|---|---|---|
| Chaîne | `'texte'` | Texte échappé |
| Lien | `M.link(href, label)` | `<a>` cliquable |
| Commande cliquable | `M.cmd('goto skills')` | Bouton « chip » qui **rejoue** la commande (pratique au tactile) |

Exemple (commande `contact`) :
```js
M.line('text', `  ${contact.label.padEnd(11)}`, M.link(contact.href, contact.value))
//             └ segment texte (aligné)         └ segment lien
```

### 3.3 Catalogue des commandes

| Commande | Données API ? | Ce qu'elle fait | Effet renvoyé |
|---|---|---|---|
| `help` | non | Liste les commandes qui ont une `desc` | — |
| `whoami` | oui | Nom, lieu, formation, disponibilité, centres d'intérêt | — |
| `skills` | oui | Langages, outils, langues avec **barre ASCII** `█████░░░░░` | — |
| `projects` | oui | Projets numérotés, titres cliquables | — |
| `contact` | oui | Email, GitHub, LinkedIn, Malt | — |
| `ls` | non | Sections sous forme de chips (`about/ skills/ …`) | — |
| `goto <section>` | non | Valide la section (`about`, `skills`, `projects`, `contact`, `lab`) | `{type:'goto'}` |
| `theme dev\|photo` | non | Valide le mode demandé | `{type:'theme'}` |
| `history` | non | Rejoue la liste des commandes tapées | — |
| `date` | non | Date/heure locales (`toLocaleString('fr-FR')`) | — |
| `echo <texte>` | non | Répète le texte | — |
| `clear` | non | — | `{type:'clear'}` |
| `exit` | non | — | `{type:'close'}` |
| `sudo hire-yann` | oui | Séquence animée « recrutement » | `{type:'sequence'}` |

**Commandes cachées** (pas de `desc`, donc absentes de `help` et de l'autocomplétion) : `hire-yann` (refus de permission), `rm` (« lecture seule »), `vim` (« tapez exit »).

### 3.4 Exécution : `M.execute()` ([terminal.js:433](public/js/terminal.js#L433))

```js
const {name, args} = this.parse(raw);                 // "goto projects" → name "goto", args ["projects"]
const command = Object.hasOwn(this.commands, name) ? this.commands[name] : null;
```

| Cas | Résultat |
|---|---|
| Commande inconnue | `zsh: commande introuvable : xxx` + suggestion `help` |
| Commande à données, API HS | `Impossible de joindre /api/terminal…` |
| Sinon | `command.run(args, {...ctx, data})` |

> **Pourquoi `Object.hasOwn` ?** Sans lui, taper `constructor` ou `toString` trouverait des propriétés héritées de tout objet JavaScript et ferait planter le terminal. `hasOwn` ne regarde que les commandes réellement définies.

### 3.5 Le chargement des données : `M.loadData()` ([terminal.js:125](public/js/terminal.js#L125))

```js
if (this.state.data) return this.state.data;          // déjà en cache → on réutilise
if (this.state.pending) return this.state.pending;    // requête en vol → on partage la même promesse
this.state.pending = (async () => { ... fetch('/api/terminal') ... })();
```

| Mécanisme | Intérêt |
|---|---|
| Cache `state.data` | Une seule requête par chargement de page |
| Promesse partagée `state.pending` | Pas de doublon si deux commandes s'enchaînent vite |
| `finally { pending = null }` | Après échec, la commande suivante **retente** |
| `fetch` + `async/await` | Conforme aux conventions du projet (appel réseau dans le Modèle) |

### 3.6 L'autocomplétion (Tab) : `M.complete()` ([terminal.js:461](public/js/terminal.js#L461))

| Saisie | Résultat |
|---|---|
| `sk` + Tab | Complété en `skills ` (un seul candidat) |
| `s` + Tab | Plusieurs candidats (`skills`, `sudo`) → ils sont **listés** sous le prompt |
| `goto pro` + Tab | `goto projects` |
| `theme p` + Tab | `theme photo` |
| `sudo h` + Tab | `sudo hire-yann` |
| `hire` + Tab | Rien : les commandes cachées restent secrètes |

### 3.7 L'historique

| Action | Code |
|---|---|
| Stockage | `state.history` (50 max, `historyMax`), sans doublon **consécutif** ([terminal.js:484](public/js/terminal.js#L484)) |
| ↑ | `previousEntry()` remonte dans la liste |
| ↓ | `nextEntry()` redescend ; au-delà de la dernière entrée = champ vide |
| Fermeture | Le curseur d'historique est remis à la fin |

L'historique vit **en mémoire** : il disparaît au rechargement de la page.

---

## 4. L'API ([ApiController.php](app/controllers/ApiController.php))

### 4.1 Rôle

`GET /api/terminal` renvoie du JSON construit depuis la table des réglages (`Setting::getAll()`). **Les données du terminal sont donc les mêmes que celles du site** et suivent ce que tu modifies dans le back-office.

### 4.2 Exemple de réponse

```json
{
  "name": "Yann Le Flohic",
  "status": "Disponible pour un stage · 8-12 semaines",
  "location": "Châteauroux — FR",
  "school": "2° Année MMI",
  "skills": ["HTML / CSS", "JavaScript", "..."],
  "tools": ["VS CODE", "FIGMA", "..."],
  "interests": ["Open Source", "..."],
  "languages": [{"name": "Français", "level": "Natif", "percent": 100}],
  "contacts": [{"label": "EMAIL", "value": "...", "href": "mailto:..."}],
  "projects": [{"title": "...", "meta": "...", "category": "...", "link": "https://..."}]
}
```

### 4.3 Sécurité de l'API

| Mesure | Détail |
|---|---|
| **Liste blanche** | Seules les clés écrites explicitement sont exposées. D'autres réglages en base ne sortent **jamais** du serveur |
| **Filtrage des URL** | `safeUrl()` n'accepte que `http`, `https`, `mailto`, `tel` : bloque `javascript:` et `data:` |
| **Valeurs de repli** | Si une clé manque en base, des valeurs par défaut sont renvoyées |
| **Pourcentages bornés** | `max(0, min(100, …))` |
| **Erreur SQL** | Détail dans le log serveur, message générique `503` au client |
| **Cache** | 300 s côté navigateur (`CACHE_SECONDS`) |
| **Sortie** | `Response::json()` centralisé : code HTTP, en-têtes, puis `exit` |

---

## 5. La Vue : afficher les lignes

### 5.1 Les gabarits `<template>`

Définis dans [footer.php](app/views/layouts/footer.php) :

```html
<template id="tpl-cli-line"><p class="cli__line cli__line--{{type}}">{{content}}</p></template>
<template id="tpl-cli-link"><a class="cli__link" href="{{href}}"{{target}}>{{label}}</a></template>
<template id="tpl-cli-chip"><button type="button" class="cli__chip" data-cli-run="{{command}}">{{label}}</button></template>
```

`V.init()` copie leur contenu dans `V.tpl`. Les `{{…}}` sont des **trous** à remplir.

### 5.2 Remplissage par chaîne : `V.fill()` ([terminal.js:560](public/js/terminal.js#L560))

```js
template.replace(/\{\{(\w+)\}\}/g, (match, key) => values[key] ?? '')
```

Remplace tous les `{{clé}}` en **une seule passe**, avec une **fonction** de remplacement. Deux pièges évités :

| Piège | Pourquoi une fonction l'évite |
|---|---|
| Motifs spéciaux `$&`, `$'` dans une valeur | Avec une chaîne de remplacement, JS les interpréterait |
| `{{…}}` présent dans une valeur déjà insérée | Une seule passe : la valeur insérée n'est pas re-scannée |

### 5.3 Échappement : la protection contre le XSS

**Toute donnée est passée par `V.escape()`** avant d'entrer dans un gabarit : `& < > " '` deviennent `&amp; &lt; &gt; &quot; &#39;`. Si un projet s'appelait `<script>alert(1)</script>`, il s'afficherait en texte, sans s'exécuter.

### 5.4 Construction du rendu : `V.renderLines()` ([terminal.js:708](public/js/terminal.js#L708))

```js
let html = '';
lines.forEach((line) => {
    const content = line.segments.map((s) => this.renderSegment(s)).join('');
    html += this.fill(this.tpl.line, {type: ..., content});
});
this.el.output.insertAdjacentHTML('beforeend', html);   // une seule insertion
this.el.output.scrollTop = this.el.output.scrollHeight;  // auto-scroll en bas
```

| Choix | Raison |
|---|---|
| HTML accumulé dans une chaîne | Un seul accès au DOM |
| `insertAdjacentHTML('beforeend')` | **Ajoute** sans recréer le contenu existant (contrairement à `innerHTML +=`) |
| Auto-scroll | La dernière ligne reste visible |

### 5.5 Liens et sécurité d'ouverture

Dans `renderSegment` ([terminal.js:683](public/js/terminal.js#L683)) :

| Type de lien | Comportement |
|---|---|
| `http(s)://…` | `target="_blank" rel="noopener noreferrer"` (nouvel onglet, sans accès à `window.opener`) |
| `mailto:` / `tel:` | Reste dans l'onglet courant |

---

## 6. Le Contrôleur : événements et effets

### 6.1 Tous les écouteurs ([terminal.js:595](public/js/terminal.js#L595))

| Écouteur | Handler | Rôle |
|---|---|---|
| clic sur le bouton | `C.toggle` | Ouvrir / fermer |
| `keydown` document | `C.handleShortcut` | ⌘K / Ctrl+K |
| `submit` formulaire | `C.handleSubmit` | Entrée = valider |
| `keydown` champ | `C.handleKeyDown` | ↑ ↓ Tab Ctrl+L |
| `click` dialog | `C.handleDialogClick` | **Délégation** : chips, bouton ✕, clic backdrop, clic dans la sortie |
| `close` dialog | `C.handleClose` | Remise à zéro de l'état ARIA et de l'historique |

Le clic sur les chips utilise la **délégation d'événements** : un seul écouteur sur le `<dialog>`, qui cherche `[data-cli-run]` via `closest()`. Les chips étant créées dynamiquement à chaque commande, un écouteur par chip serait perdu ou à recréer.

### 6.2 Raccourcis clavier

| Touche | Action | Où |
|---|---|---|
| ⌘K / Ctrl+K | Ouvrir / fermer | [terminal.js:860](public/js/terminal.js#L860) |
| Entrée | Exécuter | `handleSubmit` |
| ↑ / ↓ | Historique | [terminal.js:973-978](public/js/terminal.js#L973-L978) |
| Tab | Autocomplétion | [terminal.js:979](public/js/terminal.js#L979) |
| Ctrl+L | Effacer | [terminal.js:985](public/js/terminal.js#L985) |
| Échap | Fermer | Natif (`<dialog>`) |

`handleKeyDown` s'arrête immédiatement si `state.busy` est vrai (voir la séquence § 7).

### 6.3 `C.run()` ([terminal.js:884](public/js/terminal.js#L884))

```js
V.renderLines([M.line('command', entry || ' ')]);   // 1. écho de la commande tapée
if (!entry) return;                                  // 2. ligne vide : rien d'autre
M.pushHistory(entry);                                // 3. mémorise
const result = await M.execute(entry, {currentMode: V.getTheme()});   // 4. exécute
V.renderLines(result.lines);                         // 5. affiche
if (result.effect) await C.applyEffect(result.effect);               // 6. effet
```

Le contexte `{currentMode}` est lu côté Vue puis donné au Modèle : le Modèle ne lit jamais le DOM lui-même.

### 6.4 Les 5 effets : `C.applyEffect()` ([terminal.js:900](public/js/terminal.js#L900))

| Effet | Action |
|---|---|
| `clear` | `V.clear()` vide la sortie |
| `close` | `V.close()` ferme le dialogue |
| `theme` | Envoie l'événement `mode:request` → `theme-toggle.js` bascule Dev / Photo (même transition et même mémorisation que l'interrupteur) |
| `goto` | Voir ci-dessous |
| `sequence` | Joue une série de lignes temporisées |

**`goto`** ([terminal.js:925](public/js/terminal.js#L925)) :

| Cas | Comportement |
|---|---|
| Cible `lab` | Navigation vers `/lab` |
| La section existe sur la page courante | Ferme le terminal, puis défilement (animé, sauf mouvement réduit) |
| La section n'existe pas ici (ex. on est sur `/lab` et on tape `goto skills`) | Navigation vers `/#skills` |

---

## 7. La séquence `sudo hire-yann`

### 7.1 Ce que voit l'utilisateur

```
[sudo] mot de passe pour visiteur : ••••••••
✔ Authentification réussie.
Compilation du profil de Yann Le Flohic…
  ▸ Front-end (JS, Tailwind) .......... OK
  ▸ Back-end (PHP, MySQL) ............. OK
  ▸ Œil photo & sens du détail ........ OK
  ▸ Motivation ........................ 200 %
  [art ASCII « HIRED »]
🚀 Accès accordé : Yann est prêt à rejoindre votre équipe.
Disponibilité : <statut>
Contact direct → <email>  ·  LinkedIn
```

### 7.2 Comment c'est fait

Le Modèle construit une liste d'**étapes** `{delay, line}` avec `M.hireSequence(data)` ([terminal.js:379](public/js/terminal.js#L379)). Le Contrôleur les joue avec `C.playSequence` ([terminal.js:944](public/js/terminal.js#L944)) :

```js
M.state.busy = true;  V.setBusy(true);          // verrouille la saisie
for (const step of steps) {
    await C.wait(reducedMotion ? 0 : step.delay);   // pause (promesse + setTimeout)
    V.renderLines([step.line]);                      // affiche UNE ligne
}
M.state.busy = false; V.setBusy(false); V.focusInput();  // déverrouille
```

| Détail | Explication |
|---|---|
| `busy` | Empêche de taper une autre commande pendant la séquence (`handleSubmit`, `handleKeyDown` et les chips vérifient tous `state.busy`) |
| `readOnly` plutôt que `disabled` | Le champ **garde le focus clavier** |
| Classe `is-busy` | Permet de styler l'état en CSS |
| Mouvement réduit | Délai de 0 : tout s'affiche d'un coup |
| Email / LinkedIn | Cherchés dans les contacts de l'API (`mailto:` pour l'email, regex `/linkedin/i`) |

---

## 8. Accessibilité

| Mesure | Détail |
|---|---|
| `aria-haspopup`, `aria-controls`, `aria-expanded` | Sur le bouton ; `expanded` passe à `true` à l'ouverture, `false` à la fermeture |
| `role="log"` + `aria-live="polite"` | Les lecteurs d'écran annoncent les nouvelles lignes |
| `aria-labelledby` | Le dialogue est nommé par son titre |
| `<label for="cli-input">` | Le prompt sert de libellé au champ |
| Focus | Piégé dans la modale, rendu au bouton à la fermeture |
| Mouvement réduit | Séquences sans délai, défilement `goto` sans animation |
| Tactile | Les chips permettent d'utiliser le terminal sans clavier |

---

## 9. Étendre le terminal

### 9.1 Ajouter une commande simple

Dans `M.commands` ([terminal.js:157](public/js/terminal.js#L157)) :

```js
/**
 * Affiche l'URL du dépôt GitHub.
 */
github: {
    desc: 'Ouvrir mon GitHub',            // visible dans help ; retirer pour la cacher
    run() {
        return {lines: [M.line('text', 'Mon code : ', M.link('https://github.com/YannLf3', 'github.com/YannLf3'))]};
    }
},
```

C'est tout : `help` et l'autocomplétion la prennent en compte automatiquement.

### 9.2 Ajouter une commande qui utilise les données

Ajouter `needsData: true`, puis lire `data` dans `run(args, {data})`. Si le champ n'existe pas dans l'API, l'ajouter d'abord dans `ApiController::terminal()` (liste blanche).

### 9.3 Ajouter une commande qui agit sur la page

1. Elle renvoie `effect: {type: 'monEffet', ...}`.
2. Ajouter `case 'monEffet':` dans `C.applyEffect` ([terminal.js:900](public/js/terminal.js#L900)).
3. Si le DOM est touché, créer la méthode correspondante dans `V`.

### 9.4 Ajouter une section à `goto`

Ajouter son identifiant dans `M.config.sections` ([terminal.js:33](public/js/terminal.js#L33)). La section doit exister dans le HTML avec cet `id`.

### 9.5 Modifier l'apparence

Les classes `.cli`, `.cli__bar`, `.cli__output`, `.cli__line--error`, `.cli__chip`… sont dans la section « 7. TERMINAL CLI » de [input.css](src/css/input.css). Modifier `src/css/input.css` (jamais le `style.css` compilé) puis recompiler Tailwind.

---

## 10. Diagnostics

| Symptôme | Diagnostic | Solution |
|---|---|---|
| Le bouton n'apparaît pas | `footer.php` non inclus, ou `terminal.js` en erreur | Console : chercher une erreur ; `V.init()` retourne `false` si un élément manque |
| ⌘K ouvre la recherche du navigateur | `terminal.js` non chargé | Vérifier le `<script defer>` dans `footer.php` |
| `Impossible de joindre /api/terminal` | Route absente, erreur SQL, serveur arrêté | Ouvrir `/api/terminal` dans le navigateur ; lire le log PHP (`error_log`) |
| `commande introuvable` sur une commande qui existe | Faute de frappe, ou clé mal nommée dans `M.commands` | Vérifier l'orthographe ; les noms sont mis en minuscules |
| `goto` ne défile pas | `id` de section absent sur la page | Ajouter l'`id`, ou ajouter la cible dans `M.config.sections` |
| `theme photo` sans effet | `theme-toggle.js` non chargé (il écoute `mode:request`) | Vérifier le script dans `header.php` |
| Saisie bloquée | `state.busy` resté `true` (séquence interrompue par une erreur) | Recharger la page ; chercher l'erreur en console |
| Données obsolètes dans le terminal | Cache API de 300 s | Attendre 5 min ou recharger en vidant le cache |
| Modification JS sans effet | Cache navigateur | Changer `?v=20261005` dans `footer.php` |
| Clic hors fenêtre ne ferme pas (Safari) | `closedby` non supporté | Le repli JS le gère ; vérifier `handleDialogClick` |

---

## 11. Récapitulatif

```
 ⌘K / clic bouton ──► <dialog>.showModal()  ──► bannière (1re fois) + préchargement API
                                                        │
 Saisie + Entrée ──► C.handleSubmit ──► C.run ──► M.execute ──► commande.run()
                                                        │              │
                                              (needsData ?)            ▼
                                              fetch /api/terminal   { lines, effect? }
                                              ApiController (JSON)         │
                                                                           ▼
                                    V.renderLines (échappement + gabarits) + C.applyEffect
                                                                           │
                                          clear · close · theme · goto · sequence
```

| Notion | À retenir |
|---|---|
| Commande | Fonction du Modèle qui renvoie des **données** (`lines` + `effect`) |
| Vue | Seule à toucher au DOM ; échappe toujours le texte |
| Contrôleur | Fait le lien et applique les effets |
| Données | Une requête `/api/terminal`, mise en cache, liste blanche côté PHP |
| Sécurité | Échappement HTML, liens filtrés par schéma, `rel="noopener"`, `Object.hasOwn` |
