/**
 * @file nav-menu.js
 * @description Menu burger de la navigation principale (mobile, sous le breakpoint md).
 *
 * - Le bouton #nav-toggle (aria-expanded) déplie / replie le panneau #main-nav (data-open).
 * - Le menu se referme : au clic sur un lien, au clic en dehors, à la touche Échap
 *   (le focus revient alors sur le bouton) et au passage en affichage desktop.
 * - En desktop, la <nav> est toujours visible (pilule) : ce script ne fait rien de visible.
 *
 * Architecture M/V/C stricte :
 *  - M (Modèle)     : état ouvert / fermé, requête média du breakpoint desktop.
 *  - V (Vue)        : bouton, panneau, déclaration des écouteurs (délégation sur le document).
 *  - C (Contrôleur) : handlers qui mettent à jour M puis rafraîchissent V.
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

    /** État du menu. */
    M.isOpen = false;

    /** Requête média du breakpoint md de Tailwind (48rem = 768px) : au-delà, plus de burger. */
    M.desktopQuery = '(min-width: 48rem)';

    /** Libellés accessibles du bouton selon l'état du menu. */
    M.labels = {
        open: 'Fermer le menu',
        closed: 'Ouvrir le menu'
    };

    /**
     * Inverse l'état du menu.
     * @returns {boolean} Nouvel état (true = ouvert).
     */
    M.toggle = function () {
        this.isOpen = !this.isOpen;
        return this.isOpen;
    };

    /**
     * Ferme le menu.
     * @returns {void}
     */
    M.close = function () {
        this.isOpen = false;
    };

    /**
     * Libellé du bouton correspondant à l'état courant.
     * @returns {string}
     */
    M.label = function () {
        return this.isOpen ? this.labels.open : this.labels.closed;
    };

    /* =======================================================================
       V — VUE
       ======================================================================= */
    const V = {};

    V.toggle = null;

    V.nav = null;

    /**
     * Point d'entrée de la Vue : récupère les éléments et déclare les écouteurs.
     * @param {{onClick:Function, onKeyDown:Function, onBreakpoint:Function}} handlers
     * @param {string} desktopQuery - Requête média au-delà de laquelle le menu burger disparaît.
     * @returns {boolean} false si le bouton ou la navigation sont absents de la page.
     */
    V.init = function (handlers, desktopQuery) {
        this.toggle = document.getElementById('nav-toggle');
        this.nav = document.getElementById('main-nav');
        if (!this.toggle || !this.nav) return false;
        // Délégation sur le document : un seul écouteur couvre le bouton, les liens et le clic en dehors
        document.addEventListener('click', handlers.onClick);
        document.addEventListener('keydown', handlers.onKeyDown);
        window.matchMedia(desktopQuery).addEventListener('change', handlers.onBreakpoint);
        return true;
    };

    /**
     * Indique si le clic vise le bouton burger.
     * @param {EventTarget} target
     * @returns {boolean}
     */
    V.isToggle = function (target) {
        return target instanceof Element && this.toggle.contains(target);
    };

    /**
     * Indique si le clic vise un lien de la navigation.
     * @param {EventTarget} target
     * @returns {boolean}
     */
    V.isNavLink = function (target) {
        return target instanceof Element && Boolean(target.closest('#main-nav a'));
    };

    /**
     * Indique si le clic a lieu à l'intérieur du panneau (hors liens : marges, séparateur).
     * @param {EventTarget} target
     * @returns {boolean}
     */
    V.isInsideNav = function (target) {
        return target instanceof Element && this.nav.contains(target);
    };

    /**
     * Synchronise le bouton et le panneau avec l'état du menu.
     * @param {boolean} isOpen
     * @param {string} label - Libellé accessible du bouton.
     * @returns {void}
     */
    V.render = function (isOpen, label) {
        this.toggle.setAttribute('aria-expanded', String(isOpen));
        this.toggle.setAttribute('aria-label', label);
        this.nav.dataset.open = String(isOpen);
    };

    /**
     * Rend le focus au bouton burger (fermeture au clavier).
     * @returns {void}
     */
    V.focusToggle = function () {
        this.toggle.focus();
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
        V.init({
            onClick: C.handler_click,
            onKeyDown: C.handler_keyDown,
            onBreakpoint: C.handler_breakpoint
        }, M.desktopQuery);
    };

    /**
     * Clic n'importe où : bascule sur le bouton, fermeture sur un lien ou en dehors du panneau.
     * @param {MouseEvent} ev
     * @returns {void}
     */
    C.handler_click = function (ev) {
        if (V.isToggle(ev.target)) {
            M.toggle();
            C.refresh();
            return;
        }
        // Menu déjà fermé : aucun clic ne doit le modifier
        if (!M.isOpen) return;
        if (V.isNavLink(ev.target) || !V.isInsideNav(ev.target)) C.close();
    };

    /**
     * Touche Échap : ferme le menu ouvert et rend le focus au bouton.
     * @param {KeyboardEvent} ev
     * @returns {void}
     */
    C.handler_keyDown = function (ev) {
        if (ev.key !== 'Escape' || !M.isOpen) return;
        C.close();
        V.focusToggle();
    };

    /**
     * Passage en affichage desktop : le menu est refermé (la <nav> redevient la pilule).
     * @param {MediaQueryListEvent} ev
     * @returns {void}
     */
    C.handler_breakpoint = function (ev) {
        if (ev.matches && M.isOpen) C.close();
    };

    /**
     * Ferme le menu et rafraîchit l'affichage.
     * @returns {void}
     */
    C.close = function () {
        M.close();
        C.refresh();
    };

    /**
     * Répercute l'état de M sur la Vue.
     * @returns {void}
     */
    C.refresh = function () {
        V.render(M.isOpen, M.label());
    };

    C.init();
})();
