# Comment fonctionne l'effet « magnétique » des boutons

Quand tu passes la souris sur un bouton ou un lien du menu, il **se déplace légèrement vers le curseur**, comme attiré par un aimant. Quand tu pars, il revient doucement à sa place. En même temps, le petit point lime qui suit la souris grossit et se colle vers le centre du bouton.

---

## 0. Vue d'ensemble

### 0.1 Les fichiers concernés

| Couche | Fichier | Rôle |
|---|---|---|
| HTML | attribut `data-magnetic` sur chaque élément concerné | **Marque** l'élément comme « aimanté » |
| CSS | [input.css:738-772](src/css/input.css#L738-L772) (`.btn-glow`), [input.css:576-650](src/css/input.css#L576-L650) (`.cursor`) | Rend le déplacement **fluide** (transition) et gère l'aspect du point |
| JS | [animations.js](public/js/animations.js) (partie `M.cursor`, `V.magnet`, `C.updateCursorTarget`) | Calcule le décalage et l'applique |

### 0.2 Où est `data-magnetic` ?

12 éléments du site, par exemple :

| Élément | Fichier |
|---|---|
| Logo « Yann Le Flohic » | [header.php:141](app/views/layouts/header.php#L141) |
| Liens du menu (Parcours, Compétences, Projets, Contact, Lab) | [header.php:149-170](app/views/layouts/header.php#L149-L170) |
| Interrupteur Dev / Photo | [header.php:180](app/views/layouts/header.php#L180) |
| Boutons « Projets » et « Contact » de l'accueil | [home.php:49,55](app/views/front/home.php#L49) |
| Bouton flottant du terminal | [footer.php:50](app/views/layouts/footer.php#L50) |

**Pour aimanter un nouvel élément : ajoute simplement `data-magnetic` dessus.** Aucun JS à écrire.

### 0.3 L'idée en une phrase

> À chaque mouvement de souris, le JS mesure **où est la souris par rapport au centre du bouton**, déplace le bouton d'**environ 30 % de cet écart** (plafonné à 14 px), et le CSS rend le mouvement fluide.

### 0.4 Qui fait quoi

| Étape | Qui |
|---|---|
| Savoir que la souris bouge | JS (`mousemove` sur `window`) |
| Trouver quel élément est survolé | JS (`closest('[data-magnetic]')`) |
| Calculer le décalage | JS, Modèle (`M.cursor.computeMagnet`) |
| Appliquer le décalage | JS, Vue (`V.magnet.apply` → `style.translate`) |
| Rendre ça fluide | **CSS** (`transition: translate 0.35s`) |
| Revenir à la place au départ | JS vide `style.translate`, CSS anime le retour |

---

## 1. Le principe étape par étape

### Étape 1. La souris bouge

Un seul écouteur global ([animations.js:436](public/js/animations.js#L436)) :

```js
window.addEventListener('mousemove', handlers.onMouseMove, {passive: true});
```

Il appelle `C.handleMouseMove(e)` ([animations.js:1098](public/js/animations.js#L1098)), qui note la position puis appelle `C.updateCursorTarget(e.target, x, y)`. `e.target` est l'élément sous la souris.

> Un seul écouteur sert à tous les boutons : pas besoin d'en poser un par bouton (*délégation d'événements*).

### Étape 2. Quel élément aimanté est sous la souris ?

```js
findMagnetic(target) {
    return target instanceof Element ? target.closest('[data-magnetic]') : null;
}
```

`closest()` remonte du point survolé vers ses parents jusqu'à trouver un `data-magnetic`. Ainsi, survoler **l'icône ou le texte à l'intérieur** d'un bouton compte comme survoler le bouton.

### Étape 3. Changement de bouton ? On relâche l'ancien

```js
if (magnetic !== M.cursor.magneticEl) {
    if (M.cursor.magneticEl) V.magnet.reset(M.cursor.magneticEl);   // l'ancien revient à sa place
    M.cursor.releaseMagnet();
    M.cursor.magneticEl = magnetic;
}
```

Si tu glisses d'un lien du menu au suivant, le premier revient pendant que le second est attiré.

### Étape 4. Calcul du décalage : `computeMagnet()` ([animations.js:345](public/js/animations.js#L345))

```js
const cx = rect.left + rect.width / 2 - this.applied.x;   // centre « au repos » du bouton
const cy = rect.top  + rect.height / 2 - this.applied.y;
const ox = mx - cx;                                        // écart souris ↔ centre
const oy = my - cy;

this.applied = {
    x: clamp(ox * 0.3),     // 30 % de l'écart, plafonné à ±14 px
    y: clamp(oy * 0.3)
};
```

Les trois réglages sont dans `M.config.cursor` ([animations.js:51-56](public/js/animations.js#L51-L56)) :

| Réglage | Valeur | Effet |
|---|---|---|
| `magnetStrength` | 0,3 | Le bouton se déplace de 30 % de l'écart |
| `magnetMax` | 14 px | Déplacement maximal, dans chaque direction |
| `cursorPull` | 0,35 | Le point du curseur est attiré à 35 % vers le centre du bouton |

### Étape 5. Application : `V.magnet.apply()` ([animations.js:838](public/js/animations.js#L838))

```js
el.style.translate = `${x.toFixed(1)}px ${y.toFixed(1)}px`;
```

Cela écrit un style inline comme `translate: 12.0px 3.0px` sur le bouton.

### Étape 6. Le CSS lisse le mouvement

Sur `.btn-glow` ([input.css:763-770](src/css/input.css#L763-L770)) :

```css
transition: ... translate 0.35s var(--ease-out-expo), ...;
```

Sans cette ligne, le bouton « sauterait » à chaque mouvement de souris. Avec elle, le navigateur **rattrape** la nouvelle valeur en 0,35 s avec une courbe douce (démarre vite, finit lentement). C'est ce léger retard qui donne la sensation d'un bouton « qui suit » la souris, comme sur un élastique.

### Étape 7. Départ de la souris : retour à la place

```js
reset(el) { el.style.translate = ''; }
```

On supprime le style inline : `translate` redevient `none`, et la même transition CSS anime le **retour** (0,35 s).

---

## 2. Exemple chiffré

Un bouton dont le centre au repos est en **(500, 300)**. La souris arrive en **(540, 310)**.

| Calcul | Résultat |
|---|---|
| Écart `ox = 540 − 500` | 40 |
| Écart `oy = 310 − 300` | 10 |
| Décalage X : `40 × 0,3 = 12` | **12 px** (sous le plafond de 14) |
| Décalage Y : `10 × 0,3 = 3` | **3 px** |
| Cible du point curseur X : `500 + 40 × 0,35` | 514 |
| Cible du point curseur Y : `300 + 10 × 0,35` | 303,5 |

Le bouton glisse de 12 px vers la droite et 3 px vers le bas ; le point lime se positionne en (514 ; 303,5), c'est-à-dire **entre la souris et le centre** du bouton. Si la souris était à (600, 300) : écart de 100 → 30 px calculés, **plafonnés à 14 px**.

---

## 3. Le piège évité : la boucle de rétroaction

C'est la partie la plus subtile du code.

**Problème.** Le bouton se déplace. À la prochaine souris-move, `getBoundingClientRect()` renvoie la position **déjà décalée**. Si on recalculait l'écart depuis ce centre décalé, le bouton « poursuivrait » son propre mouvement et dériverait.

**Solution** (une ligne, [animations.js:347](public/js/animations.js#L347)) :

```js
const cx = rect.left + rect.width / 2 - this.applied.x;   // on retire le décalage déjà appliqué
```

On retrouve le centre « au repos » en soustrayant le décalage courant. Le calcul est alors **toujours le même pour la même position de souris**, stable et sans dérive.

| Symptôme | Diagnostic | Solution |
|---|---|---|
| Un bouton glisserait vers un bord et s'échapperait | Le centre était mesuré après déplacement | Soustraire `applied` du centre mesuré |

---

## 4. Pourquoi la propriété CSS `translate` et pas `transform` ?

| Propriété | Problème |
|---|---|
| `transform: translate(...)` | **Écrase** les autres transformations du même élément (`scale`, `rotate` posés par Tailwind ou par le CSS) |
| `translate: x y` | Propriété **indépendante** : se combine avec `transform`, `scale` et `rotate` sans conflit |

Exemple concret : `.btn-glow:active { scale: 0.97; }` (effet d'enfoncement au clic) fonctionne en même temps que le déplacement magnétique, parce que `scale` et `translate` sont des propriétés séparées.

Le commentaire du code le dit ([animations.js:831-832](public/js/animations.js#L831-L832)) : *« indépendante de `transform` : n'écrase pas les utilitaires scale/rotate de Tailwind »*.

> Les liens du menu utilisent `transition-all` de Tailwind, qui inclut `translate` : la fluidité y est donc aussi assurée.

---

## 5. Le point du curseur réagit en même temps

Dans la même fonction `updateCursorTarget` ([animations.js:1128](public/js/animations.js#L1128)) :

```js
if (magnetic) {
    const m = M.cursor.computeMagnet(V.magnet.getRect(magnetic), x, y);
    V.magnet.apply(magnetic, m.offsetX, m.offsetY);      // le bouton bouge
    M.cursor.setTarget(m.cursorX, m.cursorY);            // le point se colle vers le centre du bouton
} else {
    M.cursor.setTarget(x, y);                            // sinon il suit la souris tel quel
}
```

Puis `V.cursor.setState({magnetic: true, ...})` ajoute la classe `cursor--magnetic`, et le CSS ([input.css:631](src/css/input.css#L631)) fait **grossir** le point :

| État | Échelle du point | Opacité |
|---|---|---|
| Repos | ×1 (10 px) | 100 % |
| Lien / bouton ordinaire (`cursor--hover`) | ×4 | 28 % |
| **Élément aimanté** (`cursor--magnetic`) | **×5,2** | **20 %** |
| Clic | ×0,65 (×3,2 si sur bouton) | |

Le point devient un grand halo translucide centré sur le bouton, ce qui renforce l'impression que le bouton « attrape » le curseur.

### Le lissage du point

Le point ne suit pas instantanément la souris. À chaque image (`C.tick`), `M.cursor.follow()` ([animations.js:323](public/js/animations.js#L323)) le rapproche de sa cible :

```js
const k = 1 - Math.pow(1 - 0.22, dt * 60);   // facteur de lissage indépendant du framerate
this.x += (this.targetX - this.x) * k;
this.y += (this.targetY - this.y) * k;
```

Il rattrape sa cible en environ 22 % de l'écart restant par image : c'est ce qui donne sa traîne fluide.

> Le bouton est donc lissé **par le CSS** (0,35 s), et le point est lissé **par le JS** (à chaque image). Ce sont deux mécanismes différents qui produisent un mouvement cohérent.

---

## 6. Quand l'effet est désactivé

| Situation | Comportement |
|---|---|
| Écran tactile (pas de souris) | `(hover: hover) and (pointer: fine)` faux → `M.cursor.enabled = false`, **aucun effet** ([animations.js:1089](public/js/animations.js#L1089)) |
| `prefers-reduced-motion` | Les boutons **ne bougent pas** (`magnetic = null`), seul le point grossit ([animations.js:1131](public/js/animations.js#L1131)) ; transitions réduites à 0,01 ms |
| La souris sort de la fenêtre | `handleMouseOut` relâche le bouton aimanté et masque le point |
| Page défilée sans bouger la souris | `handleScroll` ré-évalue l'élément sous le pointeur (une fois par image) pour ne pas laisser un bouton attiré par erreur |
| Modales ouvertes (terminal, lightbox…) | Le curseur custom est masqué par le CSS ([input.css:472-498](src/css/input.css#L472-L498)) |
| `#custom-cursor` absent du HTML | `V.cursor.init()` renvoie `false` : rien ne s'active |

---

## 7. Réglages

| Je veux… | Je modifie | Fichier |
|---|---|---|
| Un bouton qui suit **plus** la souris | `magnetStrength` (0,3 → 0,45) | [animations.js:53](public/js/animations.js#L53) |
| Un déplacement maximal plus grand | `magnetMax` (14) | [animations.js:54](public/js/animations.js#L54) |
| Le point plus attiré vers le centre | `cursorPull` (0,35 → 0,6) | [animations.js:55](public/js/animations.js#L55) |
| Un retour / une suite plus lente ou plus élastique | `translate 0.35s` dans `.btn-glow` | [input.css:768](src/css/input.css#L768) |
| Un point qui suit plus mollement | `ease` (0,22 → 0,12) | [animations.js:52](public/js/animations.js#L52) |
| Un halo plus grand / plus discret | `--_scale` et `--_dot-opacity` de `.cursor--magnetic` | [input.css:631](src/css/input.css#L631) |

---

## 8. Ajouter l'effet à un nouvel élément

**Étape 1.** Ajouter `data-magnetic` :

```html
<a href="/contact" data-magnetic class="btn-glow">Me contacter</a>
```

**Étape 2.** Vérifier que l'élément a une transition sur `translate`, sinon il sautera au lieu de glisser :

```css
.mon-bouton {
    transition: translate 0.35s var(--ease-out-expo);
}
```

Les classes `.btn-glow`, `.cli-launcher` ([input.css:1015](src/css/input.css#L1015)) et `.mode-switch` ([input.css:1267](src/css/input.css#L1267)) l'ont déjà. Les éléments Tailwind avec `transition-all` aussi.

**Étape 3.** Éviter, sur cet élément, une propriété `translate` définie ailleurs (elle serait écrasée par le style inline du JS).

---

## 9. Diagnostics

| Symptôme | Diagnostic | Solution |
|---|---|---|
| Le bouton ne bouge pas du tout | Pas de `data-magnetic`, ou écran tactile, ou mouvement réduit activé | Vérifier l'attribut ; tester avec une souris ; vérifier le réglage d'accessibilité du système |
| Le bouton **saute** au lieu de glisser | Pas de `transition` sur `translate` | Ajouter `translate 0.35s var(--ease-out-expo)` dans la transition |
| Le bouton ne revient pas à sa place | Style inline `translate` resté posé (mouseout non reçu) | Recharger ; vérifier qu'aucun autre script ne gère `mousemove` |
| Le bouton ne bouge pas mais le point grossit | `prefers-reduced-motion` actif | Normal : seul le grossissement est conservé |
| Le point n'apparaît pas | `#custom-cursor` manquant ou `has-custom-cursor` non posé | Vérifier [footer.php:87](app/views/layouts/footer.php#L87) |
| Effet doublé / saccadé | Un second écouteur `mousemove` a été ajouté (ex. dans `main.js`) | Retirer le doublon : `main.js` précise de ne pas en réintroduire ([main.js:70-71](public/js/main.js#L70-L71)) |
| Le bouton écrase son `scale` / `rotate` | L'élément utilise `transform` au lieu des propriétés individuelles | Utiliser `scale:` / `rotate:` ou laisser Tailwind v4 |
| Modification CSS sans effet | Édition de `public/css/style.css` (compilé) | Éditer `src/css/input.css`, recompiler Tailwind |

---

## 10. Récapitulatif

```
 La souris bouge (window 'mousemove')
        │
        ▼
 C.updateCursorTarget(élément sous la souris, x, y)
        │
        ├── closest('[data-magnetic]') ──► aucun ?  → le point suit la souris, c'est tout
        │
        ▼ oui
 M.cursor.computeMagnet(rect, x, y)
        │   centre au repos = centre mesuré − décalage déjà appliqué
        │   décalage = (souris − centre) × 0,3, plafonné à ±14 px
        │
        ├──► V.magnet.apply   →  style.translate = "12px 3px"   ──► CSS transition 0,35 s ──► le bouton GLISSE
        │
        └──► M.cursor.setTarget(centre + écart × 0,35)  ──► le point se colle vers le bouton
                                                              + classe cursor--magnetic (halo ×5,2)

 La souris quitte le bouton ──► V.magnet.reset (style.translate = '') ──► le CSS ramène le bouton en 0,35 s
```

| Notion | À retenir |
|---|---|
| Déclencheur | Attribut HTML `data-magnetic` |
| Calcul | 30 % de l'écart souris ↔ centre, plafonné à 14 px |
| Anti-dérive | On retire le décalage déjà appliqué avant de recalculer |
| Fluidité du bouton | CSS : `transition: translate 0.35s` |
| Fluidité du point | JS : lissage à chaque image |
| Propriété utilisée | `translate` (indépendante de `transform`) |
| Désactivation | Tactile, `prefers-reduced-motion`, sortie de la fenêtre |
