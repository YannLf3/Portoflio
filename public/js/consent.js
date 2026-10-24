/**
 * @file consent.js
 * @description Bandeau de consentement et mesure d'audience maison (POST /api/track).
 *
 * - Rien n'est envoyé tant que le visiteur n'a pas cliqué « Accepter ». « Refuser » n'envoie rien.
 * - Signaux « Do Not Track » / Global Privacy Control : traités comme un refus, sans afficher le bandeau.
 * - Le choix est mémorisé 6 mois dans le localStorage du navigateur (aucun cookie), puis redemandé, et modifiable à tout moment
 *   (lien du pied de page, commande `cookies` du terminal : événement `consent:open`).
 * - Mesures : une page vue par chargement, plus les actions marquées [data-track="nom"] ou
 *   annoncées par l'événement `track:event` (ex : envoi réussi du formulaire de contact).
 *
 * Architecture M/V/C stricte :
 *  - M (Modèle)     : choix mémorisé, signaux de vie privée, envoi des mesures (fetch).
 *  - V (Vue)        : bandeau, boutons, lecture des attributs data-*, écouteurs.
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

    /** Clé localStorage du choix, et point d'accès de l'API. */
    M.storageKey = 'portfolio-consent';
    M.endpoint = '/api/track';

    /** Durée de validité du choix : 6 mois (recommandation CNIL), en millisecondes. */
    M.choiceLifetime = 6 * 30 * 24 * 60 * 60 * 1000;

    /**
     * Choix mémorisé et encore valide.
     * Le stockage contient {choice, savedAt}. Un choix expiré, illisible ou à l'ancien format
     * (simple chaîne sans date) est supprimé : le visiteur est de nouveau sollicité.
     * @returns {'granted'|'denied'|null} null = pas de choix valide (ou stockage indisponible).
     */
    M.readChoice = function () {
        try {
            const raw = localStorage.getItem(this.storageKey);
            if (raw === null) return null;

            const {choice, savedAt} = JSON.parse(raw);
            const isValidChoice = choice === 'granted' || choice === 'denied';
            const isFresh = Number.isFinite(savedAt) && Date.now() - savedAt < this.choiceLifetime;
            if (isValidChoice && isFresh) return choice;

            localStorage.removeItem(this.storageKey);
            return null;
        } catch (error) {
            return null;
        }
    };

    /**
     * Mémorise le choix du visiteur avec sa date, pour calculer l'expiration.
     * @param {'granted'|'denied'} choice
     * @returns {void}
     */
    M.saveChoice = function (choice) {
        try {
            localStorage.setItem(this.storageKey, JSON.stringify({choice, savedAt: Date.now()}));
        } catch (error) { /* stockage indisponible : le choix vaut pour la page en cours seulement */ }
    };

    /**
     * @returns {boolean} true si le navigateur demande de ne pas être suivi (DNT ou GPC).
     */
    M.hasPrivacySignal = function () {
        return navigator.doNotTrack === '1' || window.doNotTrack === '1' || navigator.globalPrivacyControl === true;
    };

    /**
     * Envoie une mesure au serveur (silencieux en cas d'échec : la mesure ne doit jamais gêner le site).
     * @param {'pageview'|'event'} type
     * @param {string} [name=''] - Nom de l'action (type event).
     * @returns {Promise<void>}
     */
    M.send = async function (type, name = '') {
        const body = new URLSearchParams({
            type,
            name,
            path: window.location.pathname,
            referrer: document.referrer
        });
        try {
            await fetch(this.endpoint, {method: 'POST', body, keepalive: true});
        } catch (error) { /* ignoré */ }
    };

    /* =======================================================================
       V — VUE
       ======================================================================= */
    const V = {};

    V.banner = null;

    /**
     * Point d'entrée de la Vue : récupère le bandeau et déclare les écouteurs (délégation sur le document).
     * @param {{onClick:Function, onOpen:Function, onTrack:Function}} handlers
     * @returns {void}
     */
    V.init = function (handlers) {
        this.banner = document.getElementById('consent-banner');
        document.addEventListener('click', handlers.onClick);
        document.addEventListener('consent:open', handlers.onOpen);
        document.addEventListener('track:event', handlers.onTrack);
    };

    /**
     * Choix déclaré par le bouton visé par un clic.
     * @param {EventTarget} target
     * @returns {'granted'|'denied'|null}
     */
    V.choiceFrom = function (target) {
        const button = target instanceof Element ? target.closest('[data-consent]') : null;
        return button ? button.dataset.consent : null;
    };

    /**
     * @param {EventTarget} target
     * @returns {boolean} true si le clic vise un lien « modifier mon choix ».
     */
    V.isOpenTarget = function (target) {
        return target instanceof Element && Boolean(target.closest('[data-consent-open]'));
    };

    /**
     * Nom d'action déclaré (data-track) par l'élément cliqué ou l'un de ses parents.
     * @param {EventTarget} target
     * @returns {string|null}
     */
    V.trackNameFrom = function (target) {
        const element = target instanceof Element ? target.closest('[data-track]') : null;
        return element ? element.dataset.track : null;
    };

    /**
     * Affiche le bandeau.
     * @param {boolean} [takeFocus=false] - true : le focus se place dans le bandeau (ouverture volontaire).
     * @returns {void}
     */
    V.showBanner = function (takeFocus = false) {
        if (!this.banner) return;
        this.banner.hidden = false;
        if (takeFocus) this.banner.focus();
    };

    /**
     * Masque le bandeau.
     * @returns {void}
     */
    V.hideBanner = function () {
        if (this.banner) this.banner.hidden = true;
    };

    /**
     * Indique l'état du choix dans le bandeau (texte lu par les lecteurs d'écran).
     * @param {'granted'|'denied'|'signal'|null} choice - 'signal' : le navigateur envoie DNT ou GPC.
     * @returns {void}
     */
    V.renderCurrentChoice = function (choice) {
        const status = document.getElementById('consent-current');
        if (!status) return;
        const labels = {
            granted: 'Choix actuel : mesure acceptée.',
            denied: 'Choix actuel : mesure refusée.',
            signal: 'Votre navigateur demande de ne pas être suivi (Do Not Track) : la mesure reste désactivée.'
        };
        status.textContent = labels[choice] ?? '';
    };

    /* =======================================================================
       C — CONTRÔLEUR
       ======================================================================= */
    const C = {};

    /**
     * Point d'entrée : bandeau affiché s'il n'y a pas encore de choix ; page vue envoyée si la mesure est acceptée.
     * @returns {void}
     */
    C.init = function () {
        V.init({onClick: C.handler_click, onOpen: C.handler_open, onTrack: C.handler_trackEvent});

        // Signal de vie privée du navigateur : refus implicite, aucun bandeau, aucune mesure
        if (M.hasPrivacySignal()) return;

        const choice = M.readChoice();
        if (choice === null) V.showBanner();
        if (choice === 'granted') M.send('pageview');
    };

    /**
     * Clic : choix dans le bandeau, ouverture du panneau, ou action à mesurer.
     * @param {MouseEvent} ev
     * @returns {void}
     */
    C.handler_click = function (ev) {
        const choice = V.choiceFrom(ev.target);
        if (choice) {
            C.applyChoice(choice);
            return;
        }
        if (V.isOpenTarget(ev.target)) {
            C.handler_open();
            return;
        }

        const name = V.trackNameFrom(ev.target);
        if (name && !M.hasPrivacySignal() && M.readChoice() === 'granted') M.send('event', name);
    };

    /**
     * Enregistre le choix ; une acceptation envoie aussitôt la page vue de la page en cours.
     * @param {'granted'|'denied'} choice
     * @returns {void}
     */
    C.applyChoice = function (choice) {
        const previous = M.readChoice();
        M.saveChoice(choice);
        V.hideBanner();
        // Un signal DNT/GPC l'emporte sur l'acceptation : aucune mesure envoyée
        if (choice === 'granted' && previous !== 'granted' && !M.hasPrivacySignal()) M.send('pageview');
    };

    /**
     * Ouverture volontaire du panneau de choix (pied de page, terminal).
     * @returns {void}
     */
    C.handler_open = function () {
        V.renderCurrentChoice(M.hasPrivacySignal() ? 'signal' : M.readChoice());
        V.showBanner(true);
    };

    /**
     * Action annoncée par un autre script (événement `track:event`, detail.name).
     * @param {CustomEvent} ev
     * @returns {void}
     */
    C.handler_trackEvent = function (ev) {
        if (!M.hasPrivacySignal() && M.readChoice() === 'granted' && ev.detail?.name) M.send('event', ev.detail.name);
    };

    C.init();
})();
