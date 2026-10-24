/**
 * @file motion-controls.js
 * @description Contrôles de mouvement et raccourcis d'univers de l'accueil.
 *
 * - Bandeau de compétences : bouton pause / lecture (WCAG 2.2.2). Avec « mouvement réduit », le bandeau
 *   est figé par le CSS et le bouton est masqué.
 * - Vidéo du hero (animoji) : mise en pause quand l'utilisateur demande un mouvement réduit.
 * - Liens « Je fais aussi de la photo » : demandent le changement d'univers à theme-toggle.js
 *   par l'événement `mode:request`.
 *
 * Architecture M/V/C stricte :
 *  - M (Modèle)     : état pause du bandeau, préférence de mouvement.
 *  - V (Vue)        : bouton, pistes du bandeau, vidéos, boutons de bascule d'univers.
 *  - C (Contrôleur) : handlers qui interrogent M puis mettent à jour V.
 *
 * Chargé en `defer` depuis app/views/layouts/footer.php.
 */

// IIFE : M, V et C restent privés (pas de collision avec les autres scripts classiques).
(() => {
    'use strict';

    /* =======================================================================
       M — MODÈLE
       ======================================================================= */
    const M = {};

    /** true quand le défilement du bandeau est en pause. */
    M.marqueePaused = false;

    /**
     * Inverse l'état pause du bandeau.
     * @returns {boolean} Nouvel état (true = en pause).
     */
    M.togglePause = function () {
        this.marqueePaused = !this.marqueePaused;
        return this.marqueePaused;
    };

    /**
     * @returns {boolean} true si l'utilisateur préfère réduire les animations.
     */
    M.prefersReducedMotion = function () {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    };

    /* =======================================================================
       V — VUE
       ======================================================================= */
    const V = {};

    V.toggle = null;

    /**
     * Point d'entrée de la Vue : récupère le bouton pause et déclare les écouteurs.
     * @param {{onToggle:Function, onModeHint:Function}} handlers
     * @returns {void}
     */
    V.init = function (handlers) {
        this.toggle = document.getElementById('marquee-toggle');
        if (this.toggle) this.toggle.addEventListener('click', handlers.onToggle);
        document.addEventListener('click', handlers.onModeHint);
    };

    /**
     * Applique l'état pause : classe sur les pistes, aria-pressed, libellé et icône du bouton.
     * @param {boolean} paused
     * @returns {void}
     */
    V.renderPause = function (paused) {
        document.querySelectorAll('.animate-marquee').forEach((track) => track.classList.toggle('is-paused', paused));
        if (!this.toggle) return;
        this.toggle.setAttribute('aria-pressed', String(paused));
        this.toggle.setAttribute('aria-label', paused
            ? 'Reprendre le défilement des compétences'
            : 'Mettre en pause le défilement des compétences');
        this.toggle.querySelector('.marquee-icon--pause').classList.toggle('hidden', paused);
        this.toggle.querySelector('.marquee-icon--play').classList.toggle('hidden', !paused);
    };

    /**
     * Masque le bouton pause (bandeau déjà figé par le CSS).
     * @returns {void}
     */
    V.hideToggle = function () {
        if (this.toggle) this.toggle.hidden = true;
    };

    /**
     * Met en pause les vidéos décoratives.
     * @returns {void}
     */
    V.pauseVideos = function () {
        document.querySelectorAll('video[data-motion-video]').forEach((video) => {
            video.removeAttribute('autoplay');
            video.pause();
        });
    };

    /**
     * Bouton de bascule d'univers visé par un clic.
     * @param {EventTarget} target
     * @returns {HTMLElement|null}
     */
    V.modeHintFrom = function (target) {
        return target instanceof Element ? target.closest('[data-mode-hint]') : null;
    };

    /**
     * Univers demandé par un bouton.
     * @param {HTMLElement} button
     * @returns {string} 'dev' ou 'photo'.
     */
    V.modeOf = function (button) {
        return button.dataset.modeHint;
    };

    /**
     * Demande le changement d'univers à theme-toggle.js.
     * @param {string} mode - 'dev' ou 'photo'.
     * @returns {void}
     */
    V.requestMode = function (mode) {
        document.dispatchEvent(new CustomEvent('mode:request', {detail: {mode}}));
    };

    /* =======================================================================
       C — CONTRÔLEUR
       ======================================================================= */
    const C = {};

    /**
     * Point d'entrée.
     * @returns {void}
     */
    C.init = function () {
        V.init({onToggle: C.handler_toggle, onModeHint: C.handler_modeHint});

        if (M.prefersReducedMotion()) {
            V.hideToggle();
            V.pauseVideos();
        }
    };

    /**
     * Clic sur le bouton pause / lecture du bandeau.
     * @returns {void}
     */
    C.handler_toggle = function () {
        V.renderPause(M.togglePause());
    };

    /**
     * Clic sur « Je fais aussi de la photo » (ou son pendant Dev) : bascule d'univers.
     * @param {MouseEvent} ev
     * @returns {void}
     */
    C.handler_modeHint = function (ev) {
        const button = V.modeHintFrom(ev.target);
        if (button) V.requestMode(V.modeOf(button));
    };

    C.init();
})();
