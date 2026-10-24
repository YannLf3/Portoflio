/**
 * @file contact.js
 * @description Formulaire de contact (app/views/front/partials/contact.php).
 *
 * - Validation côté navigateur (confort) ; la validation de référence reste celle du serveur (ApiController::contact).
 * - Erreurs affichées sous chaque champ (aria-invalid + aria-describedby), focus sur le premier champ en erreur.
 * - Envoi en fetch vers /api/contact, état « envoi en cours », puis confirmation à la place du formulaire.
 *
 * Architecture M/V/C stricte :
 *  - M (Modèle)     : règles de validation, appel API. Retourne des données, jamais de HTML ni de DOM.
 *  - V (Vue)        : lecture du formulaire, messages d'erreur, état d'envoi, confirmation, écouteurs.
 *  - C (Contrôleur) : handlers qui interrogent M puis mettent à jour V.
 *
 * Chargé en `defer` depuis app/views/front/partials/contact.php.
 */

// IIFE : M, V et C restent privés (pas de collision avec les autres scripts classiques).
(() => {
    'use strict';

    /* =======================================================================
       M — MODÈLE
       ======================================================================= */
    const M = {};

    /** Point d'accès de l'API et longueur minimale du message (identique au serveur). */
    M.endpoint = '/api/contact';
    M.minMessageLength = 10;

    /** Message affiché quand le serveur est injoignable. */
    M.networkError = 'Connexion impossible. Vérifiez votre réseau puis réessayez.';

    /**
     * Valide les champs saisis.
     * @param {{name:string, email:string, message:string}} values - Valeurs du formulaire.
     * @returns {Object<string,string>} Champ → message d'erreur (objet vide si tout est valide).
     */
    M.validate = function (values) {
        const checks = {
            name: [values.name.trim() === '', 'Indiquez votre nom.'],
            email: [!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.email.trim()), 'Indiquez une adresse e-mail valide, par exemple nom@exemple.fr.'],
            message: [values.message.trim().length < this.minMessageLength, `Votre message doit contenir au moins ${this.minMessageLength} caractères.`]
        };
        return Object.fromEntries(
            Object.entries(checks).filter(([, [invalid]]) => invalid).map(([field, [, message]]) => [field, message])
        );
    };

    /**
     * Envoie le message au serveur.
     * @param {Object<string,string>} fields - Tous les champs du formulaire (jeton CSRF et champ piège compris).
     * @returns {Promise<{ok:boolean, error:string, fields:Object<string,string>}>}
     */
    M.send = async function (fields) {
        try {
            const response = await fetch(this.endpoint, {
                method: 'POST',
                headers: {'Accept': 'application/json'},
                body: new URLSearchParams(fields)
            });
            const data = await response.json().catch(() => ({}));
            return {ok: response.ok && data.ok === true, error: data.error || this.networkError, fields: data.fields || {}};
        } catch (error) {
            return {ok: false, error: this.networkError, fields: {}};
        }
    };

    /* =======================================================================
       V — VUE
       ======================================================================= */
    const V = {};

    V.form = null;

    /** Champs validés, dans l'ordre d'affichage. */
    V.fieldNames = ['name', 'email', 'message'];

    /**
     * Point d'entrée de la Vue : récupère le formulaire et déclare les écouteurs.
     * @param {{onSubmit:Function, onReset:Function}} handlers
     * @returns {boolean} false si le formulaire est absent de la page.
     */
    V.init = function (handlers) {
        this.form = document.getElementById('contact-form');
        if (!this.form) return false;

        this.form.addEventListener('submit', handlers.onSubmit);
        document.getElementById('contact-reset').addEventListener('click', handlers.onReset);
        return true;
    };

    /**
     * Champ du formulaire par son nom.
     * @param {string} name
     * @returns {HTMLInputElement|HTMLTextAreaElement}
     */
    V.field = function (name) {
        return this.form.elements[name];
    };

    /**
     * Valeurs saisies.
     * @returns {{name:string, email:string, message:string}}
     */
    V.readValues = function () {
        return {name: this.field('name').value, email: this.field('email').value, message: this.field('message').value};
    };

    /**
     * Tous les champs à envoyer (y compris le jeton CSRF et le champ piège).
     * @returns {Object<string,string>}
     */
    V.readAllFields = function () {
        return Object.fromEntries(new FormData(this.form).entries());
    };

    /**
     * Affiche les erreurs sous leurs champs (les autres champs sont nettoyés) et focalise le premier en erreur.
     * @param {Object<string,string>} errors - Champ → message.
     * @returns {void}
     */
    V.showErrors = function (errors) {
        this.fieldNames.forEach((name) => {
            const message = errors[name] || '';
            const error = document.getElementById(`contact-${name}-error`);
            error.textContent = message;
            error.hidden = message === '';
            this.field(name).setAttribute('aria-invalid', String(message !== ''));
        });
        const first = this.fieldNames.find((name) => errors[name]);
        if (first) this.field(first).focus();
    };

    /**
     * État d'envoi : bouton désactivé et libellé « Envoi en cours ».
     * @param {boolean} busy
     * @returns {void}
     */
    V.setBusy = function (busy) {
        const button = document.getElementById('contact-submit');
        button.disabled = busy;
        button.setAttribute('aria-busy', String(busy));
        button.querySelector('[data-contact-label]').textContent = busy ? 'Envoi en cours…' : 'Envoyer le message';
    };

    /**
     * Message d'état (zone role="status", annoncée aux lecteurs d'écran).
     * @param {string} text
     * @returns {void}
     */
    V.setStatus = function (text) {
        document.getElementById('contact-status').textContent = text;
    };

    /**
     * Remplace le formulaire par la confirmation et lui donne le focus.
     * @returns {void}
     */
    V.showSuccess = function () {
        const success = document.getElementById('contact-success');
        this.form.hidden = true;
        success.hidden = false;
        success.focus();
    };

    /**
     * Réaffiche un formulaire vide.
     * @returns {void}
     */
    V.resetForm = function () {
        this.form.reset();
        this.showErrors({});
        this.setStatus('');
        document.getElementById('contact-success').hidden = true;
        this.form.hidden = false;
        this.field('name').focus();
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
        V.init({onSubmit: C.handler_submit, onReset: C.handler_reset});
    };

    /**
     * Soumission : validation locale, envoi, puis confirmation ou erreurs (champ par champ ou message général).
     * @param {SubmitEvent} ev
     * @returns {Promise<void>}
     */
    C.handler_submit = async function (ev) {
        ev.preventDefault();
        V.setStatus('');

        const errors = M.validate(V.readValues());
        V.showErrors(errors);
        if (Object.keys(errors).length > 0) return;

        V.setBusy(true);
        const result = await M.send(V.readAllFields());
        V.setBusy(false);

        if (result.ok) {
            V.showSuccess();
            // Annonce l'envoi réussi à consent.js (mesuré seulement si le visiteur a accepté)
            document.dispatchEvent(new CustomEvent('track:event', {detail: {name: 'contact_sent'}}));
            return;
        }
        V.showErrors(result.fields);
        V.setStatus(result.error);
    };

    /**
     * Clic sur « Envoyer un autre message ».
     * @returns {void}
     */
    C.handler_reset = function () {
        V.resetForm();
    };

    C.init();
})();
