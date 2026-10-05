/**
 * @file photo-mode.js
 * @description Interactions de l'univers « Mode Photo ».
 *
 *  1. Visionneuse (lightbox) de la galerie : <dialog id="lightbox"> plein écran,
 *     navigation ← / →, balayage au doigt, préchargement des photos voisines.
 *  2. Comparateur avant / après (post-traitement) : glisser à la souris ou au doigt
 *     (Pointer Events), clavier via le rôle ARIA « slider », amorce animée au premier affichage.
 *
 * Architecture M/V/C stricte :
 *  - M (Modèle)     : index de la visionneuse, positions des comparateurs, calculs (bornes, pas clavier, amorce).
 *  - V (Vue)        : lecture des données dans le DOM, rendu, déclaration des écouteurs (délégation).
 *  - C (Contrôleur) : handlers et orchestration.
 *
 * Chargé en `defer` depuis app/views/layouts/footer.php (sans effet sur les pages sans galerie).
 */

// IIFE : M, V et C restent privés (pas de collision avec animations.js, terminal.js…).
(() => {
    'use strict';

    /* =======================================================================
       M — MODÈLE
       ======================================================================= */
    const M = {

        /** Réglages. */
        config: {
            swipeThreshold: 50,       // px de glissement horizontal pour changer de photo
            compareStep: 2,           // pas clavier (%)
            compareBigStep: 10,       // pas Page ↑/↓ ou Maj + flèche (%)
            introDuration: 1600,      // durée de l'amorce du comparateur (ms)
            introAmplitude: 14        // amplitude de l'amorce autour de 50 % (points de %)
        },

        /** Préférences utilisateur. */
        prefs: {
            reducedMotion: false
        },

        /* ----------------------------- Visionneuse ---------------------------- */
        lightbox: {
            /** @type {Array<{src:string, alt:string, legend:string}>} */
            items: [],
            index: 0,

            /**
             * Charge la liste des photos.
             * @param {Array<{src:string, alt:string, legend:string}>} items
             * @returns {void}
             */
            load(items) {
                this.items = items;
            },

            /**
             * Place l'index sur une photo (borné).
             * @param {number} index
             * @returns {void}
             */
            setIndex(index) {
                this.index = Math.min(Math.max(0, index), Math.max(0, this.items.length - 1));
            },

            /**
             * Avance ou recule, en bouclant (après la dernière → la première).
             * @param {number} delta - +1 ou -1.
             * @returns {void}
             */
            move(delta) {
                const total = this.items.length;
                if (total === 0) return;
                // Modulo « positif » : (-1 % 5) vaut -1 en JS, d'où le + total
                this.index = (this.index + delta + total) % total;
            },

            /**
             * Photo courante.
             * @returns {{src:string, alt:string, legend:string}|undefined}
             */
            current() {
                return this.items[this.index];
            },

            /**
             * Photos voisines (à précharger pour une navigation instantanée).
             * @returns {Array<{src:string}>}
             */
            neighbours() {
                const total = this.items.length;
                if (total < 2) return [];
                return [this.items[(this.index + 1) % total], this.items[(this.index - 1 + total) % total]];
            },

            /**
             * Compteur lisible « 02 / 05 ».
             * @returns {string}
             */
            counter() {
                const pad = (n) => String(n).padStart(2, '0');
                return `${pad(this.index + 1)} / ${pad(this.items.length)}`;
            }
        },

        /* ----------------------------- Comparateur ---------------------------- */
        compare: {
            /** Comparateur en cours de glissement (référence opaque). */
            dragging: null,
            /** Position courante de chaque comparateur (%). */
            positions: new WeakMap(),
            /** Comparateurs dont l'amorce a déjà été jouée ou interrompue. */
            introDone: new WeakSet(),
            /** Comparateurs manipulés par l'utilisateur (souris, doigt ou clavier) : l'amorce s'arrête net. */
            touched: new WeakSet(),

            /**
             * Borne une valeur entre 0 et 100.
             * @param {number} value
             * @returns {number}
             */
            clamp(value) {
                return Math.min(100, Math.max(0, value));
            },

            /**
             * Position (%) correspondant à l'abscisse du pointeur dans le comparateur.
             * @param {{left:number, width:number}} rect - Boîte du comparateur.
             * @param {number} clientX - Abscisse du pointeur.
             * @returns {number}
             */
            fromPointer(rect, clientX) {
                return this.clamp(((clientX - rect.left) / rect.width) * 100);
            },

            /**
             * Nouvelle position après une touche clavier (motif ARIA « slider »).
             * @param {number} current - Position actuelle (%).
             * @param {string} key - Touche pressée.
             * @param {boolean} shift - Maj enfoncée (grand pas).
             * @returns {number|null} null si la touche n'est pas gérée.
             */
            fromKey(current, key, shift) {
                const step = shift ? M.config.compareBigStep : M.config.compareStep;
                const moves = {
                    ArrowLeft: current - step,
                    ArrowDown: current - step,
                    ArrowRight: current + step,
                    ArrowUp: current + step,
                    PageDown: current - M.config.compareBigStep,
                    PageUp: current + M.config.compareBigStep,
                    Home: 0,
                    End: 100
                };
                return key in moves ? this.clamp(moves[key]) : null;
            },

            /**
             * Position de l'amorce à l'instant t : aller-retour sinusoïdal amorti autour de 50 %
             * (50 → ~64 → ~36 → 50), qui suggère « ça se glisse » sans action de l'utilisateur.
             * @param {number} t - Progression normalisée (0 → 1).
             * @returns {number}
             */
            introAt(t) {
                const damping = 1 - t;
                return 50 + Math.sin(t * Math.PI * 2) * M.config.introAmplitude * damping;
            }
        },

        /* ------------------------------- Balayage ------------------------------ */
        swipe: {
            startX: null,

            /**
             * Direction d'un balayage terminé.
             * @param {number} endX - Abscisse de fin.
             * @returns {number} +1 (suivante), -1 (précédente) ou 0 (trop court).
             */
            direction(endX) {
                if (this.startX === null) return 0;
                const dx = endX - this.startX;
                this.startX = null;
                if (Math.abs(dx) < M.config.swipeThreshold) return 0;
                // Glisser vers la gauche = photo suivante (geste habituel des visionneuses)
                return dx < 0 ? 1 : -1;
            }
        }
    };

    /* =======================================================================
       V — VUE
       ======================================================================= */
    const V = {
        gallery: null,
        lightbox: {},
        compares: [],

        /**
         * Lit la préférence de mouvement réduit.
         * @returns {boolean}
         */
        prefersReducedMotion() {
            return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        },

        /**
         * @returns {'dev'|'photo'} Mode actif du site.
         */
        getMode() {
            return document.documentElement.dataset.mode === 'photo' ? 'photo' : 'dev';
        },

        /* ----------------------------- Visionneuse ---------------------------- */

        /**
         * Récupère la galerie et les éléments de la visionneuse.
         * @returns {boolean} false si l'un des deux est absent (pages sans galerie).
         */
        initLightbox() {
            this.gallery = document.getElementById('gallery-grid');
            const dialog = document.getElementById('lightbox');
            if (!this.gallery || !dialog) return false;

            this.lightbox = {
                dialog,
                img: dialog.querySelector('[data-lightbox="img"]'),
                counter: dialog.querySelector('[data-lightbox="counter"]'),
                legend: dialog.querySelector('[data-lightbox="legend"]')
            };
            return true;
        },

        /**
         * Extrait les photos depuis les attributs data-* générés par PHP.
         * @returns {Array<{src:string, alt:string, legend:string}>}
         */
        readGalleryItems() {
            return [...this.gallery.querySelectorAll('[data-lightbox-index]')].map((el) => ({
                src: el.dataset.src,
                alt: el.dataset.alt,
                legend: el.dataset.legend
            }));
        },

        /**
         * @returns {boolean} true si le navigateur gère <dialog closedby>.
         */
        supportsClosedBy() {
            return 'closedBy' in HTMLDialogElement.prototype;
        },

        /**
         * Délégation : un seul écouteur sur la galerie, un seul jeu sur la visionneuse.
         * @param {{onOpen:Function, onClick:Function, onKeyDown:Function, onPointerDown:Function, onPointerUp:Function}} handlers
         * @returns {void}
         */
        bindLightbox(handlers) {
            this.gallery.addEventListener('click', handlers.onOpen);
            const {dialog} = this.lightbox;
            dialog.addEventListener('click', handlers.onClick);
            dialog.addEventListener('keydown', handlers.onKeyDown);
            dialog.addEventListener('pointerdown', handlers.onPointerDown);
            dialog.addEventListener('pointerup', handlers.onPointerUp);
        },

        /**
         * Index de la photo cliquée dans la galerie.
         * @param {EventTarget} target
         * @returns {number|null}
         */
        indexFrom(target) {
            const trigger = target instanceof Element ? target.closest('[data-lightbox-index]') : null;
            return trigger ? Number(trigger.dataset.lightboxIndex) : null;
        },

        /**
         * Action déclarée par un bouton de la visionneuse (prev / next / close).
         * @param {EventTarget} target
         * @returns {string|null}
         */
        actionFrom(target) {
            const button = target instanceof Element ? target.closest('[data-lightbox-action]') : null;
            return button ? button.dataset.lightboxAction : null;
        },

        /**
         * Clic dans le vide autour de la photo (fond noir, légende exclue) : la visionneuse se ferme.
         * @param {EventTarget} target
         * @returns {boolean}
         */
        isEmptyAreaClick(target) {
            return target === this.lightbox.dialog || (target instanceof Element && target.classList.contains('lightbox__figure'));
        },

        /**
         * Affiche une photo. Fondu : l'image est masquée (.is-loading) jusqu'au chargement de la nouvelle source.
         * @param {{src:string, alt:string, legend:string}} item
         * @param {string} counter - Texte « 02 / 05 ».
         * @returns {void}
         */
        renderLightbox(item, counter) {
            const {img} = this.lightbox;
            img.classList.add('is-loading');
            img.onload = () => img.classList.remove('is-loading');
            img.src = item.src;
            img.alt = item.alt;
            this.lightbox.counter.textContent = counter;
            this.lightbox.legend.textContent = item.legend;
            // Image déjà en cache : onload peut ne pas se redéclencher
            if (img.complete) img.classList.remove('is-loading');
        },

        /**
         * Précharge des images (navigation suivante / précédente sans attente).
         * @param {Array<{src:string}>} items
         * @returns {void}
         */
        preload(items) {
            items.forEach((item) => {
                const image = new Image();
                image.src = item.src;
            });
        },

        /**
         * Ouvre la visionneuse en modale (top layer, focus piégé, Échap natif).
         * @returns {void}
         */
        openLightbox() {
            if (!this.lightbox.dialog.open) this.lightbox.dialog.showModal();
        },

        /**
         * Ferme la visionneuse (le focus revient à la photo cliquée).
         * @returns {void}
         */
        closeLightbox() {
            this.lightbox.dialog.close();
        },

        /* ----------------------------- Comparateur ---------------------------- */

        /**
         * Récupère les comparateurs de la page.
         * @returns {boolean}
         */
        initCompare() {
            this.compares = [...document.querySelectorAll('[data-compare]')];
            return this.compares.length > 0;
        },

        /**
         * Délégation au niveau du document : glissement (pointer) et clavier (poignée).
         * pointermove / pointerup sur le document : le glissement continue même si le pointeur sort de l'image.
         * @param {{onPointerDown:Function, onPointerMove:Function, onPointerUp:Function, onKeyDown:Function}} handlers
         * @returns {void}
         */
        bindCompare(handlers) {
            document.addEventListener('pointerdown', handlers.onPointerDown);
            document.addEventListener('pointermove', handlers.onPointerMove, {passive: true});
            document.addEventListener('pointerup', handlers.onPointerUp);
            document.addEventListener('pointercancel', handlers.onPointerUp);
            document.addEventListener('keydown', handlers.onKeyDown);
        },

        /**
         * Comparateur contenant la cible d'un événement.
         * @param {EventTarget} target
         * @returns {HTMLElement|null}
         */
        compareFrom(target) {
            return target instanceof Element ? target.closest('[data-compare]') : null;
        },

        /**
         * Comparateur dont la POIGNÉE est la cible (événements clavier).
         * @param {EventTarget} target
         * @returns {HTMLElement|null}
         */
        compareFromHandle(target) {
            const handle = target instanceof Element ? target.closest('.compare__handle') : null;
            return handle ? handle.closest('[data-compare]') : null;
        },

        /**
         * Boîte englobante d'un comparateur.
         * @param {HTMLElement} compare
         * @returns {DOMRect}
         */
        getRect(compare) {
            return compare.getBoundingClientRect();
        },

        /**
         * Applique une position : variable CSS --_pos (découpe + poignée) et valeurs ARIA.
         * @param {HTMLElement} compare
         * @param {number} pct - Position (0 → 100).
         * @returns {void}
         */
        setPosition(compare, pct) {
            const rounded = Math.round(pct);
            compare.style.setProperty('--_pos', `${pct.toFixed(2)}%`);
            const handle = compare.querySelector('.compare__handle');
            handle.setAttribute('aria-valuenow', String(rounded));
            handle.setAttribute('aria-valuetext', `${rounded} % de photo brute`);
        },

        /**
         * Début / fin de glissement : capture du pointeur (les événements restent ciblés même hors zone).
         * @param {HTMLElement} compare
         * @param {PointerEvent} e
         * @param {boolean} dragging
         * @returns {void}
         */
        setDragging(compare, e, dragging) {
            compare.classList.toggle('is-dragging', dragging);
            if (dragging) compare.setPointerCapture?.(e.pointerId);
        },

        /**
         * Donne le focus à la poignée (le clavier prend le relais après un clic).
         * @param {HTMLElement} compare
         * @returns {void}
         */
        focusHandle(compare) {
            compare.querySelector('.compare__handle')?.focus({preventScroll: true});
        },

        /**
         * Observe l'entrée des comparateurs dans le viewport (amorce animée).
         * @param {Function} handler
         * @returns {void}
         */
        observeCompares(handler) {
            if (!('IntersectionObserver' in window)) return;
            const observer = new IntersectionObserver(handler, {threshold: 0.6});
            this.compares.forEach((compare) => observer.observe(compare));
        }
    };

    /* =======================================================================
       C — CONTRÔLEUR
       ======================================================================= */
    const C = {

        /**
         * Point d'entrée.
         * @returns {void}
         */
        init() {
            M.prefs.reducedMotion = V.prefersReducedMotion();
            C.initLightbox();
            C.initCompare();
        },

        /* ----------------------------- Visionneuse ---------------------------- */

        /**
         * Prépare la visionneuse si la page contient une galerie.
         * @returns {void}
         */
        initLightbox() {
            if (!V.initLightbox()) return;
            M.lightbox.load(V.readGalleryItems());
            V.bindLightbox({
                onOpen: C.handleGalleryClick,
                onClick: C.handleLightboxClick,
                onKeyDown: C.handleLightboxKey,
                onPointerDown: C.handleSwipeStart,
                onPointerUp: C.handleSwipeEnd
            });
        },

        /**
         * Clic sur une photo de la galerie : ouverture sur cette photo.
         * @param {MouseEvent} e
         * @returns {void}
         */
        handleGalleryClick(e) {
            const index = V.indexFrom(e.target);
            if (index === null) return;
            M.lightbox.setIndex(index);
            C.renderLightbox();
            V.openLightbox();
        },

        /**
         * Affiche la photo courante et précharge ses voisines.
         * @returns {void}
         */
        renderLightbox() {
            const item = M.lightbox.current();
            if (!item) return;
            V.renderLightbox(item, M.lightbox.counter());
            V.preload(M.lightbox.neighbours());
        },

        /**
         * Navigation relative (-1 / +1).
         * @param {number} delta
         * @returns {void}
         */
        show(delta) {
            M.lightbox.move(delta);
            C.renderLightbox();
        },

        /**
         * Clic délégué dans la visionneuse : boutons, ou clic dans le vide pour fermer.
         * (Le vide n'est pas un ::backdrop ici car la fenêtre est plein écran : closedby ne suffit pas.)
         * @param {MouseEvent} e
         * @returns {void}
         */
        handleLightboxClick(e) {
            const action = V.actionFrom(e.target);
            if (action === 'prev') C.show(-1);
            else if (action === 'next') C.show(1);
            else if (action === 'close' || V.isEmptyAreaClick(e.target)) V.closeLightbox();
        },

        /**
         * Flèches gauche / droite dans la visionneuse (Échap est géré nativement par <dialog>).
         * @param {KeyboardEvent} e
         * @returns {void}
         */
        handleLightboxKey(e) {
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                C.show(-1);
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                C.show(1);
            }
        },

        /**
         * Début d'un balayage au doigt (ou à la souris) sur la visionneuse.
         * @param {PointerEvent} e
         * @returns {void}
         */
        handleSwipeStart(e) {
            M.swipe.startX = e.clientX;
        },

        /**
         * Fin du balayage : photo suivante / précédente si le geste est assez long.
         * @param {PointerEvent} e
         * @returns {void}
         */
        handleSwipeEnd(e) {
            const direction = M.swipe.direction(e.clientX);
            if (direction !== 0) C.show(direction);
        },

        /* ----------------------------- Comparateur ---------------------------- */

        /**
         * Prépare les comparateurs (position initiale, écouteurs, amorce).
         * @returns {void}
         */
        initCompare() {
            if (!V.initCompare()) return;
            V.compares.forEach((compare) => C.setCompare(compare, 50));
            V.bindCompare({
                onPointerDown: C.handleComparePointerDown,
                onPointerMove: C.handleComparePointerMove,
                onPointerUp: C.handleComparePointerUp,
                onKeyDown: C.handleCompareKey
            });
            if (!M.prefs.reducedMotion) V.observeCompares(C.handleCompareIntersect);
        },

        /**
         * Enregistre et affiche une position.
         * @param {HTMLElement} compare
         * @param {number} pct
         * @returns {void}
         */
        setCompare(compare, pct) {
            M.compare.positions.set(compare, pct);
            V.setPosition(compare, pct);
        },

        /**
         * Appui dans un comparateur : la position saute sous le pointeur et le glissement commence.
         * @param {PointerEvent} e
         * @returns {void}
         */
        handleComparePointerDown(e) {
            const compare = V.compareFrom(e.target);
            if (!compare || e.button !== 0) return;
            // Toute interaction interrompt définitivement l'amorce animée
            M.compare.introDone.add(compare);
            M.compare.touched.add(compare);
            M.compare.dragging = compare;
            V.setDragging(compare, e, true);
            V.focusHandle(compare);
            C.setCompare(compare, M.compare.fromPointer(V.getRect(compare), e.clientX));
        },

        /**
         * Déplacement pendant le glissement.
         * @param {PointerEvent} e
         * @returns {void}
         */
        handleComparePointerMove(e) {
            const compare = M.compare.dragging;
            if (!compare) return;
            C.setCompare(compare, M.compare.fromPointer(V.getRect(compare), e.clientX));
        },

        /**
         * Fin du glissement (relâchement ou annulation, ex : défilement vertical au doigt).
         * @param {PointerEvent} e
         * @returns {void}
         */
        handleComparePointerUp(e) {
            const compare = M.compare.dragging;
            if (!compare) return;
            M.compare.dragging = null;
            V.setDragging(compare, e, false);
        },

        /**
         * Clavier sur la poignée : flèches, Page ↑/↓, Début / Fin (motif ARIA « slider »).
         * @param {KeyboardEvent} e
         * @returns {void}
         */
        handleCompareKey(e) {
            const compare = V.compareFromHandle(e.target);
            if (!compare) return;
            const next = M.compare.fromKey(M.compare.positions.get(compare) ?? 50, e.key, e.shiftKey);
            if (next === null) return;
            e.preventDefault(); // pas de défilement de la page avec les flèches
            M.compare.introDone.add(compare);
            M.compare.touched.add(compare);
            C.setCompare(compare, next);
        },

        /**
         * Premier affichage d'un comparateur en mode Photo : courte amorce animée.
         * @param {IntersectionObserverEntry[]} entries
         * @returns {void}
         */
        handleCompareIntersect(entries) {
            entries.forEach((entry) => {
                const compare = entry.target;
                if (!entry.isIntersecting || M.compare.introDone.has(compare) || V.getMode() !== 'photo') return;
                M.compare.introDone.add(compare);
                C.playIntro(compare);
            });
        },

        /**
         * Anime l'amorce image par image ; s'arrête dès que l'utilisateur prend la main.
         * @param {HTMLElement} compare
         * @returns {void}
         */
        playIntro(compare) {
            const start = performance.now();
            /**
             * Frame de l'amorce.
             * @param {number} now - Horodatage requestAnimationFrame.
             * @returns {void}
             */
            const frame = (now) => {
                // L'utilisateur a pris la main (souris, doigt ou clavier) : on lui laisse la position
                if (M.compare.touched.has(compare)) return;
                const t = Math.min(1, (now - start) / M.config.introDuration);
                C.setCompare(compare, M.compare.introAt(t));
                if (t < 1) requestAnimationFrame(frame);
            };
            requestAnimationFrame(frame);
        }
    };

    C.init();
})();
