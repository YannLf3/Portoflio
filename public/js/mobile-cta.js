/**
 * @file mobile-cta.js
 * @description Bouton fixe « Me contacter » affiché en bas de l'écran sur mobile (accueil uniquement).
 *
 * - Le bouton #mobile-cta (visible sous le breakpoint md) reste collé en bas de l'écran.
 * - Il se masque tant que la section #contact ou le pied de page est à l'écran : il serait redondant.
 *
 * Architecture M/V/C stricte :
 *  - M (Modèle)     : zones visibles à l'écran, état masqué / affiché du bouton.
 *  - V (Vue)        : bouton, zones observées (IntersectionObserver), rendu de l'état.
 *  - C (Contrôleur) : handler qui met à jour M à chaque changement de visibilité puis rafraîchit V.
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

    /** Sélecteur des zones qui rendent le bouton inutile (section contact, pied de page). */
    M.zonesSelector = '#contact, footer';

    /** Identifiants des zones actuellement visibles à l'écran (Set de noms de balise / id). */
    M.visibleZones = new Set();

    /**
     * Met à jour la visibilité d'une zone.
     * @param {string} zone - Identifiant de la zone.
     * @param {boolean} isVisible
     * @returns {void}
     */
    M.setZoneVisible = function (zone, isVisible) {
        if (isVisible) this.visibleZones.add(zone);
        else this.visibleZones.delete(zone);
    };

    /**
     * Le bouton est masqué dès qu'une zone « contact » est visible.
     * @returns {boolean}
     */
    M.isHidden = function () {
        return this.visibleZones.size > 0;
    };

    /* =======================================================================
       V — VUE
       ======================================================================= */
    const V = {};

    V.button = null;

    /**
     * Point d'entrée de la Vue : récupère le bouton et observe les zones.
     * @param {Function} onIntersect - Callback IntersectionObserver.
     * @param {string} zonesSelector - Sélecteur des zones observées.
     * @returns {boolean} false si le bouton est absent de la page (autres pages que l'accueil).
     */
    V.init = function (onIntersect, zonesSelector) {
        this.button = document.getElementById('mobile-cta');
        if (!this.button || !('IntersectionObserver' in window)) return false;
        const observer = new IntersectionObserver(onIntersect);
        document.querySelectorAll(zonesSelector).forEach((zone) => observer.observe(zone));
        return true;
    };

    /**
     * Nom stable d'une zone observée (son id, sinon sa balise).
     * @param {Element} zone
     * @returns {string}
     */
    V.zoneName = function (zone) {
        return zone.id || zone.tagName.toLowerCase();
    };

    /**
     * Affiche ou masque le bouton ; `inert` le retire aussi du clavier et des lecteurs d'écran.
     * @param {boolean} isHidden
     * @returns {void}
     */
    V.render = function (isHidden) {
        this.button.dataset.hidden = String(isHidden);
        this.button.inert = isHidden;
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
        V.init(C.handler_intersect, M.zonesSelector);
    };

    /**
     * Changement de visibilité d'une ou plusieurs zones : met à jour M puis le bouton.
     * @param {IntersectionObserverEntry[]} entries
     * @returns {void}
     */
    C.handler_intersect = function (entries) {
        entries.forEach((entry) => M.setZoneVisible(V.zoneName(entry.target), entry.isIntersecting));
        V.render(M.isHidden());
    };

    C.init();
})();
