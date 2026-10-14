<section id="skills" class="mt-24 w-full flex flex-col gap-12">

    <div class="flex flex-col items-start gap-2">
        <span class="text-xs font-mono tracking-widest text-accent uppercase [html[data-mode='photo']_&]:text-amber-400">
            02 — Compétences &amp; Outils
        </span>
    </div>

    <div class="w-full overflow-hidden border-y border-border-strong py-4 relative group">
        <div class="absolute left-0 top-0 bottom-0 w-12 bg-gradient-to-r from-background to-transparent z-10 pointer-events-none"></div>
        <div class="absolute right-0 top-0 bottom-0 w-12 bg-gradient-to-l from-background to-transparent z-10 pointer-events-none"></div>

        <div class="flex whitespace-nowrap animate-marquee gap-8 items-center text-xl sm:text-2xl font-display italic">

            <div class="contents [html[data-mode='photo']_&]:hidden text-foreground/80">
                <div class="flex items-center gap-8 shrink-0">
                    <?php array_walk($skillsTech, static function (string $tech): void { ?>
                        <span><?= htmlspecialchars($tech) ?></span> <span class="text-accent text-xs">✦</span>
                    <?php }); ?>
                </div>
                <div class="flex items-center gap-8 shrink-0" aria-hidden="true">
                    <?php array_walk($skillsTech, static function (string $tech): void { ?>
                        <span><?= htmlspecialchars($tech) ?></span> <span class="text-accent text-xs">✦</span>
                    <?php }); ?>
                </div>
            </div>

            <div class="hidden [html[data-mode='photo']_&]:flex items-center gap-8 text-amber-400/90">
                <div class="flex items-center gap-8 shrink-0">
                    <span>Shooting Portrait</span> <span class="text-amber-400 text-xs">✦</span>
                    <span>Color Grading</span> <span class="text-amber-400 text-xs">✦</span>
                    <span>Lightroom</span> <span class="text-amber-400 text-xs">✦</span>
                    <span>Montage DaVinci</span> <span class="text-amber-400 text-xs">✦</span>
                    <span>Affinity by Canva</span> <span class="text-amber-400 text-xs">✦</span>
                    <span>Panasonic Lumix</span> <span class="text-amber-400 text-xs">✦</span>
                </div>
                <div class="flex items-center gap-8 shrink-0" aria-hidden="true">
                    <span>Shooting Portrait</span> <span class="text-amber-400 text-xs">✦</span>
                    <span>Color Grading</span> <span class="text-amber-400 text-xs">✦</span>
                    <span>Lightroom</span> <span class="text-amber-400 text-xs">✦</span>
                    <span>Montage DaVinci</span> <span class="text-amber-400 text-xs">✦</span>
                    <span>Affinity by Canva</span> <span class="text-amber-400 text-xs">✦</span>
                    <span>Panasonic Lumix</span> <span class="text-amber-400 text-xs">✦</span>
                </div>
            </div>

        </div>
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
                    <span class="px-3 py-1 rounded-md border border-border-strong bg-background-card/20 text-[11px] font-mono uppercase tracking-wider text-muted-foreground">
                        <?= htmlspecialchars($tool) ?>
                    </span>
                <?php }); ?>
            </div>

            <div class="hidden [html[data-mode='photo']_&]:flex flex-col gap-6">
                <div class="flex flex-wrap gap-2.5">
                    <span class="px-3.5 py-1.5 rounded-full border border-amber-400/30 bg-amber-950/20 text-xs font-body text-amber-200">Portrait &amp; Street Photography</span>
                    <span class="px-3.5 py-1.5 rounded-full border border-amber-400/30 bg-amber-950/20 text-xs font-body text-amber-200">Color Grading</span>
                    <span class="px-3.5 py-1.5 rounded-full border border-amber-400/30 bg-amber-950/20 text-xs font-body text-amber-200">Montage vidéo dynamique</span>
                    <span class="px-3.5 py-1.5 rounded-full border border-amber-400/30 bg-amber-950/20 text-xs font-body text-amber-200">Retouche photo avancée</span>
                    <span class="px-3.5 py-1.5 rounded-full border border-amber-400/30 bg-amber-950/20 text-xs font-body text-amber-200">Création de contenu réseaux</span>
                </div>

                <div class="flex flex-wrap gap-2 pt-2">
                    <span class="px-3 py-1 rounded-md border border-amber-400/20 bg-amber-950/10 text-[11px] font-mono uppercase tracking-wider text-amber-400/80">LIGHTROOM</span>
                    <span class="px-3 py-1 rounded-md border border-amber-400/20 bg-amber-950/10 text-[11px] font-mono uppercase tracking-wider text-amber-400/80">DAVINCI RESOLVE</span>
                    <span class="px-3 py-1 rounded-md border border-amber-400/20 bg-amber-950/10 text-[11px] font-mono uppercase tracking-wider text-amber-400/80">PHOTOSHOP</span>
                    <span class="px-3 py-1 rounded-md border border-amber-400/20 bg-amber-950/10 text-[11px] font-mono uppercase tracking-wider text-amber-400/80">AFFINITY BY CANVA</span>
                    <span class="px-3 py-1 rounded-md border border-amber-400/20 bg-amber-950/10 text-[11px] font-mono uppercase tracking-wider text-amber-400/80">BOÎTIER PANASONIC</span>
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
                        <div class="w-full h-px bg-border-strong relative">
                            <div class="absolute left-0 top-0 bottom-0 bg-accent [html[data-mode='photo']_&]:bg-amber-400"
                                 style="width: <?= $language['percent'] ?>%;"></div>
                        </div>
                    </div>
                <?php }); ?>

            </div>

        </div>

    </div>

</section>
