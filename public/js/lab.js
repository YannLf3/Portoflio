/**
 * @file lab.js
 * @description Console de code de la page Lab (app/views/front/lab.php).
 *
 * - Console : <dialog> natif (focus piégé, Échap, retour du focus gérés par le navigateur).
 * - Affiche le HTML / CSS / JS d'un snippet, un onglet par langage (rôles ARIA tablist / tab).
 * - Fermeture : bouton, clic sur le fond ou touche Échap.
 *
 * Architecture M/V/C stricte :
 *  - M (Modèle)     : snippet affiché, textes de remplacement, ordre des onglets.
 *  - V (Vue)        : lecture des data-* des boutons, affichage des modales, déclaration des écouteurs.
 *  - C (Contrôleur) : handlers qui interrogent M puis mettent à jour V.
 *
 * Chargé en `defer` depuis app/views/front/lab.php.
 */

// IIFE : M, V et C restent privés (pas de collision avec les autres scripts).
(() => {
    'use strict';

    /* =======================================================================
       M — MODÈLE
       ======================================================================= */
    const M = {};

    /** Code du snippet affiché dans la console, par langage. */
    M.snippet = {html: '', css: '', js: ''};

    /** Langages proposés, dans l'ordre des onglets. */
    M.langs = ['html', 'css', 'js'];

    /** Textes affichés quand un snippet n'a pas de code (ou pas de titre). */
    M.fallbacks = {
        html: '<!-- Pas de code HTML -->',
        css: '/* Pas de CSS */',
        js: '// Pas de JS',
        title: 'Console'
    };

    /**
     * Mémorise le snippet à afficher ; un langage sans code reçoit son texte de remplacement.
     * @param {{html:string, css:string, js:string}} code - Code brut lu sur le bouton.
     * @returns {void}
     */
    M.setSnippet = function (code) {
        this.snippet = {
            html: code.html || this.fallbacks.html,
            css: code.css || this.fallbacks.css,
            js: code.js || this.fallbacks.js
        };
    };

    /**
     * Code du snippet courant pour un langage.
     * @param {string} lang - 'html', 'css' ou 'js'.
     * @returns {string}
     */
    M.codeFor = function (lang) {
        return this.snippet[lang] || '';
    };

    /**
     * Titre affiché dans la barre de la console.
     * @param {string|undefined} title - Titre lu sur le bouton.
     * @returns {string}
     */
    M.titleOrDefault = function (title) {
        return title || this.fallbacks.title;
    };

    /**
     * Langage voisin dans l'ordre des onglets (navigation aux flèches, boucle aux extrémités).
     * @param {string} lang - Langage courant.
     * @param {number} step - +1 (suivant) ou -1 (précédent).
     * @returns {string}
     */
    M.neighbor = function (lang, step) {
        const index = this.langs.indexOf(lang);
        return this.langs[(index + step + this.langs.length) % this.langs.length];
    };


    /* =======================================================================
       V — VUE
       ======================================================================= */
    const V = {};

    V.dialog = null;

    /**
     * Point d'entrée de la Vue : récupère la console et déclare les écouteurs (délégation sur le document).
     * @param {{onOpenCode:Function, onSwitchTab:Function, onTabKey:Function, onClose:Function}} handlers
     * @returns {boolean} false si la console est absente de la page.
     */
    V.init = function (handlers) {
        this.dialog = document.getElementById('code-dialog');
        if (!this.dialog) return false;

        document.addEventListener('click', handlers.onOpenCode);
        this.dialog.addEventListener('click', handlers.onSwitchTab);
        this.dialog.addEventListener('click', handlers.onClose);
        this.dialog.addEventListener('keydown', handlers.onTabKey);
        return true;
    };

    /**
     * Bouton « Voir le code » visé par un clic.
     * @param {EventTarget} target
     * @returns {HTMLElement|null}
     */
    V.codeButtonFrom = function (target) {
        return target instanceof Element ? target.closest('.open-code-modal') : null;
    };

    /**
     * Onglet de langage visé par un clic ou une touche.
     * @param {EventTarget} target
     * @returns {HTMLElement|null}
     */
    V.codeTabFrom = function (target) {
        return target instanceof Element ? target.closest('.code-tab') : null;
    };

    /**
     * Indique si un clic doit fermer la console : bouton de fermeture, ou fond (le <dialog> lui-même).
     * @param {EventTarget} target
     * @returns {boolean}
     */
    V.isCloseTarget = function (target) {
        const onCloseButton = target instanceof Element && Boolean(target.closest('.close-modal'));
        return onCloseButton || target === this.dialog;
    };

    /**
     * Code d'un snippet, lu dans les data-* de son bouton.
     * @param {HTMLElement} button
     * @returns {{title:string, html:string, css:string, js:string}}
     */
    V.readSnippet = function (button) {
        return {
            title: button.dataset.title,
            html: button.dataset.html,
            css: button.dataset.css,
            js: button.dataset.js
        };
    };

    /**
     * Langage d'un onglet de la console.
     * @param {HTMLElement} tab
     * @returns {string}
     */
    V.langOf = function (tab) {
        return tab.dataset.lang;
    };

    /**
     * Titre affiché dans la barre de la console.
     * @param {string} title
     * @returns {void}
     */
    V.setCodeTitle = function (title) {
        document.getElementById('code-modal-title').textContent = title;
    };

    /**
     * Code affiché dans la console (texte brut : jamais interprété).
     * @param {string} code
     * @returns {void}
     */
    V.setCode = function (code) {
        document.getElementById('code-content').textContent = code;
    };

    /**
     * Active un onglet : aria-selected, tabindex itinérant, style, et panneau relié à l'onglet.
     * @param {string} lang - Langage de l'onglet à activer.
     * @returns {HTMLElement|null} L'onglet activé.
     */
    V.activateCodeTab = function (lang) {
        const tabs = [...document.querySelectorAll('.code-tab')];
        tabs.forEach((tab) => {
            const isActive = this.langOf(tab) === lang;
            tab.setAttribute('aria-selected', String(isActive));
            tab.tabIndex = isActive ? 0 : -1;
            tab.classList.toggle('bg-background-card', isActive);
            tab.classList.toggle('text-accent', isActive);
            tab.classList.toggle('text-muted-foreground', !isActive);
        });
        const active = tabs.find((tab) => this.langOf(tab) === lang) ?? null;
        if (active) document.getElementById('code-panel').setAttribute('aria-labelledby', active.id);
        return active;
    };

    /**
     * Ouvre la console en modal (le navigateur piège le focus et le rend à la fermeture).
     * @returns {void}
     */
    V.open = function () {
        if (!this.dialog.open) this.dialog.showModal();
    };

    /**
     * Ferme la console.
     * @returns {void}
     */
    V.close = function () {
        if (this.dialog.open) this.dialog.close();
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
            onOpenCode: C.handler_openCode,
            onSwitchTab: C.handler_switchTab,
            onTabKey: C.handler_tabKey,
            onClose: C.handler_close
        });
    };

    /**
     * Clic sur « Voir le code » : console ouverte sur le HTML du snippet.
     * @param {MouseEvent} ev
     * @returns {void}
     */
    C.handler_openCode = function (ev) {
        const button = V.codeButtonFrom(ev.target);
        if (!button) return;
        const snippet = V.readSnippet(button);
        M.setSnippet(snippet);
        V.setCodeTitle(M.titleOrDefault(snippet.title));
        V.activateCodeTab('html');
        V.setCode(M.codeFor('html'));
        V.open();
    };

    /**
     * Clic sur un onglet de langage : affichage du code correspondant.
     * @param {MouseEvent} ev
     * @returns {void}
     */
    C.handler_switchTab = function (ev) {
        const tab = V.codeTabFrom(ev.target);
        if (!tab) return;
        const lang = V.langOf(tab);
        V.activateCodeTab(lang);
        V.setCode(M.codeFor(lang));
    };

    /**
     * Flèches gauche / droite sur un onglet : onglet voisin activé et focalisé.
     * @param {KeyboardEvent} ev
     * @returns {void}
     */
    C.handler_tabKey = function (ev) {
        const tab = V.codeTabFrom(ev.target);
        const steps = {ArrowRight: 1, ArrowLeft: -1};
        if (!tab || !(ev.key in steps)) return;
        ev.preventDefault();
        const lang = M.neighbor(V.langOf(tab), steps[ev.key]);
        V.activateCodeTab(lang)?.focus();
        V.setCode(M.codeFor(lang));
    };

    /**
     * Clic sur un bouton de fermeture ou sur le fond de la console.
     * @param {MouseEvent} ev
     * @returns {void}
     */
    C.handler_close = function (ev) {
        if (V.isCloseTarget(ev.target)) V.close();
    };

    C.init();
})();
