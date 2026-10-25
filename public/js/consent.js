/**
 * @file consent.js
 * @description Bandeau de consentement et mesure d'audience maison (POST /api/track).
 *
 * - Rien n'est envoyé tant que le visiteur n'a pas cliqué « Accepter ». « Refuser » n'envoie rien.
 * - Signaux « Do Not Track » / Global Privacy Control : traités comme un refus, sans afficher le bandeau.
 * - Le choix est mémorisé 6 mois dans le localStorage du navigateur (aucun cookie), puis redemandé, et modifiable à tout moment
 *   (lien du pied de page, commande `cookies` du terminal : événement `consent:open`).
 * - Mesures : une page vue par chargement, les actions marquées [data-track="nom"] ou annoncées par
 *   l'événement `track:event` (ex : envoi réussi du formulaire de contact), la profondeur de défilement
 *   (50 % et 90 %, une fois chacune) et le temps passé visible à l'écran (envoyé quand l'onglet se masque).
 * - Contexte joint à chaque mesure : mode Dev/Photo, langue du navigateur et, pour la page vue,
 *   les paramètres de campagne utm_source et utm_campaign.
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

    /** Seuils de défilement mesurés (pourcentage de la page), chacun envoyé une seule fois par chargement. */
    M.scrollSteps = [50, 90];

    /** État de la mesure en cours : active après consentement, seuils déjà atteints, chronomètre de présence. */
    M.measuring = false;
    M.reachedSteps = [];
    M.visibleSince = null;
    M.visibleMs = 0;

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
     * @param {Object} payload - Champs à envoyer (type, name, path, referrer, mode, lang, utm_*, value).
     * @returns {Promise<void>}
     */
    M.send = async function (payload) {
        const body = new URLSearchParams(payload);
        try {
            await fetch(this.endpoint, {method: 'POST', body, keepalive: true});
        } catch (error) { /* ignoré */ }
    };

    /**
     * Démarre la mesure de la page (idempotent : un second appel ne fait rien).
     * @returns {boolean} true si la mesure vient réellement de démarrer.
     */
    M.startMeasuring = function () {
        if (this.measuring) return false;
        this.measuring = true;
        this.reachedSteps = [];
        this.visibleMs = 0;
        return true;
    };

    /**
     * Arrête la mesure (choix refusé après coup) et oublie le temps accumulé.
     * @returns {void}
     */
    M.stopMeasuring = function () {
        this.measuring = false;
        this.visibleSince = null;
        this.visibleMs = 0;
    };

    /**
     * Mémorise qu'un seuil de défilement est atteint.
     * @param {number} step - Seuil en pourcentage.
     * @returns {boolean} true la première fois seulement.
     */
    M.markReached = function (step) {
        if (this.reachedSteps.includes(step)) return false;
        this.reachedSteps.push(step);
        return true;
    };

    /**
     * Lance le chronomètre de présence (page visible à l'écran).
     * @returns {void}
     */
    M.startClock = function () {
        if (this.visibleSince === null) this.visibleSince = Date.now();
    };

    /**
     * Arrête le chronomètre et remet le cumul à zéro.
     * @returns {number} Secondes de présence depuis la dernière remise à zéro.
     */
    M.stopClock = function () {
        if (this.visibleSince !== null) this.visibleMs += Date.now() - this.visibleSince;
        this.visibleSince = null;
        const seconds = Math.round(this.visibleMs / 1000);
        this.visibleMs = 0;
        return seconds;
    };

    /* =======================================================================
       V — VUE
       ======================================================================= */
    const V = {};

    V.banner = null;

    /**
     * Point d'entrée de la Vue : récupère le bandeau et déclare les écouteurs (délégation sur le document).
     * @param {{onClick:Function, onOpen:Function, onTrack:Function, onScroll:Function, onVisibility:Function}} handlers
     * @returns {void}
     */
    V.init = function (handlers) {
        this.banner = document.getElementById('consent-banner');
        document.addEventListener('click', handlers.onClick);
        document.addEventListener('consent:open', handlers.onOpen);
        document.addEventListener('track:event', handlers.onTrack);
        window.addEventListener('scroll', handlers.onScroll, {passive: true});
        document.addEventListener('visibilitychange', handlers.onVisibility);
    };

    /**
     * Contexte de la page joint à chaque mesure. Les paramètres de campagne ne servent qu'à la page vue
     * (c'est la page d'arrivée qui porte les utm_*).
     * @param {'pageview'|'event'} type
     * @returns {Object} Champs prêts à être envoyés.
     */
    V.pageContext = function (type) {
        const params = new URLSearchParams(window.location.search);
        const context = {
            path: window.location.pathname,
            referrer: document.referrer,
            mode: document.documentElement.dataset.mode ?? '',
            lang: navigator.language ?? ''
        };
        if (type === 'pageview') {
            context.utm_source = params.get('utm_source') ?? '';
            context.utm_campaign = params.get('utm_campaign') ?? '';
        }
        return context;
    };

    /**
     * Part de la page déjà parcourue (bas de l'écran rapporté à la hauteur totale du document).
     * @returns {number} Pourcentage de 0 à 100 (100 si la page tient dans l'écran).
     */
    V.scrollPercent = function () {
        const root = document.documentElement;
        return Math.min(100, (window.scrollY + window.innerHeight) / Math.max(1, root.scrollHeight) * 100);
    };

    /**
     * @returns {boolean} true si l'onglet est visible à l'écran.
     */
    V.isPageVisible = function () {
        return document.visibilityState === 'visible';
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
        V.init({
            onClick: C.handler_click,
            onOpen: C.handler_open,
            onTrack: C.handler_trackEvent,
            onScroll: C.handler_scroll,
            onVisibility: C.handler_visibility
        });

        // Signal de vie privée du navigateur : refus implicite, aucun bandeau, aucune mesure
        if (M.hasPrivacySignal()) return;

        const choice = M.readChoice();
        if (choice === null) V.showBanner();
        if (choice === 'granted') C.startMeasuring();
    };

    /**
     * Envoie une mesure avec le contexte de la page.
     * @param {'pageview'|'event'} type
     * @param {string} [name=''] - Nom de l'action (type event).
     * @param {Object} [extra={}] - Champs supplémentaires (ex : value pour le temps passé).
     * @returns {void}
     */
    C.send = function (type, name = '', extra = {}) {
        M.send({type, name, ...V.pageContext(type), ...extra});
    };

    /**
     * Démarre la mesure de la page : page vue, chronomètre de présence et premier contrôle du défilement
     * (une page qui tient dans l'écran compte comme lue en entier).
     * @returns {void}
     */
    C.startMeasuring = function () {
        if (!M.startMeasuring()) return;
        C.send('pageview');
        if (V.isPageVisible()) M.startClock();
        C.handler_scroll();
    };

    /**
     * Défilement : envoie chaque seuil (50 %, 90 %) la première fois qu'il est franchi.
     * @returns {void}
     */
    C.handler_scroll = function () {
        if (!M.measuring) return;
        const percent = V.scrollPercent();
        M.scrollSteps
            .filter((step) => percent >= step && M.markReached(step))
            .forEach((step) => C.send('event', 'scroll_' + step));
    };

    /**
     * Changement de visibilité de l'onglet : masqué = envoi du temps passé (secondes), visible = chronomètre relancé.
     * @returns {void}
     */
    C.handler_visibility = function () {
        if (!M.measuring) return;
        if (V.isPageVisible()) {
            M.startClock();
            return;
        }
        const seconds = M.stopClock();
        if (seconds > 0) C.send('event', 'time_spent', {value: seconds});
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
        if (name && !M.hasPrivacySignal() && M.readChoice() === 'granted') C.send('event', name);
    };

    /**
     * Enregistre le choix ; une acceptation lance aussitôt la mesure de la page en cours, un refus l'arrête.
     * @param {'granted'|'denied'} choice
     * @returns {void}
     */
    C.applyChoice = function (choice) {
        const previous = M.readChoice();
        M.saveChoice(choice);
        V.hideBanner();
        if (choice === 'denied') M.stopMeasuring();
        // Un signal DNT/GPC l'emporte sur l'acceptation : aucune mesure envoyée
        if (choice === 'granted' && previous !== 'granted' && !M.hasPrivacySignal()) C.startMeasuring();
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
        if (!M.hasPrivacySignal() && M.readChoice() === 'granted' && ev.detail?.name) C.send('event', ev.detail.name);
    };

    C.init();
})();
