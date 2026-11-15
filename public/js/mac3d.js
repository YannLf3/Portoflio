/**
 * @file mac3d.js
 * @description MacBook Air 3D du hero (three.js) : il suit le curseur et se manipule au drag (souris ou doigt).
 *
 * - Le modèle /assets/models/mac.glb s'ouvre à l'arrivée (animation « LidOpen » exportée depuis Blender).
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

/** Chemin du modèle 3D. */
M.modelUrl = '/assets/models/mac.glb';

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
 * Centre et met à l'échelle le modèle, prépare l'animation d'ouverture puis l'ajoute à la scène.
 * @param {object} gltf
 * @param {boolean} startOpen true = écran déjà ouvert (mouvement réduit).
 * @returns {void}
 */
V.mountModel = function (gltf, startOpen) {
    const model = gltf.scene;

    // Écran et verre : reflet atténué, sinon l'écran vire au blanc vu de face
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
    // La plus grande dimension du Mac occupe 2.2 unités de scène
    const scale = 2.2 / Math.max(size.x, size.y, size.z);
    model.scale.setScalar(scale);
    model.position.sub(center.multiplyScalar(scale));

    if (action) {
        // Arrivée en fin de clip, l'action passe en pause : reset() la relance depuis le début
        action.reset();
        action.time = startOpen ? action.getClip().duration : 0;
        this.mixer.update(0);
    }

    this.pivot.add(model);
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

    try {
        V.mountModel(await V.loadModel(M.modelUrl), this.reducedMotion);
    } catch {
        V.abort();
        return;
    }

    V.showCanvas();
    V.resize();

    window.addEventListener('pointermove', C.handler_follow);
    V.canvas.addEventListener('pointerdown', C.handler_dragStart);
    V.canvas.addEventListener('pointermove', C.handler_dragMove);
    V.canvas.addEventListener('pointerup', C.handler_dragEnd);
    V.canvas.addEventListener('pointercancel', C.handler_dragEnd);
    document.addEventListener('visibilitychange', C.handler_visibility);
    new ResizeObserver(() => V.resize()).observe(V.host);
    new IntersectionObserver(C.handler_intersect).observe(V.host);

    C.start();
};

/**
 * Le Mac suit la souris (pas le doigt : un écran tactile n'a pas de survol).
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
