<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main id="main" tabindex="-1" class="w-full pt-16 pb-24 flex flex-col gap-16">

    <nav aria-label="Fil d'Ariane" class="text-xs font-mono uppercase tracking-widest text-muted-foreground">
        <ol class="flex flex-wrap items-center gap-2">
            <li><a href="/" class="inline-block py-1.5 hover:text-foreground">Accueil</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="/#projects" class="inline-block py-1.5 hover:text-foreground">Projets</a></li>
            <li aria-hidden="true">/</li>
            <li aria-current="page" class="text-foreground"><?= htmlspecialchars($project['title']) ?></li>
        </ol>
    </nav>

    <article class="flex flex-col gap-16">

        <header class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start">

            <div class="lg:col-span-5 flex flex-col items-start gap-6">
                <?php if ($project['hasCategory']): ?>
                    <span class="text-xs font-mono tracking-widest text-accent uppercase">
                        <?= htmlspecialchars($project['category']) ?>
                    </span>
                <?php endif; ?>

                <h1 class="text-display-md font-display font-normal tracking-tight text-foreground leading-tight">
                    <?= htmlspecialchars($project['title']) ?>
                </h1>

                <?php if ($project['description'] !== ''): ?>
                    <p class="text-muted-foreground text-base sm:text-lg leading-relaxed">
                        <?= nl2br(htmlspecialchars($project['description'])) ?>
                    </p>
                <?php endif; ?>

                <?php if ($project['hasTags']): ?>
                    <ul class="flex flex-wrap gap-2" aria-label="Technologies utilisées">
                        <?php array_walk($project['tags'], static function (string $tag): void { ?>
                            <li class="px-2.5 py-1 rounded bg-background border border-border-strong text-xs font-mono tracking-wider text-accent uppercase">
                                <?= htmlspecialchars($tag) ?>
                            </li>
                        <?php }); ?>
                    </ul>
                <?php endif; ?>

                <?php if ($project['link'] !== '#' || $project['repoLink'] !== ''): ?>
                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <?php if ($project['link'] !== '#'): ?>
                            <a href="<?= htmlspecialchars($project['link']) ?>"
                                    <?= $project['isExternal'] ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
                               class="btn-glow" data-track="project_link">
                                <span>Voir le projet</span>
                                <?php if ($project['isExternal']): ?>
                                    <span class="sr-only">(s'ouvre dans un nouvel onglet)</span>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($project['repoLink'] !== ''): ?>
                            <a href="<?= htmlspecialchars($project['repoLink']) ?>" target="_blank" rel="noopener noreferrer"
                               class="btn-glow btn-glow--ghost" data-track="repo_link">
                                <span>Code source</span>
                                <span class="sr-only">(dépôt GitHub, s'ouvre dans un nouvel onglet)</span>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="lg:col-span-7 flex flex-col gap-6">
                <div class="rounded-2xl border border-border-strong overflow-hidden bg-background-card">
                    <img src="<?= htmlspecialchars($project['image']) ?>"
                         alt="Aperçu du projet <?= htmlspecialchars($project['title']) ?>"
                         width="1600" height="900" class="w-full h-auto object-cover">
                </div>

                <?php if ($project['hasMeta'] || $project['year'] !== '' || $project['role'] !== ''): ?>
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-6 border-y border-border-strong py-6">
                        <?php if ($project['hasMeta']): ?>
                            <div class="flex flex-col gap-1">
                                <dt class="text-xs font-mono tracking-widest text-muted-foreground uppercase">Type</dt>
                                <dd class="text-sm text-foreground"><?= htmlspecialchars($project['meta']) ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if ($project['year'] !== ''): ?>
                            <div class="flex flex-col gap-1">
                                <dt class="text-xs font-mono tracking-widest text-muted-foreground uppercase">Année</dt>
                                <dd class="text-sm text-foreground"><?= htmlspecialchars($project['year']) ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if ($project['role'] !== ''): ?>
                            <div class="flex flex-col gap-1">
                                <dt class="text-xs font-mono tracking-widest text-muted-foreground uppercase">Rôle</dt>
                                <dd class="text-sm text-foreground"><?= htmlspecialchars($project['role']) ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                <?php endif; ?>
            </div>

        </header>

        <?php if ($project['context'] !== '' || $project['solution'] !== '' || $project['result'] !== ''): ?>
            <?php
            // Sections dans l'ordre d'affichage : titre, texte (une section vide n'est pas rendue)
            $sections = [
                ['Contexte et objectifs', $project['context']],
                ['Solution apportée', $project['solution']],
                ['Résultat', $project['result']],
            ];
            ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 lg:gap-16 max-w-6xl">
                <?php array_walk($sections, static function (array $section): void {
                    if ($section[1] === '') {
                        return;
                    } ?>
                    <section class="flex flex-col gap-4">
                        <h2 class="text-xl font-display text-foreground"><?= htmlspecialchars($section[0]) ?></h2>
                        <p class="text-sm sm:text-base text-muted-foreground leading-relaxed">
                            <?= nl2br(htmlspecialchars($section[1])) ?>
                        </p>
                    </section>
                <?php }); ?>
            </div>
        <?php endif; ?>

        <?php if ($project['gallery'] !== []): ?>
            <section class="flex flex-col gap-8" aria-labelledby="project-gallery-title">
                <h2 id="project-gallery-title" class="text-2xl sm:text-3xl font-display font-normal text-foreground">
                    Galerie du projet
                </h2>

                <div id="gallery-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php array_walk($project['gallery'], static function (string $src, int $index) use ($project): void {
                        $alt = sprintf('%s, capture %d', $project['title'], $index + 1); ?>
                        <button type="button"
                                data-lightbox-index="<?= $index ?>"
                                data-src="<?= htmlspecialchars($src) ?>"
                                data-alt="<?= htmlspecialchars($alt) ?>"
                                data-legend="<?= htmlspecialchars($alt) ?>"
                                aria-label="Agrandir l'image : <?= htmlspecialchars($alt) ?>"
                                class="block overflow-hidden rounded-xl border border-border-strong bg-background-card aspect-video hover:border-accent/40">
                            <img src="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($alt) ?>"
                                 width="1600" height="900" loading="lazy" decoding="async"
                                 class="w-full h-full object-cover">
                        </button>
                    <?php }); ?>
                </div>
            </section>

            <dialog id="lightbox" class="lightbox" closedby="any" aria-label="Visionneuse d'images">
                <figure class="lightbox__figure">
                    <img class="lightbox__img" data-lightbox="img" alt="">
                    <figcaption class="lightbox__caption">
                        <span class="lightbox__counter" data-lightbox="counter"></span>
                        <span class="lightbox__legend" data-lightbox="legend"></span>
                    </figcaption>
                </figure>

                <button type="button" class="lightbox__btn lightbox__btn--prev" data-lightbox-action="prev" aria-label="Image précédente">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button type="button" class="lightbox__btn lightbox__btn--next" data-lightbox-action="next" aria-label="Image suivante">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <button type="button" class="lightbox__btn lightbox__btn--close" data-lightbox-action="close" aria-label="Fermer la visionneuse">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>
            </dialog>
        <?php endif; ?>

    </article>

    <div class="flex flex-wrap items-center gap-3 border-t border-border-strong pt-10">
        <a href="/#projects" class="btn-glow btn-glow--ghost"><span>Tous les projets</span></a>
        <a href="/#contact" class="btn-glow"><span>Discuter d'un projet similaire</span></a>
    </div>

</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
