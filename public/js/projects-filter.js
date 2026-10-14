/**
 * @file projects-filter.js
 * @description Filtres de la section Projets de l'accueil (app/views/front/partials/projects.php).
 *
 * - Un bouton par catégorie (data-filter) + « Tous » (data-filter="all").
 * - L'état actif est porté par aria-pressed (stylé en CSS via .btn-glow[aria-pressed='true'])
 *   et annonce le filtre actif aux lecteurs d'écran.
 * - Une carte reste visible si sa catégorie contient le filtre, ou l'inverse (correspondance partielle).
 *
 * Architecture M/V/C stricte :
 *  - M (Modèle)     : règle de correspondance filtre / catégorie.
 *  - V (Vue)        : boutons, cartes, déclaration des écouteurs (délégation sur la barre de filtres).
 *  - C (Contrôleur) : handler qui interroge M puis met à jour V.
 *
 * Chargé en `defer` depuis app/views/front/partials/projects.php.
 */

// IIFE : M, V et C restent privés (pas de collision avec les autres scripts).
(() => {
    'use strict';

    /* =======================================================================
       M — MODÈLE
       ======================================================================= */
    const M = {};

    /** Valeur du filtre qui affiche tous les projets. */
    M.allFilter = 'all';

    /**
     * Indique si une carte de la catégorie donnée correspond au filtre (correspondance partielle dans les deux sens).
     * @param {string} filter - Valeur data-filter du bouton (en minuscules).
     * @param {string} category - Catégorie normalisée de la carte (en minuscules).
     * @returns {boolean}
     */
    M.matches = function (filter, category) {
        return filter === this.allFilter || category.includes(filter) || filter.includes(category);
    };

    /* =======================================================================
       V — VUE
       ======================================================================= */
    const V = {};

    V.filterBar = null;

    /**
     * Point d'entrée de la Vue : déclare l'écouteur de la barre de filtres (délégation).
     * @param {{onFilter:Function}} handlers
     * @returns {boolean} false si la section Projets est absente de la page.
     */
    V.init = function (handlers) {
        this.filterBar = document.getElementById('project-filters');
        if (!this.filterBar) return false;
        this.filterBar.addEventListener('click', handlers.onFilter);
        return true;
    };

    /**
     * Bouton de filtre visé par un clic.
     * @param {EventTarget} target
     * @returns {HTMLElement|null}
     */
    V.filterButtonFrom = function (target) {
        return target instanceof Element ? target.closest('#project-filters .filter-btn') : null;
    };

    /**
     * Valeur de filtre d'un bouton.
     * @param {HTMLElement} button
     * @returns {string}
     */
    V.filterOf = function (button) {
        return button.getAttribute('data-filter');
    };

    /**
     * Marque le bouton actif (aria-pressed) et désactive les autres.
     * @param {HTMLElement} activeButton
     * @returns {void}
     */
    V.setPressed = function (activeButton) {
        document.querySelectorAll('#project-filters .filter-btn')
            .forEach((button) => button.setAttribute('aria-pressed', String(button === activeButton)));
    };

    /**
     * Cartes projet de la grille.
     * @returns {HTMLElement[]}
     */
    V.getCards = function () {
        return [...document.querySelectorAll('#projects-grid .project-card')];
    };

    /**
     * Catégorie normalisée d'une carte (data-category).
     * @param {HTMLElement} card
     * @returns {string}
     */
    V.categoryOf = function (card) {
        return card.getAttribute('data-category') || '';
    };

    /**
     * Affiche ou masque une carte.
     * @param {HTMLElement} card
     * @param {boolean} visible
     * @returns {void}
     */
    V.setCardVisible = function (card, visible) {
        card.style.display = visible ? 'flex' : 'none';
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
        V.init({onFilter: C.handler_filter});
    };

    /**
     * Clic sur un filtre : bouton actif mis à jour, cartes affichées selon leur catégorie.
     * @param {MouseEvent} ev
     * @returns {void}
     */
    C.handler_filter = function (ev) {
        const button = V.filterButtonFrom(ev.target);
        if (!button) return;
        const filter = V.filterOf(button);
        V.setPressed(button);
        V.getCards().forEach((card) => V.setCardVisible(card, M.matches(filter, V.categoryOf(card))));
    };

    C.init();
})();
