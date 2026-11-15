/**
 * @file mac3d.js
 * @description Objet 3D du hero (three.js) : il suit le curseur et se manipule au drag (souris ou doigt).
 *
 * - Mode Dev : MacBook Air (/assets/models/mac.glb), il s'ouvre à l'arrivée (animation « LidOpen ») et
 *   affiche une capture du site pro sur son écran.
 * - Mode Photo : appareil photo (/assets/models/camera.glb). Il n'est téléchargé qu'à la première bascule.
 * - Le changement de mode est détecté sur l'attribut data-mode de <html> (posé par theme-toggle.js).
 * - Sans WebGL ou si le chargement échoue, la <video> de l'animoji reste affichée (fallback).
 * - Mouvement réduit : écran ouvert d'emblée, pas de suivi du curseur (le drag reste possible).
 * - Rendu en pause quand le hero sort de l'écran ou que l'onglet est caché (batterie mobile).
 *
 * Architecture M/V/C stricte :
 *  - M (Modèle)     : état de rotation (cible du curseur, décalage du drag), préférences navigateur.
 *  - V (Vue)        : canvas, vidéo de fallback, scène three.js et rendu.
 *  - C (Contrôleur) : handlers pointeur / visibilité qui mettent à jour M puis demandent un rendu à V.
 *
 * Module ES chargé depuis app/views/front/home.php ; « three » est résolu par l'importmap (fichiers dans /js/vendor/three/).
 */

import * as THREE from 'three';
import {GLTFLoader} from 'three/addons/loaders/GLTFLoader.js';
import {RoomEnvironment} from 'three/addons/environments/RoomEnvironment.js';

/* =======================================================================
   M — MODÈLE
   ======================================================================= */
const M = {};

/**
 * Un modèle par univers.
 * size : plus grande dimension de l'objet en unités de scène ; yaw : orientation de départ (rad) ;
 * envIntensity : force des reflets de l'environnement (le métal sombre du Mac en demande moins).
 */
M.models = {
    dev: {
        url: '/assets/models/mac.glb', size: 2.2, yaw: 0, envIntensity: 0.45,
        label: 'MacBook Air en 3D, orientable à la souris ou au doigt'
    },
    photo: {
        url: '/assets/models/camera.glb', size: 2.0, yaw: -0.5, envIntensity: 0.9,
        label: 'Appareil photo en 3D, orientable à la souris ou au doigt'
    }
};

/** Normal map du grain du grip de l'appareil photo (fichier séparé : la CSP interdit les textures embarquées en blob:). */
M.grainUrl = '/assets/models/camera-grain-normal.png';

/** Univers affiché : 'dev' ou 'photo'. */
M.mode = 'dev';

/**
 * @returns {'dev'|'photo'} Univers actif, lu sur l'attribut data-mode de <html>.
 */
M.readMode = function () {
    return document.documentElement.getAttribute('data-mode') === 'photo' ? 'photo' : 'dev';
};

/** Capture de pro.yannleflohic.fr affichée sur l'écran (format de l'écran : 1280 x 832). */
M.screenUrl = '/assets/images/mac-screen.webp';

/** Rotation cible issue de la position du curseur (radians). */
M.follow = {x: 0, y: 0};

/** Décalage de rotation ajouté par le drag (radians). */
M.drag = {x: 0, y: 0};

/** Drag en cours et dernière position du pointeur. */
M.dragging = false;
M.last = {x: 0, y: 0};

/** true quand le hero est visible à l'écran. */
M.visible = true;

/**
 * @returns {boolean} true si l'utilisateur préfère réduire les animations.
 */
M.prefersReducedMotion = function () {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
};

/**
 * @returns {boolean} true si le navigateur sait créer un contexte WebGL 2.
 */
M.supportsWebGL = function () {
    try {
        return !!document.createElement('canvas').getContext('webgl2');
    } catch {
        return false;
    }
};

/**
 * Convertit la position du curseur dans la fenêtre en rotation cible.
 * @param {number} clientX
 * @param {number} clientY
 * @returns {void}
 */
M.setFollow = function (clientX, clientY) {
    // Centre de l'écran = 0 ; bords = ±0.45 rad en lacet (gauche/droite), ±0.2 rad en tangage (haut/bas)
    this.follow.y = (clientX / window.innerWidth - 0.5) * 0.9;
    this.follow.x = (clientY / window.innerHeight - 0.5) * 0.4;
};

/**
 * Ajoute le déplacement du pointeur au décalage du drag.
 * @param {number} clientX
 * @param {number} clientY
 * @returns {void}
 */
M.addDrag = function (clientX, clientY) {
    this.drag.y += (clientX - this.last.x) * 0.01;
    // Tangage borné pour ne jamais retourner le Mac à l'envers
    this.drag.x = Math.min(0.8, Math.max(-0.8, this.drag.x + (clientY - this.last.y) * 0.01));
    this.last = {x: clientX, y: clientY};
};

/**
 * Fait revenir le décalage du drag vers 0 quand le pointeur est relâché.
 * @param {number} dt Temps écoulé depuis la dernière image (s).
 * @returns {void}
 */
M.relaxDrag = function (dt) {
    if (this.dragging) return;
    // Amortissement exponentiel : indépendant du nombre d'images par seconde
    const k = 1 - Math.exp(-dt * 2.5);
    this.drag.x -= this.drag.x * k;
    this.drag.y -= this.drag.y * k;
};

/**
 * @returns {{x:number, y:number}} Rotation finale visée (curseur + drag).
 */
M.targetRotation = function () {
    return {x: this.follow.x + this.drag.x, y: this.follow.y + this.drag.y};
};

/* =======================================================================
   V — VUE
   ======================================================================= */
const V = {};

V.host = null;
V.canvas = null;
V.fallback = null;
V.renderer = null;
V.scene = null;
V.camera = null;
V.pivot = null;
V.mixer = null;
V.clock = null;

/** Modèles déjà chargés, par univers : { dev: Group, photo: Group }. */
V.models = {};

/** Chargements en cours, par univers (évite de télécharger deux fois le même .glb). */
V.pending = {};

/** Action « LidOpen » du Mac (null tant que le Mac n'est pas chargé). */
V.openAction = null;

/**
 * Récupère les éléments du hero.
 * @returns {boolean} false si le hero 3D est absent de la page.
 */
V.init = function () {
    this.host = document.getElementById('mac3d');
    this.canvas = document.getElementById('mac3d-canvas');
    this.fallback = document.getElementById('mac3d-fallback');
    return !!(this.host && this.canvas);
};

/**
 * Crée le moteur de rendu, la scène, la lumière et la caméra.
 * @returns {void}
 */
V.createScene = function () {
    this.renderer = new THREE.WebGLRenderer({canvas: this.canvas, alpha: true, antialias: true});
    // Plafonné à 2 : au-delà, le coût GPU explose sur mobile sans gain visible
    this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
    this.renderer.outputColorSpace = THREE.SRGBColorSpace;

    this.scene = new THREE.Scene();
    // Environnement « pièce » précalculé : donne les reflets réalistes de l'aluminium
    const pmrem = new THREE.PMREMGenerator(this.renderer);
    this.scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
    this.scene.environmentIntensity = 0.45;

    const key = new THREE.DirectionalLight(0xffffff, 1.0);
    key.position.set(2, 3, 2);
    this.scene.add(key);

    this.camera = new THREE.PerspectiveCamera(30, 1, 0.1, 50);
    this.camera.position.set(0, 1.2, 6);
    this.camera.lookAt(0, 0, 0);

    // Le pivot porte la rotation : le modèle y est centré pour tourner autour de son milieu
    this.pivot = new THREE.Group();
    this.scene.add(this.pivot);
    this.clock = new THREE.Clock();
};

/**
 * Charge le .glb.
 * @param {string} url
 * @returns {Promise<object>} Résultat GLTFLoader (scene, animations).
 */
V.loadModel = function (url) {
    return new GLTFLoader().loadAsync(url);
};

/**
 * Centre et met à l'échelle le modèle, prépare son animation éventuelle, puis l'ajoute (masqué) à la scène.
 * @param {'dev'|'photo'} mode
 * @param {object} gltf
 * @param {boolean} startOpen true = écran du Mac déjà ouvert (mouvement réduit).
 * @returns {THREE.Group} Le conteneur du modèle (porte l'orientation de départ).
 */
V.mountModel = function (mode, gltf, startOpen) {
    const config = M.models[mode];
    const model = gltf.scene;

    // Écran et verre du Mac : reflet atténué, sinon l'écran vire au blanc vu de face
    model.traverse((node) => {
        if (node.isMesh && ['Glass_Bezel', 'Screen'].includes(node.material.name)) {
            // envMapIntensity n'agit que si la map est portée par le matériau (pas seulement par la scène)
            node.material.envMap = this.scene.environment;
            node.material.envMapIntensity = 0.08;
        }
    });

    let action = null;
    if (gltf.animations.length) {
        this.mixer = new THREE.AnimationMixer(model);
        action = this.mixer.clipAction(gltf.animations[0]);
        action.setLoop(THREE.LoopOnce, 1);
        action.clampWhenFinished = true;
        action.play();
        // Pose écran ouvert le temps de mesurer la boîte englobante complète
        this.mixer.setTime(gltf.animations[0].duration);
    }

    model.updateMatrixWorld(true);
    const box = new THREE.Box3().setFromObject(model);
    const size = box.getSize(new THREE.Vector3());
    const center = box.getCenter(new THREE.Vector3());
    const scale = config.size / Math.max(size.x, size.y, size.z);
    model.scale.setScalar(scale);
    model.position.sub(center.multiplyScalar(scale));

    if (action) {
        this.openAction = action;
        // Arrivée en fin de clip, l'action passe en pause : reset() la relance depuis le début
        action.reset();
        action.time = startOpen ? action.getClip().duration : 0;
        this.mixer.update(0);
    }

    // Le conteneur porte l'orientation de départ ; la rotation curseur/drag reste sur le pivot
    const holder = new THREE.Group();
    holder.rotation.y = config.yaw;
    holder.visible = false;
    holder.add(model);
    this.pivot.add(holder);
    this.models[mode] = holder;
    return holder;
};

/**
 * Affiche le modèle d'un univers et masque l'autre.
 * @param {'dev'|'photo'} mode
 * @param {boolean} reducedMotion true = pas de réouverture animée du Mac.
 * @returns {void}
 */
V.showMode = function (mode, reducedMotion) {
    Object.entries(this.models).forEach(([key, holder]) => {
        holder.visible = key === mode;
    });
    this.scene.environmentIntensity = M.models[mode].envIntensity;
    this.canvas.setAttribute('aria-label', M.models[mode].label);

    // Le Mac se rouvre à chaque retour en mode Dev
    if (mode === 'dev' && this.openAction && !reducedMotion) {
        this.openAction.reset().play();
        this.mixer.update(0);
    }
};

/**
 * Change d'univers avec un fondu : le canvas s'efface, on échange les modèles, il réapparaît.
 * @param {'dev'|'photo'} mode
 * @param {boolean} reducedMotion true = échange immédiat, sans fondu.
 * @returns {void}
 */
V.swapMode = function (mode, reducedMotion) {
    if (reducedMotion) {
        this.showMode(mode, true);
        return;
    }
    this.canvas.classList.add('opacity-0');
    // 350 ms : le canvas est à moitié transparent, l'échange passe inaperçu
    setTimeout(() => {
        this.showMode(mode, false);
        this.showCanvas();
    }, 350);
};

/**
 * Plaque une image sur l'écran du Mac (matériau « Screen » du .glb).
 * Si l'image manque, l'écran garde sa couleur d'origine : aucune erreur bloquante.
 * @param {THREE.Object3D} root Conteneur du Mac.
 * @param {string} url
 * @returns {Promise<void>}
 */
V.applyScreen = async function (root, url) {
    const texture = await new THREE.TextureLoader().loadAsync(url);
    // Les UV d'un glTF ont l'origine en haut à gauche : pas d'inversion verticale
    texture.flipY = false;
    texture.colorSpace = THREE.SRGBColorSpace;
    // Filtrage anisotrope : le texte reste net quand l'écran est vu de biais
    texture.anisotropy = this.renderer.capabilities.getMaxAnisotropy();

    // MeshBasicMaterial ignore la lumière de la scène, comme un écran rétroéclairé ;
    // toneMapped: false garde les couleurs exactes de la capture (pas de tone mapping ACES)
    const screen = new THREE.MeshBasicMaterial({map: texture, toneMapped: false});
    root.traverse((node) => {
        if (node.isMesh && node.material.name === 'Screen') node.material = screen;
    });
};

/**
 * Applique le grain en relief sur le caoutchouc de l'appareil photo (matériau « Rubber_Grip »).
 * Sans l'image, le grip reste lisse : aucune erreur bloquante.
 * @param {THREE.Object3D} root Conteneur de l'appareil photo.
 * @param {string} url
 * @returns {Promise<void>}
 */
V.applyGrain = async function (root, url) {
    const texture = await new THREE.TextureLoader().loadAsync(url);
    // Réglages d'origine du .glb : texture répétée, UV glTF (origine en haut à gauche)
    texture.wrapS = texture.wrapT = THREE.RepeatWrapping;
    texture.flipY = false;
    texture.anisotropy = this.renderer.capabilities.getMaxAnisotropy();

    root.traverse((node) => {
        if (!node.isMesh || node.material.name !== 'Rubber_Grip') return;
        node.material.normalMap = texture;
        node.material.normalScale.set(0.7, 0.7);
        node.material.needsUpdate = true;
    });
};

/**
 * Affiche le canvas et masque la vidéo de fallback.
 * @returns {void}
 */
V.showCanvas = function () {
    this.canvas.classList.remove('opacity-0');
    if (this.fallback) {
        this.fallback.pause();
        this.fallback.classList.add('hidden');
    }
};

/**
 * Remet la vidéo de l'animoji à la place du canvas (le modèle d'un univers n'a pas pu se charger).
 * @returns {void}
 */
V.showFallback = function () {
    this.canvas.classList.add('opacity-0');
    if (this.fallback) {
        this.fallback.classList.remove('hidden');
        this.fallback.play();
    }
};

/**
 * Abandonne la 3D : le canvas disparaît, la vidéo reste.
 * @returns {void}
 */
V.abort = function () {
    if (this.renderer) this.renderer.dispose();
    this.canvas.remove();
};

/**
 * Adapte le rendu à la taille du conteneur.
 * @returns {void}
 */
V.resize = function () {
    const {clientWidth: w, clientHeight: h} = this.host;
    if (!w || !h) return;
    this.renderer.setSize(w, h, false);
    this.camera.aspect = w / h;
    this.camera.updateProjectionMatrix();
};

/**
 * Curseur « main » pendant le drag.
 * @param {boolean} grabbing
 * @returns {void}
 */
V.setGrabbing = function (grabbing) {
    this.canvas.style.cursor = grabbing ? 'grabbing' : '';
};

/**
 * Rapproche la rotation du pivot de la cible puis dessine une image.
 * @param {{x:number, y:number}} target
 * @param {number} dt
 * @returns {void}
 */
V.render = function (target, dt) {
    if (this.mixer) this.mixer.update(dt);
    // Lissage : le Mac « rattrape » la cible au lieu de sauter dessus
    const k = 1 - Math.exp(-dt * 8);
    this.pivot.rotation.x += (target.x - this.pivot.rotation.x) * k;
    this.pivot.rotation.y += (target.y - this.pivot.rotation.y) * k;
    this.renderer.render(this.scene, this.camera);
};

/* =======================================================================
   C — CONTRÔLEUR
   ======================================================================= */
const C = {};

C.running = false;
C.reducedMotion = false;

/**
 * Point d'entrée unique : vérifie le support, construit la scène et branche les écouteurs.
 * @returns {Promise<void>}
 */
C.init = async function () {
    if (!V.init() || !M.supportsWebGL()) return;

    this.reducedMotion = M.prefersReducedMotion();
    V.createScene();

    // Seul le modèle de l'univers actif est téléchargé ; l'autre attend la première bascule
    M.mode = M.readMode();
    try {
        await C.ensureModel(M.mode);
    } catch {
        V.abort();
        return;
    }

    V.showMode(M.mode, this.reducedMotion);
    V.showCanvas();
    V.resize();

    window.addEventListener('pointermove', C.handler_follow);
    V.canvas.addEventListener('pointerdown', C.handler_dragStart);
    V.canvas.addEventListener('pointermove', C.handler_dragMove);
    V.canvas.addEventListener('pointerup', C.handler_dragEnd);
    V.canvas.addEventListener('pointercancel', C.handler_dragEnd);
    document.addEventListener('visibilitychange', C.handler_visibility);
    new MutationObserver(C.handler_mode).observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-mode']
    });
    new ResizeObserver(() => V.resize()).observe(V.host);
    new IntersectionObserver(C.handler_intersect).observe(V.host);

    C.start();
};

/**
 * Charge (une seule fois) le modèle d'un univers.
 * @param {'dev'|'photo'} mode
 * @returns {Promise<void>}
 */
C.ensureModel = function (mode) {
    if (V.models[mode]) return Promise.resolve();
    if (!V.pending[mode]) {
        V.pending[mode] = (async () => {
            const holder = V.mountModel(mode, await V.loadModel(M.models[mode].url), C.reducedMotion);
            // Non bloquant : l'objet s'affiche tout de suite, ses textures (écran, grain) arrivent ensuite
            if (mode === 'dev') V.applyScreen(holder, M.screenUrl).catch(() => {});
            if (mode === 'photo') V.applyGrain(holder, M.grainUrl).catch(() => {});
        })().catch((err) => {
            // Échec : on libère l'entrée pour qu'une bascule ultérieure puisse réessayer
            delete V.pending[mode];
            throw err;
        });
    }
    return V.pending[mode];
};

/**
 * Bascule Dev / Photo : charge le modèle voulu si besoin, puis échange avec un fondu.
 * @returns {Promise<void>}
 */
C.handler_mode = async function () {
    const mode = M.readMode();
    if (mode === M.mode) return;
    M.mode = mode;
    try {
        await C.ensureModel(mode);
    } catch {
        V.showFallback();
        return;
    }
    // L'utilisateur a pu rebasculer pendant le téléchargement : on n'affiche que l'univers courant
    if (M.mode === mode) V.swapMode(mode, C.reducedMotion);
};

/**
 * L'objet suit la souris (pas le doigt : un écran tactile n'a pas de survol).
 * @param {PointerEvent} ev
 * @returns {void}
 */
C.handler_follow = function (ev) {
    if (ev.pointerType !== 'mouse' || C.reducedMotion) return;
    M.setFollow(ev.clientX, ev.clientY);
};

/**
 * @param {PointerEvent} ev
 * @returns {void}
 */
C.handler_dragStart = function (ev) {
    M.dragging = true;
    M.last = {x: ev.clientX, y: ev.clientY};
    // Capture : le drag continue même si le pointeur sort du canvas
    V.canvas.setPointerCapture(ev.pointerId);
    V.setGrabbing(true);
};

/**
 * @param {PointerEvent} ev
 * @returns {void}
 */
C.handler_dragMove = function (ev) {
    if (!M.dragging) return;
    M.addDrag(ev.clientX, ev.clientY);
};

/**
 * @returns {void}
 */
C.handler_dragEnd = function () {
    M.dragging = false;
    V.setGrabbing(false);
};

/**
 * @param {IntersectionObserverEntry[]} entries
 * @returns {void}
 */
C.handler_intersect = function ([entry]) {
    M.visible = entry.isIntersecting;
    if (M.visible) C.start();
};

/**
 * @returns {void}
 */
C.handler_visibility = function () {
    if (!document.hidden) C.start();
};

/**
 * Relance la boucle de rendu si elle est arrêtée.
 * @returns {void}
 */
C.start = function () {
    if (C.running) return;
    C.running = true;
    // Remet l'horloge à zéro : évite un « saut » d'animation après une pause
    V.clock.getDelta();
    requestAnimationFrame(C.loop);
};

/**
 * Boucle de rendu : s'arrête seule quand le hero est hors écran ou l'onglet caché.
 * @returns {void}
 */
C.loop = function () {
    if (!M.visible || document.hidden) {
        C.running = false;
        return;
    }
    requestAnimationFrame(C.loop);
    // dt plafonné : après un ralentissement, pas de bond brutal de la rotation
    const dt = Math.min(V.clock.getDelta(), 0.05);
    M.relaxDrag(dt);
    V.render(M.targetRotation(), dt);
};

C.init();
