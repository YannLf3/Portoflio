<section id="gallery" class="mt-24 flex flex-col gap-12 w-full">

    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6">
        <div class="flex flex-col items-start gap-3">
            <span class="text-xs font-mono tracking-widest text-accent uppercase">
                04 — L'objectif
            </span>
            <h2 class="reveal-title text-3xl sm:text-4xl lg:text-5xl font-display italic font-light tracking-tight text-foreground">
                <span class="reveal-title__mask">
                    <span class="reveal-title__inner">Regarder autrement</span>
                </span>
            </h2>
        </div>

        <p class="text-xs sm:text-sm text-muted-foreground max-w-xs leading-relaxed">
            <span class="mode-dev-content">Bascule en mode Photographe pour révéler la sélection en couleur.</span>
            <span class="mode-photo-content">Une sélection de photographies — portraits et paysages. Cliquez pour agrandir.</span>
        </p>
    </div>

    <div class="gallery" id="gallery-grid">
        <?php array_walk($photos, static function (array $photo): void { ?>
            <figure class="gallery__item gallery__item--<?= $photo['format'] ?>">
                <button type="button" class="gallery__media grain"
                        data-lightbox-index="<?= $photo['index'] ?>"
                        data-src="<?= htmlspecialchars($photo['src']) ?>"
                        data-alt="<?= htmlspecialchars($photo['alt']) ?>"
                        data-legend="<?= htmlspecialchars($photo['legend']) ?>"
                        data-cursor="view" data-cursor-label="Voir"
                        aria-label="Agrandir la photo : <?= htmlspecialchars($photo['alt']) ?>"
                        style="view-transition-name: gallery-photo-<?= $photo['index'] ?>; view-transition-class: gallery-photo;">
                    <img class="gallery__img" src="<?= htmlspecialchars($photo['src']) ?>"
                         alt="<?= htmlspecialchars($photo['alt']) ?>" width="<?= $photo['width'] ?>" height="<?= $photo['height'] ?>"
                         loading="lazy" decoding="async">
                </button>
                <?php if ($photo['legend'] !== ''): ?>
                    <figcaption class="gallery__caption">
                        <span class="gallery__index"><?= $photo['number'] ?></span>
                        <span class="gallery__legend"><?= htmlspecialchars($photo['legend']) ?></span>
                    </figcaption>
                <?php endif; ?>
            </figure>
        <?php }); ?>
    </div>

</section>

<dialog id="lightbox" class="lightbox" closedby="any" aria-label="Visionneuse photo">
    <figure class="lightbox__figure">
        <img class="lightbox__img" data-lightbox="img" alt="">
        <figcaption class="lightbox__caption">
            <span class="lightbox__counter" data-lightbox="counter"></span>
            <span class="lightbox__legend" data-lightbox="legend"></span>
        </figcaption>
    </figure>

    <button type="button" class="lightbox__btn lightbox__btn--prev" data-lightbox-action="prev" aria-label="Photo précédente">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>
    <button type="button" class="lightbox__btn lightbox__btn--next" data-lightbox-action="next" aria-label="Photo suivante">
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
