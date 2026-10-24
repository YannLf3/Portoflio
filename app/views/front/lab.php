<?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <main id="main" tabindex="-1" class="mt-12 w-full flex flex-col gap-20">

        <section class="flex flex-col items-start gap-4 border-b border-border-strong pb-12">
            <span class="text-xs font-mono tracking-widest text-accent uppercase">
                Lab — Laboratoire &amp; Veille
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
                            <span class="font-mono text-xs text-accent">&lt;/&gt;</span>
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
                                <p class="text-sm text-muted-foreground mt-1">Snippet interactif</p>
                            </div>
                            <span class="text-xs font-mono px-2 py-0.5 rounded border border-accent/30 text-accent">
                                <?= htmlspecialchars($snippet['category']) ?>
                            </span>
                        </div>

                        <div class="h-48 rounded-lg bg-background border border-border-strong relative overflow-hidden" id="snippet-container-<?= $index ?>">
                            <iframe sandbox="allow-scripts"
                                    srcdoc="<?= htmlspecialchars($snippet['srcdoc'], ENT_QUOTES) ?>"
                                    title="Aperçu : <?= htmlspecialchars($snippet['title'], ENT_QUOTES) ?>"
                                    loading="lazy"
                                    class="w-full h-full border-0"></iframe>
                        </div>

                        <div class="flex justify-between items-center text-xs font-mono text-muted-foreground pt-2 border-t border-border-strong">
                            <span><?= htmlspecialchars($snippet['badge']) ?></span>
                            <button type="button" class="text-accent underline-offset-4 hover:underline py-1 open-code-modal"
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
                            <span class="font-mono text-accent"></span>
                            <span>Workflows, Mac &amp; Design Notes</span>
                        </span>
                    </span>
                </h2>
                <span class="text-xs font-mono text-muted-foreground">Affinity / macOS / Tech</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php array_walk($articles, static function (array $article): void { ?>
                    <article
                            class="border border-border-strong rounded-xl bg-background-card/20 p-5 flex flex-col gap-4 hover:border-accent/40 transition-all duration-300">
                        <div class="flex items-center justify-between text-xs font-mono text-muted-foreground">
                            <span class="text-accent"><?= htmlspecialchars($article['category']) ?></span>
                            <span><?= htmlspecialchars($article['date']) ?></span>
                        </div>
                        <h3 class="font-bold text-foreground text-base leading-snug">
                            <?= htmlspecialchars($article['title']) ?>
                        </h3>
                        <p class="text-sm text-muted-foreground leading-relaxed line-clamp-3">
                            <?= htmlspecialchars($article['excerpt']) ?>
                        </p>
                        <div class="pt-2 mt-auto">
                            <a href="/lab/<?= htmlspecialchars($article['slug']) ?>"
                               class="inline-block py-1 text-sm font-mono text-accent underline-offset-4 hover:underline">
                                Lire l'article<span class="sr-only"> : <?= htmlspecialchars($article['title']) ?></span>
                            </a>
                        </div>
                    </article>
                <?php }); ?>
            </div>
        </section>

    </main>

    <dialog id="code-dialog" aria-labelledby="code-modal-title"
            class="m-auto w-[calc(100%-2rem)] max-w-3xl max-h-[85vh] p-0 overflow-hidden rounded-xl border border-border-strong bg-background text-foreground shadow-2xl backdrop:bg-black/80 backdrop:backdrop-blur-md open:flex flex-col">

        <div class="flex items-center justify-between gap-4 px-4 py-3 border-b border-border-strong bg-background-2">
            <h2 id="code-modal-title" class="text-sm font-mono text-muted-foreground">Console</h2>

            <div role="tablist" aria-label="Langage affiché" class="flex gap-1 font-mono text-sm">
                <button type="button" role="tab" id="code-tab-html" aria-selected="true" aria-controls="code-panel"
                        class="code-tab px-3 py-2 rounded bg-background-card text-accent" data-lang="html">HTML</button>
                <button type="button" role="tab" id="code-tab-css" aria-selected="false" aria-controls="code-panel" tabindex="-1"
                        class="code-tab px-3 py-2 rounded text-muted-foreground hover:text-foreground" data-lang="css">CSS</button>
                <button type="button" role="tab" id="code-tab-js" aria-selected="false" aria-controls="code-panel" tabindex="-1"
                        class="code-tab px-3 py-2 rounded text-muted-foreground hover:text-foreground" data-lang="js">JS</button>
            </div>

            <button type="button" class="close-modal inline-flex items-center justify-center w-10 h-10 rounded-full text-muted-foreground hover:text-foreground hover:bg-background-card"
                    aria-label="Fermer la console">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>

        <div id="code-panel" role="tabpanel" tabindex="0" aria-labelledby="code-tab-html"
             class="p-6 overflow-y-auto font-mono text-sm text-foreground bg-background leading-relaxed">
            <pre><code id="code-content" class="select-text whitespace-pre-wrap"></code></pre>
        </div>

        <p class="px-4 py-2 border-t border-border-strong text-xs font-mono text-muted-foreground">
            Lecture seule. Échap pour fermer.
        </p>
    </dialog>

    <script src="/js/lab.js?v=20261025" defer></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>