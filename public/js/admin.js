/**
 * @file admin.js
 * @description Interactions du back-office (app/views/admin/dashboard.php).
 *
 * - Onglets : un seul panneau de formulaire visible à la fois.
 * - Listes répétables (parcours, langues, projets, photos, contacts, snippets, articles) :
 *   ajout d'une ligne par clonage d'un gabarit <template> (remplacement de {{index}} et {{number}}),
 *   suppression après confirmation.
 * - Index d'une nouvelle ligne = plus grand index existant + 1 : après une suppression, aucune
 *   ligne ne réutilise l'index d'une autre (sinon PHP écraserait ses champs à l'envoi).
 *
 * Architecture M/V/C stricte :
 *  - M (Modèle)     : classes des onglets, message de confirmation, calcul des index.
 *  - V (Vue)        : lecture du DOM, gabarits, déclaration des écouteurs (délégation sur le document).
 *  - C (Contrôleur) : handlers qui interrogent M puis mettent à jour V.
 *
 * Chargé en `defer` depuis le <head> du dashboard.
 */

// IIFE : M, V et C restent privés.
(() => {
    'use strict';

    /* =======================================================================
       M — MODÈLE
       ======================================================================= */
    const M = {};

    /** Classes d'état des boutons d'onglet. */
    M.tabClasses = {
        active: ['active', 'border-accent', 'bg-accent/10', 'text-accent'],
        inactive: ['border-border-strong', 'text-muted-foreground']
    };

    /** Message de confirmation avant suppression d'une ligne. */
    M.removeMessage = 'Voulez-vous vraiment supprimer cet élément ?';

    /**
     * Index de la prochaine ligne d'une liste : plus grand index existant + 1 (0 pour une liste vide).
     * @param {number[]} indexes - Index déjà utilisés dans la liste.
     * @returns {number}
     */
    M.nextIndex = function (indexes) {
        return indexes.length > 0 ? Math.max(...indexes) + 1 : 0;
    };

    /**
     * Numéro affiché dans le titre d'une nouvelle ligne (« Nouveau Projet #4 »).
     * @param {number} rowCount - Nombre de lignes déjà présentes.
     * @returns {number}
     */
    M.rowNumber = function (rowCount) {
        return rowCount + 1;
    };

    /* =======================================================================
       V — VUE
       ======================================================================= */
    const V = {};

    /** Listes répétables : bouton d'ajout → conteneur des lignes et gabarit <template>. */
    V.lists = {
        'add-snippet-btn': {container: 'snippets-container', template: 'tpl-admin-snippet'},
        'add-article-btn': {container: 'articles-container', template: 'tpl-admin-article'},
        'add-project-btn': {container: 'projects-container', template: 'tpl-admin-project'},
        'add-photo-btn': {container: 'galerie-container', template: 'tpl-admin-photo'},
        'add-parcours-btn': {container: 'parcours-container', template: 'tpl-admin-parcours'},
        'add-langue-btn': {container: 'langues-container', template: 'tpl-admin-langue'},
        'add-contact-btn': {container: 'contact-container', template: 'tpl-admin-contact'}
    };

    /** Lignes supprimables. */
    V.itemSelector = '.snippet-item, .article-item, .project-item, .photo-item, .parcours-item, .langue-item, .contact-item';

    /**
     * Point d'entrée de la Vue : déclare les écouteurs, par délégation sur le document.
     * @param {{onTabClick:Function, onAddClick:Function, onRemoveClick:Function}} handlers
     * @returns {void}
     */
    V.init = function (handlers) {
        document.addEventListener('click', handlers.onTabClick);
        document.addEventListener('click', handlers.onAddClick);
        document.addEventListener('click', handlers.onRemoveClick);
    };

    /**
     * Bouton d'onglet visé par un clic.
     * @param {EventTarget} target
     * @returns {HTMLElement|null}
     */
    V.tabFrom = function (target) {
        return target instanceof Element ? target.closest('.tab-btn') : null;
    };

    /**
     * Active un onglet : style des boutons et affichage du seul panneau ciblé (data-target).
     * @param {HTMLElement} tab - Bouton cliqué.
     * @param {{active:string[], inactive:string[]}} classes - Classes d'état.
     * @returns {void}
     */
    V.activateTab = function (tab, classes) {
        document.querySelectorAll('.tab-btn').forEach((button) => {
            button.classList.remove(...classes.active);
            button.classList.add(...classes.inactive);
        });
        document.querySelectorAll('.tab-content').forEach((panel) => {
            panel.classList.add('hidden');
            panel.classList.remove('flex');
        });

        tab.classList.add(...classes.active);
        tab.classList.remove(...classes.inactive);

        const panel = document.getElementById(tab.getAttribute('data-target'));
        if (panel) {
            panel.classList.remove('hidden');
            panel.classList.add('flex');
        }
    };

    /**
     * Liste répétable dont le bouton d'ajout est visé par un clic.
     * @param {EventTarget} target
     * @returns {string|null} Identifiant du bouton d'ajout (clé de V.lists).
     */
    V.addButtonFrom = function (target) {
        if (!(target instanceof Element)) return null;
        const button = target.closest(Object.keys(this.lists).map((id) => `#${id}`).join(', '));
        return button ? button.id : null;
    };

    /**
     * Conteneur des lignes d'une liste.
     * @param {string} listId - Clé de V.lists.
     * @returns {HTMLElement|null}
     */
    V.container = function (listId) {
        return document.getElementById(this.lists[listId].container);
    };

    /**
     * Index déjà utilisés dans une liste, lus dans les noms des champs (ex : projects[3][title] → 3).
     * @param {string} listId - Clé de V.lists.
     * @returns {number[]}
     */
    V.readIndexes = function (listId) {
        const container = this.container(listId);
        if (!container) return [];
        return [...container.querySelectorAll('[name]')]
            .map((field) => field.getAttribute('name').match(/^\w+\[(\d+)\]/))
            .filter(Boolean)
            .map((match) => Number(match[1]));
    };

    /**
     * Nombre de lignes affichées dans une liste.
     * @param {string} listId - Clé de V.lists.
     * @returns {number}
     */
    V.countRows = function (listId) {
        const container = this.container(listId);
        return container ? container.children.length : 0;
    };

    /**
     * Ajoute une ligne à partir du gabarit de la liste.
     * @param {string} listId - Clé de V.lists.
     * @param {number} index - Index des champs (name="liste[index][champ]").
     * @param {number} number - Numéro affiché dans le titre de la ligne.
     * @returns {void}
     */
    V.appendRow = function (listId, index, number) {
        const container = this.container(listId);
        const template = document.getElementById(this.lists[listId].template);
        if (!container || !template) return;

        const html = template.innerHTML
            .replace(/{{index}}/g, String(index))
            .replace(/{{number}}/g, String(number));
        container.insertAdjacentHTML('beforeend', html);
    };

    /**
     * Bouton de suppression visé par un clic.
     * @param {EventTarget} target
     * @returns {HTMLElement|null}
     */
    V.removeButtonFrom = function (target) {
        return target instanceof Element && target.classList.contains('remove-item-btn') ? target : null;
    };

    /**
     * Demande confirmation à l'utilisateur.
     * @param {string} message
     * @returns {boolean}
     */
    V.confirm = function (message) {
        return window.confirm(message);
    };

    /**
     * Supprime la ligne contenant le bouton.
     * @param {HTMLElement} button - Bouton « Supprimer » de la ligne.
     * @returns {void}
     */
    V.removeRow = function (button) {
        const item = button.closest(this.itemSelector);
        if (item) item.remove();
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
            onTabClick: C.handler_tabClick,
            onAddClick: C.handler_addRow,
            onRemoveClick: C.handler_removeRow
        });
    };

    /**
     * Clic sur un onglet : affichage de son panneau.
     * @param {MouseEvent} ev
     * @returns {void}
     */
    C.handler_tabClick = function (ev) {
        const tab = V.tabFrom(ev.target);
        if (!tab) return;
        V.activateTab(tab, M.tabClasses);
    };

    /**
     * Clic sur un bouton « + Ajouter » : nouvelle ligne avec un index inutilisé.
     * @param {MouseEvent} ev
     * @returns {void}
     */
    C.handler_addRow = function (ev) {
        const listId = V.addButtonFrom(ev.target);
        if (!listId) return;
        V.appendRow(listId, M.nextIndex(V.readIndexes(listId)), M.rowNumber(V.countRows(listId)));
    };

    /**
     * Clic sur « Supprimer » : retrait de la ligne après confirmation.
     * @param {MouseEvent} ev
     * @returns {void}
     */
    C.handler_removeRow = function (ev) {
        const button = V.removeButtonFrom(ev.target);
        if (!button) return;
        if (V.confirm(M.removeMessage)) V.removeRow(button);
    };

    C.init();
})();
