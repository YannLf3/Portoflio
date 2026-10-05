/**
 * @file theme-toggle.js
 * @description Bascule du double univers « Mode Dev » ↔ « Mode Photo ».
 *
 * - Interrupteur accessible : <button role="switch" aria-checked> (Espace / Entrée natifs).
 * - Le mode est porté par l'attribut data-mode de <html> : tout le CSS (tokens sémantiques,
 *   police des titres, galerie, curseur…) réagit à ce seul attribut.
 * - Mémorisation dans localStorage ; le mode est réappliqué AVANT le premier rendu par le
 *   script inline du <head> (header.php), ce fichier ne fait que synchroniser l'interrupteur.
 * - Transition : View Transitions API (révélation circulaire depuis l'interrupteur + morphing
 *   des photos de la galerie). Repli : onde #theme-transition-overlay. Rien en mouvement réduit.
 * - Les autres scripts demandent un changement via l'événement `mode:request` (ex : commande
 *   `theme photo` du terminal), sans dépendre du markup de l'interrupteur.
 *
 * Architecture M/V/C. Chargé (synchrone) juste après l'interrupteur, dans header.php.
 */

// IIFE : M, V et C restent privés (pas de collision avec les autres scripts classiques).
(() => {
    'use strict';

    /* =======================================================================
       M — MODÈLE
       ======================================================================= */
    const M = {
        /** Modes disponibles (le premier est le mode par défaut). */
        modes: ['dev', 'photo'],
        /** Clé de mémorisation (partagée avec le script inline du <head>). */
        storageKey: 'portfolio-mode',
        /** Mode courant. */
        current: 'dev',
        /** Durée de la révélation circulaire (ms). */
        revealDuration: 750,

        /**
         * Ramène une valeur quelconque à un mode valide.
         * @param {*} mode
         * @returns {'dev'|'photo'}
         */
        normalize(mode) {
            return this.modes.includes(mode) ? mode : this.modes[0];
        },

        /**
         * Mode opposé au mode courant.
         * @returns {'dev'|'photo'}
         */
        other() {
            return this.current === 'dev' ? 'photo' : 'dev';
        },

        /**
         * Définit le mode courant et le mémorise.
         * try/catch : localStorage peut être bloqué (navigation privée stricte, cookies refusés).
         * @param {'dev'|'photo'} mode
         * @returns {void}
         */
        set(mode) {
            this.current = this.normalize(mode);
            try {
                localStorage.setItem(this.storageKey, this.current);
            } catch (error) {
                // Mémorisation impossible : la bascule fonctionne quand même pour la page courante
            }
        }
    };

    /* =======================================================================
       V — VUE
       ======================================================================= */
    const V = {
        root: document.documentElement,
        switchEl: null,
        overlay: null,

        /**
         * Récupère l'interrupteur et l'overlay de repli.
         * @returns {boolean} false si l'interrupteur est absent.
         */
        init() {
            this.switchEl = document.getElementById('mode-switch');
            this.overlay = document.getElementById('theme-transition-overlay');
            return Boolean(this.switchEl);
        },

        /**
         * @returns {string} Mode actuellement appliqué sur <html>.
         */
        readMode() {
            return this.root.dataset.mode;
        },

        /**
         * Applique un mode : attribut data-mode (CSS) + état ARIA de l'interrupteur.
         * @param {'dev'|'photo'} mode
         * @returns {void}
         */
        render(mode) {
            this.root.dataset.mode = mode;
            this.switchEl.setAttribute('aria-checked', String(mode === 'photo'));
        },

        /**
         * Déclare les écouteurs : clic sur l'interrupteur, demandes externes (mode:request).
         * @param {{onToggle:Function, onRequest:Function}} handlers
         * @returns {void}
         */
        bind(handlers) {
            this.switchEl.addEventListener('click', handlers.onToggle);
            document.addEventListener('mode:request', handlers.onRequest);
        },

        /**
         * @returns {boolean} true si l'utilisateur préfère réduire les animations.
         */
        prefersReducedMotion() {
            return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        },

        /**
         * @returns {boolean} true si la View Transitions API est disponible.
         */
        supportsViewTransition() {
            return typeof document.startViewTransition === 'function';
        },

        /**
         * Centre de l'interrupteur dans le viewport (origine de la révélation circulaire).
         * @returns {{x:number, y:number}}
         */
        getOrigin() {
            const rect = this.switchEl.getBoundingClientRect();
            return {x: rect.left + rect.width / 2, y: rect.top + rect.height / 2};
        },

        /**
         * Bascule avec la View Transitions API : le navigateur capture l'ancien état, applique
         * la mise à jour, puis on révèle le nouvel état par un cercle qui grandit depuis l'origine.
         * Les photos de la galerie (view-transition-name dédié) se déplacent seules vers leur
         * nouvelle place dans la mise en page magazine.
         * @param {Function} update - Mise à jour du DOM (application du mode).
         * @param {{x:number, y:number}} origin - Origine du cercle.
         * @param {number} duration - Durée (ms).
         * @returns {void}
         */
        transitionWithViewTransition(update, origin, duration) {
            const transition = document.startViewTransition(update);
            transition.ready.then(() => {
                // Rayon final = distance jusqu'au coin le plus éloigné : le cercle couvre tout l'écran
                const radius = Math.hypot(
                    Math.max(origin.x, window.innerWidth - origin.x),
                    Math.max(origin.y, window.innerHeight - origin.y)
                );
                this.root.animate(
                    {clipPath: [`circle(0px at ${origin.x}px ${origin.y}px)`, `circle(${radius}px at ${origin.x}px ${origin.y}px)`]},
                    {duration, easing: 'cubic-bezier(0.76, 0, 0.24, 1)', pseudoElement: '::view-transition-new(root)'}
                );
            }).catch(() => {
                // Transition annulée (onglet masqué, nouvelle bascule rapide) : le DOM est déjà à jour
            });
        },

        /**
         * Bascule de repli : onde colorée (#theme-transition-overlay) partant de l'interrupteur.
         * Séquence : expansion (500 ms) → fondu (300 ms) → remise à zéro invisible.
         * @param {Function} update - Mise à jour du DOM.
         * @param {{x:number, y:number}} origin - Origine de l'onde.
         * @returns {void}
         */
        transitionWithOverlay(update, origin) {
            if (!this.overlay) {
                update();
                return;
            }
            this.overlay.style.setProperty('--ripple-x', `${origin.x}px`);
            this.overlay.style.setProperty('--ripple-y', `${origin.y}px`);
            this.overlay.classList.add('active');
            update();

            setTimeout(() => {
                this.overlay.style.opacity = '0';
                setTimeout(() => {
                    this.overlay.classList.remove('active');
                    this.overlay.style.opacity = '';
                }, 300);
            }, 500);
        }
    };

    /* =======================================================================
       C — CONTRÔLEUR
       ======================================================================= */
    const C = {

        /**
         * Point d'entrée : synchronise l'interrupteur avec le mode déjà appliqué par le <head>.
         * @returns {void}
         */
        init() {
            if (!V.init()) return;
            M.current = M.normalize(V.readMode());
            V.render(M.current);
            V.bind({onToggle: C.handleToggle, onRequest: C.handleRequest});
        },

        /**
         * Clic sur l'interrupteur : passage au mode opposé.
         * @returns {void}
         */
        handleToggle() {
            C.switchTo(M.other());
        },

        /**
         * Demande externe (CustomEvent « mode:request », detail.mode = 'dev' | 'photo').
         * @param {CustomEvent} e
         * @returns {void}
         */
        handleRequest(e) {
            C.switchTo(e.detail?.mode);
        },

        /**
         * Change de mode avec la meilleure transition disponible.
         * @param {*} requested - Mode demandé.
         * @returns {void}
         */
        switchTo(requested) {
            const mode = M.normalize(requested);
            if (mode === M.current) return;

            M.set(mode);
            const update = () => V.render(mode);

            if (V.prefersReducedMotion()) {
                update();
            } else if (V.supportsViewTransition()) {
                V.transitionWithViewTransition(update, V.getOrigin(), M.revealDuration);
            } else {
                V.transitionWithOverlay(update, V.getOrigin());
            }
        }
    };

    C.init();
})();
