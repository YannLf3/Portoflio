<section id="skills" aria-labelledby="skills-title" class="mt-24 w-full flex flex-col gap-12">

    <div class="flex flex-col items-start gap-2">
        <span class="text-xs font-mono tracking-widest text-accent uppercase">
            02 — Compétences &amp; Outils
        </span>
        <h2 id="skills-title" class="sr-only">Compétences, outils et langues</h2>
    </div>

    <div class="w-full overflow-hidden border-y border-border-strong py-4 relative flex items-center gap-4">
        <div class="relative overflow-hidden grow" aria-hidden="true">
            <div class="absolute left-0 top-0 bottom-0 w-12 bg-linear-to-r from-background to-transparent z-10 pointer-events-none"></div>
            <div class="absolute right-0 top-0 bottom-0 w-12 bg-linear-to-l from-background to-transparent z-10 pointer-events-none"></div>
            <?php
            // Mots défilants par univers : stack en mode Dev, vocabulaire photo en mode Photo
            $marqueeSets = [
                'dev' => ['words' => $skillsTech, 'visibility' => "[html[data-mode='photo']_&]:hidden"],
                'photo' => [
                    'words' => ['Shooting Portrait', 'Color Grading', 'Lightroom', 'Montage DaVinci', 'Affinity by Canva', 'Panasonic Lumix'],
                    'visibility' => "hidden [html[data-mode='photo']_&]:block",
                ],
            ];
            ?>
            <?php array_walk($marqueeSets, static function (array $set, string $mode): void { ?>
                <div class="<?= $set['visibility'] ?>" data-marquee-mode="<?= $mode ?>">
                    <div class="animate-marquee whitespace-nowrap gap-8 items-center text-xl sm:text-2xl font-display italic text-foreground/80">
                    <?php array_map(static function (int $copy) use ($set): void { ?>
                        <div class="flex items-center gap-8 shrink-0">
                            <?php array_walk($set['words'], static function (string $word): void { ?>
                                <span><?= htmlspecialchars($word) ?></span>
                                <span class="w-1 h-1 rounded-full bg-accent"></span>
                            <?php }); ?>
                        </div>
                    <?php }, [1, 2]); ?>
                    </div>
                </div>
            <?php }); ?>
        </div>

        <button type="button" id="marquee-toggle" aria-pressed="false"
                class="shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-full border border-border-strong text-muted-foreground hover:text-accent hover:border-accent"
                aria-label="Mettre en pause le défilement des compétences">
            <svg class="marquee-icon marquee-icon--pause" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 5h4v14H7zM13 5h4v14h-4z"/></svg>
            <svg class="marquee-icon marquee-icon--play hidden" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start">

        <div class="lg:col-span-7 flex flex-col gap-6">

            <div class="[html[data-mode='photo']_&]:hidden flex flex-wrap gap-2.5">
                <?php array_walk($skillsTech, static function (string $tech): void { ?>
                    <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-body text-foreground">
                        <?= htmlspecialchars($tech) ?>
                    </span>
                <?php }); ?>
            </div>

            <div class="[html[data-mode='photo']_&]:hidden flex flex-wrap gap-2 pt-2">
                <?php array_walk($skillsTools, static function (string $tool): void { ?>
                    <span class="px-3 py-1 rounded-md border border-border-strong bg-background-card/20 text-xs font-mono uppercase tracking-wider text-muted-foreground">
                        <?= htmlspecialchars($tool) ?>
                    </span>
                <?php }); ?>
            </div>

            <div class="hidden [html[data-mode='photo']_&]:flex flex-col gap-6">
                <div class="flex flex-wrap gap-2.5">
                    <span class="px-3.5 py-1.5 rounded-full border border-accent/30 bg-accent-soft text-xs font-body text-foreground">Portrait &amp; Street Photography</span>
                    <span class="px-3.5 py-1.5 rounded-full border border-accent/30 bg-accent-soft text-xs font-body text-foreground">Color Grading</span>
                    <span class="px-3.5 py-1.5 rounded-full border border-accent/30 bg-accent-soft text-xs font-body text-foreground">Montage vidéo dynamique</span>
                    <span class="px-3.5 py-1.5 rounded-full border border-accent/30 bg-accent-soft text-xs font-body text-foreground">Retouche photo avancée</span>
                    <span class="px-3.5 py-1.5 rounded-full border border-accent/30 bg-accent-soft text-xs font-body text-foreground">Création de contenu réseaux</span>
                </div>

                <div class="flex flex-wrap gap-2 pt-2">
                    <span class="px-3 py-1 rounded-md border border-accent/20 bg-accent-soft text-xs font-mono uppercase tracking-wider text-accent">LIGHTROOM</span>
                    <span class="px-3 py-1 rounded-md border border-accent/20 bg-accent-soft text-xs font-mono uppercase tracking-wider text-accent">DAVINCI RESOLVE</span>
                    <span class="px-3 py-1 rounded-md border border-accent/20 bg-accent-soft text-xs font-mono uppercase tracking-wider text-accent">PHOTOSHOP</span>
                    <span class="px-3 py-1 rounded-md border border-accent/20 bg-accent-soft text-xs font-mono uppercase tracking-wider text-accent">AFFINITY BY CANVA</span>
                    <span class="px-3 py-1 rounded-md border border-accent/20 bg-accent-soft text-xs font-mono uppercase tracking-wider text-accent">BOÎTIER PANASONIC</span>
                </div>
            </div>

        </div>

        <div class="lg:col-span-5 flex flex-col gap-6 pt-2">

            <span class="text-xs font-mono tracking-widest text-muted-foreground uppercase">
                LANGUES
            </span>

            <div class="flex flex-col gap-6">

                <?php array_walk($languages, static function (array $language): void { ?>
                    <div class="flex flex-col gap-2">
                        <div class="flex justify-between items-center text-sm font-body">
                            <span class="text-foreground"><?= htmlspecialchars($language['name']) ?></span>
                            <span class="text-xs font-mono text-muted-foreground"><?= htmlspecialchars($language['level']) ?></span>
                        </div>
                        <div class="w-full h-1 rounded-full bg-border-strong relative overflow-hidden" role="meter"
                             aria-label="Niveau en <?= htmlspecialchars($language['name']) ?>"
                             aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $language['percent'] ?>"
                             aria-valuetext="<?= htmlspecialchars($language['level']) ?>">
                            <div class="absolute left-0 top-0 bottom-0 bg-accent"
                                 style="width: <?= $language['percent'] ?>%;"></div>
                        </div>
                    </div>
                <?php }); ?>

            </div>

        </div>

    </div>

</section>
