<section id="post-traitement" class="mt-24 w-full hidden [html[data-mode='photo']_&]:flex flex-col gap-10"
         aria-labelledby="compare-title">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-12 items-end">
        <div class="lg:col-span-7 flex flex-col items-start gap-3">
            <span class="text-xs font-mono tracking-widest text-accent uppercase">
                04 bis — Post-traitement
            </span>
            <h2 id="compare-title"
                class="reveal-title text-3xl sm:text-4xl lg:text-5xl font-display italic font-light tracking-tight text-foreground">
                <span class="reveal-title__mask">
                    <span class="reveal-title__inner"><?= htmlspecialchars($compareTitle) ?></span>
                </span>
            </h2>
        </div>
        <div class="lg:col-span-5 flex flex-col gap-4">
            <p class="text-sm text-muted-foreground leading-relaxed"><?= htmlspecialchars($compareCaption) ?></p>
            <?php if ($compareTools !== []): ?>
                <div class="flex flex-wrap gap-2">
                    <?php array_walk($compareTools, static function (string $tool): void { ?>
                        <span class="px-3 py-1 rounded-full border border-border-strong text-xs font-mono uppercase tracking-wider text-muted-foreground">
                            <?= htmlspecialchars($tool) ?>
                        </span>
                    <?php }); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="compare grain" data-compare data-cursor="drag" data-cursor-label="Glisser">
        <img class="compare__img" src="<?= htmlspecialchars($compareAfter) ?>"
             alt="Photographie après étalonnage colorimétrique" width="1600" height="1200" loading="lazy" decoding="async" draggable="false">

        <div class="compare__before">
            <img class="compare__img<?= $isSimulated ? ' compare__img--simulated' : '' ?>"
                 src="<?= htmlspecialchars($compareBeforeSrc) ?>" width="1600" height="1200"
                 alt="<?= $isSimulated ? 'Aperçu simulé du fichier brut, avant étalonnage' : 'Photographie brute (RAW), avant étalonnage' ?>"
                 loading="lazy" decoding="async" draggable="false">
        </div>

        <span class="compare__tag compare__tag--before"><?= $isSimulated ? 'RAW · simulé' : 'RAW' ?></span>
        <span class="compare__tag compare__tag--after">Étalonné</span>

        <div class="compare__handle" role="slider" tabindex="0"
             aria-label="Comparer la photo brute et la photo étalonnée"
             aria-valuemin="0" aria-valuemax="100" aria-valuenow="50" aria-valuetext="50 % de photo brute">
            <span class="compare__knob" aria-hidden="true">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7l-5 5 5 5M15 7l5 5-5 5"/>
                </svg>
            </span>
        </div>
    </div>

    <?php if ($isSimulated): ?>
        <p class="text-xs font-mono uppercase tracking-wider text-muted-foreground">
            Aperçu : le rendu brut est simulé à partir de l'image finale.
        </p>
    <?php endif; ?>
</section>
