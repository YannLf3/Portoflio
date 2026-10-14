<section id="projects" class="mt-24 flex flex-col gap-12 w-full">

    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-8">

        <div class="flex flex-col items-start gap-3">
            <span class="text-xs font-mono tracking-widest text-accent uppercase">
                03 — Projets sélectionnés
            </span>
            <h2 class="reveal-title text-3xl sm:text-4xl lg:text-5xl font-display font-normal tracking-tight text-foreground leading-tight">
                <span class="reveal-title__mask">
                    <span class="reveal-title__inner">Mes projets</span>
                </span>
            </h2>
        </div>

        <div class="flex flex-wrap gap-2 sm:gap-3" id="project-filters">
            <button type="button"
                    data-filter="all"
                    data-magnetic
                    aria-pressed="true"
                    class="filter-btn btn-glow btn-glow--sm">
                Tous
            </button>

            <?php array_walk($projectFilters, static function (array $filter): void { ?>
                <button type="button"
                        data-filter="<?= htmlspecialchars($filter['value']) ?>"
                        data-magnetic
                        aria-pressed="false"
                        class="filter-btn btn-glow btn-glow--sm">
                    <?= htmlspecialchars($filter['label']) ?>
                </button>
            <?php }); ?>
        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:grid-cols-3 lg:gap-8" id="projects-grid">

        <?php array_walk($projects, static function (array $project): void { ?>
            <a href="<?= htmlspecialchars($project['link']) ?>"
                    <?= $project['isExternal'] ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
               data-category="<?= htmlspecialchars($project['filterCategory']) ?>"
               data-tilt
               class="project-card tilt-card group relative flex flex-col rounded-2xl border border-border-strong bg-background-card overflow-hidden hover:shadow-2xl hover:border-accent/30 z-10 lg:max-w-130">

                <div class="relative h-56 sm:h-64 w-full bg-border-strong/20">
                    <img src="<?= htmlspecialchars($project['image']) ?>"
                         alt="<?= htmlspecialchars($project['title']) ?>"
                         width="1600" height="900"
                         class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                         loading="lazy">

                    <div class="absolute inset-0 bg-gradient-to-b from-background/80 via-transparent to-transparent"></div>

                    <span class="absolute top-5 left-6 text-4xl font-display italic text-white font-light"><?= $project['number'] ?></span>

                    <?php if ($project['hasMeta']): ?>
                        <span class="absolute top-6 right-6 px-3 py-1 rounded-full border border-white/20 bg-background/50 backdrop-blur-md text-[10px] font-mono tracking-widest text-white uppercase">
                            <?= htmlspecialchars($project['meta']) ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($project['hasCategory']): ?>
                        <span class="absolute -bottom-3 left-6 z-20 px-3 py-1 rounded-full bg-accent text-background text-[11px] font-bold tracking-widest uppercase">
                            <?= htmlspecialchars($project['category']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="flex flex-col gap-4 p-6 pt-8 relative grow">

                    <div class="flex items-center justify-between gap-4">
                        <h3 class="text-xl sm:text-2xl font-medium text-foreground">
                            <?= htmlspecialchars($project['title']) ?>
                        </h3>

                        <div class="w-10 h-10 shrink-0 rounded-full border border-border-strong flex items-center justify-center transition-all duration-300 group-hover:bg-accent group-hover:border-accent text-muted-foreground group-hover:text-background">
                            <svg class="w-5 h-5 transition-transform duration-300 group-hover:-rotate-45"
                                 fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>

                    <p class="text-sm text-muted-foreground leading-relaxed">
                        <?= htmlspecialchars($project['description']) ?>
                    </p>

                    <?php if ($project['hasTags']): ?>
                        <div class="flex flex-wrap gap-2 mt-auto pt-4">
                            <?php array_walk($project['tags'], static function (string $tag): void { ?>
                                <span class="px-2.5 py-1 rounded bg-background border border-border-strong text-[10px] font-mono tracking-wider text-accent uppercase">
                                    <?= htmlspecialchars($tag) ?>
                                </span>
                            <?php }); ?>
                        </div>
                    <?php endif; ?>

                </div>
            </a>
        <?php }); ?>

    </div>

</section>

<script src="/js/projects-filter.js?v=20261014" defer></script>
