<section class="mt-24 flex flex-col gap-12 w-full">

    <!-- En-tête de section -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6">
        <div class="flex flex-col items-start gap-3">
            <span class="text-xs font-mono tracking-widest text-accent uppercase">
                04 — L'objectif
            </span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-serif italic font-light tracking-tight text-foreground">
                Regarder autrement
            </h2>
        </div>

        <!-- Description adaptative selon le mode -->
        <p class="text-xs sm:text-sm text-muted-foreground max-w-xs leading-relaxed">
            <span class="mode-dev-content">Bascule en mode Photographe pour révéler la sélection.</span>
            <span class="mode-photo-content">Une sélection de photographies — portraits et paysages.</span>
        </p>
    </div>

    <!-- Galerie Bento (Dynamique BDD) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">

        <?php
        $photosList = $content['photos'] ?? [
                [
                        'url' => '/assets/images/gallery/gallery-6.webp',
                        'legend' => 'FLEURS ROSES · LUMIÈRE NATURELLE',
                        'alt' => 'Fleurs du jardin roses',
                        'format' => 'portrait'
                ],
                [
                        'url' => '/assets/images/gallery/gallery-1.webp',
                        'legend' => 'PAYSAGE · MARMOTTES',
                        'alt' => 'Paysage Marmottes',
                        'format' => 'landscape'
                ],
                [
                        'url' => '/assets/images/gallery/gallery-2.webp',
                        'legend' => 'PAYSAGE · MONTAGNE',
                        'alt' => 'Montagne, dans les dolomites',
                        'format' => 'landscape'
                ],
                [
                        'url' => '/assets/images/gallery/gallery-5.webp',
                        'legend' => "PORTRAIT · LUMIÈRE D'AUTOMNE",
                        'alt' => 'Expression portrait',
                        'format' => 'landscape'
                ],
                [
                        'url' => '/assets/images/gallery/gallery-3.webp',
                        'legend' => 'PAYSAGE · MONTAGNE',
                        'alt' => 'Montagne, dans les dolomites',
                        'format' => 'landscape'
                ]
        ];

        if (!empty($photosList)):
            foreach ($photosList as $photo):
                // Détermination de la source de l'image (URL directe ou via Unsplash ID)
                $imgSrc = !empty($photo['url'])
                        ? $photo['url']
                        : (!empty($photo['unsplash_id']) ? "https://images.unsplash.com/photo-{$photo['unsplash_id']}?auto=format&fit=crop&w=1200&q=80" : '/assets/images/gallery/gallery-1.webp');

                $isPortrait = ($photo['format'] ?? '') === 'portrait';
                $aspectClass = $isPortrait ? 'aspect-3/4 lg:row-span-2' : 'aspect-16/10';
                ?>
                <div class="group relative overflow-hidden rounded-2xl bg-background-card <?= $aspectClass ?>">
                    <img src="<?= htmlspecialchars($imgSrc) ?>"
                         alt="<?= htmlspecialchars($photo['alt'] ?? 'Photographie Yann Le Flohic') ?>"
                         class="h-full w-full object-cover transition-all duration-700 ease-out group-hover:scale-105 grayscale [html[data-mode='photo']_&]:grayscale-0"
                         loading="lazy">

                    <!-- Overlay dégradé + Texte d'infos au survol -->
                    <?php if (!empty($photo['legend'])): ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-background/90 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 flex items-end p-6">
                        <span class="text-[11px] font-mono tracking-widest uppercase text-white/90">
                            <?= htmlspecialchars($photo['legend']) ?>
                        </span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php
            endforeach;
        endif;
        ?>

    </div>

</section>