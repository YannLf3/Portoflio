<?php
/*
 * Galerie « Regarder autrement » (photos gérées dans le back-office, onglet Galerie).
 *
 * - Mode Dev   : grille bento en noir & blanc, légende en surimpression au survol.
 * - Mode Photo : mise en page magazine asymétrique, formats cinéma, grain argentique,
 *                zoom d'objectif, légende éditoriale (composant CSS .gallery, input.css § 10).
 * - Chaque photo ouvre la visionneuse plein écran (#lightbox, public/js/photo-mode.js).
 * - view-transition-name unique par photo : lors de la bascule Dev ↔ Photo, chaque image
 *   se déplace d'une mise en page à l'autre (theme-toggle.js).
 *
 * Formats acceptés (champ « format » du back-office) :
 *   portrait (3:4 Dev / 4:5 Photo) · landscape (16:10 Dev / 16:9 Photo) · panorama (16:10 Dev / 21:9 Photo)
 */
$photosList = $content['photos'] ?? [
        ['url' => '/assets/images/gallery/gallery-6.webp', 'legend' => 'FLEURS ROSES · LUMIÈRE NATURELLE', 'alt' => 'Fleurs du jardin roses', 'format' => 'portrait'],
        ['url' => '/assets/images/gallery/gallery-1.webp', 'legend' => 'PAYSAGE · MARMOTTES', 'alt' => 'Paysage Marmottes', 'format' => 'landscape'],
        ['url' => '/assets/images/gallery/gallery-2.webp', 'legend' => 'PAYSAGE · MONTAGNE', 'alt' => 'Montagne, dans les dolomites', 'format' => 'landscape'],
        ['url' => '/assets/images/gallery/gallery-5.webp', 'legend' => "PORTRAIT · LUMIÈRE D'AUTOMNE", 'alt' => 'Expression portrait', 'format' => 'landscape'],
        ['url' => '/assets/images/gallery/gallery-3.webp', 'legend' => 'PAYSAGE · MONTAGNE', 'alt' => 'Montagne, dans les dolomites', 'format' => 'landscape'],
];

// Liste blanche des formats : une valeur inconnue retombe sur « landscape »
$allowedFormats = ['portrait', 'landscape', 'panorama'];
?>
<section id="gallery" class="mt-24 flex flex-col gap-12 w-full">

    <!-- En-tête de section -->
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

        <!-- Description adaptative selon le mode -->
        <p class="text-xs sm:text-sm text-muted-foreground max-w-xs leading-relaxed">
            <span class="mode-dev-content">Bascule en mode Photographe pour révéler la sélection en couleur.</span>
            <span class="mode-photo-content">Une sélection de photographies — portraits et paysages. Cliquez pour agrandir.</span>
        </p>
    </div>

    <!-- Galerie (Dynamique BDD) : une seule délégation d'événements sur ce conteneur (photo-mode.js) -->
    <div class="gallery" id="gallery-grid">
        <?php
        if (!empty($photosList) && is_array($photosList)):
            foreach (array_values($photosList) as $index => $photo):
                if (!is_array($photo)) {
                    continue;
                }
                // Source : URL directe, sinon identifiant Unsplash, sinon image par défaut
                $imgSrc = !empty($photo['url'])
                        ? $photo['url']
                        : (!empty($photo['unsplash_id']) ? "https://images.unsplash.com/photo-{$photo['unsplash_id']}?auto=format&fit=crop&w=1600&q=80" : '/assets/images/gallery/gallery-1.webp');
                $format = in_array($photo['format'] ?? '', $allowedFormats, true) ? $photo['format'] : 'landscape';
                $alt = $photo['alt'] ?? 'Photographie Yann Le Flohic';
                $legend = $photo['legend'] ?? '';
                $number = sprintf('N° %02d', $index + 1);
                ?>
                <figure class="gallery__item gallery__item--<?= $format ?>">
                    <!-- Bouton = zone cliquable accessible (clavier) ; data-cursor : grand cercle « Voir » en mode Photo -->
                    <button type="button" class="gallery__media grain"
                            data-lightbox-index="<?= $index ?>"
                            data-src="<?= htmlspecialchars($imgSrc) ?>"
                            data-alt="<?= htmlspecialchars($alt) ?>"
                            data-legend="<?= htmlspecialchars($legend) ?>"
                            data-cursor="view" data-cursor-label="Voir"
                            aria-label="Agrandir la photo : <?= htmlspecialchars($alt) ?>"
                            style="view-transition-name: gallery-photo-<?= $index ?>; view-transition-class: gallery-photo;">
                        <img class="gallery__img" src="<?= htmlspecialchars($imgSrc) ?>"
                             alt="<?= htmlspecialchars($alt) ?>" loading="lazy" decoding="async">
                    </button>
                    <?php if ($legend !== ''): ?>
                        <figcaption class="gallery__caption">
                            <span class="gallery__index"><?= $number ?></span>
                            <span class="gallery__legend"><?= htmlspecialchars($legend) ?></span>
                        </figcaption>
                    <?php endif; ?>
                </figure>
            <?php
            endforeach;
        endif;
        ?>
    </div>

</section>

<!-- Visionneuse plein écran (photo-mode.js). closedby="any" : Échap / geste retour ; clic hors photo géré en JS -->
<dialog id="lightbox" class="lightbox" closedby="any" aria-label="Visionneuse photo">
    <figure class="lightbox__figure">
        <img class="lightbox__img" data-lightbox="img" src="" alt="">
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
