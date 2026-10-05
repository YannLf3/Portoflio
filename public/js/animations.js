/**
 * @file animations.js
 * @description Animations & micro-interactions du design « Graphite × Lime Spark ».
 *
 * Fonctionnalités :
 *  1. Fond spatial WebGL (particules + répulsion + connexions géométriques au survol)
 *  2. Curseur magnétique (point Lime Spark, agrandi / semi-transparent / attraction des éléments [data-magnetic])
 *  3. Révélation typographique des titres (.reveal-title) au scroll
 *  4. Cartes portfolio : apparition en cascade + tilt 3D suivant la souris (.tilt-card)
 *
 * Architecture M/V/C stricte :
 *  - M (Modèle)     : configuration, état, calculs purs (physique, géométrie). Aucun accès au DOM.
 *  - V (Vue)        : lecture/écriture du DOM, rendu WebGL, déclaration des écouteurs.
 *  - C (Contrôleur) : handlers d'événements, orchestration, boucle requestAnimationFrame.
 * Le Modèle ne parle jamais à la Vue et réciproquement : tout transite par le Contrôleur.
 *
 * Chargé en `defer` depuis app/views/layouts/footer.php.
 */

// IIFE : isole M, V et C de la portée globale (main.js / theme-toggle.js sont des scripts classiques
// qui partagent cette portée) tout en conservant l'appel C.init() en toute fin de script.
(() => {
    'use strict';

    /* =======================================================================
       M — MODÈLE
       ======================================================================= */
    const M = {

        /**
         * Configuration centralisée de toutes les animations (valeurs de réglage).
         * @type {Object}
         */
        config: {
            particles: {
                areaPerParticle: 12000, // px² d'écran par particule → densité constante quelle que soit la taille
                min: 40,
                max: 160,
                speed: [3, 12],         // vitesse de dérive en px/s (lente et discrète)
                size: [1.6, 3.4],       // diamètre en px CSS
                alpha: [0.2, 0.7],
                repelRadius: 140,       // rayon d'influence de la souris (px)
                repelForce: 650,        // accélération max de répulsion (px/s²)
                spring: 6,              // raideur du ressort qui ramène la particule à sa trajectoire
                damping: 0.9,           // amortissement par frame à 60 fps
                linkRadius: 190,        // seules les particules à moins de 190 px de la souris se relient
                linkDistance: 100,      // distance max entre deux particules reliées
                maxLinkCandidates: 48,  // borne le coût O(n²) des connexions
                maxDpr: 1.75            // plafonne la résolution du canvas (perf. sur écrans Retina)
            },
            cursor: {
                ease: 0.22,             // facteur de lissage du suivi (0 = immobile, 1 = instantané)
                magnetStrength: 0.3,    // part du décalage souris/centre transmise à l'élément
                magnetMax: 14,          // déplacement max de l'élément magnétisé (px)
                cursorPull: 0.35        // le point reste attiré vers le centre de l'élément
            },
            tilt: {
                maxDeg: 7               // inclinaison max de la carte sur chaque axe
            },
            reveal: {
                stagger: 110,           // décalage (ms) entre deux cartes d'un même lot
                titleThreshold: 0.35,
                cardThreshold: 0.15,
                readyFallback: 6000     // délai max d'attente de la fin du loader (ms)
            }
        },

        /** Préférences de l'utilisateur, renseignées au démarrage. */
        prefs: {
            reducedMotion: false,
            finePointer: false
        },

        /** Position de la souris dans le viewport (coordonnées client). */
        pointer: {
            x: -9999,
            y: -9999,
            active: false
        },

        /* -------------------------------------------------------------------
           M.particles — Simulation du fond spatial
           ------------------------------------------------------------------- */
        particles: {
            /** @type {Array<Object>} */
            list: [],
            width: 0,
            height: 0,
            time: 0,
            /** Données sommets prêtes pour le GPU : [x, y, taille, alpha] par particule. */
            pointData: new Float32Array(0),
            /** Données segments : 2 sommets × [x, y, taille, alpha] par connexion. */
            lineData: new Float32Array(0),
            lineVertexCount: 0,

            /**
             * Tire un nombre aléatoire dans un intervalle.
             * @param {number[]} range - Intervalle [min, max].
             * @returns {number}
             */
            rand([min, max]) {
                return min + Math.random() * (max - min);
            },

            /**
             * Crée une particule à une position aléatoire de la zone.
             * Sa position de rendu = position de base (dérive) + déplacement (répulsion amortie).
             * @returns {Object} Particule.
             */
            spawn() {
                const cfg = M.config.particles;
                const angle = Math.random() * Math.PI * 2;
                const speed = this.rand(cfg.speed);
                return {
                    bx: Math.random() * this.width,          // position de base
                    by: Math.random() * this.height,
                    vx: Math.cos(angle) * speed,             // vitesse de dérive
                    vy: Math.sin(angle) * speed,
                    dx: 0, dy: 0,                            // déplacement dû à la souris
                    ux: 0, uy: 0,                            // vitesse de ce déplacement
                    size: this.rand(cfg.size),
                    alpha: this.rand(cfg.alpha),
                    twinkle: this.rand([0.6, 1.8]),          // fréquence de scintillement
                    phase: Math.random() * Math.PI * 2
                };
            },

            /**
             * Calcule le nombre de particules adapté à la surface de l'écran.
             * @param {number} width - Largeur en px CSS.
             * @param {number} height - Hauteur en px CSS.
             * @returns {number}
             */
            countFor(width, height) {
                const cfg = M.config.particles;
                return Math.round(Math.min(cfg.max, Math.max(cfg.min, (width * height) / cfg.areaPerParticle)));
            },

            /**
             * (Ré)initialise ou redimensionne la population en conservant les particules existantes
             * (positions remises à l'échelle) pour éviter un « saut » visuel au redimensionnement.
             * @param {number} width - Largeur en px CSS.
             * @param {number} height - Hauteur en px CSS.
             * @returns {void}
             */
            resize(width, height) {
                const sx = this.width ? width / this.width : 1;
                const sy = this.height ? height / this.height : 1;
                this.width = width;
                this.height = height;

                this.list.forEach((p) => {
                    p.bx *= sx;
                    p.by *= sy;
                });

                const target = this.countFor(width, height);
                while (this.list.length < target) this.list.push(this.spawn());
                this.list.length = target;

                // Pré-allocation des buffers : aucun « new » dans la boucle d'animation (pas de GC en continu)
                const cfg = M.config.particles;
                const maxLinks = (cfg.maxLinkCandidates * (cfg.maxLinkCandidates - 1)) / 2;
                this.pointData = new Float32Array(target * 4);
                this.lineData = new Float32Array(maxLinks * 2 * 4);
            },

            /**
             * Avance la simulation d'un pas de temps puis remplit les buffers de rendu.
             * - Dérive linéaire avec rebouclage sur les bords.
             * - Répulsion radiale autour de la souris (force ∝ (1 - d/R)²) appliquée à un ressort amorti :
             *   les particules s'écartent puis reviennent en douceur sur leur trajectoire.
             * - Connexions : seules les particules proches de la souris sont reliées entre elles.
             * @param {number} dt - Pas de temps en secondes (borné par le contrôleur).
             * @param {{x:number, y:number, active:boolean}} pointer - Position de la souris.
             * @param {boolean} animate - false = image figée (prefers-reduced-motion).
             * @returns {void}
             */
            step(dt, pointer, animate) {
                const cfg = M.config.particles;
                const {width, height, list, pointData} = this;
                const margin = 10;
                const repelR = cfg.repelRadius;
                const repelR2 = repelR * repelR;
                // Amortissement indépendant du framerate (défini pour 60 fps)
                const damp = Math.pow(cfg.damping, dt * 60);
                const interact = animate && pointer.active;

                this.time += dt;

                for (let i = 0; i < list.length; i++) {
                    const p = list[i];

                    if (animate) {
                        p.bx += p.vx * dt;
                        p.by += p.vy * dt;

                        // Rebouclage : une particule sortie d'un côté réapparaît de l'autre
                        if (p.bx < -margin) p.bx += width + margin * 2;
                        else if (p.bx > width + margin) p.bx -= width + margin * 2;
                        if (p.by < -margin) p.by += height + margin * 2;
                        else if (p.by > height + margin) p.by -= height + margin * 2;

                        // Ressort : accélération proportionnelle au déplacement, vers la position de base
                        let ax = -p.dx * cfg.spring;
                        let ay = -p.dy * cfg.spring;

                        if (interact) {
                            const ex = p.bx + p.dx - pointer.x;
                            const ey = p.by + p.dy - pointer.y;
                            const d2 = ex * ex + ey * ey;
                            if (d2 < repelR2 && d2 > 0.01) {
                                const d = Math.sqrt(d2);
                                const falloff = 1 - d / repelR;
                                const f = falloff * falloff * cfg.repelForce;
                                ax += (ex / d) * f;
                                ay += (ey / d) * f;
                            }
                        }

                        p.ux = (p.ux + ax * dt) * damp;
                        p.uy = (p.uy + ay * dt) * damp;
                        p.dx += p.ux * dt;
                        p.dy += p.uy * dt;
                    }

                    // Scintillement doux : alpha modulé entre 70 % et 100 % de sa valeur de base
                    const twinkle = animate ? 0.7 + 0.3 * Math.sin(this.time * p.twinkle + p.phase) : 1;
                    const o = i * 4;
                    pointData[o] = p.bx + p.dx;
                    pointData[o + 1] = p.by + p.dy;
                    pointData[o + 2] = p.size;
                    pointData[o + 3] = p.alpha * twinkle;
                }

                this.lineVertexCount = interact ? this.buildLinks(pointer) : 0;
            },

            /**
             * Construit les segments entre particules voisines situées près de la souris.
             * Opacité = proximité entre les deux particules × proximité à la souris.
             * @param {{x:number, y:number}} pointer - Position de la souris.
             * @returns {number} Nombre de sommets écrits dans lineData.
             */
            buildLinks(pointer) {
                const cfg = M.config.particles;
                const pts = this.pointData;
                const lines = this.lineData;
                const linkR2 = cfg.linkRadius * cfg.linkRadius;
                const maxD2 = cfg.linkDistance * cfg.linkDistance;
                const candidates = [];

                // 1. Pré-sélection : particules dans le rayon de la souris (borne le coût de la double boucle)
                for (let i = 0; i < this.list.length && candidates.length < cfg.maxLinkCandidates; i++) {
                    const ex = pts[i * 4] - pointer.x;
                    const ey = pts[i * 4 + 1] - pointer.y;
                    const d2 = ex * ex + ey * ey;
                    if (d2 < linkR2) candidates.push({i, proximity: 1 - Math.sqrt(d2) / cfg.linkRadius});
                }

                // 2. Paires de candidates suffisamment proches → un segment chacune
                let v = 0;
                for (let a = 0; a < candidates.length; a++) {
                    const ia = candidates[a].i * 4;
                    for (let b = a + 1; b < candidates.length; b++) {
                        const ib = candidates[b].i * 4;
                        const dx = pts[ia] - pts[ib];
                        const dy = pts[ia + 1] - pts[ib + 1];
                        const d2 = dx * dx + dy * dy;
                        if (d2 > maxD2) continue;

                        const alpha = (1 - Math.sqrt(d2) / cfg.linkDistance)
                            * Math.min(candidates[a].proximity, candidates[b].proximity) * 0.6;
                        const o = v * 4;
                        lines[o] = pts[ia];
                        lines[o + 1] = pts[ia + 1];
                        lines[o + 2] = 0;
                        lines[o + 3] = alpha;
                        lines[o + 4] = pts[ib];
                        lines[o + 5] = pts[ib + 1];
                        lines[o + 6] = 0;
                        lines[o + 7] = alpha;
                        v += 2;
                    }
                }
                return v;
            }
        },

        /* -------------------------------------------------------------------
           M.cursor — Position lissée et magnétisme
           ------------------------------------------------------------------- */
        cursor: {
            enabled: false,
            x: -9999,
            y: -9999,
            targetX: -9999,
            targetY: -9999,
            /** Élément actuellement magnétisé (référence opaque, jamais manipulée par le Modèle). */
            magneticEl: null,
            /** Zone « visionneuse » survolée (data-cursor), pour ne réécrire le libellé qu'au changement. */
            viewEl: null,
            /** Décalage actuellement appliqué à l'élément magnétisé. */
            applied: {x: 0, y: 0},

            /**
             * Définit la position que le point doit rejoindre.
             * @param {number} x
             * @param {number} y
             * @returns {void}
             */
            setTarget(x, y) {
                this.targetX = x;
                this.targetY = y;
            },

            /**
             * Interpole la position vers la cible (lissage exponentiel indépendant du framerate).
             * @param {number} dt - Pas de temps en secondes.
             * @param {boolean} instant - true = pas de lissage (prefers-reduced-motion).
             * @returns {{x:number, y:number}} Position à afficher.
             */
            follow(dt, instant) {
                // Premier placement ou mode réduit : on se cale directement sur la cible
                if (instant || this.x === -9999) {
                    this.x = this.targetX;
                    this.y = this.targetY;
                } else {
                    const k = 1 - Math.pow(1 - M.config.cursor.ease, dt * 60);
                    this.x += (this.targetX - this.x) * k;
                    this.y += (this.targetY - this.y) * k;
                }
                return {x: this.x, y: this.y};
            },

            /**
             * Calcule l'effet magnétique entre la souris et un élément.
             * Le centre « au repos » est retrouvé en retirant le décalage déjà appliqué,
             * sinon l'élément se poursuivrait lui-même (boucle de rétroaction).
             * @param {{left:number, top:number, width:number, height:number}} rect - Boîte de l'élément.
             * @param {number} mx - Abscisse de la souris.
             * @param {number} my - Ordonnée de la souris.
             * @returns {{offsetX:number, offsetY:number, cursorX:number, cursorY:number}}
             */
            computeMagnet(rect, mx, my) {
                const cfg = M.config.cursor;
                const cx = rect.left + rect.width / 2 - this.applied.x;
                const cy = rect.top + rect.height / 2 - this.applied.y;
                const ox = mx - cx;
                const oy = my - cy;
                const clamp = (val) => Math.max(-cfg.magnetMax, Math.min(cfg.magnetMax, val));

                this.applied = {x: clamp(ox * cfg.magnetStrength), y: clamp(oy * cfg.magnetStrength)};
                return {
                    offsetX: this.applied.x,
                    offsetY: this.applied.y,
                    cursorX: cx + ox * cfg.cursorPull,
                    cursorY: cy + oy * cfg.cursorPull
                };
            },

            /**
             * Oublie l'élément magnétisé courant.
             * @returns {void}
             */
            releaseMagnet() {
                this.magneticEl = null;
                this.applied = {x: 0, y: 0};
            }
        },

        /* -------------------------------------------------------------------
           M.tilt — Géométrie du tilt 3D
           ------------------------------------------------------------------- */
        tilt: {
            /** Mouvement en attente de rendu (throttling par requestAnimationFrame). */
            pending: null,
            frame: 0,

            /**
             * Convertit la position de la souris sur la carte en angles de rotation.
             * Coordonnées normalisées (0 → 1) puis centrées (-0.5 → 0.5) : la zone survolée « se lève ».
             * @param {{left:number, top:number, width:number, height:number}} rect - Boîte de la carte.
             * @param {number} x - Abscisse de la souris.
             * @param {number} y - Ordonnée de la souris.
             * @returns {{rx:string, ry:string, glareX:string, glareY:string}} Valeurs CSS prêtes à poser.
             */
            compute(rect, x, y) {
                const max = M.config.tilt.maxDeg;
                const nx = Math.min(1, Math.max(0, (x - rect.left) / rect.width));
                const ny = Math.min(1, Math.max(0, (y - rect.top) / rect.height));
                return {
                    rx: `${((ny - 0.5) * 2 * max).toFixed(2)}deg`,
                    ry: `${((0.5 - nx) * 2 * max).toFixed(2)}deg`,
                    glareX: `${(nx * 100).toFixed(1)}%`,
                    glareY: `${(ny * 100).toFixed(1)}%`
                };
            }
        }
    };

    /* =======================================================================
       V — VUE
       ======================================================================= */
    const V = {

        /**
         * Lit les préférences système (mouvement réduit, type de pointeur).
         * @returns {{reducedMotion:boolean, finePointer:boolean}}
         */
        readPreferences() {
            return {
                reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
                finePointer: window.matchMedia('(hover: hover) and (pointer: fine)').matches
            };
        },

        /**
         * Dimensions courantes du viewport.
         * @returns {{width:number, height:number, dpr:number}}
         */
        getViewport() {
            return {
                width: window.innerWidth,
                height: window.innerHeight,
                dpr: window.devicePixelRatio || 1
            };
        },

        /**
         * Déclare les écouteurs globaux (un seul de chaque type, partagés par toutes les fonctionnalités).
         * @param {Object} handlers - {onMouseMove, onMouseOut, onMouseDown, onMouseUp, onScroll, onResize, onModeChange}.
         * @returns {void}
         */
        bindGlobalEvents(handlers) {
            window.addEventListener('mousemove', handlers.onMouseMove, {passive: true});
            window.addEventListener('mouseout', handlers.onMouseOut);
            window.addEventListener('mousedown', handlers.onMouseDown);
            window.addEventListener('mouseup', handlers.onMouseUp);
            window.addEventListener('scroll', handlers.onScroll, {passive: true});

            // Debounce : on ne recalcule le canvas qu'une fois le redimensionnement terminé
            let resizeTimer = 0;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(handlers.onResize, 150);
            });

            // Bascule Dev/Photo (theme-toggle.js modifie data-mode sur <html>) → nouvelle couleur d'accent
            new MutationObserver(handlers.onModeChange)
                .observe(document.documentElement, {attributes: true, attributeFilter: ['data-mode']});
        },

        /**
         * Élément affiché sous un point du viewport (hit-test du navigateur).
         * @param {number} x
         * @param {number} y
         * @returns {Element|null}
         */
        elementAt(x, y) {
            return document.elementFromPoint(x, y);
        },

        /**
         * Mode actif du site (attribut data-mode de <html>, piloté par theme-toggle.js).
         * @returns {'dev'|'photo'}
         */
        getMode() {
            return document.documentElement.dataset.mode === 'photo' ? 'photo' : 'dev';
        },

        /**
         * Indique si le loader de main.js a déjà terminé.
         * @returns {boolean}
         */
        isAppReady() {
            return document.documentElement.dataset.appReady === 'true';
        },

        /**
         * Écoute la fin du loader (événement émis par main.js).
         * @param {Function} handler
         * @returns {void}
         */
        bindAppReady(handler) {
            document.addEventListener('app:ready', handler, {once: true});
        },

        /**
         * Crée un IntersectionObserver et observe une liste d'éléments.
         * @param {Element[]} elements - Éléments à observer.
         * @param {IntersectionObserverCallback} handler - Handler du contrôleur.
         * @param {IntersectionObserverInit} options - Seuil, marges.
         * @returns {IntersectionObserver}
         */
        observe(elements, handler, options) {
            const observer = new IntersectionObserver(handler, options);
            elements.forEach((el) => observer.observe(el));
            return observer;
        },

        /* -------------------------------------------------------------------
           V.particles — Rendu WebGL
           ------------------------------------------------------------------- */
        particles: {
            canvas: null,
            gl: null,
            program: null,
            pointBuffer: null,
            lineBuffer: null,
            loc: {},
            colorProbe: null,

            /** Vertex shader : px CSS → espace de clip, taille des points selon le DPR. */
            vertexSource: `
                attribute vec2 a_position;
                attribute float a_size;
                attribute float a_alpha;
                uniform vec2 u_resolution;
                uniform float u_dpr;
                varying float v_alpha;
                void main() {
                    vec2 clip = (a_position / u_resolution) * 2.0 - 1.0;
                    gl_Position = vec4(clip.x, -clip.y, 0.0, 1.0);
                    gl_PointSize = a_size * u_dpr;
                    v_alpha = a_alpha;
                }`,

            /** Fragment shader : disque à bord doux pour les points, trait plein pour les lignes (alpha prémultiplié). */
            fragmentSource: `
                precision mediump float;
                uniform vec3 u_color;
                uniform bool u_isPoint;
                varying float v_alpha;
                void main() {
                    float a = v_alpha;
                    if (u_isPoint) {
                        float d = length(gl_PointCoord - 0.5);
                        a *= smoothstep(0.5, 0.1, d);
                    }
                    gl_FragColor = vec4(u_color * a, a);
                }`,

            /**
             * Récupère le canvas et initialise le contexte WebGL.
             * @returns {boolean} false si l'élément est absent ou WebGL indisponible.
             */
            init() {
                this.canvas = document.getElementById('fx-particles');
                if (!this.canvas) return false;

                const gl = this.canvas.getContext('webgl', {
                    alpha: true,
                    antialias: true,
                    premultipliedAlpha: true,
                    powerPreference: 'low-power'
                });
                if (!gl) {
                    this.canvas.remove();
                    return false;
                }
                this.gl = gl;
                return this.setupProgram();
            },

            /**
             * Compile les shaders, crée le programme et les buffers (aussi rappelé après une perte de contexte).
             * @returns {boolean} Succès de la compilation.
             */
            setupProgram() {
                const gl = this.gl;

                /**
                 * Compile un shader et remonte l'erreur en console si besoin.
                 * @param {number} type - gl.VERTEX_SHADER | gl.FRAGMENT_SHADER.
                 * @param {string} source - Code GLSL.
                 * @returns {WebGLShader|null}
                 */
                const compile = (type, source) => {
                    const shader = gl.createShader(type);
                    gl.shaderSource(shader, source);
                    gl.compileShader(shader);
                    if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
                        console.warn('[animations] Shader :', gl.getShaderInfoLog(shader));
                        return null;
                    }
                    return shader;
                };

                const vs = compile(gl.VERTEX_SHADER, this.vertexSource);
                const fs = compile(gl.FRAGMENT_SHADER, this.fragmentSource);
                if (!vs || !fs) return false;

                const program = gl.createProgram();
                gl.attachShader(program, vs);
                gl.attachShader(program, fs);
                gl.linkProgram(program);
                if (!gl.getProgramParameter(program, gl.LINK_STATUS)) return false;

                this.program = program;
                this.loc = {
                    position: gl.getAttribLocation(program, 'a_position'),
                    size: gl.getAttribLocation(program, 'a_size'),
                    alpha: gl.getAttribLocation(program, 'a_alpha'),
                    resolution: gl.getUniformLocation(program, 'u_resolution'),
                    dpr: gl.getUniformLocation(program, 'u_dpr'),
                    color: gl.getUniformLocation(program, 'u_color'),
                    isPoint: gl.getUniformLocation(program, 'u_isPoint')
                };
                this.pointBuffer = gl.createBuffer();
                this.lineBuffer = gl.createBuffer();

                gl.useProgram(program);
                gl.enable(gl.BLEND);
                // Alpha prémultiplié (cohérent avec premultipliedAlpha:true) : pas de halo sombre sur les bords
                gl.blendFunc(gl.ONE, gl.ONE_MINUS_SRC_ALPHA);
                return true;
            },

            /**
             * Déclare les écouteurs de perte / restauration du contexte WebGL (veille GPU, onglets nombreux).
             * @param {Function} onLost
             * @param {Function} onRestored
             * @returns {void}
             */
            bindContextEvents(onLost, onRestored) {
                this.canvas.addEventListener('webglcontextlost', (e) => {
                    e.preventDefault(); // indispensable pour autoriser la restauration
                    onLost();
                });
                this.canvas.addEventListener('webglcontextrestored', onRestored);
            },

            /**
             * Adapte la résolution interne du canvas à l'écran.
             * @param {number} width - Largeur en px CSS.
             * @param {number} height - Hauteur en px CSS.
             * @param {number} dpr - Ratio de pixels (déjà plafonné).
             * @returns {void}
             */
            resize(width, height, dpr) {
                const gl = this.gl;
                this.canvas.width = Math.round(width * dpr);
                this.canvas.height = Math.round(height * dpr);
                gl.viewport(0, 0, this.canvas.width, this.canvas.height);
                gl.uniform2f(this.loc.resolution, width, height);
                gl.uniform1f(this.loc.dpr, dpr);
            },

            /**
             * Lit la couleur d'accent active (--accent) et la convertit en RGB normalisé.
             * Passe par un canvas 2D d'1 px : le navigateur résout lui-même tout format CSS (hex, oklch…).
             * @returns {number[]} [r, g, b] entre 0 et 1.
             */
            readAccentColor() {
                const raw = getComputedStyle(document.documentElement).getPropertyValue('--accent').trim();
                if (!this.colorProbe) {
                    const probe = document.createElement('canvas');
                    probe.width = probe.height = 1;
                    this.colorProbe = probe.getContext('2d', {willReadFrequently: true});
                }
                const ctx = this.colorProbe;
                ctx.clearRect(0, 0, 1, 1);
                ctx.fillStyle = '#B6FF2E'; // valeur de repli Lime Spark si le format n'est pas reconnu
                ctx.fillStyle = raw || '#B6FF2E';
                ctx.fillRect(0, 0, 1, 1);
                const [r, g, b] = ctx.getImageData(0, 0, 1, 1).data;
                return [r / 255, g / 255, b / 255];
            },

            /**
             * Applique la couleur des particules.
             * @param {number[]} rgb - [r, g, b] normalisés.
             * @returns {void}
             */
            setColor(rgb) {
                this.gl.uniform3f(this.loc.color, rgb[0], rgb[1], rgb[2]);
            },

            /**
             * Envoie un lot de sommets au GPU et décrit leur format (stride de 4 floats).
             * @param {WebGLBuffer} buffer
             * @param {Float32Array} data
             * @returns {void}
             */
            upload(buffer, data) {
                const gl = this.gl;
                const stride = 4 * Float32Array.BYTES_PER_ELEMENT;
                gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
                gl.bufferData(gl.ARRAY_BUFFER, data, gl.DYNAMIC_DRAW);
                gl.enableVertexAttribArray(this.loc.position);
                gl.vertexAttribPointer(this.loc.position, 2, gl.FLOAT, false, stride, 0);
                gl.enableVertexAttribArray(this.loc.size);
                gl.vertexAttribPointer(this.loc.size, 1, gl.FLOAT, false, stride, 8);
                gl.enableVertexAttribArray(this.loc.alpha);
                gl.vertexAttribPointer(this.loc.alpha, 1, gl.FLOAT, false, stride, 12);
            },

            /**
             * Dessine une frame : connexions d'abord (sous les points), puis particules.
             * @param {Float32Array} points - Données des particules.
             * @param {number} pointCount - Nombre de particules.
             * @param {Float32Array} lines - Données des segments.
             * @param {number} lineVertexCount - Nombre de sommets de segments.
             * @returns {void}
             */
            draw(points, pointCount, lines, lineVertexCount) {
                const gl = this.gl;
                gl.clearColor(0, 0, 0, 0);
                gl.clear(gl.COLOR_BUFFER_BIT);

                if (lineVertexCount > 0) {
                    this.upload(this.lineBuffer, lines.subarray(0, lineVertexCount * 4));
                    gl.uniform1i(this.loc.isPoint, 0);
                    gl.drawArrays(gl.LINES, 0, lineVertexCount);
                }

                this.upload(this.pointBuffer, points.subarray(0, pointCount * 4));
                gl.uniform1i(this.loc.isPoint, 1);
                gl.drawArrays(gl.POINTS, 0, pointCount);
            },

            /**
             * Révèle le canvas (fondu) une fois la première frame dessinée.
             * @returns {void}
             */
            show() {
                this.canvas.classList.remove('fx-particles--idle');
            }
        },

        /* -------------------------------------------------------------------
           V.cursor — Point Lime Spark & magnétisme
           ------------------------------------------------------------------- */
        cursor: {
            el: null,
            interactiveSelector: 'a, button, [role="button"], label, summary, select, [data-magnetic]',
            magneticSelector: '[data-magnetic]',
            /** Zones « visionneuse » : images de la galerie, comparateur avant/après… */
            viewSelector: '[data-cursor]',
            labelEl: null,

            /**
             * Récupère l'élément curseur et masque le curseur natif.
             * @returns {boolean} false si le composant est absent du DOM.
             */
            init() {
                this.el = document.getElementById('custom-cursor');
                if (!this.el) return false;
                this.labelEl = this.el.querySelector('.cursor__label');
                document.documentElement.classList.add('has-custom-cursor');
                return true;
            },

            /**
             * Trouve l'élément interactif sous la souris (délégation : un seul écouteur global).
             * @param {EventTarget} target
             * @returns {Element|null}
             */
            findInteractive(target) {
                return target instanceof Element ? target.closest(this.interactiveSelector) : null;
            },

            /**
             * Trouve l'élément magnétique sous la souris.
             * @param {EventTarget} target
             * @returns {Element|null}
             */
            findMagnetic(target) {
                return target instanceof Element ? target.closest(this.magneticSelector) : null;
            },

            /**
             * Trouve la zone « visionneuse » sous la souris (attribut data-cursor).
             * @param {EventTarget} target
             * @returns {HTMLElement|null}
             */
            findView(target) {
                return target instanceof Element ? target.closest(this.viewSelector) : null;
            },

            /**
             * Texte affiché dans le grand cercle du mode Photo (« Voir », « Glisser »…).
             * @param {HTMLElement|null} viewEl - Zone survolée (son data-cursor-label est lu).
             * @returns {void}
             */
            setLabel(viewEl) {
                if (this.labelEl) this.labelEl.textContent = viewEl?.dataset.cursorLabel ?? '';
            },

            /**
             * Déplace le point (transform uniquement : pas de reflow).
             * @param {number} x
             * @param {number} y
             * @returns {void}
             */
            move(x, y) {
                this.el.style.transform = `translate3d(${x}px, ${y}px, 0)`;
            },

            /**
             * Met à jour les modifiers BEM d'état du curseur.
             * @param {{visible?:boolean, hover?:boolean, magnetic?:boolean, view?:boolean, pressed?:boolean}} state
             * @returns {void}
             */
            setState(state) {
                const list = this.el.classList;
                if ('visible' in state) list.toggle('is-visible', state.visible);
                // Priorité : visionneuse > magnétique > simple survol
                if ('hover' in state) list.toggle('cursor--hover', state.hover && !state.magnetic && !state.view);
                if ('magnetic' in state) list.toggle('cursor--magnetic', state.magnetic && !state.view);
                if ('view' in state) list.toggle('cursor--view', state.view);
                if ('pressed' in state) list.toggle('cursor--pressed', state.pressed);
            }
        },

        /* -------------------------------------------------------------------
           V.magnet — Déplacement des éléments [data-magnetic]
           ------------------------------------------------------------------- */
        magnet: {
            /**
             * Boîte englobante actuelle de l'élément.
             * @param {Element} el
             * @returns {DOMRect}
             */
            getRect(el) {
                return el.getBoundingClientRect();
            },

            /**
             * Attire l'élément vers la souris via la propriété CSS `translate`
             * (indépendante de `transform` : n'écrase pas les utilitaires scale/rotate de Tailwind).
             * @param {HTMLElement} el
             * @param {number} x - Décalage horizontal (px).
             * @param {number} y - Décalage vertical (px).
             * @returns {void}
             */
            apply(el, x, y) {
                el.style.translate = `${x.toFixed(1)}px ${y.toFixed(1)}px`;
            },

            /**
             * Relâche l'élément (retour animé par la transition CSS existante).
             * @param {HTMLElement} el
             * @returns {void}
             */
            reset(el) {
                el.style.translate = '';
            }
        },

        /* -------------------------------------------------------------------
           V.reveal — Titres & cartes révélés au scroll
           ------------------------------------------------------------------- */
        reveal: {
            /**
             * Liste les titres à révéler.
             * @returns {Element[]}
             */
            getTitles() {
                return [...document.querySelectorAll('.reveal-title')];
            },

            /**
             * Liste les cartes portfolio animées.
             * @returns {Element[]}
             */
            getCards() {
                return [...document.querySelectorAll('[data-tilt]')];
            },

            /**
             * Révèle un élément, avec un délai optionnel (effet cascade).
             * @param {HTMLElement} el
             * @param {number} [delay=0] - Délai en ms.
             * @returns {void}
             */
            show(el, delay = 0) {
                if (delay) el.style.setProperty('--_delay', `${delay}ms`);
                el.classList.add('is-revealed');
            },

            /**
             * Supprime le délai de cascade une fois l'apparition terminée
             * (sinon le tilt hériterait du transition-delay).
             * @param {HTMLElement} el
             * @returns {void}
             */
            clearDelay(el) {
                el.style.removeProperty('--_delay');
            }
        },

        /* -------------------------------------------------------------------
           V.tilt — Inclinaison 3D des cartes
           ------------------------------------------------------------------- */
        tilt: {
            grid: null,

            /**
             * Récupère la grille des projets (ancêtre commun des cartes).
             * @returns {boolean}
             */
            init() {
                this.grid = document.getElementById('projects-grid');
                return Boolean(this.grid);
            },

            /**
             * Délégation : un seul jeu d'écouteurs sur la grille pour toutes les cartes.
             * @param {{onMove:Function, onOut:Function, onTransitionEnd:Function}} handlers
             * @param {boolean} withTilt - false = seule la fin d'apparition est écoutée.
             * @returns {void}
             */
            bind(handlers, withTilt) {
                if (withTilt) {
                    this.grid.addEventListener('mousemove', handlers.onMove, {passive: true});
                    this.grid.addEventListener('mouseout', handlers.onOut);
                }
                this.grid.addEventListener('transitionend', handlers.onTransitionEnd);
            },

            /**
             * Carte contenant la cible d'un événement.
             * @param {EventTarget} target
             * @returns {HTMLElement|null}
             */
            cardFrom(target) {
                return target instanceof Element ? target.closest('[data-tilt]') : null;
            },

            /**
             * Carte réellement quittée lors d'un mouseout (ignore les passages entre enfants de la carte).
             * @param {MouseEvent} e
             * @returns {HTMLElement|null}
             */
            leftCard(e) {
                const card = this.cardFrom(e.target);
                return card && !card.contains(e.relatedTarget) ? card : null;
            },

            /**
             * Boîte englobante de la carte.
             * @param {HTMLElement} card
             * @returns {DOMRect}
             */
            getRect(card) {
                return card.getBoundingClientRect();
            },

            /**
             * Applique les variables privées de la carte (le transform est composé en CSS).
             * @param {HTMLElement} card
             * @param {{rx:string, ry:string, glareX:string, glareY:string}} v
             * @returns {void}
             */
            apply(card, v) {
                card.classList.add('is-tilting');
                card.style.setProperty('--_rx', v.rx);
                card.style.setProperty('--_ry', v.ry);
                card.style.setProperty('--_glare-x', v.glareX);
                card.style.setProperty('--_glare-y', v.glareY);
            },

            /**
             * Remet la carte à plat (transition de retour longue définie en CSS).
             * @param {HTMLElement} card
             * @returns {void}
             */
            reset(card) {
                card.classList.remove('is-tilting');
                card.style.removeProperty('--_rx');
                card.style.removeProperty('--_ry');
            }
        }
    };

    /* =======================================================================
       C — CONTRÔLEUR
       ======================================================================= */
    const C = {
        lastTime: 0,
        scrollFrame: 0,
        loopRunning: false,
        particlesEnabled: false,
        particlesAnimated: false,

        /**
         * Point d'entrée : lit les préférences, initialise chaque fonctionnalité, puis la boucle.
         * @returns {void}
         */
        init() {
            M.prefs = V.readPreferences();

            C.initParticles();
            C.initCursor();
            C.initTilt();
            // Les révélations attendent la fin du loader terminal (sinon elles se joueraient dessous)
            C.whenAppReady(C.initReveal);

            V.bindGlobalEvents({
                onMouseMove: C.handleMouseMove,
                onMouseOut: C.handleMouseOut,
                onMouseDown: C.handleMouseDown,
                onMouseUp: C.handleMouseUp,
                onScroll: C.handleScroll,
                onResize: C.handleResize,
                onModeChange: C.handleModeChange
            });

            C.startLoop();
        },

        /**
         * Exécute un callback une seule fois quand le loader a fini (ou après un délai de sécurité).
         * @param {Function} callback
         * @returns {void}
         */
        whenAppReady(callback) {
            let done = false;
            const run = () => {
                if (done) return;
                done = true;
                callback();
            };
            if (V.isAppReady()) return run();
            V.bindAppReady(run);
            // Filet de sécurité : les titres ne doivent jamais rester masqués
            setTimeout(run, M.config.reveal.readyFallback);
        },

        /* ---------------------------- Particules ---------------------------- */

        /**
         * Initialise le fond WebGL. En mouvement réduit : une seule image fixe, sans interaction.
         * @returns {void}
         */
        initParticles() {
            if (!V.particles.init()) return;

            C.particlesEnabled = true;
            // Animées uniquement en mode Dev : en mode Photo le canvas est masqué, inutile de calculer
            C.particlesAnimated = !M.prefs.reducedMotion && V.getMode() === 'dev';
            V.particles.bindContextEvents(C.handleContextLost, C.handleContextRestored);
            C.handleResize();
            V.particles.setColor(V.particles.readAccentColor());
            C.renderParticles(0);
            V.particles.show();
        },

        /**
         * Avance la simulation puis dessine.
         * @param {number} dt - Pas de temps (s).
         * @returns {void}
         */
        renderParticles(dt) {
            const sim = M.particles;
            sim.step(dt, M.pointer, C.particlesAnimated);
            V.particles.draw(sim.pointData, sim.list.length, sim.lineData, sim.lineVertexCount);
        },

        /**
         * Contexte WebGL perdu : on suspend le rendu.
         * @returns {void}
         */
        handleContextLost() {
            C.particlesEnabled = false;
        },

        /**
         * Contexte restauré : recompilation des shaders et reprise.
         * @returns {void}
         */
        handleContextRestored() {
            if (!V.particles.setupProgram()) return;
            C.particlesEnabled = true;
            C.handleResize();
            V.particles.setColor(V.particles.readAccentColor());
            C.renderParticles(0);
        },

        /* ----------------------------- Curseur ----------------------------- */

        /**
         * Active le curseur custom uniquement avec une souris / un trackpad (jamais au tactile).
         * @returns {void}
         */
        initCursor() {
            if (!M.prefs.finePointer || !V.cursor.init()) return;
            M.cursor.enabled = true;
        },

        /**
         * Handler global mousemove : alimente les particules, le curseur et le magnétisme.
         * @param {MouseEvent} e
         * @returns {void}
         */
        handleMouseMove(e) {
            M.pointer.x = e.clientX;
            M.pointer.y = e.clientY;
            M.pointer.active = true;

            if (!M.cursor.enabled) return;
            C.updateCursorTarget(e.target, e.clientX, e.clientY);
        },

        /**
         * Défilement sans mouvement de souris : l'élément sous le pointeur change (photo → comparateur…).
         * On réévalue la cible au plus une fois par frame, sinon libellé et état resteraient périmés.
         * @returns {void}
         */
        handleScroll() {
            if (!M.cursor.enabled || !M.pointer.active || C.scrollFrame) return;
            C.scrollFrame = requestAnimationFrame(() => {
                C.scrollFrame = 0;
                const target = V.elementAt(M.pointer.x, M.pointer.y);
                if (target) C.updateCursorTarget(target, M.pointer.x, M.pointer.y);
            });
        },

        /**
         * Met à jour le curseur pour un élément survolé : magnétisme, zone visionneuse, états BEM.
         * @param {EventTarget} target - Élément sous le pointeur.
         * @param {number} x - Abscisse du pointeur.
         * @param {number} y - Ordonnée du pointeur.
         * @returns {void}
         */
        updateCursorTarget(target, x, y) {
            const interactive = V.cursor.findInteractive(target);
            // Pas d'attraction des éléments en mouvement réduit : seul le grossissement du point reste
            const magnetic = M.prefs.reducedMotion ? null : V.cursor.findMagnetic(target);

            // Changement d'élément magnétique : on relâche l'ancien
            if (magnetic !== M.cursor.magneticEl) {
                if (M.cursor.magneticEl) V.magnet.reset(M.cursor.magneticEl);
                M.cursor.releaseMagnet();
                M.cursor.magneticEl = magnetic;
            }

            if (magnetic) {
                const m = M.cursor.computeMagnet(V.magnet.getRect(magnetic), x, y);
                V.magnet.apply(magnetic, m.offsetX, m.offsetY);
                M.cursor.setTarget(m.cursorX, m.cursorY);
            } else {
                M.cursor.setTarget(x, y);
            }

            // Zone visionneuse : le libellé n'est réécrit que lorsque la zone change
            const view = V.cursor.findView(target);
            if (view !== M.cursor.viewEl) {
                M.cursor.viewEl = view;
                V.cursor.setLabel(view);
            }

            V.cursor.setState({
                visible: true,
                hover: Boolean(interactive),
                magnetic: Boolean(magnetic),
                view: Boolean(view)
            });
        },

        /**
         * Souris sortie de la fenêtre (relatedTarget null) : on masque le point et coupe l'interaction.
         * @param {MouseEvent} e
         * @returns {void}
         */
        handleMouseOut(e) {
            if (e.relatedTarget) return;
            M.pointer.active = false;
            if (!M.cursor.enabled) return;
            if (M.cursor.magneticEl) V.magnet.reset(M.cursor.magneticEl);
            M.cursor.releaseMagnet();
            M.cursor.viewEl = null;
            V.cursor.setState({visible: false, hover: false, magnetic: false, view: false});
        },

        /**
         * Pression du bouton : compression du point.
         * @returns {void}
         */
        handleMouseDown() {
            if (M.cursor.enabled) V.cursor.setState({pressed: true});
        },

        /**
         * Relâchement du bouton.
         * @returns {void}
         */
        handleMouseUp() {
            if (M.cursor.enabled) V.cursor.setState({pressed: false});
        },

        /* --------------------------- Boucle rAF ---------------------------- */

        /**
         * Démarre la boucle d'animation si au moins une fonctionnalité en a besoin.
         * @returns {void}
         */
        startLoop() {
            if (C.loopRunning || (!C.particlesAnimated && !M.cursor.enabled)) return;
            C.loopRunning = true;
            requestAnimationFrame(C.tick);
        },

        /**
         * Frame d'animation. dt est borné à 50 ms : au retour d'un onglet inactif,
         * la simulation ne fait pas de « saut » brutal.
         * @param {number} now - Horodatage fourni par requestAnimationFrame.
         * @returns {void}
         */
        tick(now) {
            const dt = C.lastTime ? Math.min((now - C.lastTime) / 1000, 0.05) : 0;
            C.lastTime = now;

            if (C.particlesEnabled && C.particlesAnimated) C.renderParticles(dt);

            if (M.cursor.enabled && M.cursor.targetX !== -9999) {
                const pos = M.cursor.follow(dt, M.prefs.reducedMotion);
                V.cursor.move(pos.x, pos.y);
            }

            requestAnimationFrame(C.tick);
        },

        /**
         * Redimensionnement (debouncé par la Vue) : canvas + population de particules.
         * @returns {void}
         */
        handleResize() {
            if (!C.particlesEnabled) return;
            const vp = V.getViewport();
            const dpr = Math.min(vp.dpr, M.config.particles.maxDpr);
            M.particles.resize(vp.width, vp.height);
            V.particles.resize(vp.width, vp.height, dpr);
            // En mode figé, la boucle ne tourne pas : on redessine manuellement
            if (!C.particlesAnimated) C.renderParticles(0);
        },

        /**
         * Bascule Dev ↔ Photo : nouvelle couleur d'accent, et suspension / reprise de l'animation.
         * @returns {void}
         */
        handleModeChange() {
            if (!C.particlesEnabled) return;
            V.particles.setColor(V.particles.readAccentColor());
            // Mode Photo : rendu suspendu (canvas effacé en CSS) ; retour en mode Dev : reprise de la boucle
            C.particlesAnimated = !M.prefs.reducedMotion && V.getMode() === 'dev';
            if (C.particlesAnimated) C.startLoop();
            else C.renderParticles(0);
        },

        /* ---------------------------- Révélations --------------------------- */

        /**
         * Observe titres et cartes. Sans IntersectionObserver ou en mouvement réduit : tout est révélé.
         * @returns {void}
         */
        initReveal() {
            const titles = V.reveal.getTitles();
            const cards = V.reveal.getCards();

            if (M.prefs.reducedMotion || !('IntersectionObserver' in window)) {
                [...titles, ...cards].forEach((el) => V.reveal.show(el));
                return;
            }

            V.observe(titles, C.handleTitleIntersect, {
                threshold: M.config.reveal.titleThreshold,
                rootMargin: '0px 0px -8% 0px'
            });
            V.observe(cards, C.handleCardIntersect, {
                threshold: M.config.reveal.cardThreshold,
                rootMargin: '0px 0px -5% 0px'
            });
        },

        /**
         * Titre entré dans le viewport : glissement puis soulignement (enchaînés en CSS).
         * @param {IntersectionObserverEntry[]} entries
         * @param {IntersectionObserver} observer
         * @returns {void}
         */
        handleTitleIntersect(entries, observer) {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                V.reveal.show(entry.target);
                observer.unobserve(entry.target); // animation jouée une seule fois
            });
        },

        /**
         * Cartes entrées dans le viewport : les cartes d'un même lot apparaissent en cascade
         * (index dans le lot × pas de stagger), dans l'ordre du DOM.
         * @param {IntersectionObserverEntry[]} entries
         * @param {IntersectionObserver} observer
         * @returns {void}
         */
        handleCardIntersect(entries, observer) {
            entries
                .filter((entry) => entry.isIntersecting)
                .forEach((entry, index) => {
                    V.reveal.show(entry.target, index * M.config.reveal.stagger);
                    observer.unobserve(entry.target);
                });
        },

        /* ------------------------------ Tilt 3D ----------------------------- */

        /**
         * Active le tilt (souris uniquement, hors mouvement réduit) et le nettoyage post-apparition.
         * @returns {void}
         */
        initTilt() {
            if (!V.tilt.init()) return;
            const withTilt = M.prefs.finePointer && !M.prefs.reducedMotion;
            V.tilt.bind({
                onMove: C.handleTiltMove,
                onOut: C.handleTiltOut,
                onTransitionEnd: C.handleCardTransitionEnd
            }, withTilt);
        },

        /**
         * mousemove local sur la grille : mémorise la carte et la position, rendu au prochain frame.
         * @param {MouseEvent} e
         * @returns {void}
         */
        handleTiltMove(e) {
            const card = V.tilt.cardFrom(e.target);
            if (!card) return;
            M.tilt.pending = {card, x: e.clientX, y: e.clientY};
            // Throttling : au plus un calcul par frame, même si mousemove se déclenche plus souvent
            if (!M.tilt.frame) M.tilt.frame = requestAnimationFrame(C.flushTilt);
        },

        /**
         * Calcule et applique l'inclinaison en attente.
         * @returns {void}
         */
        flushTilt() {
            M.tilt.frame = 0;
            const pending = M.tilt.pending;
            if (!pending) return;
            V.tilt.apply(pending.card, M.tilt.compute(V.tilt.getRect(pending.card), pending.x, pending.y));
        },

        /**
         * Sortie réelle d'une carte : retour à plat.
         * @param {MouseEvent} e
         * @returns {void}
         */
        handleTiltOut(e) {
            const card = V.tilt.leftCard(e);
            if (!card) return;
            if (M.tilt.pending?.card === card) M.tilt.pending = null;
            V.tilt.reset(card);
        },

        /**
         * Fin de l'apparition d'une carte (transition d'opacité) : suppression du délai de cascade.
         * @param {TransitionEvent} e
         * @returns {void}
         */
        handleCardTransitionEnd(e) {
            if (e.propertyName !== 'opacity') return;
            const card = V.tilt.cardFrom(e.target);
            if (card === e.target) V.reveal.clearDelay(card);
        }
    };

    C.init();
})();
