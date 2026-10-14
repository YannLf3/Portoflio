/**
 * @file lab.js
 * @description Modales de la page Lab (app/views/front/lab.php).
 *
 * - Console de code : affiche le HTML / CSS / JS d'un snippet, un onglet par langage.
 * - Lecteur d'article : contenu HTML (s'il contient des balises) ou texte brut.
 * - Fermeture : bouton, clic sur le fond de la modale ou touche Échap.
 *
 * Architecture M/V/C stricte :
 *  - M (Modèle)     : snippet affiché, textes de remplacement, nature du contenu d'un article.
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
     * Indique si le contenu d'un article contient du HTML (sinon il est affiché en texte brut).
     * @param {string} content
     * @returns {boolean}
     */
    M.isHtml = function (content) {
        return content.includes('<');
    };

    /* =======================================================================
       V — VUE
       ======================================================================= */
    const V = {};

    V.codeModal = null;

    V.articleModal = null;

    /**
     * Point d'entrée de la Vue : récupère les modales et déclare les écouteurs (délégation sur le document).
     * @param {{onOpenCode:Function, onSwitchTab:Function, onOpenArticle:Function, onClose:Function, onKeyDown:Function}} handlers
     * @returns {void}
     */
    V.init = function (handlers) {
        this.codeModal = document.getElementById('code-modal');
        this.articleModal = document.getElementById('article-modal');

        document.addEventListener('click', handlers.onOpenCode);
        document.addEventListener('click', handlers.onSwitchTab);
        document.addEventListener('click', handlers.onOpenArticle);
        document.addEventListener('click', handlers.onClose);
        document.addEventListener('keydown', handlers.onKeyDown);
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
     * Onglet de langage de la console visé par un clic.
     * @param {EventTarget} target
     * @returns {HTMLElement|null}
     */
    V.codeTabFrom = function (target) {
        return target instanceof Element ? target.closest('.code-tab') : null;
    };

    /**
     * Bouton « Lire l'article » visé par un clic.
     * @param {EventTarget} target
     * @returns {HTMLElement|null}
     */
    V.articleButtonFrom = function (target) {
        return target instanceof Element ? target.closest('.open-article-modal') : null;
    };

    /**
     * Indique si un clic doit fermer les modales : bouton de fermeture, ou fond d'une modale.
     * @param {EventTarget} target
     * @returns {boolean}
     */
    V.isCloseTarget = function (target) {
        const onCloseButton = target instanceof Element && Boolean(target.closest('.close-modal'));
        return onCloseButton || target === this.codeModal || target === this.articleModal;
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
     * Article, lu dans les data-* de son bouton.
     * @param {HTMLElement} button
     * @returns {{title:string, category:string, content:string}}
     */
    V.readArticle = function (button) {
        return {
            title: button.dataset.title,
            category: button.dataset.category,
            content: button.dataset.content
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
     * Met en évidence l'onglet actif de la console.
     * @param {HTMLElement} tab
     * @returns {void}
     */
    V.activateCodeTab = function (tab) {
        document.querySelectorAll('.code-tab').forEach((other) => other.classList.remove('bg-zinc-800', 'text-accent', 'active'));
        tab.classList.add('bg-zinc-800', 'text-accent', 'active');
    };

    /**
     * Remplit le lecteur d'article.
     * @param {{title:string, category:string, content:string}} article
     * @param {boolean} asHtml - true : contenu interprété en HTML ; false : texte brut.
     * @returns {void}
     */
    V.renderArticle = function (article, asHtml) {
        document.getElementById('article-modal-title').textContent = article.title;
        document.getElementById('article-modal-category').textContent = article.category;

        const body = document.getElementById('article-modal-body');
        if (asHtml) {
            body.innerHTML = article.content;
        } else {
            body.textContent = article.content;
        }
    };

    /**
     * Ouvre une modale.
     * @param {HTMLElement} modal
     * @returns {void}
     */
    V.openModal = function (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    /**
     * Ferme les deux modales.
     * @returns {void}
     */
    V.closeModals = function () {
        [this.codeModal, this.articleModal].forEach((modal) => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });
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
            onOpenArticle: C.handler_openArticle,
            onClose: C.handler_close,
            onKeyDown: C.handler_keyDown
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
        V.setCode(M.codeFor('html'));
        V.openModal(V.codeModal);
    };

    /**
     * Clic sur un onglet de langage : affichage du code correspondant.
     * @param {MouseEvent} ev
     * @returns {void}
     */
    C.handler_switchTab = function (ev) {
        const tab = V.codeTabFrom(ev.target);
        if (!tab) return;
        V.activateCodeTab(tab);
        V.setCode(M.codeFor(V.langOf(tab)));
    };

    /**
     * Clic sur « Lire l'article » : lecteur ouvert sur l'article.
     * @param {MouseEvent} ev
     * @returns {void}
     */
    C.handler_openArticle = function (ev) {
        const button = V.articleButtonFrom(ev.target);
        if (!button) return;
        const article = V.readArticle(button);
        V.renderArticle(article, M.isHtml(article.content));
        V.openModal(V.articleModal);
    };

    /**
     * Clic sur un bouton de fermeture ou sur le fond d'une modale.
     * @param {MouseEvent} ev
     * @returns {void}
     */
    C.handler_close = function (ev) {
        if (V.isCloseTarget(ev.target)) V.closeModals();
    };

    /**
     * Échap ferme les modales.
     * @param {KeyboardEvent} ev
     * @returns {void}
     */
    C.handler_keyDown = function (ev) {
        if (ev.key === 'Escape') V.closeModals();
    };

    C.init();
})();
