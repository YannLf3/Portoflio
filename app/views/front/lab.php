<?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <main class="mt-12 w-full flex flex-col gap-20">

        <!-- En-tête de la page -->
        <section class="flex flex-col items-start gap-4 border-b border-border-strong pb-12">
        <span class="text-xs font-mono tracking-widest text-accent uppercase [html[data-mode='photo']_&]:text-amber-400">
            04 — Laboratoire &amp; Veille
        </span>
            <h1 class="text-3xl sm:text-5xl font-serif font-normal tracking-tight text-foreground">
                Le Bac à Sable.
            </h1>
            <p class="text-muted-foreground text-sm sm:text-base max-w-2xl leading-relaxed">
                Espace d'expérimentation pour tester en direct des propriétés CSS modernes, des scripts JS, ainsi que
                mes notes et retours d'expérience sur l'écosystème Apple et la suite Affinity.
            </p>
        </section>

        <!-- SECTION 1 : CODEPEN / EXPERIMENTATIONS CSS & JS -->
        <section class="flex flex-col gap-8">
            <div class="flex items-center justify-between">
                <h2 class="text-xl sm:text-2xl font-serif text-foreground flex items-center gap-3">
                    <span class="font-mono text-xs text-accent [html[data-mode='photo']_&]:text-amber-400">&lt;/&gt;</span>
                    <span>Snippets &amp; UI Experiments</span>
                </h2>
                <span class="text-xs font-mono text-muted-foreground">CSS / JS / Canvas</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                <!-- Expérience 1 : Glow Effect -->
                <div class="border border-border-strong rounded-xl bg-background-card/30 p-6 flex flex-col justify-between gap-6 relative overflow-hidden group">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-foreground text-base">Glow Effect &amp; Dynamic Border</h3>
                            <p class="text-xs text-muted-foreground mt-1">CSS `@property` &amp; Conic Gradients</p>
                        </div>
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded border border-accent/30 text-accent [html[data-mode='photo']_&]:border-amber-400/30 [html[data-mode='photo']_&]:text-amber-400">CSS Modern</span>
                    </div>

                    <div class="h-40 rounded-lg bg-zinc-950/80 border border-zinc-800 flex items-center justify-center relative overflow-hidden">
                        <button class="relative px-6 py-3 rounded-xl bg-zinc-900 border border-zinc-700 text-xs font-mono text-zinc-200 transition-all duration-300 hover:scale-105 hover:border-accent hover:shadow-[0_0_20px_rgba(34,197,94,0.3)]">
                            Survole-moi !
                        </button>
                    </div>

                    <div class="flex justify-between items-center text-xs font-mono text-muted-foreground pt-2 border-t border-zinc-800/60">
                        <span>Tested on Chrome / Safari</span>
                        <button class="text-accent hover:underline [html[data-mode='photo']_&]:text-amber-400 open-code-modal"
                                data-title="Glow Effect & Dynamic Border"
                                data-html='<button class="glow-button">\n  Survole-moi !\n</button>'
                                data-css='@property --angle {\n  syntax: "<angle>";\n  initial-value: 0deg;\n  inherits: false;\n}\n\n.glow-button {\n  background: #18181b;\n  border: 1px solid transparent;\n  border-image: conic-gradient(from var(--angle), #22c55e, transparent) 1;\n  animation: rotate 4s linear infinite;\n}'
                                data-js='// Pas de JavaScript requis pour cet effet (100% CSS moderne)'>
                            Voir le code →
                        </button>
                    </div>
                </div>

                <!-- Expérience 2 : Canvas Particle -->
                <div class="border border-border-strong rounded-xl bg-background-card/30 p-6 flex flex-col justify-between gap-6 relative overflow-hidden group">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-foreground text-base">Mini Canvas Particle System</h3>
                            <p class="text-xs text-muted-foreground mt-1">Vanilla JS &amp; RequestAnimationFrame</p>
                        </div>
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded border border-accent/30 text-accent [html[data-mode='photo']_&]:border-amber-400/30 [html[data-mode='photo']_&]:text-amber-400">JS Native</span>
                    </div>

                    <div class="h-40 rounded-lg bg-zinc-950/80 border border-zinc-800 flex items-center justify-center">
                        <span class="text-xs font-mono text-zinc-500">[ Zone Canvas / DÉMO ]</span>
                    </div>

                    <div class="flex justify-between items-center text-xs font-mono text-muted-foreground pt-2 border-t border-zinc-800/60">
                        <span>Performance 60 FPS</span>
                        <button class="text-accent hover:underline [html[data-mode='photo']_&]:text-amber-400 open-code-modal"
                                data-title="Mini Canvas Particle System"
                                data-html='<canvas id="particle-canvas" width="400" height="160"></canvas>'
                                data-css='canvas {\n  width: 100%;\n  height: 100%;\n  background: #09090b;\n}'
                                data-js='const canvas = document.getElementById("particle-canvas");\nconst ctx = canvas.getContext("2d");\n\nfunction animate() {\n  ctx.clearRect(0, 0, canvas.width, canvas.height);\n  // Animation de particules à 60 FPS\n  requestAnimationFrame(animate);\n}\nanimate();'>
                            Voir le code →
                        </button>
                    </div>
                </div>

            </div>
        </section>

        <!-- SECTION 2 : BLOG & NOTES D'EXPÉRIENCE -->
        <section class="flex flex-col gap-8 pb-20">
            <div class="flex items-center justify-between border-t border-border-strong pt-12">
                <h2 class="text-xl sm:text-2xl font-serif text-foreground flex items-center gap-3">
                    <span class="font-mono text-accent [html[data-mode='photo']_&]:text-amber-400"></span>
                    <span>Workflows, Mac &amp; Design Notes</span>
                </h2>
                <span class="text-xs font-mono text-muted-foreground">Affinity / macOS / Tech</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Note 1 -->
                <article
                        class="border border-border-strong rounded-xl bg-background-card/20 p-5 flex flex-col gap-4 hover:border-zinc-700 transition-all duration-300">
                    <div class="flex items-center justify-between text-xs font-mono text-muted-foreground">
                        <span>AFFINITY DESIGNER</span>
                        <span>12 SEP 2026</span>
                    </div>
                    <h3 class="font-bold text-foreground text-base leading-snug">
                        Passer d'Illustrator à Affinity V3 : Mon retour d'expérience en vectoriel
                    </h3>
                    <p class="text-xs text-muted-foreground leading-relaxed">
                        Gestion des grilles vectorielles, export SVG web et optimisation des assets sans abonnement
                        Adobe.
                    </p>
                    <div class="pt-2 mt-auto">
                        <button class="text-xs font-mono text-accent [html[data-mode='photo']_&]:text-amber-400 open-article-modal"
                                data-title="Passer d'Illustrator à Affinity V3 : Mon retour d'expérience en vectoriel"
                                data-category="AFFINITY DESIGNER — 12 SEP 2026"
                                data-content="Après plusieurs mois d'utilisation intensive d'Affinity Designer V3 dans mes projets web et UI/UX, le passage depuis la suite Adobe Creative Cloud s'est fait sans friction majeure.\n\n### Les points forts :\n- **Paiement unique** : Fin de l'abonnement mensuel Adobe.\n- **Performance** : Temps de chargement instantané sur macOS Apple Silicon.\n- **Export SVG propre** : Les fichiers générés sont légers et directement exploitables en HTML/CSS.\n\nEn résumé, pour le design d'icônes, de maquettes web et d'illustrations vectorielles, Affinity constitue une alternative d'une grande fluidité.">
                            Lire l'article →
                        </button>
                    </div>
                </article>

                <!-- Note 2 -->
                <article
                        class="border border-border-strong rounded-xl bg-background-card/20 p-5 flex flex-col gap-4 hover:border-zinc-700 transition-all duration-300">
                    <div class="flex items-center justify-between text-xs font-mono text-muted-foreground">
                        <span>MACOS / DEV</span>
                        <span>02 SEP 2026</span>
                    </div>
                    <h3 class="font-bold text-foreground text-base leading-snug">
                        Mon Setup macOS pour coder en PHP MVC sans Docker lourd
                    </h3>
                    <p class="text-xs text-muted-foreground leading-relaxed">
                        Configuration d'Homebrew, PHP 8.3, MySQL natif et raccourcis Raycast pour booster sa
                        productivité.
                    </p>
                    <div class="pt-2 mt-auto">
                        <button class="text-xs font-mono text-accent [html[data-mode='photo']_&]:text-amber-400 open-article-modal"
                                data-title="Mon Setup macOS pour coder en PHP MVC sans Docker lourd"
                                data-category="MACOS / DEV — 02 SEP 2026"
                                data-content="Pour maintenir une excellente autonomie sur mon MacBook et éviter le surplus de mémoire consommé par Docker sur des projets légers, j'utilise un environnement serveur natif.\n\n### La Stack de développement :\n1. **Homebrew** pour l'installation de PHP 8.3 et MySQL.\n2. **Apache / Nginx** configuré localement avec des hôtes virtuels.\n3. **PhpStorm & VS Code** avec liaisons Git automatisées.\n4. **Raycast** pour lancer mes scripts et requêtes de base de données en un raccourci clavier.">
                            Lire l'article →
                        </button>
                    </div>
                </article>

                <!-- Note 3 -->
                <article
                        class="border border-border-strong rounded-xl bg-background-card/20 p-5 flex flex-col gap-4 hover:border-zinc-700 transition-all duration-300">
                    <div class="flex items-center justify-between text-xs font-mono text-muted-foreground">
                        <span>HARDWARE</span>
                        <span>28 AOU 2026</span>
                    </div>
                    <h3 class="font-bold text-foreground text-base leading-snug">
                        Étalonnage vidéo sur écran Apple Silicon : Retours sur DaVinci Resolve
                    </h3>
                    <p class="text-xs text-muted-foreground leading-relaxed">
                        Gestion des espaces colorimétriques Rec.709 vs Display P3 lors des exports pour TikTok et le
                        web.
                    </p>
                    <div class="pt-2 mt-auto">
                        <button class="text-xs font-mono text-accent [html[data-mode='photo']_&]:text-amber-400 open-article-modal"
                                data-title="Étalonnage vidéo sur écran Apple Silicon : Retours sur DaVinci Resolve"
                                data-category="HARDWARE — 28 AOU 2026"
                                data-content="Travailler l'étalonnage de vidéos automobiles ou de montages réseaux sur DaVinci Resolve demande une grande rigueur sur la chaîne de couleur.\n\n### Le piège du Gamma QuickTime sous macOS :\nLes écrans Apple utilisent l'espace Display P3 avec un profil de restitution spécifique. Lors de l'exportation :\n- Configurer la Timeline sur **Rec.709-DB** ou **Rec.709 A**.\n- Activer la gestion des profils de couleur d'affichage macOS dans les préférences DaVinci Resolve.\n\nCela évite l'effet d'image délavée lors de la relecture sur Safari ou sur smartphone.">
                            Lire l'article →
                        </button>
                    </div>
                </article>

            </div>
        </section>

    </main>

    <!-- ========================================== -->
    <!-- MODALE CONSOLE / CODE (NON MODIFIABLE)     -->
    <!-- ========================================== -->
    <div id="code-modal"
         class="fixed inset-0 z-[10000] bg-black/80 backdrop-blur-md hidden items-center justify-center p-4">
        <div class="bg-zinc-950 border border-zinc-800 rounded-xl w-full max-w-3xl overflow-hidden shadow-2xl flex flex-col max-h-[85vh]">

            <!-- En-tête macOS style -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-800 bg-zinc-900/50">
                <div class="flex items-center gap-2">
                    <button class="w-3 h-3 rounded-full bg-red-500/80 hover:bg-red-500 close-modal"></button>
                    <span class="w-3 h-3 rounded-full bg-yellow-500/80"></span>
                    <span class="w-3 h-3 rounded-full bg-green-500/80"></span>
                    <span id="code-modal-title" class="text-xs font-mono text-zinc-400 ml-2">Console</span>
                </div>

                <!-- Onglets HTML / CSS / JS -->
                <div class="flex gap-1 font-mono text-xs">
                    <button class="code-tab px-3 py-1 rounded bg-zinc-800 text-accent active" data-lang="html">HTML
                    </button>
                    <button class="code-tab px-3 py-1 rounded text-zinc-400 hover:text-zinc-200" data-lang="css">CSS
                    </button>
                    <button class="code-tab px-3 py-1 rounded text-zinc-400 hover:text-zinc-200" data-lang="js">JS
                    </button>
                </div>
            </div>

            <!-- Zone d'affichage du code (read-only) -->
            <div class="p-6 overflow-y-auto font-mono text-xs md:text-sm text-zinc-300 bg-zinc-950 leading-relaxed">
                <pre><code id="code-content" class="select-text whitespace-pre-wrap"></code></pre>
            </div>

            <div class="px-4 py-2 border-t border-zinc-800 text-[11px] font-mono text-zinc-500 flex justify-between">
                <span>READ-ONLY CONSOLE</span>
                <span>PRESS ESC TO CLOSE</span>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODALE LECTEUR ARTICLE / BLOG              -->
    <!-- ========================================== -->
    <div id="article-modal"
         class="fixed inset-0 z-[10000] bg-black/80 backdrop-blur-md hidden items-center justify-center p-4">
        <div class="bg-zinc-950 border border-zinc-800 rounded-xl w-full max-w-2xl overflow-hidden shadow-2xl flex flex-col max-h-[85vh]">

            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-800">
                <span id="article-modal-category"
                      class="text-xs font-mono text-accent [html[data-mode='photo']_&]:text-amber-400">CATEGORY</span>
                <button class="text-zinc-500 hover:text-zinc-200 font-mono text-sm close-modal">✕ Fermer</button>
            </div>

            <div class="p-6 md:p-8 overflow-y-auto space-y-4">
                <h2 id="article-modal-title" class="text-xl md:text-2xl font-serif text-foreground">Titre</h2>
                <div id="article-modal-body"
                     class="text-sm text-zinc-300 leading-relaxed space-y-3 font-sans whitespace-pre-line">
                    Contenu de l'article...
                </div>
            </div>
        </div>
    </div>

    <!-- Script d'interaction des modales -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const codeModal = document.getElementById('code-modal');
            const articleModal = document.getElementById('article-modal');
            const codeContent = document.getElementById('code-content');
            const codeTitle = document.getElementById('code-modal-title');
            const codeTabs = document.querySelectorAll('.code-tab');

            let currentSnippet = {html: '', css: '', js: ''};

            // 1. Ouverture Modale Code
            document.querySelectorAll('.open-code-modal').forEach(btn => {
                btn.addEventListener('click', () => {
                    currentSnippet = {
                        html: btn.dataset.html || '<!-- Pas de code HTML -->',
                        css: btn.dataset.css || '/* Pas de CSS */',
                        js: btn.dataset.js || '// Pas de JS'
                    };

                    codeTitle.textContent = btn.dataset.title || 'Console';
                    switchTab('html');
                    codeModal.classList.remove('hidden');
                    codeModal.classList.add('flex');
                });
            });

            // Gestion des onglets de code
            codeTabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    codeTabs.forEach(t => t.classList.remove('bg-zinc-800', 'text-accent', 'active'));
                    tab.classList.add('bg-zinc-800', 'text-accent', 'active');
                    switchTab(tab.dataset.lang);
                });
            });

            function switchTab(lang) {
                codeContent.textContent = currentSnippet[lang] || '';
            }

            // 2. Ouverture Modale Article
            document.querySelectorAll('.open-article-modal').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('article-modal-title').textContent = btn.dataset.title;
                    document.getElementById('article-modal-category').textContent = btn.dataset.category;
                    document.getElementById('article-modal-body').textContent = btn.dataset.content;

                    articleModal.classList.remove('hidden');
                    articleModal.classList.add('flex');
                });
            });

            // 3. Fermeture des modales
            document.querySelectorAll('.close-modal').forEach(btn => {
                btn.addEventListener('click', closeModal);
            });

            [codeModal, articleModal].forEach(modal => {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) closeModal();
                });
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeModal();
            });

            function closeModal() {
                codeModal.classList.add('hidden');
                codeModal.classList.remove('flex');
                articleModal.classList.add('hidden');
                articleModal.classList.remove('flex');
            }
        });
    </script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>