<?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <main class="mt-12 w-full flex flex-col gap-20">

        <section class="flex flex-col items-start gap-4 border-b border-border-strong pb-12">
            <span class="text-xs font-mono tracking-widest text-accent uppercase [html[data-mode='photo']_&]:text-amber-400">
                04 — Laboratoire &amp; Veille
            </span>
            <h1 class="reveal-title reveal-title--short text-3xl sm:text-5xl font-display font-normal tracking-tight text-foreground">
                <span class="reveal-title__mask">
                    <span class="reveal-title__inner">Le Bac à Sable.</span>
                </span>
            </h1>
            <p class="text-muted-foreground text-sm sm:text-base max-w-2xl leading-relaxed">
                Espace d'expérimentation pour tester en direct des propriétés CSS modernes, des scripts JS, ainsi que
                mes notes et retours d'expérience sur l'écosystème Apple et la suite Affinity.
            </p>
        </section>

        <section class="flex flex-col gap-8">
            <div class="flex items-center justify-between">
                <h2 class="reveal-title reveal-title--short text-xl sm:text-2xl font-display text-foreground">
                    <span class="reveal-title__mask">
                        <span class="reveal-title__inner flex items-center gap-3">
                            <span class="font-mono text-xs text-accent [html[data-mode='photo']_&]:text-amber-400">&lt;/&gt;</span>
                            <span>Snippets &amp; UI Experiments</span>
                        </span>
                    </span>
                </h2>
                <span class="text-xs font-mono text-muted-foreground">CSS / JS / Console</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <?php array_walk($snippets, static function (array $snippet, int|string $index): void { ?>
                    <div class="border border-border-strong rounded-xl bg-background-card/30 p-6 flex flex-col justify-between gap-6 relative overflow-hidden group">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-foreground text-base"><?= htmlspecialchars($snippet['title']) ?></h3>
                                <p class="text-xs text-muted-foreground mt-1">Snippet interactif</p>
                            </div>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded border border-accent/30 text-accent [html[data-mode='photo']_&]:border-amber-400/30 [html[data-mode='photo']_&]:text-amber-400">
                                <?= htmlspecialchars($snippet['category']) ?>
                            </span>
                        </div>

                        <div class="h-48 rounded-lg bg-zinc-950/80 border border-zinc-800 relative overflow-hidden" id="snippet-container-<?= $index ?>">
                            <iframe sandbox="allow-scripts"
                                    srcdoc="<?= htmlspecialchars($snippet['srcdoc'], ENT_QUOTES) ?>"
                                    title="Aperçu : <?= htmlspecialchars($snippet['title'], ENT_QUOTES) ?>"
                                    loading="lazy"
                                    class="w-full h-full border-0"></iframe>
                        </div>

                        <div class="flex justify-between items-center text-xs font-mono text-muted-foreground pt-2 border-t border-zinc-800/60">
                            <span><?= htmlspecialchars($snippet['badge']) ?></span>
                            <button type="button" class="text-accent hover:underline [html[data-mode='photo']_&]:text-amber-400 open-code-modal"
                                    data-title="<?= htmlspecialchars($snippet['modalTitle'], ENT_QUOTES) ?>"
                                    data-html="<?= htmlspecialchars($snippet['html'], ENT_QUOTES) ?>"
                                    data-css="<?= htmlspecialchars($snippet['css'], ENT_QUOTES) ?>"
                                    data-js="<?= htmlspecialchars($snippet['js'], ENT_QUOTES) ?>">
                                Voir le code →
                            </button>
                        </div>
                    </div>
                <?php }); ?>
            </div>
        </section>

        <section class="flex flex-col gap-8 pb-20">
            <div class="flex items-center justify-between border-t border-border-strong pt-12">
                <h2 class="reveal-title reveal-title--short text-xl sm:text-2xl font-display text-foreground">
                    <span class="reveal-title__mask">
                        <span class="reveal-title__inner flex items-center gap-3">
                            <span class="font-mono text-accent [html[data-mode='photo']_&]:text-amber-400"></span>
                            <span>Workflows, Mac &amp; Design Notes</span>
                        </span>
                    </span>
                </h2>
                <span class="text-xs font-mono text-muted-foreground">Affinity / macOS / Tech</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php array_walk($articles, static function (array $article): void { ?>
                    <article
                            class="border border-border-strong rounded-xl bg-background-card/20 p-5 flex flex-col gap-4 hover:border-zinc-700 transition-all duration-300">
                        <div class="flex items-center justify-between text-xs font-mono text-muted-foreground">
                            <span class="text-accent [html[data-mode='photo']_&]:text-amber-400"><?= htmlspecialchars($article['category']) ?></span>
                            <span><?= htmlspecialchars($article['date']) ?></span>
                        </div>
                        <h3 class="font-bold text-foreground text-base leading-snug">
                            <?= htmlspecialchars($article['title']) ?>
                        </h3>
                        <p class="text-xs text-muted-foreground leading-relaxed line-clamp-3">
                            <?= htmlspecialchars($article['excerpt']) ?>
                        </p>
                        <div class="pt-2 mt-auto">
                            <button type="button" class="text-xs font-mono text-accent hover:underline [html[data-mode='photo']_&]:text-amber-400 open-article-modal"
                                    data-title="<?= htmlspecialchars($article['title'], ENT_QUOTES) ?>"
                                    data-category="<?= htmlspecialchars($article['meta'], ENT_QUOTES) ?>"
                                    data-content="<?= htmlspecialchars($article['content'], ENT_QUOTES) ?>">
                                Lire l'article →
                            </button>
                        </div>
                    </article>
                <?php }); ?>
            </div>
        </section>

    </main>

    <div id="code-modal"
         class="fixed inset-0 z-[10000] bg-black/80 backdrop-blur-md hidden items-center justify-center p-4">
        <div class="bg-zinc-950 border border-zinc-800 rounded-xl w-full max-w-3xl overflow-hidden shadow-2xl flex flex-col max-h-[85vh]">

            <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-800 bg-zinc-900/50">
                <div class="flex items-center gap-2">
                    <button type="button" class="w-3 h-3 rounded-full bg-red-500/80 hover:bg-red-500 close-modal" aria-label="Fermer la console"></button>
                    <span class="w-3 h-3 rounded-full bg-yellow-500/80"></span>
                    <span class="w-3 h-3 rounded-full bg-green-500/80"></span>
                    <span id="code-modal-title" class="text-xs font-mono text-zinc-400 ml-2">Console</span>
                </div>

                <div class="flex gap-1 font-mono text-xs">
                    <button type="button" class="code-tab px-3 py-1 rounded bg-zinc-800 text-accent active" data-lang="html">HTML
                    </button>
                    <button type="button" class="code-tab px-3 py-1 rounded text-zinc-400 hover:text-zinc-200" data-lang="css">CSS
                    </button>
                    <button type="button" class="code-tab px-3 py-1 rounded text-zinc-400 hover:text-zinc-200" data-lang="js">JS
                    </button>
                </div>
            </div>

            <div class="p-6 overflow-y-auto font-mono text-xs md:text-sm text-zinc-300 bg-zinc-950 leading-relaxed">
                <pre><code id="code-content" class="select-text whitespace-pre-wrap"></code></pre>
            </div>

            <div class="px-4 py-2 border-t border-zinc-800 text-[11px] font-mono text-zinc-500 flex justify-between">
                <span>READ-ONLY CONSOLE</span>
                <span>PRESS ESC TO CLOSE</span>
            </div>
        </div>
    </div>

    <div id="article-modal"
         class="fixed inset-0 z-[10000] bg-black/80 backdrop-blur-md hidden items-center justify-center p-4">
        <div class="bg-zinc-950 border border-zinc-800 rounded-xl w-full max-w-2xl overflow-hidden shadow-2xl flex flex-col max-h-[85vh]">

            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-800">
                <span id="article-modal-category"
                      class="text-xs font-mono text-accent [html[data-mode='photo']_&]:text-amber-400">CATEGORY</span>
                <button type="button" class="text-zinc-500 hover:text-zinc-200 font-mono text-sm close-modal">✕ Fermer</button>
            </div>

            <div class="p-6 md:p-8 overflow-y-auto space-y-4">
                <h2 id="article-modal-title" class="text-xl md:text-2xl font-display text-foreground">Titre</h2>
                <div id="article-modal-body"
                     class="text-sm text-zinc-300 leading-relaxed space-y-3 font-body whitespace-pre-line">
                    Contenu de l'article...
                </div>
            </div>
        </div>
    </div>

    <script src="/js/lab.js?v=20261014" defer></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>