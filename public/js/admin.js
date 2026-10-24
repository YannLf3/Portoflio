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
 * - Galerie d'un projet : miniatures, déplacement (glisser-déposer ou boutons), retrait, ajout par URL ou
 *   par fichiers. Le champ texte d'origine (une URL par ligne) reste la source envoyée au serveur ; les
 *   fichiers choisis s'affichent en aperçu « Nouveau » et sont envoyés, à la suite des URL, à l'enregistrement.
 *
 * Architecture M/V/C stricte :
 *  - M (Modèle)     : classes des onglets, message de confirmation, calcul des index, liste d'URL de galerie.
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

    /** Type MIME propre au glisser-déposer de la galerie (ignore les textes et fichiers glissés depuis l'extérieur). */
    M.dragType = 'application/x-gallery-index';

    /** Classes de la vignette survolée pendant un glisser-déposer. */
    M.dropClasses = ['ring-2', 'ring-accent'];

    /** Fichiers de galerie en attente d'enregistrement, par champ fichier (clé = attribut name du champ). */
    M.pendingFiles = new Map();

    /** Messages annoncés aux lecteurs d'écran après une action sur la galerie. */
    M.galleryMessages = {
        invalidUrl: 'Adresse refusée : utilisez un chemin commençant par / ou une adresse en https://.',
        removed: 'Image retirée de la galerie. Elle disparaîtra à l\'enregistrement.',
        urlAdded: 'Image ajoutée à la fin de la galerie.',
        moved: (position) => `Image déplacée en position ${position}.`,
        filesAdded: (count) => count > 0
            ? `${count} image(s) en attente, envoyée(s) à l'enregistrement.`
            : 'Aucune nouvelle image ajoutée.'
    };

    /**
     * Liste d'URL d'une galerie à partir du texte stocké (une URL par ligne) ; les lignes invalides sont écartées.
     * @param {string} text - Contenu du champ « gallery ».
     * @returns {string[]}
     */
    M.parseGallery = function (text) {
        return text.split(/\r?\n/).map(M.cleanUrl).filter(Boolean);
    };

    /**
     * Texte à stocker pour une liste d'URL (une par ligne).
     * @param {string[]} urls
     * @returns {string}
     */
    M.serializeGallery = function (urls) {
        return urls.join('\n');
    };

    /**
     * Valide une URL d'image : chemin du site ou adresse http(s), sans espace.
     * @param {string} raw - Saisie brute.
     * @returns {string} L'URL nettoyée, ou une chaîne vide si elle est refusée.
     */
    M.cleanUrl = function (raw) {
        const url = String(raw).trim();
        // « // » et « /\ » mènent hors du site ; javascript:, data:… ne passent pas non plus
        const isSitePath = url.startsWith('/') && !url.startsWith('//') && !url.startsWith('/\\');
        const isWebUrl = /^https?:\/\//i.test(url);
        return (isSitePath || isWebUrl) && !/\s/.test(url) ? url : '';
    };

    /**
     * Déplace un élément d'une liste, sans modifier la liste d'origine.
     * @param {string[]} list
     * @param {number} from - Position actuelle.
     * @param {number} to - Nouvelle position.
     * @returns {string[]} Nouvelle liste (identique si une position est hors limites).
     */
    M.move = function (list, from, to) {
        const inRange = (position) => Number.isInteger(position) && position >= 0 && position < list.length;
        if (!inRange(from) || !inRange(to)) return list;

        const next = list.slice();
        next.splice(to, 0, ...next.splice(from, 1));
        return next;
    };

    /**
     * Retire un élément d'une liste, sans modifier la liste d'origine.
     * @param {Array} list
     * @param {number} index
     * @returns {Array}
     */
    M.removeAt = function (list, index) {
        return list.filter((_, position) => position !== index);
    };

    /**
     * Ajoute une URL à la fin d'une liste.
     * @param {string[]} urls
     * @param {string} url
     * @returns {string[]}
     */
    M.addUrl = function (urls, url) {
        return [...urls, url];
    };

    /**
     * Empreinte d'un fichier, pour ne pas l'ajouter deux fois.
     * @param {File} file
     * @returns {string}
     */
    M.fileSignature = function (file) {
        return `${file.name}|${file.size}|${file.lastModified}`;
    };

    /**
     * Fichiers en attente + fichiers nouvellement choisis : images uniquement, sans doublon.
     * @param {File[]} current - Fichiers déjà en attente.
     * @param {File[]} added - Fichiers du dernier choix.
     * @returns {File[]}
     */
    M.mergeFiles = function (current, added) {
        const known = new Set(current.map((file) => M.fileSignature(file)));
        const fresh = added
            .filter((file) => file.type.startsWith('image/'))
            .filter((file) => {
                const signature = M.fileSignature(file);
                if (known.has(signature)) return false;
                known.add(signature);
                return true;
            });
        return [...current, ...fresh];
    };

    /**
     * @param {string} key - Nom du champ fichier.
     * @returns {File[]}
     */
    M.getPending = function (key) {
        return M.pendingFiles.get(key) ?? [];
    };

    /**
     * @param {string} key - Nom du champ fichier.
     * @param {File[]} files
     * @returns {void}
     */
    M.setPending = function (key, files) {
        M.pendingFiles.set(key, files);
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
     * @param {Object<string, Function>} handlers - Handlers du contrôleur (onglets, lignes, galerie).
     * @returns {void}
     */
    V.init = function (handlers) {
        document.addEventListener('click', handlers.onTabClick);
        document.addEventListener('click', handlers.onAddClick);
        document.addEventListener('click', handlers.onRemoveClick);

        // Galerie d'un projet
        document.addEventListener('click', handlers.onGalleryClick);
        document.addEventListener('keydown', handlers.onGalleryKeydown);
        document.addEventListener('change', handlers.onGalleryFilesChange);
        document.addEventListener('dragstart', handlers.onGalleryDragStart);
        document.addEventListener('dragover', handlers.onGalleryDragOver);
        document.addEventListener('drop', handlers.onGalleryDrop);
        document.addEventListener('dragend', handlers.onGalleryDragEnd);
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
     * Bouton d'onglet correspondant à l'ancre de l'URL (ex : /admin?range=7#stats).
     * @returns {HTMLElement|null}
     */
    V.tabFromHash = function () {
        const target = window.location.hash.replace('#', '');
        return target ? document.querySelector(`.tab-btn[data-target="${CSS.escape(target)}"]`) : null;
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

    /* ---- Galerie d'un projet : miniatures, réordonnancement, aperçu des fichiers en attente ---- */

    /** Aperçus (URL d'objet) des fichiers en attente, mémorisés par fichier. */
    V.previews = new WeakMap();

    /** Galerie dont une vignette est en cours de glisser-déposer (null hors glisser-déposer). */
    V.dragRoot = null;

    /**
     * Blocs « galerie » de la page.
     * @returns {HTMLElement[]}
     */
    V.galleryRoots = function () {
        return [...document.querySelectorAll('[data-gallery]')];
    };

    /**
     * @param {HTMLElement} root - Bloc galerie.
     * @returns {boolean} Vrai si l'interface de miniatures est déjà en place.
     */
    V.isGalleryMounted = function (root) {
        return root.dataset.galleryReady === '1';
    };

    /**
     * Installe l'interface de miniatures : le champ texte reste dans le formulaire mais caché.
     * @param {HTMLElement} root - Bloc galerie.
     * @returns {void}
     */
    V.mountGallery = function (root) {
        const textarea = root.querySelector('[data-gallery-input]');
        const template = document.getElementById('tpl-admin-gallery-ui');
        if (!textarea || !template) return;

        textarea.classList.add('hidden');
        textarea.after(document.importNode(template.content, true));
        root.dataset.galleryReady = '1';
    };

    /**
     * Bloc galerie contenant un élément.
     * @param {EventTarget} target
     * @returns {HTMLElement|null}
     */
    V.galleryRootFrom = function (target) {
        return target instanceof Element ? target.closest('[data-gallery]') : null;
    };

    /**
     * Bouton d'action de galerie visé par un clic.
     * @param {EventTarget} target
     * @returns {HTMLElement|null}
     */
    V.galleryActionFrom = function (target) {
        return target instanceof Element ? target.closest('[data-gallery-action]') : null;
    };

    /**
     * Vignette contenant un élément.
     * @param {EventTarget} target
     * @returns {HTMLElement|null}
     */
    V.galleryItemFrom = function (target) {
        return target instanceof Element ? target.closest('[data-gallery-item]') : null;
    };

    /**
     * Vignette d'une image déjà enregistrée (les images en attente ne se déplacent pas).
     * @param {EventTarget} target
     * @returns {HTMLElement|null}
     */
    V.savedItemFrom = function (target) {
        const item = this.galleryItemFrom(target);
        return item && item.dataset.kind === 'saved' ? item : null;
    };

    /**
     * @param {HTMLElement} item - Vignette.
     * @returns {number} Position de l'image dans sa catégorie (enregistrée ou en attente).
     */
    V.galleryItemIndex = function (item) {
        return Number(item.dataset.index);
    };

    /**
     * @param {HTMLElement} item - Vignette.
     * @returns {string} « saved » ou « pending ».
     */
    V.galleryItemKind = function (item) {
        return item.dataset.kind;
    };

    /**
     * @param {HTMLElement} root - Bloc galerie.
     * @returns {string} Texte du champ caché (une URL par ligne).
     */
    V.readGalleryText = function (root) {
        return root.querySelector('[data-gallery-input]').value;
    };

    /**
     * @param {HTMLElement} root - Bloc galerie.
     * @param {string} text - Nouveau contenu du champ caché.
     * @returns {void}
     */
    V.writeGalleryText = function (root, text) {
        root.querySelector('[data-gallery-input]').value = text;
    };

    /**
     * @param {HTMLElement} root - Bloc galerie.
     * @returns {HTMLInputElement} Champ d'envoi de fichiers du bloc.
     */
    V.galleryFileInput = function (root) {
        return root.querySelector('[data-gallery-files]');
    };

    /**
     * @param {HTMLInputElement} input - Champ d'envoi de fichiers.
     * @returns {File[]}
     */
    V.readFiles = function (input) {
        return [...input.files];
    };

    /**
     * Remplace les fichiers du champ (un FileList n'est pas modifiable : on passe par DataTransfer).
     * @param {HTMLInputElement} input - Champ d'envoi de fichiers.
     * @param {File[]} files
     * @returns {void}
     */
    V.writeFiles = function (input, files) {
        const transfer = new DataTransfer();
        files.forEach((file) => transfer.items.add(file));
        input.files = transfer.files;
    };

    /**
     * Aperçu d'un fichier en attente (URL d'objet créée une seule fois par fichier).
     * @param {File} file
     * @returns {string}
     */
    V.previewUrl = function (file) {
        if (!this.previews.has(file)) this.previews.set(file, URL.createObjectURL(file));
        return this.previews.get(file);
    };

    /**
     * Libère l'aperçu d'un fichier retiré.
     * @param {File} file
     * @returns {void}
     */
    V.releasePreview = function (file) {
        if (!this.previews.has(file)) return;
        URL.revokeObjectURL(this.previews.get(file));
        this.previews.delete(file);
    };

    /**
     * @param {HTMLElement} root - Bloc galerie.
     * @returns {string} Saisie du champ « Ajouter l'URL ».
     */
    V.readUrlInput = function (root) {
        return root.querySelector('[data-gallery-url]').value;
    };

    /**
     * @param {HTMLElement} root - Bloc galerie.
     * @returns {void}
     */
    V.clearUrlInput = function (root) {
        root.querySelector('[data-gallery-url]').value = '';
    };

    /**
     * Écrit un message dans la zone annoncée aux lecteurs d'écran.
     * @param {HTMLElement} root - Bloc galerie.
     * @param {string} message
     * @returns {void}
     */
    V.announce = function (root, message) {
        root.querySelector('[data-gallery-status]').textContent = message;
    };

    /**
     * Construit une vignette à partir du gabarit.
     * @param {{kind:string, src:string, index:number, position:number, isFirst?:boolean, isLast?:boolean}} spec
     * @returns {HTMLElement}
     */
    V.buildGalleryItem = function (spec) {
        const template = document.getElementById('tpl-admin-gallery-item');
        const item = document.importNode(template.content, true).firstElementChild;
        const isSaved = spec.kind === 'saved';

        item.dataset.kind = spec.kind;
        item.dataset.index = String(spec.index);
        item.draggable = isSaved;
        item.classList.toggle('cursor-grab', isSaved);

        // L'image ne doit pas démarrer son propre glisser-déposer : c'est la vignette entière qui se déplace
        const img = item.querySelector('[data-gallery-img]');
        img.src = spec.src;
        img.alt = `Image ${spec.position} de la galerie`;
        img.draggable = false;

        item.querySelector('[data-gallery-badge]').classList.toggle('hidden', isSaved);
        item.querySelector('[data-gallery-position]').textContent = String(spec.position);
        item.querySelector('[data-gallery-action="remove"]')
            .setAttribute('aria-label', `Retirer l'image ${spec.position} de la galerie`);

        const before = item.querySelector('[data-gallery-action="before"]');
        const after = item.querySelector('[data-gallery-action="after"]');
        if (!isSaved) {
            before.remove();
            after.remove();
            return item;
        }

        before.disabled = Boolean(spec.isFirst);
        after.disabled = Boolean(spec.isLast);
        before.setAttribute('aria-label', `Déplacer l'image ${spec.position} vers le début`);
        after.setAttribute('aria-label', `Déplacer l'image ${spec.position} vers la fin`);
        return item;
    };

    /**
     * Affiche les miniatures : images enregistrées (réordonnables), puis fichiers en attente.
     * @param {HTMLElement} root - Bloc galerie.
     * @param {string[]} urls - Images enregistrées, dans l'ordre.
     * @param {File[]} pending - Fichiers choisis, envoyés à l'enregistrement.
     * @returns {void}
     */
    V.renderGallery = function (root, urls, pending) {
        const saved = urls.map((url, index) => this.buildGalleryItem({
            kind: 'saved',
            src: url,
            index,
            position: index + 1,
            isFirst: index === 0,
            isLast: index === urls.length - 1
        }));
        const waiting = pending.map((file, index) => this.buildGalleryItem({
            kind: 'pending',
            src: this.previewUrl(file),
            index,
            position: urls.length + index + 1
        }));

        root.querySelector('[data-gallery-list]').replaceChildren(...saved, ...waiting);
        root.querySelector('[data-gallery-empty]').classList.toggle('hidden', urls.length + pending.length > 0);
    };

    /**
     * Redonne le focus clavier au bouton de déplacement après le réaffichage des miniatures.
     * @param {HTMLElement} root - Bloc galerie.
     * @param {number} index - Position de la vignette déplacée.
     * @param {string} action - « before » ou « after ».
     * @returns {void}
     */
    V.focusGalleryAction = function (root, index, action) {
        const item = root.querySelector(`[data-gallery-item][data-kind="saved"][data-index="${index}"]`);
        if (!item) return;

        // Bouton désactivé en bout de liste : le focus passe au bouton opposé, puis à « Retirer »
        const opposite = action === 'before' ? 'after' : 'before';
        const button = [action, opposite, 'remove']
            .map((name) => item.querySelector(`[data-gallery-action="${name}"]`))
            .find((candidate) => candidate && !candidate.disabled);
        if (button) button.focus();
    };

    /**
     * Champ « Ajouter l'URL » visé par une touche.
     * @param {EventTarget} target
     * @returns {HTMLElement|null}
     */
    V.galleryUrlFieldFrom = function (target) {
        return target instanceof Element ? target.closest('[data-gallery-url]') : null;
    };

    /**
     * Champ d'envoi de fichiers visé par un changement.
     * @param {EventTarget} target
     * @returns {HTMLInputElement|null}
     */
    V.galleryFilesFieldFrom = function (target) {
        return target instanceof Element ? target.closest('[data-gallery-files]') : null;
    };

    /**
     * Débute le déplacement d'une vignette.
     * @param {DragEvent} ev
     * @param {HTMLElement} root - Bloc galerie.
     * @param {number} index - Position de la vignette saisie.
     * @returns {void}
     */
    V.startDrag = function (ev, root, index) {
        ev.dataTransfer.setData(M.dragType, String(index));
        ev.dataTransfer.effectAllowed = 'move';
        this.dragRoot = root;
    };

    /**
     * @param {DragEvent} ev
     * @param {HTMLElement} root - Bloc galerie visé.
     * @returns {boolean} Vrai si le glissement vient d'une vignette de ce même bloc.
     */
    V.isGalleryDrag = function (ev, root) {
        return this.dragRoot === root && ev.dataTransfer.types.includes(M.dragType);
    };

    /**
     * @param {DragEvent} ev
     * @returns {number} Position de la vignette saisie au départ.
     */
    V.readDragIndex = function (ev) {
        return Number(ev.dataTransfer.getData(M.dragType));
    };

    /**
     * Met en évidence la vignette qui recevra le dépôt.
     * @param {HTMLElement} item - Vignette survolée.
     * @returns {void}
     */
    V.markDropTarget = function (item) {
        this.clearDropTargets();
        item.classList.add(...M.dropClasses);
    };

    /**
     * Retire toute mise en évidence de dépôt.
     * @returns {void}
     */
    V.clearDropTargets = function () {
        document.querySelectorAll('[data-gallery-item]').forEach((item) => item.classList.remove(...M.dropClasses));
    };

    /**
     * Termine un glisser-déposer.
     * @returns {void}
     */
    V.endDrag = function () {
        this.dragRoot = null;
        this.clearDropTargets();
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
            onRemoveClick: C.handler_removeRow,
            onGalleryClick: C.handler_galleryClick,
            onGalleryKeydown: C.handler_galleryKeydown,
            onGalleryFilesChange: C.handler_galleryFilesChange,
            onGalleryDragStart: C.handler_galleryDragStart,
            onGalleryDragOver: C.handler_galleryDragOver,
            onGalleryDrop: C.handler_galleryDrop,
            onGalleryDragEnd: C.handler_galleryDragEnd
        });

        C.mountGalleries();

        // Retour sur un onglet précis (ex : changement de période des statistiques)
        const hashTab = V.tabFromHash();
        if (hashTab) V.activateTab(hashTab, M.tabClasses);
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

        // Un nouveau projet contient un bloc galerie à équiper
        C.mountGalleries();
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

    /* ---- Galerie d'un projet ---- */

    /**
     * Équipe chaque bloc galerie pas encore équipé : miniatures à partir du champ texte.
     * @returns {void}
     */
    C.mountGalleries = function () {
        V.galleryRoots()
            .filter((root) => !V.isGalleryMounted(root))
            .forEach((root) => {
                V.mountGallery(root);

                // Le navigateur peut restaurer des fichiers choisis avant un rechargement : on les reprend en compte
                const input = V.galleryFileInput(root);
                M.setPending(input.name, M.mergeFiles([], V.readFiles(input)));
                C.refreshGallery(root);
            });
    };

    /**
     * Réaffiche les miniatures d'une galerie depuis le champ texte et les fichiers en attente.
     * @param {HTMLElement} root - Bloc galerie.
     * @returns {void}
     */
    C.refreshGallery = function (root) {
        const urls = M.parseGallery(V.readGalleryText(root));
        const pending = M.getPending(V.galleryFileInput(root).name);
        V.renderGallery(root, urls, pending);
    };

    /**
     * Enregistre une nouvelle liste d'URL dans le champ caché, réaffiche et annonce le résultat.
     * @param {HTMLElement} root - Bloc galerie.
     * @param {string[]} urls - Nouvelle liste d'images enregistrées.
     * @param {string} message - Message annoncé.
     * @returns {void}
     */
    C.commitGallery = function (root, urls, message) {
        V.writeGalleryText(root, M.serializeGallery(urls));
        C.refreshGallery(root);
        V.announce(root, message);
    };

    /**
     * Décale une image enregistrée d'une position.
     * @param {HTMLElement} root - Bloc galerie.
     * @param {HTMLElement|null} item - Vignette concernée.
     * @param {number} step - -1 vers le début, +1 vers la fin.
     * @returns {void}
     */
    C.moveGalleryItem = function (root, item, step) {
        if (!item || V.galleryItemKind(item) !== 'saved') return;

        const from = V.galleryItemIndex(item);
        const to = from + step;
        const urls = M.parseGallery(V.readGalleryText(root));
        C.commitGallery(root, M.move(urls, from, to), M.galleryMessages.moved(to + 1));
        V.focusGalleryAction(root, to, step < 0 ? 'before' : 'after');
    };

    /**
     * Retire une image de la galerie : URL enregistrée ou fichier en attente.
     * @param {HTMLElement} root - Bloc galerie.
     * @param {HTMLElement|null} item - Vignette concernée.
     * @returns {void}
     */
    C.removeGalleryItem = function (root, item) {
        if (!item) return;

        const index = V.galleryItemIndex(item);
        if (V.galleryItemKind(item) === 'saved') {
            const urls = M.parseGallery(V.readGalleryText(root));
            C.commitGallery(root, M.removeAt(urls, index), M.galleryMessages.removed);
            return;
        }

        const input = V.galleryFileInput(root);
        const pending = M.getPending(input.name);
        const files = M.removeAt(pending, index);
        V.releasePreview(pending[index]);
        M.setPending(input.name, files);
        V.writeFiles(input, files);
        C.refreshGallery(root);
        V.announce(root, M.galleryMessages.removed);
    };

    /**
     * Ajoute l'URL saisie à la fin de la galerie.
     * @param {HTMLElement} root - Bloc galerie.
     * @returns {void}
     */
    C.addGalleryUrl = function (root) {
        const url = M.cleanUrl(V.readUrlInput(root));
        if (url === '') {
            V.announce(root, M.galleryMessages.invalidUrl);
            return;
        }

        const urls = M.parseGallery(V.readGalleryText(root));
        V.clearUrlInput(root);
        C.commitGallery(root, M.addUrl(urls, url), M.galleryMessages.urlAdded);
    };

    /**
     * Clic sur un bouton de la galerie : déplacer, retirer ou ajouter une URL.
     * @param {MouseEvent} ev
     * @returns {void}
     */
    C.handler_galleryClick = function (ev) {
        const button = V.galleryActionFrom(ev.target);
        const root = button && V.galleryRootFrom(button);
        if (!root) return;

        const item = V.galleryItemFrom(button);
        const actions = {
            before: () => C.moveGalleryItem(root, item, -1),
            after: () => C.moveGalleryItem(root, item, 1),
            remove: () => C.removeGalleryItem(root, item),
            'add-url': () => C.addGalleryUrl(root)
        };
        const run = actions[button.dataset.galleryAction];
        if (run) run();
    };

    /**
     * Touche Entrée dans le champ « Ajouter l'URL » : ajoute l'image sans envoyer le formulaire.
     * @param {KeyboardEvent} ev
     * @returns {void}
     */
    C.handler_galleryKeydown = function (ev) {
        if (ev.key !== 'Enter') return;

        const field = V.galleryUrlFieldFrom(ev.target);
        const root = field && V.galleryRootFrom(field);
        if (!root) return;

        ev.preventDefault();
        C.addGalleryUrl(root);
    };

    /**
     * Choix de fichiers : ils s'ajoutent à ceux déjà en attente (le champ natif remplacerait la sélection).
     * @param {Event} ev
     * @returns {void}
     */
    C.handler_galleryFilesChange = function (ev) {
        const input = V.galleryFilesFieldFrom(ev.target);
        const root = input && V.galleryRootFrom(input);
        if (!root) return;

        const current = M.getPending(input.name);
        const merged = M.mergeFiles(current, V.readFiles(input));
        M.setPending(input.name, merged);
        V.writeFiles(input, merged);
        C.refreshGallery(root);
        V.announce(root, M.galleryMessages.filesAdded(merged.length - current.length));
    };

    /**
     * Début du glisser d'une vignette enregistrée.
     * @param {DragEvent} ev
     * @returns {void}
     */
    C.handler_galleryDragStart = function (ev) {
        const item = V.savedItemFrom(ev.target);
        const root = item && V.galleryRootFrom(item);
        if (!root) return;

        V.startDrag(ev, root, V.galleryItemIndex(item));
    };

    /**
     * Survol d'une vignette enregistrée pendant un glisser : autorise le dépôt et la met en évidence.
     * @param {DragEvent} ev
     * @returns {void}
     */
    C.handler_galleryDragOver = function (ev) {
        const target = V.savedItemFrom(ev.target);
        const root = target && V.galleryRootFrom(target);
        if (!root || !V.isGalleryDrag(ev, root)) return;

        ev.preventDefault();
        V.markDropTarget(target);
    };

    /**
     * Dépôt sur une vignette : l'image saisie prend sa place.
     * @param {DragEvent} ev
     * @returns {void}
     */
    C.handler_galleryDrop = function (ev) {
        const target = V.savedItemFrom(ev.target);
        const root = target && V.galleryRootFrom(target);
        if (!root || !V.isGalleryDrag(ev, root)) return;

        ev.preventDefault();
        const from = V.readDragIndex(ev);
        const to = V.galleryItemIndex(target);
        V.endDrag();

        const urls = M.parseGallery(V.readGalleryText(root));
        C.commitGallery(root, M.move(urls, from, to), M.galleryMessages.moved(to + 1));
    };

    /**
     * Fin du glisser (dépôt réussi ou annulé).
     * @returns {void}
     */
    C.handler_galleryDragEnd = function () {
        V.endDrag();
    };

    C.init();
})();
