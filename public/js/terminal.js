/**
 * @file terminal.js
 * @description Terminal de commande interactif (Easter Egg) du portfolio.
 *
 * Ouverture : bouton flottant #cli-launcher ou raccourci ⌘K (macOS) / Ctrl+K.
 * Commandes : help, man, whoami, neofetch, skills, stack, projects, project, open, random, lab, contact,
 * ls, tree, goto, theme, cookies, history, clear, date, echo, fortune, exit, sudo hire-yann
 * (+ quelques commandes cachées).
 * Données : GET /api/terminal (ApiController), chargées à la première ouverture puis mises en cache.
 *
 * Architecture M/V/C stricte :
 *  - M (Modèle)     : registre des commandes, historique, appel réseau (fetch async/await), autocomplétion.
 *                     Les commandes renvoient des DONNÉES (lignes + « effet »), jamais du HTML.
 *  - V (Vue)        : <dialog>, rendu des lignes par gabarits <template> + replace(), déclaration des écouteurs.
 *  - C (Contrôleur) : handlers (raccourci, saisie, clavier, clics délégués) et application des effets.
 *
 * Chargé en `defer` depuis app/views/layouts/footer.php.
 */

// IIFE : M, V et C restent privés (pas de collision avec animations.js ni main.js).
(() => {
    'use strict';

    /* =======================================================================
       M — MODÈLE
       ======================================================================= */
    const M = {};

    /** Réglages du terminal. */
    M.config = {
        endpoint: '/api/terminal',
        historyMax: 50,
        /** Sections atteignables avec `goto` (identifiants des <section> de l'accueil + page Lab). */
        sections: ['about', 'skills', 'projects', 'contact', 'lab'],
        themes: ['dev', 'photo']
    };

    /** État de la session de terminal. */
    M.state = {
        data: null,
        pending: null,
        history: [],
        historyCursor: 0,
        booted: false,
        busy: false,
        reducedMotion: false
    };

    /** Art ASCII d'accueil (initiales YLF, style « ANSI Shadow »). */
    M.bannerArt = [
        '██╗   ██╗██╗     ███████╗',
        '╚██╗ ██╔╝██║     ██╔════╝',
        ' ╚████╔╝ ██║     █████╗  ',
        '  ╚██╔╝  ██║     ██╔══╝  ',
        '   ██║   ███████╗██║     ',
        '   ╚═╝   ╚══════╝╚═╝     '
    ].join('\n');

    /** Art ASCII de la séquence `sudo hire-yann`. */
    M.hiredArt = [
        '██╗  ██╗██╗██████╗ ███████╗██████╗ ',
        '██║  ██║██║██╔══██╗██╔════╝██╔══██╗',
        '███████║██║██████╔╝█████╗  ██║  ██║',
        '██╔══██║██║██╔══██╗██╔══╝  ██║  ██║',
        '██║  ██║██║██║  ██║███████╗██████╔╝',
        '╚═╝  ╚═╝╚═╝╚═╝  ╚═╝╚══════╝╚═════╝ '
    ].join('\n');

    /* ----------------------- Fabriques de lignes ----------------------- */

    /**
     * Crée une ligne de sortie. Un segment est soit du texte brut, soit un lien, soit une commande cliquable.
     * @param {'text'|'accent'|'muted'|'error'|'success'|'command'|'ascii'} type - Style de la ligne.
     * @param {...(string|{href:string, label:string}|{command:string, label:string})} segments
     * @returns {{type:string, segments:Array}}
     */
    M.line = function (type, ...segments) {
        return {type, segments};
    };

    /**
     * Segment « commande cliquable » (rejoue la commande au clic, pratique au tactile).
     * @param {string} command - Commande exécutée.
     * @param {string} [label=command] - Texte affiché.
     * @returns {{command:string, label:string}}
     */
    M.cmd = function (command, label = command) {
        return {command, label};
    };

    /**
     * Segment « lien ».
     * @param {string} href - URL (déjà filtrée par schéma côté serveur).
     * @param {string} label - Texte affiché.
     * @returns {{href:string, label:string}}
     */
    M.link = function (href, label) {
        return {href, label};
    };

    /**
     * Ligne vide (espace insécable pour conserver la hauteur de ligne).
     * @returns {{type:string, segments:Array}}
     */
    M.blank = function () {
        return this.line('text', ' ');
    };

    /**
     * Barre de progression ASCII sur 10 cases.
     * @param {number} percent - Valeur entre 0 et 100.
     * @returns {string}
     */
    M.bar = function (percent) {
        const filled = Math.round(Math.max(0, Math.min(100, percent)) / 10);
        return '█'.repeat(filled) + '░'.repeat(10 - filled);
    };

    /* --------------------------- Réseau -------------------------------- */

    /**
     * Charge les données publiques du portfolio (une seule requête par page, mutualisée).
     * En cas d'échec, renvoie null et autorise une nouvelle tentative à la commande suivante.
     * @returns {Promise<Object|null>}
     */
    M.loadData = async function () {
        if (this.state.data) return this.state.data;
        // Une requête déjà en vol est partagée : pas de doublon si deux commandes s'enchaînent
        if (this.state.pending) return this.state.pending;

        this.state.pending = (async () => {
            try {
                const response = await fetch(this.config.endpoint, {headers: {Accept: 'application/json'}});
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                this.state.data = await response.json();
            } catch (error) {
                console.warn('[terminal] Données indisponibles :', error.message);
                this.state.data = null;
            } finally {
                this.state.pending = null;
            }
            return this.state.data;
        })();

        return this.state.pending;
    };

    /* ------------------------- Recherche de projet ---------------------- */

    /**
     * Retrouve un projet à partir d'un argument : numéro affiché par `projects` (1 = premier) ou morceau du titre.
     * @param {Array<{title:string}>} projects - Projets de l'API.
     * @param {string[]} args - Arguments saisis après la commande.
     * @returns {Object|null} Le projet, ou null s'il n'y a pas de correspondance.
     */
    M.findProject = function (projects, args) {
        const query = args.join(' ').trim().toLowerCase();
        if (!query) return null;
        if (/^\d+$/.test(query)) return projects[Number(query) - 1] ?? null;
        return projects.find((project) => project.title.toLowerCase().includes(query)) ?? null;
    };

    /**
     * Lignes d'erreur « projet introuvable » avec rappel d'usage.
     * @param {string} name - Nom de la commande utilisée.
     * @returns {{lines:Array<Object>}}
     */
    M.projectNotFound = function (name) {
        return {
            lines: [
                M.line('error', `${name} : projet introuvable.`),
                M.line('muted', 'Usage : ', M.cmd('projects'), ` pour la liste, puis ${name} <numéro ou titre>.`)
            ]
        };
    };

    /** Citations de la commande `fortune` (proverbes de développeur, sans attribution). */
    M.fortunes = [
        'Un bug est une fonctionnalité qui n\'a pas encore trouvé son public.',
        'Fonctionne sur ma machine, donc livraison.',
        'Il y a deux difficultés en informatique : nommer les choses, invalider le cache, et les erreurs de décalage de un.',
        'Le meilleur code est celui qu\'on n\'a pas eu besoin d\'écrire.',
        'Mesurer deux fois, déployer une fois. Sauf le vendredi.',
        'Un commentaire à jour vaut mieux que dix commentaires menteurs.'
    ];

    /* ------------------------- Commandes ------------------------------- */

    /**
     * Registre des commandes.
     * - desc : description affichée par `help` (absente = commande cachée) ;
     * - needsData : la commande attend les données de l'API ;
     * - run(args, ctx) : renvoie {lines, effect?}. L'effet (clear, close, theme, goto, sequence)
     *   est appliqué par le Contrôleur : le Modèle ne touche jamais au DOM.
     * @type {Object<string, {desc?:string, needsData?:boolean, run:Function}>}
     */
    M.commands = {
        help: {
            desc: 'Lister les commandes disponibles',
            run() {
                const visible = Object.entries(M.commands).filter(([, command]) => command.desc);
                const lines = [M.line('accent', 'Commandes disponibles :')];
                visible.forEach(([name, command]) => {
                    // padEnd : alignement en colonnes (police monospace)
                    lines.push(M.line('text', '  ', M.cmd(name), ' '.repeat(Math.max(2, 12 - name.length)) + command.desc));
                });
                lines.push(M.blank());
                lines.push(M.line('muted', 'Tab : autocomplétion · ↑/↓ : historique · Ctrl+L : effacer · Échap : fermer'));
                return {lines};
            }
        },

        whoami: {
            desc: 'Qui est Yann ?',
            needsData: true,
            run(args, {data}) {
                return {
                    lines: [
                        M.line('accent', data.name),
                        M.line('text', 'Développeur web full-stack & photographe.'),
                        M.line('text', `📍 ${data.location}`),
                        M.line('text', `🎓 ${data.school}`),
                        M.line('success', `● ${data.status}`),
                        data.interests.length ? M.line('muted', `♥ ${data.interests.join(' · ')}`) : M.blank()
                    ]
                };
            }
        },

        skills: {
            desc: 'Stack technique, outils et langues',
            needsData: true,
            run(args, {data}) {
                const lines = [
                    M.line('accent', '▸ Langages & frameworks'),
                    M.line('text', `  ${data.skills.join('  ·  ')}`),
                    M.blank(),
                    M.line('accent', '▸ Outils & environnement'),
                    M.line('text', `  ${data.tools.join('  ·  ')}`)
                ];
                if (data.languages.length) {
                    lines.push(M.blank(), M.line('accent', '▸ Langues'));
                    data.languages.forEach((lang) => {
                        lines.push(M.line('text', `  ${lang.name.padEnd(10)} ${M.bar(lang.percent)}  ${lang.level}`));
                    });
                }
                lines.push(M.blank(), M.line('muted', 'Version visuelle : ', M.cmd('goto skills')));
                return {lines};
            }
        },

        projects: {
            desc: 'Projets sélectionnés (liens cliquables)',
            needsData: true,
            run(args, {data}) {
                if (!data.projects.length) {
                    return {lines: [M.line('muted', 'Aucun projet publié pour le moment.')]};
                }
                const lines = [M.line('accent', `▸ ${data.projects.length} projets sélectionnés`)];
                data.projects.forEach((project, index) => {
                    const num = String(index + 1).padStart(2, '0');
                    const title = project.link ? M.link(project.link, project.title) : project.title;
                    lines.push(M.line('text', `  ${num}  `, title, project.meta ? `  — ${project.meta}` : ''));
                });
                lines.push(M.blank(), M.line('muted', 'Les cartes en 3D : ', M.cmd('goto projects')));
                return {lines};
            }
        },

        contact: {
            desc: 'Me contacter (email, GitHub, LinkedIn…)',
            needsData: true,
            run(args, {data}) {
                const lines = [M.line('accent', '▸ Contact')];
                data.contacts.forEach((contact) => {
                    const value = contact.href ? M.link(contact.href, contact.value) : contact.value;
                    lines.push(M.line('text', `  ${contact.label.padEnd(11)}`, value));
                });
                lines.push(M.blank(), M.line('muted', 'Plus direct : ', M.cmd('sudo hire-yann')));
                return {lines};
            }
        },

        ls: {
            desc: 'Lister les sections du site',
            run() {
                const segments = M.config.sections.flatMap((section) => [M.cmd(`goto ${section}`, `${section}/`), '   ']);
                return {
                    lines: [
                        M.line('text', ...segments),
                        M.line('muted', 'Cliquez une section ou tapez goto <section>.')
                    ]
                };
            }
        },

        goto: {
            desc: 'Aller à une section (goto projects)',
            run([target = '']) {
                const section = target.toLowerCase().replace(/[/#]/g, '');
                if (!M.config.sections.includes(section)) {
                    return {
                        lines: [
                            M.line('error', section ? `goto : section inconnue « ${section} »` : 'goto : section manquante'),
                            M.line('muted', `Sections : ${M.config.sections.join(', ')}`)
                        ]
                    };
                }
                return {lines: [M.line('muted', `→ Navigation vers ${section}…`)], effect: {type: 'goto', target: section}};
            }
        },

        theme: {
            desc: 'Changer de mode (theme dev | theme photo)',
            run([mode = ''], {currentMode}) {
                const wanted = mode.toLowerCase();
                if (!M.config.themes.includes(wanted)) {
                    return {
                        lines: [
                            M.line('text', `Mode actuel : ${currentMode}`),
                            M.line('muted', 'Usage : ', M.cmd('theme dev'), ' ou ', M.cmd('theme photo'))
                        ]
                    };
                }
                if (wanted === currentMode) return {lines: [M.line('muted', `Déjà en mode ${wanted}.`)]};
                return {
                    lines: [M.line('success', `✔ Mode ${wanted === 'dev' ? 'Développeur' : 'Photographe'} activé.`)],
                    effect: {type: 'theme', mode: wanted}
                };
            }
        },

        history: {
            desc: 'Historique des commandes',
            run() {
                if (!M.state.history.length) return {lines: [M.line('muted', 'Historique vide.')]};
                return {
                    lines: M.state.history.map((entry, index) => M.line('text', `  ${String(index + 1).padStart(3)}  `, M.cmd(entry)))
                };
            }
        },

        date: {
            desc: 'Date et heure locales',
            run() {
                const now = new Date().toLocaleString('fr-FR', {dateStyle: 'full', timeStyle: 'short'});
                return {lines: [M.line('text', now)]};
            }
        },

        echo: {
            desc: 'Répéter un texte',
            run(args) {
                return {lines: [M.line('text', args.join(' ') || ' ')]};
            }
        },

        clear: {
            desc: 'Effacer l\'écran',
            run() {
                return {lines: [], effect: {type: 'clear'}};
            }
        },

        exit: {
            desc: 'Fermer le terminal',
            run() {
                return {lines: [], effect: {type: 'close'}};
            }
        },

        sudo: {
            desc: 'Super-pouvoirs… (essayez sudo hire-yann)',
            needsData: true,
            run([target = ''], {data}) {
                if (target.toLowerCase() !== 'hire-yann') {
                    return {
                        lines: [
                            M.line('error', 'visiteur n\'est pas dans le fichier sudoers. Cet incident sera signalé. 🚨'),
                            M.line('muted', 'Indice : ', M.cmd('sudo hire-yann'))
                        ]
                    };
                }
                return {lines: [], effect: {type: 'sequence', steps: M.hireSequence(data)}};
            }
        },

        man: {
            desc: 'Aide détaillée d\'une commande (man project)',
            run([target = '']) {
                const name = target.toLowerCase();
                const command = Object.hasOwn(M.commands, name) ? M.commands[name] : null;
                if (!command || !command.desc) {
                    return {lines: [M.line('error', `man : pas de page de manuel pour « ${name || '?'} »`), M.line('muted', 'Commandes : ', M.cmd('help'))]};
                }
                return {
                    lines: [
                        M.line('accent', name.toUpperCase()),
                        M.line('text', `  ${command.desc}`),
                        M.line('muted', `  Usage : ${command.usage ?? name}`)
                    ]
                };
            }
        },

        neofetch: {
            desc: 'Fiche système du portfolio',
            needsData: true,
            run(args, {data, currentMode}) {
                const rows = [
                    ['visiteur@yannleflohic.fr', ''],
                    ['-------------------------', ''],
                    ['Profil', data.name],
                    ['Lieu', data.location],
                    ['Études', data.school],
                    ['Stack', data.skills.slice(0, 5).join(', ')],
                    ['Projets', String(data.projects.length)],
                    ['Mode', currentMode === 'photo' ? 'Photographe' : 'Développeur'],
                    ['Statut', data.status]
                ];
                return {
                    lines: [
                        M.line('ascii', M.bannerArt),
                        ...rows.map(([label, value]) => M.line('text', label.padEnd(12), value))
                    ]
                };
            }
        },

        stack: {
            desc: 'Projets utilisant une techno (stack php)',
            usage: 'stack <techno>',
            needsData: true,
            run(args, {data}) {
                const query = args.join(' ').trim().toLowerCase();
                if (!query) {
                    return {lines: [M.line('muted', 'Usage : stack <techno>, par exemple ', M.cmd('stack php'), ' ou ', M.cmd('stack figma'))]};
                }
                const matches = data.projects.filter((project) => project.tags.join(' ').toLowerCase().includes(query));
                if (!matches.length) return {lines: [M.line('muted', `Aucun projet ne mentionne « ${query} ».`)]};
                return {
                    lines: [
                        M.line('accent', `▸ ${matches.length} projet(s) avec « ${query} »`),
                        ...matches.map((project) => M.line('text', '  ', M.cmd(`open ${data.projects.indexOf(project) + 1}`, project.title)))
                    ]
                };
            }
        },

        project: {
            desc: 'Détail d\'un projet (project 1)',
            usage: 'project <numéro ou titre>',
            needsData: true,
            run(args, {data}) {
                const project = M.findProject(data.projects, args);
                if (!project) return M.projectNotFound('project');

                const lines = [
                    M.line('accent', project.title),
                    M.line('muted', [project.meta, project.year, project.role].filter(Boolean).join(' · ') || ' '),
                    M.line('text', project.description || ' '),
                    project.tags.length ? M.line('muted', `Stack : ${project.tags.join(', ')}`) : M.blank()
                ];
                const links = [];
                if (project.link) links.push(M.link(project.link, 'Voir le projet'));
                if (project.repo) links.push(M.link(project.repo, 'Code source'));
                if (links.length) lines.push(M.line('text', ...links.flatMap((link, index) => (index ? ['  ·  ', link] : [link]))));
                lines.push(M.line('muted', 'Page détail : ', M.cmd(`open ${data.projects.indexOf(project) + 1}`)));
                return {lines};
            }
        },

        open: {
            desc: 'Ouvrir la page détail d\'un projet (open 2)',
            usage: 'open <numéro ou titre>',
            needsData: true,
            run(args, {data}) {
                const project = M.findProject(data.projects, args);
                if (!project) return M.projectNotFound('open');
                return {lines: [M.line('muted', `→ Ouverture de « ${project.title} »…`)], effect: {type: 'navigate', target: project.url}};
            }
        },

        random: {
            desc: 'Ouvrir un projet au hasard',
            needsData: true,
            run(args, {data}) {
                if (!data.projects.length) return {lines: [M.line('muted', 'Aucun projet publié pour le moment.')]};
                const project = data.projects[Math.floor(Math.random() * data.projects.length)];
                return {lines: [M.line('muted', `🎲 ${project.title}`)], effect: {type: 'navigate', target: project.url}};
            }
        },

        lab: {
            desc: 'Articles du lab (liens cliquables)',
            needsData: true,
            run(args, {data}) {
                const lines = [M.line('accent', '▸ Lab')];
                data.articles.forEach((article) => {
                    lines.push(M.line('text', '  ', M.link(article.url, article.title), article.category ? `  · ${article.category}` : ''));
                });
                if (!data.articles.length) lines.push(M.line('muted', '  Aucun article publié pour le moment.'));
                lines.push(M.blank(), M.line('muted', 'Snippets interactifs : ', M.cmd('goto lab')));
                return {lines};
            }
        },

        tree: {
            desc: 'Arborescence du site',
            needsData: true,
            run(args, {data}) {
                const lines = [
                    M.line('accent', '/'),
                    M.line('text', '├── #about  #skills  #projects  #gallery  #contact'),
                    M.line('text', '├── projets/')
                ];
                data.projects.forEach((project, index) => {
                    const branch = index === data.projects.length - 1 ? '└──' : '├──';
                    lines.push(M.line('text', `│   ${branch} `, M.link(project.url, project.url.replace('/projets/', ''))));
                });
                lines.push(M.line('text', '├── ', M.link('/lab', 'lab/')), M.line('text', '└── mentions-legales  politique-de-confidentialite  cgv'));
                return {lines};
            }
        },

        cookies: {
            desc: 'Gérer mon choix de mesure d\'audience',
            run() {
                return {lines: [M.line('muted', 'Ouverture du panneau de choix…')], effect: {type: 'consent'}};
            }
        },

        fortune: {
            desc: 'Une phrase de développeur au hasard',
            run() {
                return {lines: [M.line('text', M.fortunes[Math.floor(Math.random() * M.fortunes.length)])]};
            }
        },

        /* ---- Commandes cachées (absentes de help) ---- */
        'hire-yann': {
            run() {
                return {
                    lines: [
                        M.line('error', 'Permission refusée : cette action nécessite les droits administrateur.'),
                        M.line('muted', 'Essayez : ', M.cmd('sudo hire-yann'))
                    ]
                };
            }
        },

        coffee: {
            run() {
                return {lines: [M.line('ascii', '  ( (\n   ) )\n  .______.\n  |      |]\n  \\      /\n   `----\''), M.line('muted', 'Café servi. Le build peut continuer.')]};
            }
        },

        rm: {
            run() {
                return {lines: [M.line('error', 'Bien tenté 😏 Ce portfolio est en lecture seule.')]};
            }
        },

        vim: {
            run() {
                return {lines: [M.line('muted', 'Pas de panique, ce n\'est pas vim : tapez ', M.cmd('exit'), ' pour sortir.')]};
            }
        }
    };

    /**
     * Construit la séquence animée de `sudo hire-yann` : liste d'étapes {délai, ligne}
     * que le Contrôleur affiche une à une (effet « exécution en temps réel »).
     * @param {Object} data - Données de l'API (statut, contacts).
     * @returns {Array<{delay:number, line:Object}>}
     */
    M.hireSequence = function (data) {
        const email = data.contacts.find((contact) => contact.href.startsWith('mailto:'));
        const linkedin = data.contacts.find((contact) => /linkedin/i.test(contact.label + contact.href));
        const contactSegments = ['Contact direct → '];
        if (email) contactSegments.push(M.link(email.href, email.value));
        if (linkedin) contactSegments.push('  ·  ', M.link(linkedin.href, 'LinkedIn'));

        return [
            {delay: 300, line: M.line('muted', '[sudo] mot de passe pour visiteur : ••••••••')},
            {delay: 500, line: M.line('success', '✔ Authentification réussie.')},
            {delay: 400, line: M.line('muted', 'Compilation du profil de Yann Le Flohic…')},
            {delay: 300, line: M.line('text', '  ▸ Front-end (JS, Tailwind) .......... OK')},
            {delay: 250, line: M.line('text', '  ▸ Back-end (PHP, MySQL) ............. OK')},
            {delay: 250, line: M.line('text', '  ▸ Œil photo & sens du détail ........ OK')},
            {delay: 250, line: M.line('text', '  ▸ Motivation ........................ 200 %')},
            {delay: 450, line: M.line('ascii', M.hiredArt)},
            {delay: 250, line: M.line('success', '🚀 Accès accordé : Yann est prêt à rejoindre votre équipe.')},
            {delay: 200, line: M.line('text', `Disponibilité : ${data.status}`)},
            {delay: 200, line: M.line('text', ...contactSegments)}
        ];
    };

    /**
     * Lignes d'accueil affichées à la première ouverture.
     * @returns {Array<Object>}
     */
    M.welcome = function () {
        return [
            this.line('ascii', this.bannerArt),
            this.blank(),
            this.line('text', 'Portfolio CLI v1.0 — Yann Le Flohic'),
            this.line('muted', 'Développeur web full-stack & photographe · Châteauroux / Limoges'),
            this.blank(),
            this.line('text', 'Tapez ', this.cmd('help'), ' pour commencer, ou essayez ',
                this.cmd('whoami'), ', ', this.cmd('skills'), ', ', this.cmd('contact'), '.')
        ];
    };

    /**
     * Découpe une saisie en nom de commande + arguments.
     * @param {string} raw - Saisie brute.
     * @returns {{name:string, args:string[]}}
     */
    M.parse = function (raw) {
        const [name = '', ...args] = raw.trim().split(/\s+/);
        return {name: name.toLowerCase(), args};
    };

    /**
     * Exécute une saisie : résolution de la commande, chargement des données si besoin, exécution.
     * @param {string} raw - Saisie brute.
     * @param {{currentMode:string}} ctx - Contexte fourni par le Contrôleur (mode actif).
     * @returns {Promise<{lines:Array<Object>, effect?:Object}>}
     */
    M.execute = async function (raw, ctx) {
        const {name, args} = this.parse(raw);
        const command = Object.hasOwn(this.commands, name) ? this.commands[name] : null;

        if (!command) {
            return {
                lines: [
                    this.line('error', `zsh: commande introuvable : ${name}`),
                    this.line('muted', 'Tapez ', this.cmd('help'), ' pour voir les commandes disponibles.')
                ]
            };
        }

        let data = null;
        if (command.needsData) {
            data = await this.loadData();
            if (!data) {
                return {lines: [this.line('error', 'Impossible de joindre /api/terminal. Réessayez dans un instant.')]};
            }
        }
        return command.run(args, {...ctx, data});
    };

    /**
     * Autocomplétion (touche Tab) du nom de commande, puis de l'argument pour goto / theme / sudo.
     * @param {string} raw - Saisie en cours.
     * @returns {{completed:string|null, matches:string[]}} completed = saisie complétée si un seul candidat.
     */
    M.complete = function (raw) {
        const [first = '', ...rest] = raw.trimStart().split(/\s+/);
        const lower = first.toLowerCase();

        // 1. Nom de commande (commandes visibles uniquement : les cachées restent secrètes)
        if (rest.length === 0) {
            const names = Object.keys(this.commands).filter((name) => this.commands[name].desc);
            const matches = names.filter((name) => name.startsWith(lower));
            return {completed: matches.length === 1 ? `${matches[0]} ` : null, matches};
        }

        // 2. Argument de commande
        const options = {
            goto: this.config.sections,
            theme: this.config.themes,
            sudo: ['hire-yann'],
            man: Object.keys(this.commands).filter((name) => this.commands[name].desc)
        }[lower];
        if (!options || rest.length > 1) return {completed: null, matches: []};
        const matches = options.filter((option) => option.startsWith(rest[0].toLowerCase()));
        return {completed: matches.length === 1 ? `${lower} ${matches[0]}` : null, matches};
    };

    /**
     * Ajoute une commande à l'historique (sans doublon consécutif, taille bornée).
     * @param {string} entry
     * @returns {void}
     */
    M.pushHistory = function (entry) {
        if (entry && entry !== this.state.history.at(-1)) this.state.history.push(entry);
        if (this.state.history.length > this.config.historyMax) this.state.history.shift();
        this.state.historyCursor = this.state.history.length;
    };

    /**
     * Remonte dans l'historique (flèche ↑).
     * @returns {string}
     */
    M.previousEntry = function () {
        if (this.state.historyCursor > 0) this.state.historyCursor--;
        return this.state.history[this.state.historyCursor] ?? '';
    };

    /**
     * Redescend dans l'historique (flèche ↓) ; au-delà de la dernière entrée, saisie vide.
     * @returns {string}
     */
    M.nextEntry = function () {
        if (this.state.historyCursor < this.state.history.length) this.state.historyCursor++;
        return this.state.history[this.state.historyCursor] ?? '';
    };

    /* =======================================================================
       V — VUE
       ======================================================================= */
    const V = {};

    V.el = {};

    V.tpl = {};

    /**
     * Récupère les éléments et les gabarits du terminal, puis déclare les écouteurs.
     * Un seul écouteur de clic sur le <dialog> gère par délégation les commandes cliquables,
     * le bouton de fermeture et le clic dans la zone de sortie.
     * @param {Object} handlers - {onToggle, onShortcut, onSubmit, onKeyDown, onDialogClick, onClose}.
     * @returns {boolean} false si le markup est absent (pages sans footer : aucun écouteur déclaré).
     */
    V.init = function (handlers) {
        this.el = {
            launcher: document.getElementById('cli-launcher'),
            dialog: document.getElementById('cli'),
            output: document.getElementById('cli-output'),
            form: document.getElementById('cli-form'),
            input: document.getElementById('cli-input'),
            shortcut: document.querySelector('[data-cli-shortcut]')
        };
        const tplLine = document.getElementById('tpl-cli-line');
        const tplLink = document.getElementById('tpl-cli-link');
        const tplChip = document.getElementById('tpl-cli-chip');
        if (Object.values(this.el).some((el) => !el) || !tplLine || !tplLink || !tplChip) return false;

        this.tpl = {line: tplLine.innerHTML, link: tplLink.innerHTML, chip: tplChip.innerHTML};

        this.el.launcher.addEventListener('click', handlers.onToggle);
        document.addEventListener('keydown', handlers.onShortcut);
        this.el.form.addEventListener('submit', handlers.onSubmit);
        this.el.input.addEventListener('keydown', handlers.onKeyDown);
        this.el.dialog.addEventListener('click', handlers.onDialogClick);
        this.el.dialog.addEventListener('close', handlers.onClose);
        return true;
    };

    /**
     * Échappe une chaîne avant injection dans un gabarit HTML (texte ou attribut).
     * @param {string} str
     * @returns {string}
     */
    V.escape = function (str) {
        return String(str)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#39;');
    };

    /**
     * Remplit un gabarit {{clé}} en UNE passe. La fonction de remplacement évite deux pièges de
     * String.replace : les motifs spéciaux « $& », « $' »… dans la valeur insérée, et la
     * ré-interprétation d'un « {{…}} » qui figurerait dans une valeur déjà insérée.
     * @param {string} template - Gabarit HTML.
     * @param {Object<string, string>} values - Valeurs DÉJÀ échappées.
     * @returns {string}
     */
    V.fill = function (template, values) {
        return template.replace(/\{\{(\w+)\}\}/g, (match, key) => values[key] ?? '');
    };

    /**
     * Lit les préférences d'animation.
     * @returns {boolean} true si l'utilisateur préfère réduire les animations.
     */
    V.prefersReducedMotion = function () {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    };

    /**
     * Affiche le bon libellé du raccourci (⌘ K sur Apple, Ctrl K ailleurs).
     * @returns {void}
     */
    V.renderShortcutLabel = function () {
        const platform = navigator.userAgentData?.platform ?? navigator.platform ?? '';
        this.el.shortcut.textContent = /mac|iphone|ipad/i.test(platform) ? '⌘ K' : 'Ctrl K';
    };

    /**
     * Indique si le navigateur gère nativement <dialog closedby> (sinon repli JS du « light dismiss »).
     * @returns {boolean}
     */
    V.supportsClosedBy = function () {
        return 'closedBy' in HTMLDialogElement.prototype;
    };

    /**
     * @returns {boolean} true si le terminal est ouvert.
     */
    V.isOpen = function () {
        return this.el.dialog.open;
    };

    /**
     * Ouvre le terminal en modale (top layer, focus piégé, Échap géré nativement).
     * @returns {void}
     */
    V.open = function () {
        this.el.dialog.showModal();
        this.el.launcher.setAttribute('aria-expanded', 'true');
    };

    /**
     * Ferme le terminal (le navigateur rend le focus à l'élément déclencheur).
     * @returns {void}
     */
    V.close = function () {
        this.el.dialog.close();
    };

    /**
     * Met à jour l'état ARIA du bouton après fermeture.
     * @returns {void}
     */
    V.markClosed = function () {
        this.el.launcher.setAttribute('aria-expanded', 'false');
    };

    /**
     * Repli Safari du « light dismiss » : vrai si le clic tombe hors de la boîte du <dialog>
     * (sur le ::backdrop, la cible de l'événement est le <dialog> lui-même).
     * @param {MouseEvent} e
     * @returns {boolean}
     */
    V.isBackdropClick = function (e) {
        if (e.target !== this.el.dialog) return false;
        const rect = this.el.dialog.getBoundingClientRect();
        return e.clientX < rect.left || e.clientX > rect.right || e.clientY < rect.top || e.clientY > rect.bottom;
    };

    /**
     * Commande portée par une « chip » cliquée.
     * @param {EventTarget} target
     * @returns {string|null}
     */
    V.chipCommandFrom = function (target) {
        const chip = target instanceof Element ? target.closest('[data-cli-run]') : null;
        return chip ? chip.dataset.cliRun : null;
    };

    /**
     * Action déclarée par un bouton (ex : data-cli-action="close").
     * @param {EventTarget} target
     * @returns {string|null}
     */
    V.actionFrom = function (target) {
        const button = target instanceof Element ? target.closest('[data-cli-action]') : null;
        return button ? button.dataset.cliAction : null;
    };

    /**
     * Clic dans la zone de sortie sans sélection de texte en cours (pour redonner le focus au champ).
     * @param {EventTarget} target
     * @returns {boolean}
     */
    V.isPlainOutputClick = function (target) {
        const inOutput = target instanceof Element && this.el.output.contains(target) && !target.closest('a, button');
        return inOutput && !window.getSelection()?.toString();
    };

    /**
     * Convertit un segment de ligne en HTML via les gabarits (toute donnée est échappée).
     * @param {string|{href:string, label:string}|{command:string, label:string}} segment
     * @returns {string}
     */
    V.renderSegment = function (segment) {
        if (typeof segment === 'string') return this.escape(segment);

        if ('href' in segment) {
            // Liens web externes : nouvel onglet sécurisé ; mailto:/tel: restent dans l'onglet courant
            const external = /^https?:/i.test(segment.href);
            return this.fill(this.tpl.link, {
                href: this.escape(segment.href),
                target: external ? ' target="_blank" rel="noopener noreferrer"' : '',
                label: this.escape(segment.label)
            });
        }

        return this.fill(this.tpl.chip, {
            command: this.escape(segment.command),
            label: this.escape(segment.label)
        });
    };

    /**
     * Ajoute des lignes à la sortie. Le HTML est accumulé dans une chaîne puis inséré
     * en une seule opération (insertAdjacentHTML n'écrase pas les lignes existantes).
     * @param {Array<{type:string, segments:Array}>} lines
     * @returns {void}
     */
    V.renderLines = function (lines) {
        if (!lines.length) return;
        let html = '';
        lines.forEach((line) => {
            const content = line.segments.map((segment) => this.renderSegment(segment)).join('');
            html += this.fill(this.tpl.line, {type: this.escape(line.type), content});
        });
        this.el.output.insertAdjacentHTML('beforeend', html);
        this.el.output.scrollTop = this.el.output.scrollHeight;
    };

    /**
     * Vide la sortie (commande clear / Ctrl+L).
     * @returns {void}
     */
    V.clear = function () {
        this.el.output.innerHTML = '';
    };

    /**
     * @returns {string} Saisie courante.
     */
    V.getInput = function () {
        return this.el.input.value;
    };

    /**
     * Remplace la saisie et place le curseur texte en fin de ligne.
     * @param {string} value
     * @returns {void}
     */
    V.setInput = function (value) {
        this.el.input.value = value;
        this.el.input.setSelectionRange(value.length, value.length);
    };

    /**
     * Donne le focus au champ de saisie.
     * @returns {void}
     */
    V.focusInput = function () {
        this.el.input.focus({preventScroll: true});
    };

    /**
     * Verrouille / déverrouille la saisie pendant une séquence animée.
     * readOnly (et non disabled) : le champ garde le focus clavier.
     * @param {boolean} busy
     * @returns {void}
     */
    V.setBusy = function (busy) {
        this.el.dialog.classList.toggle('is-busy', busy);
        this.el.input.readOnly = busy;
    };

    /**
     * @returns {string} Mode actif du site (dev | photo).
     */
    V.getTheme = function () {
        return document.documentElement.dataset.mode === 'photo' ? 'photo' : 'dev';
    };

    /**
     * Demande un changement de mode à theme-toggle.js (événement « mode:request ») :
     * même transition et même mémorisation que l'interrupteur, sans dépendre de son markup.
     * @param {string} mode - dev | photo.
     * @returns {void}
     */
    V.setTheme = function (mode) {
        document.dispatchEvent(new CustomEvent('mode:request', {detail: {mode}}));
    };

    /**
     * @param {string} id
     * @returns {boolean} true si la section existe sur la page courante.
     */
    V.hasSection = function (id) {
        return Boolean(document.getElementById(id));
    };

    /**
     * Fait défiler la page jusqu'à une section.
     * @param {string} id
     * @param {boolean} smooth - false en mouvement réduit.
     * @returns {void}
     */
    V.scrollToSection = function (id, smooth) {
        document.getElementById(id)?.scrollIntoView({behavior: smooth ? 'smooth' : 'auto', block: 'start'});
    };

    /**
     * Change de page.
     * @param {string} url
     * @returns {void}
     */
    V.navigate = function (url) {
        window.location.assign(url);
    };

    /**
     * Demande à consent.js de rouvrir le panneau de choix de mesure d'audience (événement `consent:open`).
     * @returns {void}
     */
    V.requestConsentPanel = function () {
        document.dispatchEvent(new CustomEvent('consent:open'));
    };

    /* =======================================================================
       C — CONTRÔLEUR
       ======================================================================= */
    const C = {};

    /**
     * Point d'entrée : prépare la Vue et branche les handlers.
     * @returns {void}
     */
    C.init = function () {
        const ready = V.init({
            onToggle: C.toggle,
            onShortcut: C.handler_shortcut,
            onSubmit: C.handler_submit,
            onKeyDown: C.handler_keyDown,
            onDialogClick: C.handler_dialogClick,
            onClose: C.handler_close
        });
        if (!ready) return;
        M.state.reducedMotion = V.prefersReducedMotion();
        V.renderShortcutLabel();
    };

    /**
     * Ouvre ou ferme le terminal.
     * @returns {void}
     */
    C.toggle = function () {
        if (V.isOpen()) V.close();
        else C.open();
    };

    /**
     * Ouverture : bannière à la première fois, focus du champ, préchargement des données.
     * @returns {void}
     */
    C.open = function () {
        V.open();
        if (!M.state.booted) {
            M.state.booted = true;
            V.renderLines(M.welcome());
        }
        V.focusInput();
        // Préchargement silencieux : la première commande « data » répondra sans attente
        M.loadData();
    };

    /**
     * Raccourci global ⌘K / Ctrl+K (bascule ouverture / fermeture).
     * @param {KeyboardEvent} e
     * @returns {void}
     */
    C.handler_shortcut = function (e) {
        if (!(e.metaKey || e.ctrlKey) || e.altKey || e.shiftKey || e.key.toLowerCase() !== 'k') return;
        e.preventDefault(); // neutralise la recherche du navigateur associée à ce raccourci
        if (!e.repeat) C.toggle();
    };

    /**
     * Validation de la saisie (Entrée).
     * @param {SubmitEvent} e
     * @returns {Promise<void>}
     */
    C.handler_submit = async function (e) {
        e.preventDefault();
        if (M.state.busy) return;
        const raw = V.getInput();
        V.setInput('');
        await C.run(raw);
    };

    /**
     * Exécute une commande : écho, historique, exécution par le Modèle, puis effet éventuel.
     * @param {string} raw - Saisie brute.
     * @returns {Promise<void>}
     */
    C.run = async function (raw) {
        const entry = raw.trim();
        V.renderLines([M.line('command', entry || ' ')]);
        if (!entry) return;

        M.pushHistory(entry);
        const result = await M.execute(entry, {currentMode: V.getTheme()});
        V.renderLines(result.lines);
        if (result.effect) await C.applyEffect(result.effect);
    };

    /**
     * Applique l'effet demandé par une commande (seul endroit où une commande agit sur la page).
     * @param {{type:string, mode?:string, target?:string, steps?:Array}} effect (types : clear, close, theme, goto, navigate, consent, sequence)
     * @returns {Promise<void>}
     */
    C.applyEffect = async function (effect) {
        switch (effect.type) {
            case 'clear':
                V.clear();
                break;
            case 'close':
                V.close();
                break;
            case 'theme':
                V.setTheme(effect.mode);
                break;
            case 'goto':
                C.goto(effect.target);
                break;
            case 'navigate':
                V.navigate(effect.target);
                break;
            case 'consent':
                V.close();
                V.requestConsentPanel();
                break;
            case 'sequence':
                await C.playSequence(effect.steps);
                break;
        }
    };

    /**
     * Navigation `goto` : défilement si la section est sur la page, sinon changement de page.
     * @param {string} target - Identifiant de section.
     * @returns {void}
     */
    C.goto = function (target) {
        if (target === 'lab') {
            V.navigate('/lab');
            return;
        }
        if (V.hasSection(target)) {
            V.close();
            V.scrollToSection(target, !M.state.reducedMotion);
            return;
        }
        V.navigate(`/#${target}`);
    };

    /**
     * Joue une séquence de lignes temporisées (saisie verrouillée pendant la lecture).
     * En mouvement réduit, tout s'affiche immédiatement.
     * @param {Array<{delay:number, line:Object}>} steps
     * @returns {Promise<void>}
     */
    C.playSequence = async function (steps) {
        M.state.busy = true;
        V.setBusy(true);
        for (const step of steps) {
            await C.wait(M.state.reducedMotion ? 0 : step.delay);
            V.renderLines([step.line]);
        }
        M.state.busy = false;
        V.setBusy(false);
        V.focusInput();
    };

    /**
     * Pause asynchrone.
     * @param {number} ms
     * @returns {Promise<void>}
     */
    C.wait = function (ms) {
        return new Promise((resolve) => setTimeout(resolve, ms));
    };

    /**
     * Raccourcis du champ : ↑/↓ historique, Tab autocomplétion, Ctrl+L effacer.
     * @param {KeyboardEvent} e
     * @returns {void}
     */
    C.handler_keyDown = function (e) {
        if (M.state.busy) return;

        if (e.key === 'ArrowUp') {
            e.preventDefault();
            V.setInput(M.previousEntry());
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            V.setInput(M.nextEntry());
        } else if (e.key === 'Tab') {
            // Tab ne doit pas sortir du champ : on complète à la place
            e.preventDefault();
            const {completed, matches} = M.complete(V.getInput());
            if (completed) V.setInput(completed);
            else if (matches.length > 1) V.renderLines([M.line('muted', matches.join('   '))]);
        } else if (e.ctrlKey && e.key.toLowerCase() === 'l') {
            e.preventDefault();
            V.clear();
        }
    };

    /**
     * Clic délégué dans le terminal : backdrop (repli Safari), commande cliquable, fermeture,
     * ou clic dans la sortie (rend le focus au champ).
     * @param {MouseEvent} e
     * @returns {void}
     */
    C.handler_dialogClick = function (e) {
        if (!V.supportsClosedBy() && V.isBackdropClick(e)) {
            V.close();
            return;
        }

        const command = V.chipCommandFrom(e.target);
        if (command) {
            if (!M.state.busy) C.run(command).then(V.focusInput.bind(V));
            return;
        }

        if (V.actionFrom(e.target) === 'close') {
            V.close();
            return;
        }

        if (V.isPlainOutputClick(e.target)) V.focusInput();
    };

    /**
     * Fermeture (bouton, Échap, clic extérieur, exit) : état ARIA et curseur d'historique remis à zéro.
     * @returns {void}
     */
    C.handler_close = function () {
        V.markClosed();
        M.state.historyCursor = M.state.history.length;
    };

    C.init();
})();
