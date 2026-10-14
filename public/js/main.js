/**
 * @file main.js
 * @description Loader « terminal » affiché au premier chargement de la session.
 *
 * - Les lignes de log apparaissent une à une, avec le pourcentage de progression.
 * - Après la dernière ligne, le loader remonte puis disparaît ; il n'est plus rejoué dans l'onglet (sessionStorage).
 * - La fin du loader est signalée à animations.js : drapeau data-app-ready sur <html> (cas où animations.js
 *   s'initialise après) ET événement app:ready (cas inverse) → lancement des révélations au scroll.
 *
 * Architecture M/V/C stricte :
 *  - M (Modèle)     : lignes de log, délais, progression, mémorisation de session.
 *  - V (Vue)        : éléments du loader, affichage, déclaration des écouteurs.
 *  - C (Contrôleur) : handlers et enchaînement des minuteries.
 *
 * Chargé (synchrone) en fin de <body> depuis app/views/layouts/footer.php.
 */

// IIFE : M, V et C restent privés (pas de collision avec les autres scripts classiques).
(() => {
    'use strict';

    /* =======================================================================
       M — MODÈLE
       ======================================================================= */
    const M = {};

    /** Clé sessionStorage : le loader n'est joué qu'une fois par onglet. */
    M.storageKey = 'portfolio_loaded';

    /** Lignes de log et instant d'apparition de chacune (ms depuis le chargement). */
    M.logs = [
        {text: '> INITIALIZING PORTFOLIO_CORE v2.0...', delay: 200},
        {text: '> LOADING ASSETS & GRAPHIC_ENGINE...', delay: 700},
        {text: '> MOUNTING COMPONENTS [HERO, PROJECTS, GALLERY]...', delay: 1300},
        {text: '> OPTIMIZING LIGHTROOM_PRESETS & STACK...', delay: 1900},
        {text: '> SYSTEM READY. LAUNCHING INTERFACE...', delay: 2500}
    ];

    /** Délais de fermeture (ms) : pause après la dernière ligne, puis durée de la remontée. */
    M.timing = {
        closeDelay: 800,
        hideDelay: 700
    };

    /**
     * Indique si le loader a déjà été joué dans cet onglet.
     * @returns {boolean}
     */
    M.hasPlayed = function () {
        return Boolean(sessionStorage.getItem(this.storageKey));
    };

    /**
     * Mémorise que le loader a été joué (il ne sera plus affiché dans cet onglet).
     * @returns {void}
     */
    M.markPlayed = function () {
        sessionStorage.setItem(this.storageKey, 'true');
    };

    /**
     * Pourcentage de progression une fois la ligne d'index donné affichée.
     * @param {number} index - Index de la ligne (0 = première).
     * @returns {number} Pourcentage arrondi (0 → 100).
     */
    M.progress = function (index) {
        return Math.round(((index + 1) / this.logs.length) * 100);
    };

    /**
     * @param {number} index - Index d'une ligne de log.
     * @returns {boolean} true s'il s'agit de la dernière ligne.
     */
    M.isLast = function (index) {
        return index === this.logs.length - 1;
    };

    /* =======================================================================
       V — VUE
       ======================================================================= */
    const V = {};

    V.loader = null;

    V.logsContainer = null;

    V.progressEl = null;

    /**
     * Point d'entrée de la Vue : attend que le DOM soit prêt.
     * @param {{onReady:Function}} handlers
     * @returns {void}
     */
    V.init = function (handlers) {
        document.addEventListener('DOMContentLoaded', handlers.onReady);
    };

    /**
     * Récupère les éléments du loader.
     * @returns {boolean} false si le loader est absent de la page.
     */
    V.findLoader = function () {
        this.loader = document.getElementById('app-loader');
        this.logsContainer = document.getElementById('terminal-logs');
        this.progressEl = document.getElementById('loader-progress');
        return Boolean(this.loader && this.logsContainer);
    };

    /**
     * Ajoute une ligne de log (Lime Spark du design system : le loader s'affiche toujours en mode Dev).
     * @param {string} text
     * @returns {void}
     */
    V.appendLog = function (text) {
        const line = document.createElement('div');
        line.className = 'flex items-center gap-2 text-accent';
        line.innerHTML = `<span class="text-zinc-600">$</span> <span>${text}</span>`;
        this.logsContainer.appendChild(line);
    };

    /**
     * Met à jour le pourcentage affiché.
     * @param {number} percent
     * @returns {void}
     */
    V.setProgress = function (percent) {
        if (this.progressEl) this.progressEl.textContent = `${percent}%`;
    };

    /**
     * Fait remonter le loader en fondu (transition CSS).
     * @returns {void}
     */
    V.slideUp = function () {
        this.loader.style.transform = 'translateY(-100%)';
        this.loader.style.opacity = '0';
    };

    /**
     * Masque définitivement le loader.
     * @returns {void}
     */
    V.hideLoader = function () {
        this.loader.style.display = 'none';
    };

    /**
     * Signale que l'interface est visible : drapeau data-app-ready + événement app:ready.
     * @returns {void}
     */
    V.markAppReady = function () {
        document.documentElement.dataset.appReady = 'true';
        document.dispatchEvent(new CustomEvent('app:ready'));
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
        V.init({onReady: C.handler_ready});
    };

    /**
     * DOM prêt : loader joué une seule fois par onglet ; sans loader, l'interface est prête tout de suite.
     * @returns {void}
     */
    C.handler_ready = function () {
        if (!V.findLoader()) {
            V.markAppReady();
            return;
        }
        if (M.hasPlayed()) {
            V.hideLoader();
            V.markAppReady();
            return;
        }
        M.logs.forEach((item, index) => setTimeout(() => C.showLog(index), item.delay));
    };

    /**
     * Affiche une ligne de log et la progression ; après la dernière, programme la fermeture.
     * @param {number} index - Index de la ligne.
     * @returns {void}
     */
    C.showLog = function (index) {
        V.appendLog(M.logs[index].text);
        V.setProgress(M.progress(index));
        if (M.isLast(index)) setTimeout(C.closeLoader, M.timing.closeDelay);
    };

    /**
     * Remontée du loader : les titres se révèlent pendant qu'il disparaît.
     * @returns {void}
     */
    C.closeLoader = function () {
        V.slideUp();
        V.markAppReady();
        setTimeout(C.finishLoader, M.timing.hideDelay);
    };

    /**
     * Fin de l'animation : loader masqué et mémorisé pour l'onglet.
     * @returns {void}
     */
    C.finishLoader = function () {
        V.hideLoader();
        M.markPlayed();
    };

    C.init();
})();
