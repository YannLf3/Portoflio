# Comment fonctionnent les animations du site

Guide pédagogique des trois effets visuels du portfolio :

1. Les **points de fond** qui s'écartent et se relient autour du curseur.
2. Les **traits** qui se dessinent sous les titres.
3. Le **glitch** sur « Yann Le Flohic » dans le header.

---

## 0. Vue d'ensemble : qui fait quoi ?

### 0.1 Les fichiers concernés

| Effet | HTML (où l'élément existe) | CSS (l'apparence) | JS (le cerveau) |
|---|---|---|---|
| Points de fond | [header.php:114](app/views/layouts/header.php#L114) : `<canvas id="fx-particles">` | [input.css:548-569](src/css/input.css#L548-L569) | [animations.js](public/js/animations.js) (tout le moteur) |
| Traits sous les titres | [home.php:13](app/views/front/home.php#L13), [lab.php](app/views/front/lab.php) : classes `reveal-title` | [input.css:821-872](src/css/input.css#L821-L872) | [animations.js:1284-1306](public/js/animations.js#L1284-L1306) (déclenchement au scroll) |
| Glitch du logo | [header.php:121](app/views/layouts/header.php#L121) : classe `glitch` + `data-glitch` | [input.css:945-988](src/css/input.css#L945-L988) et [input.css:1784-1890](src/css/input.css#L1784-L1890) | **Aucun JS** : 100 % CSS |

> `public/css/style.css` est le fichier **compilé** par Tailwind à partir de `src/css/input.css`.
> On modifie toujours `src/css/input.css`, jamais `style.css`.

### 0.2 Les fichiers qui n'ont PAS de rapport avec ces effets

| Fichier | Rôle réel |
|---|---|
| [terminal.js](public/js/terminal.js) | Le terminal interactif (Easter egg, raccourci ⌘K / Ctrl+K). Aucun lien avec les points, traits ou glitch. |
| [photo-mode.js](public/js/photo-mode.js) | Visionneuse de la galerie et comparateur avant/après du mode Photo. Aucun lien non plus. |

Deux autres fichiers jouent un rôle de **soutien** :

| Fichier | Ce qu'il apporte aux animations |
|---|---|
| [main.js](public/js/main.js) | Joue le loader du début, puis envoie le signal `app:ready` (« tu peux démarrer les animations de titres »). |
| [theme-toggle.js](public/js/theme-toggle.js) | Bascule Dev ↔ Photo en changeant l'attribut `data-mode` sur `<html>`. Les animations réagissent à ce changement. |

### 0.3 Le principe général (à retenir)

> **Le JavaScript ne dessine presque rien lui-même : il pose des classes et des variables, et le CSS fait l'animation.**
> Seul le fond de points est dessiné directement par le JS (via WebGL).

| Effet | Qui anime ? |
|---|---|
| Points + traits entre eux | JS + GPU (WebGL), image par image |
| Traits sous les titres | **CSS** (transition), déclenché par une classe ajoutée par le JS |
| Glitch du logo | **CSS seul** (`:hover` + `@keyframes`) |

### 0.4 Organisation du code JS : M / V / C

`animations.js` suit le pattern MVC demandé dans le projet. Trois objets :

| Objet | Rôle | Interdit |
|---|---|---|
| **M** (Modèle) | Réglages, calculs de physique et de géométrie | Toucher au DOM |
| **V** (Vue) | Lire/écrire le DOM, dessiner en WebGL, déclarer les écouteurs | Faire de la logique |
| **C** (Contrôleur) | Reçoit les événements, appelle M puis V | — |

Le Modèle et la Vue ne se parlent **jamais** directement : tout passe par C. Le tout est dans une IIFE `(() => { ... })()` pour ne pas polluer la portée globale, et `C.init()` est appelé tout à la fin ([animations.js:1400](public/js/animations.js#L1400)).

---

## 1. Le fond de points et les connexions autour du curseur

### 1.1 Ce que tu vois

- Des points lime qui dérivent lentement et scintillent.
- Quand la souris approche, les points proches **s'écartent** comme repoussés.
- Les points restés dans la zone de la souris se **relient entre eux par des traits fins**. Cela dessine des triangles / polygones qui suivent le curseur.

> Précision : les points ne « se déplacent pas pour former une forme ». Ce sont **des traits qui apparaissent entre points voisins**. Ces traits composent les formes géométriques que tu vois.

### 1.2 Les 4 ingrédients

| # | Ingrédient | Où |
|---|---|---|
| 1 | Un `<canvas>` plein écran, placé derrière le contenu | [header.php:114](app/views/layouts/header.php#L114) |
| 2 | Une liste de particules avec leur physique | `M.particles` ([animations.js:35-288](public/js/animations.js#L35-L288)) |
| 3 | Un rendu WebGL (dessin par la carte graphique) | `V.particles` ([animations.js:35-730](public/js/animations.js#L35-L730)) |
| 4 | Une boucle qui répète « calcule puis dessine » 60 fois par seconde | `C.tick` ([animations.js:1241](public/js/animations.js#L1241)) |

### 1.3 Le canvas (la « toile »)

```html
<canvas id="fx-particles" class="fx-particles fx-particles--idle" aria-hidden="true"></canvas>
```

Et son CSS ([input.css:548](src/css/input.css#L548)) :

| Propriété | Pourquoi |
|---|---|
| `position: fixed; inset: 0` | Couvre tout l'écran, ne bouge pas au scroll |
| `z-index: -1` | Passe **derrière** tout le contenu |
| `pointer-events: none` | Il ne bloque jamais un clic (la souris est lue sur `window`) |
| `--_opacity: 0` (classe `--idle`) | Invisible au départ ; le JS retire la classe après la 1re image → fondu de 1,2 s |
| `[data-mode='photo']` → opacité 0 | Pas de points en mode Photo |

### 1.4 Une particule = un petit objet

Créée par `spawn()` ([animations.js:117](public/js/animations.js#L117)) :

| Champ | Signification |
|---|---|
| `bx, by` | **Position de base** : là où la particule « devrait » être (elle dérive) |
| `vx, vy` | Vitesse de dérive (3 à 12 px/s, très lent), direction aléatoire |
| `dx, dy` | **Décalage** causé par la souris (0 au repos) |
| `ux, uy` | Vitesse de ce décalage |
| `size`, `alpha` | Taille (1,6 à 3,4 px) et transparence |
| `twinkle`, `phase` | Rythme et décalage du scintillement |

**Position affichée = base + décalage** → `bx + dx`.

C'est l'idée clé : la souris ne déplace jamais la position de base, seulement le décalage. Quand la souris part, le décalage revient à 0 et la particule retrouve sa trajectoire.

### 1.5 Combien de particules ?

```js
nombre = (largeur × hauteur) / 12000   // borné entre 40 et 160
```

Exemple : un écran 1440×900 donne 1 296 000 / 12 000 ≈ 108 points. La densité reste constante quelle que soit la taille de la fenêtre.

Les points sont conservés lors d'un redimensionnement (leurs positions sont mises à l'échelle), donc pas de « saut » visuel ([animations.js:153-166](public/js/animations.js#L153-L166)).

### 1.6 Chaque image : la fonction `step()` ([animations.js:186](public/js/animations.js#L186))

Pour **chaque** particule, dans l'ordre :

**Étape A. La dérive**
```js
p.bx += p.vx * dt;   // dt = temps écoulé depuis la dernière image (en secondes)
p.by += p.vy * dt;
```
Si le point sort d'un bord, il réapparaît du côté opposé (« rebouclage »).

**Étape B. Le ressort** (ramène vers la trajectoire)
```js
let ax = -p.dx * cfg.spring;   // plus le décalage est grand, plus la force de rappel est forte
```
Comme un élastique accroché entre le point décalé et sa position de base.

**Étape C. La répulsion de la souris** (seulement si la souris est dans la fenêtre)
```js
d = distance point ↔ souris
si d < 140 px :
    falloff = 1 - d / 140        // 1 = collé à la souris, 0 = au bord de la zone
    force   = falloff² × 650     // plus on est près, beaucoup plus fort
    → le point est poussé dans la direction opposée à la souris
```

| Distance à la souris | `falloff` | Force |
|---|---|---|
| 0 px (sur le curseur) | 1 | maximale (650) |
| 70 px (moitié) | 0,5 | 0,25 × 650 ≈ 162 |
| 140 px ou plus | 0 | aucune |

Le **carré** (`falloff²`) rend la poussée douce au bord et brutale au centre : c'est ce qui donne un effet « bulle » naturel.

**Étape D. Appliquer les forces avec de l'inertie**
```js
p.ux = (p.ux + ax * dt) * damp;   // la force change la vitesse
p.dx += p.ux * dt;                // la vitesse change la position
```
`damp` (0,9 par image à 60 fps) freine la particule : sans lui, elle oscillerait indéfiniment comme un ressort sans frottement. `Math.pow(damping, dt * 60)` rend ce freinage identique que l'écran soit à 60 Hz ou 120 Hz.

**Étape E. Le scintillement**
```js
twinkle = 0.7 + 0.3 * Math.sin(temps * fréquence + phase)   // oscille entre 0,7 et 1
```

**Étape F. Écrire le résultat** dans un tableau `pointData` : `[x, y, taille, alpha]` par point, prêt pour la carte graphique.

### 1.7 Les traits entre points : `buildLinks()` ([animations.js:252](public/js/animations.js#L252))

C'est ici que naissent les « formes ». Deux étapes :

**Étape 1. Présélection** : on garde les points à moins de **190 px** de la souris (max 48 points). Chacun reçoit un score de proximité (1 = sur la souris, 0 = au bord).

**Étape 2. Appariement** : pour chaque paire de points présélectionnés, si leur distance est **inférieure à 100 px**, on crée un segment.

```
opacité du trait = (1 - distance_entre_points / 100)
                 × min(proximité_A, proximité_B)
                 × 0.6
```

| Cas | Résultat |
|---|---|
| Deux points collés, près de la souris | Trait bien visible |
| Deux points éloignés l'un de l'autre | Trait presque transparent |
| Deux points loin de la souris | Pas de trait du tout |

Si la souris n'est pas dans la fenêtre : `lineVertexCount = 0`, aucun trait.

> **Pourquoi présélectionner ?** Comparer toutes les paires de 160 points = ~12 700 comparaisons par image. En limitant à 48 candidats, on tombe à 1 128 maximum. C'est la raison de `maxLinkCandidates`.

Les points repoussés par la souris laissent un « trou » au centre, et les traits relient les points du **pourtour** : on obtient un réseau qui entoure le curseur.

### 1.8 Le dessin avec WebGL

WebGL envoie des données à la carte graphique (GPU) qui dessine des milliers d'éléments en parallèle, bien plus vite que le CSS ou un canvas 2D.

| Pièce | Rôle ([animations.js:537-542](public/js/animations.js#L537-L542)) |
|---|---|
| **Vertex shader** | Convertit les pixels écran (0 → largeur) en coordonnées GPU (-1 → +1), et fixe la taille du point |
| **Fragment shader** | Colorie chaque pixel. Pour un **point** : un disque à bord flou (`smoothstep`). Pour une **ligne** : un trait plein. |
| **Buffers** | Deux « boîtes » de données envoyées au GPU : une pour les points, une pour les lignes |

`draw()` ([animations.js:729](public/js/animations.js#L729)) fait, à chaque image :
1. Efface le canvas.
2. Envoie les traits au GPU et les dessine (`gl.LINES`) **d'abord**, pour qu'ils passent sous les points.
3. Envoie les points et les dessine (`gl.POINTS`).

**La couleur** vient de la variable CSS `--accent` ([animations.js:677](public/js/animations.js#L677)). Le JS la lit, la fait résoudre par un mini canvas 2D d'1 pixel (astuce pour accepter n'importe quel format CSS : hex, oklch…), puis la donne au GPU. En mode Photo, `--accent` passe à l'ambre et la couleur change toute seule.

### 1.9 La boucle d'animation

```js
C.tick = function (now) {
    dt = min(temps depuis dernière image, 0.05 s)   // borne à 50 ms
    renderParticles(dt);        // step() puis draw()
    // ... déplace aussi le curseur custom
    requestAnimationFrame(tick); // se rappelle à la prochaine image
};
```

`requestAnimationFrame` s'exécute à la cadence de l'écran (60 fois/s en général) et se met en pause quand l'onglet est caché. La borne de 50 ms évite un bond si on revient sur un onglet resté inactif longtemps.

### 1.10 D'où vient la position de la souris ?

```js
window.addEventListener('mousemove', handler_mouseMove)    // animations.js:451
→ M.pointer.x = e.clientX; M.pointer.y = e.clientY; M.pointer.active = true
```

Un seul écouteur global sur `window` alimente tout. Quand la souris quitte la fenêtre (`mouseout` sans `relatedTarget`), `active = false` : plus de répulsion ni de traits.

### 1.11 Cas particuliers gérés

| Situation | Comportement |
|---|---|
| `prefers-reduced-motion` (accessibilité) | **Une image fixe**, pas de boucle, pas d'interaction |
| Mode Photo | Rendu suspendu, canvas invisible (économie de batterie) |
| Retour en mode Dev | `handler_modeChange` relance la boucle |
| Écran Retina | Résolution du canvas plafonnée à ×1,75 (`maxDpr`) pour ne pas surcharger le GPU |
| WebGL indisponible | Le canvas est supprimé, le site fonctionne sans |
| Perte du contexte GPU (veille, trop d'onglets) | Rendu suspendu puis shaders recompilés à la restauration |
| Mobile / tactile | Pas de souris donc pas d'interaction ; les points dérivent seulement |

### 1.12 Réglages (tout est dans `M.config.particles`, [animations.js:35](public/js/animations.js#L35))

| Je veux… | Je modifie | Valeur actuelle |
|---|---|---|
| Plus / moins de points | `areaPerParticle` (plus petit = plus de points), `min`, `max` | 12000 / 40 / 160 |
| Points plus rapides | `speed` | `[3, 12]` px/s |
| Zone de répulsion plus large | `repelRadius` | 140 px |
| Répulsion plus violente | `repelForce` | 650 |
| Retour plus lent / mou | `spring` plus petit, `damping` plus grand | 6 / 0,9 |
| Réseau de traits plus grand autour du curseur | `linkRadius` | 190 px |
| Traits plus longs entre points | `linkDistance` | 100 px |
| Fond plus ou moins visible | `--_opacity` dans [input.css:549](src/css/input.css#L549) | 0,7 |

---

## 2. Les traits qui se dessinent sous les titres

### 2.1 Ce que tu vois

Quand un titre entre dans l'écran :
1. Le texte **monte** depuis le bas (comme sorti d'une fente).
2. Puis un **trait lime** se déploie de gauche à droite en dessous.

Cela se joue **une seule fois** par titre.

### 2.2 Le HTML : 3 couches imbriquées

Exemple dans [lab.php:9](app/views/front/lab.php#L9) :

```html
<h1 class="reveal-title reveal-title--short ...">
    <span class="reveal-title__mask">          <!-- la « fenêtre » : cache ce qui dépasse -->
        <span class="reveal-title__inner">     <!-- le texte, qui glisse -->
            Le Bac à Sable.
        </span>
    </span>
</h1>
```

| Classe | Rôle |
|---|---|
| `reveal-title` | Conteneur. Porte les variables et le **trait** (pseudo-élément `::after`) |
| `reveal-title__mask` | `overflow: hidden` : tout ce qui sort de sa boîte est invisible |
| `reveal-title__inner` | Le texte. Il est déplacé vers le bas, hors du masque, puis remonte |
| `reveal-title--short` | Variante : trait limité à 5 rem au lieu de 100 % de la largeur |

### 2.3 Le CSS : deux états, une transition

Le titre a **deux états** définis par des variables privées ([input.css:821](src/css/input.css#L821)) :

| Variable | État visible (par défaut) | État masqué (`.fx` + pas encore `.is-revealed`) |
|---|---|---|
| `--_inner-y` | `0%` (texte en place) | `110%` (texte sous le masque, caché) |
| `--_line-scale` | `1` (trait complet) | `0` (trait de largeur nulle) |

```css
/* État masqué : seulement si JS actif ET titre pas encore révélé */
.fx .reveal-title:not(.is-revealed) {
    --_inner-y: 110%;
    --_line-scale: 0;
}
```

Passer de l'un à l'autre est géré par `transition`, donc **le navigateur interpole tout seul** :

| Élément | Propriété animée | Durée | Courbe | Délai |
|---|---|---|---|---|
| Texte (`__inner`) | `transform: translate3d(0, var(--_inner-y), 0)` | 0,9 s | `ease-out-expo` (démarre vite, finit doucement) | 0 |
| Trait (`::after`) | `transform: scaleX(var(--_line-scale))` | 0,8 s | `ease-in-out-quart` | **0,55 s** |

**C'est le délai de 0,55 s sur le trait qui crée l'enchaînement** « texte puis trait » : aucun JS ne les synchronise.

Le trait se déploie vers la droite car `transform-origin: left center` : il grandit à partir de son bord gauche. Sa position est `bottom: -0.3em` (légèrement sous le texte) et il a une lueur (`box-shadow` de la couleur `--accent`).

### 2.4 Le JS : un seul rôle, ajouter `.is-revealed`

Dans [animations.js:1288](public/js/animations.js#L1288) (`initReveal`) :

```js
const titles = V.reveal.getTitles();       // tous les .reveal-title de la page
V.observe(titles, C.handler_titleIntersect, {
    threshold: 0.35,                       // 35 % du titre doit être visible
    rootMargin: '0px 0px -8% 0px'          // et au-dessus des 8 % du bas de l'écran
});
```

`IntersectionObserver` est une API du navigateur qui prévient quand un élément **entre dans la zone visible**. Pas besoin d'écouter le scroll à la main.

```js
C.handler_titleIntersect = function (entries, observer) {
    entries.forEach((entry) => {
        if (!entry.isIntersecting) return;      // pas encore visible → on ignore
        V.reveal.show(entry.target);            // ajoute la classe is-revealed
        observer.unobserve(entry.target);       // on arrête d'observer : une seule fois
    });
};
```

`is-revealed` retire l'état masqué du CSS, ce qui **déclenche les transitions**.

### 2.5 Le chronomètre complet

```
Page chargée
   │
   ├─ <head> : script inline ajoute .fx sur <html>        → titres masqués (110 % / 0)
   │
   ├─ main.js : loader terminal (~3,3 s la 1re visite)
   │        └─ envoie l'événement "app:ready"
   │
   ├─ animations.js : attend "app:ready"  (ou 6 s max : filet de sécurité)
   │        └─ initReveal() : observe tous les titres
   │
   ├─ Le titre entre dans l'écran
   │        └─ JS ajoute .is-revealed
   │
   ├─ t = 0 s    : le texte monte (0,9 s)
   └─ t = 0,55 s : le trait se déploie (0,8 s)
```

> **Pourquoi attendre `app:ready` ?** Sans cela, les titres se révéleraient **sous** le loader plein écran, et on ne verrait rien. Le loader remonte, le signal part, les titres jouent.

### 2.6 Sécurités intégrées

| Cas | Comportement |
|---|---|
| JS désactivé ou en échec | La classe `.fx` n'est jamais posée → **aucun titre n'est masqué** (amélioration progressive) |
| `app:ready` jamais envoyé | Après 6 s (`readyFallback`), les titres se révèlent quand même |
| `prefers-reduced-motion` | Tous les titres sont révélés immédiatement, transitions réduites à 0,01 ms |
| Navigateur sans `IntersectionObserver` | Tout est révélé d'un coup |
| 2ᵉ visite dans la même session | `sessionStorage` : loader sauté, `app:ready` immédiat |

### 2.7 Réglages

| Je veux… | Je modifie | Fichier |
|---|---|---|
| Trait plus épais | `--_line-height` (2px) | [input.css:825](src/css/input.css#L825) |
| Trait plus long / court | `--_line-width` ou `.reveal-title--short` (5rem) | [input.css:826](src/css/input.css#L826) |
| Trait plus bas / haut | `--_line-offset` (-0.3em) | [input.css:827](src/css/input.css#L827) |
| Trait qui démarre plus tôt / tard | `--_line-delay` (0.55s) | [input.css:828](src/css/input.css#L828) |
| Texte plus lent / rapide | `--duration-reveal` (0.9s) | [input.css:240](src/css/input.css#L240) |
| Déclencher plus tôt au scroll | `titleThreshold` (0.35) | [animations.js:62](public/js/animations.js#L62) |
| Couleur du trait | `--_line-color` (= `--accent`) | [input.css:824](src/css/input.css#L824) |

**Ajouter ce trait à un nouveau titre** : recopier les 3 couches HTML ci-dessus avec les classes `reveal-title`, `reveal-title__mask`, `reveal-title__inner`. Aucun JS à écrire, `getTitles()` les trouve tout seul.

---

## 3. Le glitch sur « Yann Le Flohic » (header)

### 3.1 Ce que tu vois

Au survol du logo, pendant **0,5 s** : le texte tremble et deux copies colorées (lime et magenta) se décalent en **tranches horizontales** qui sautent. Puis tout se stabilise, même si la souris reste dessus. C'est un effet d'« aberration chromatique », comme un vieil écran défaillant.

**Il n'y a aucun JavaScript.** Tout est dans [input.css:945-988](src/css/input.css#L945-L988) et les `@keyframes` en fin de fichier.

### 3.2 Le HTML ([header.php:121](app/views/layouts/header.php#L121))

```html
<a href="/" data-glitch="Yann Le Flohic" class="glitch ...">
    <span class="glitch__text">Yann Le Flohic</span>
</a>
```

> ⚠ `data-glitch` doit contenir **exactement** le même texte que l'affichage. Sinon les copies colorées ne correspondent plus au texte.

### 3.3 L'astuce : trois couches superposées

Le CSS fabrique deux copies **fantômes** du texte avec les pseudo-éléments `::before` et `::after`, qui lisent l'attribut `data-glitch` :

```css
.glitch::before, .glitch::after {
    content: attr(data-glitch);   /* recopie le texte de l'attribut */
    position: absolute; inset: 0; /* exactement par-dessus le vrai texte */
    opacity: 0;                   /* invisibles au repos */
    mix-blend-mode: screen;       /* les couleurs s'additionnent comme de la lumière */
}
.glitch::before { color: var(--_c1); }   /* lime  (--accent) */
.glitch::after  { color: var(--_c2); }   /* magenta #FF2E9A */
```

| Couche | Couleur | Rôle |
|---|---|---|
| Texte réel `.glitch__text` | couleur normale | Le vrai texte, qui tremble légèrement |
| `::before` (canal A) | Lime (accent actif) | Copie décalée vers la gauche |
| `::after` (canal B) | Magenta | Copie décalée vers la droite |

`mix-blend-mode: screen` donne l'effet du RVB d'un écran : là où lime et magenta se chevauchent, ça éclaircit.

### 3.4 Le déclenchement : le survol

```css
.glitch:is(:hover, :focus-visible)::before { animation: glitch-channel-a 0.5s steps(1, end) both; }
.glitch:is(:hover, :focus-visible)::after  { animation: glitch-channel-b 0.5s steps(1, end) both; }
.glitch:is(:hover, :focus-visible) .glitch__text { animation: glitch-jitter 0.5s steps(1, end) both; }
```

| Élément | Explication |
|---|---|
| `:hover` | Souris dessus |
| `:focus-visible` | Idem au clavier (touche Tab), pour l'accessibilité |
| `0.5s` | Durée **unique** : l'animation joue une fois, pas en boucle |
| `both` | Applique l'état initial avant et l'état final après (donc retour à opacité 0 à la fin) |
| `steps(1, end)` | **La clé du rendu « glitch »** (voir ci-dessous) |

### 3.5 Pourquoi `steps(1, end)` ?

Par défaut, une animation CSS **glisse** progressivement d'une étape à la suivante. Avec `steps(1, end)`, chaque étape est **tenue** puis **saute** d'un coup à la suivante : pas de fondu, des sauts nets, comme un signal numérique qui bégaie.

### 3.6 Les keyframes : ce qui change à chaque saut

Exemple du canal A ([input.css:1785](src/css/input.css#L1785)) :

```css
@keyframes glitch-channel-a {
    0%  { opacity: 1; clip-path: inset(8% 0 62% 0);  transform: translate(-3px, 0);  }
    12% {             clip-path: inset(58% 0 18% 0); transform: translate(3px, -1px); }
    24% {             clip-path: inset(28% 0 48% 0); transform: translate(-4px, 1px); }
    ...
    100%{ opacity: 0; clip-path: inset(0);           transform: none; }
}
```

| Propriété | Effet |
|---|---|
| `clip-path: inset(haut droite bas gauche)` | **Découpe** la copie : on ne voit qu'une **tranche horizontale**. `inset(8% 0 62% 0)` = on masque 8 % du haut et 62 % du bas, il ne reste qu'une bande au milieu-haut. |
| `transform: translate(x, y)` | Décale cette tranche de quelques pixels |
| `opacity` | 1 pendant l'effet, 0,6 à 86 %, 0 à la fin → disparition |

À chaque palier (0 %, 12 %, 24 %…), la **bande visible change de position** et la copie se **décale** d'un côté ou de l'autre. Visuellement : des bandes qui sautent partout sur le texte.

Les deux canaux ont des **tranches différentes et des décalages opposés** (A à gauche quand B est à droite), d'où l'impression de désynchronisation.

Le texte réel utilise `glitch-jitter` ([input.css:1871](src/css/input.css#L1871)) : micro-secousses (`translate` de 1 px) et cisaillement (`skewX(-8deg)` puis `5deg`…) qui reviennent à `none` à 80 %.

### 3.7 Pourquoi ça ne boucle pas ?

Les animations ne sont attachées qu'à l'état `:hover`, avec une durée de 0,5 s et **sans** `infinite`. Quand elles se terminent, `both` fige l'état final (opacité 0). Pour rejouer l'effet il faut quitter le logo puis y revenir.

### 3.8 Les couleurs

Définies dans [input.css:113-115, 174-176](src/css/input.css#L113) :

```css
--clr-glitch-magenta: #FF2E9A;
--glitch-a: var(--accent);               /* suit le mode : lime en Dev, ambre en Photo */
--glitch-b: var(--clr-glitch-magenta);
```

### 3.9 Accessibilité

```css
@media (prefers-reduced-motion: reduce) {
    .glitch::before, .glitch::after, .glitch .glitch__text { animation: none !important; }
}
```
Pour les personnes qui ont réglé leur système sur « réduire les animations », le logo reste parfaitement stable.

### 3.10 Réglages

| Je veux… | Je modifie |
|---|---|
| Glitch plus long / court | `--_duration: 0.5s` ([input.css:948](src/css/input.css#L948)) |
| Autre couleur du 2ᵉ canal | `--clr-glitch-magenta` ([input.css:115](src/css/input.css#L115)) |
| Décalages plus violents | Les valeurs `translate(-3px, 0)` dans les `@keyframes` |
| Tranches différentes | Les valeurs `clip-path: inset(...)` dans les `@keyframes` |
| Glitch sur un autre texte | Ajouter `class="glitch"`, `data-glitch="<même texte>"` et le `<span class="glitch__text">` |

---

## 4. Bonus : le curseur custom (même fichier, effet lié)

Le petit point lime qui suit la souris est géré dans le même `animations.js` et utilise les **mêmes** événements `mousemove` que les particules. Il n'est pas demandé, mais il intervient dans ce que tu vois au survol :

| Élément | Explication |
|---|---|
| Point lissé | `M.cursor.follow()` rattrape la souris avec un retard (`ease: 0.22`) : effet « glissant » |
| Grossit sur liens/boutons | Les classes `cursor--hover`, `cursor--magnetic` changent `--_scale` en CSS |
| Éléments `[data-magnetic]` | Le logo, les liens du menu, les boutons sont **attirés** (max 14 px) vers la souris via `style.translate` |
| Logo = `data-magnetic` + `glitch` | Les deux effets se cumulent sur le logo : il est attiré **et** glitche |
| Désactivé au tactile | `(hover: hover) and (pointer: fine)` : uniquement souris / trackpad |

---

## 5. Diagnostics : « ça ne marche pas, pourquoi ? »

| Symptôme | Diagnostic probable | Solution |
|---|---|---|
| Aucun point en fond | Canvas absent, WebGL non supporté, ou mode Photo actif | Vérifier `#fx-particles` dans le DOM ; console : message `[animations] Shader` ; repasser en mode Dev |
| Points figés, pas d'interaction | `prefers-reduced-motion` activé dans le système | Désactiver « réduire les animations » (macOS : Accessibilité → Affichage) |
| Pas de traits au curseur | Souris pas encore bougée (`pointer.active` faux) ou aucun point proche | Bouger la souris ; augmenter `linkRadius` / `linkDistance` |
| Les titres restent invisibles | `app:ready` jamais envoyé, erreur JS dans `main.js` | Attendre 6 s (filet) ; regarder la console |
| Titres visibles sans animation | `.fx` absent de `<html>` ou JS bloqué | Vérifier le script inline du `<head>` |
| Trait sous le titre absent | Modifié `src/css/input.css` mais pas recompilé | Relancer le build Tailwind pour régénérer `public/css/style.css` |
| Glitch absent | `data-glitch` ou `.glitch__text` manquant, ou `prefers-reduced-motion` | Vérifier le markup du logo |
| Glitch qui ne montre que le texte normal | `data-glitch` ≠ texte affiché | Mettre exactement le même texte |
| Modif CSS sans effet | Édition de `public/css/style.css` (compilé) | Éditer `src/css/input.css`, puis recompiler |
| Modif JS sans effet (cache) | Navigateur garde l'ancien fichier | Changer le `?v=20261005` dans [footer.php](app/views/layouts/footer.php) |

---

## 6. Récapitulatif en un schéma

```
SOURIS BOUGE ─────────────────────────────┐
  window 'mousemove'                      │
        │                                 ▼
        │                     M.pointer {x, y, active}
        │                                 │
        │            ┌────────────────────┴───────────────┐
        │            ▼                                    ▼
        │   BOUCLE rAF (60×/s)                   Curseur custom
        │   C.tick → M.particles.step()          (point + magnétisme)
        │      • dérive des points
        │      • ressort + répulsion souris
        │      • buildLinks (traits)
        │            │
        │            ▼
        │   V.particles.draw() ──► GPU (WebGL) ──► <canvas> derrière la page
        │
SCROLL ────► IntersectionObserver ──► ajoute .is-revealed ──► le CSS anime
                                       (titres)               texte puis trait

SURVOL du logo ───────────────────────────────► le CSS anime (:hover), 0 JS
                                                 3 couches : texte + lime + magenta
```

| Effet | Déclencheur | Moteur | Fréquence |
|---|---|---|---|
| Points + traits | Position de la souris | JS + WebGL | En continu (60 fps) |
| Trait sous titre | Titre visible à l'écran | CSS `transition` | 1 fois par titre |
| Glitch | Survol / focus du logo | CSS `@keyframes` | 0,5 s à chaque survol |

## 7. Check-list avant de modifier une animation

- [ ] Je modifie `src/css/input.css` (pas `public/css/style.css`) et je recompile Tailwind.
- [ ] Je teste avec `prefers-reduced-motion` activé : le contenu doit rester visible et lisible.
- [ ] Je teste en mode Dev **et** Photo (la couleur d'accent change).
- [ ] Je teste sur écran tactile : pas de dépendance à la souris pour le contenu.
- [ ] J'incrémente `?v=` dans `footer.php` / `header.php` si je change un fichier JS ou CSS (cache navigateur).
- [ ] Je garde `C.init()` en toute fin de `animations.js`.
