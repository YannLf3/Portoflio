<section id="about" class="mt-24 w-full grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-start">

    <div class="flex flex-col items-start gap-6 lg:sticky lg:top-24">
        <span class="text-xs font-mono tracking-widest text-accent uppercase [html[data-mode='photo']_&]:text-amber-400">
            01 — Le Parcours
        </span>

        <h2 class="reveal-title text-3xl sm:text-4xl lg:text-5xl font-display font-normal tracking-tight text-foreground leading-tight">
            <span class="reveal-title__mask">
                <span class="reveal-title__inner">
                    <span class="[html[data-mode='photo']_&]:hidden">Entre code et image, j'essaye de toujours faire les choses avec rigueur.</span>
                    <span class="hidden [html[data-mode='photo']_&]:inline italic text-amber-200">Capturer l'instant, travailler la lumière et composer des images percutantes.</span>
                </span>
            </span>
        </h2>

        <p class="text-muted-foreground text-sm sm:text-base leading-relaxed max-w-lg">
            <span class="[html[data-mode='photo']_&]:hidden">
                <?= nl2br(htmlspecialchars($parcoursIntro)) ?>
            </span>
            <span class="hidden [html[data-mode='photo']_&]:inline">
                Passionné par l'image sous toutes ses formes, je combine photographie de terrain, étalonnage vidéo sur DaVinci Resolve et création visuelle pour des projets créatifs.
            </span>
        </p>
    </div>

    <div class="relative pl-6 sm:pl-8 border-l border-border-strong flex flex-col gap-10">

        <?php array_walk($parcours, static function (array $step): void { ?>
            <div class="relative flex flex-col gap-2">
                <span class="absolute -left-7.75 sm:-left-9.75 top-1 w-3 h-3 rounded-full bg-background border-2 border-accent [html[data-mode='photo']_&]:border-amber-400"></span>

                <div class="flex items-center gap-3">
                    <span class="text-xs font-mono text-accent tracking-wider [html[data-mode='photo']_&]:text-amber-400 uppercase">
                        <?= htmlspecialchars($step['period']) ?>
                    </span>
                    <span class="px-2 py-0.5 rounded-full border border-border-strong text-[10px] font-mono uppercase tracking-wider text-muted-foreground bg-background-card/40">
                        <?= htmlspecialchars($step['type']) ?>
                    </span>
                </div>

                <div class="[html[data-mode='photo']_&]:hidden">
                    <h3 class="text-base sm:text-lg font-medium text-foreground">
                        <?= htmlspecialchars($step['title']) ?>
                    </h3>
                    <p class="text-xs sm:text-sm text-muted-foreground">
                        <?= htmlspecialchars($step['detail']) ?>
                    </p>
                </div>

                <div class="hidden [html[data-mode='photo']_&]:block">
                    <h3 class="text-base sm:text-lg font-medium text-amber-200 font-display italic">
                        <?= htmlspecialchars($step['title']) ?>
                    </h3>
                    <p class="text-xs sm:text-sm text-muted-foreground">
                        <?= htmlspecialchars($step['detail']) ?>
                    </p>
                </div>
            </div>
        <?php }); ?>

    </div>

</section>
