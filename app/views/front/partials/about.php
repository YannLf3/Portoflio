<section id="about" class="mt-24 w-full grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-start">

    <!-- Colonne Gauche : Titre et Description -->
    <div class="flex flex-col items-start gap-6 lg:sticky lg:top-24">
        <!-- Tag section -->
        <span class="text-xs font-mono tracking-widest text-accent uppercase [html[data-mode='photo']_&]:text-amber-400">
            01 — Le Parcours
        </span>

        <!-- Titre principal adaptatif selon le mode -->
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-serif font-normal tracking-tight text-foreground leading-tight">
            <span class="[html[data-mode='photo']_&]:hidden">Entre code et image, j'essaye de toujours faire les choses avec rigueur.</span>
            <span class="hidden [html[data-mode='photo']_&]:inline italic text-amber-200">Capturer l'instant, travailler la lumière et composer des images percutantes.</span>
        </h2>

        <!-- Description dynamique depuis le Back-Office -->
        <p class="text-muted-foreground text-sm sm:text-base leading-relaxed max-w-lg">
            <span class="[html[data-mode='photo']_&]:hidden">
                <?= nl2br(htmlspecialchars($content['parcours_intro'] ?? 'Étudiant en deuxième année de BUT MMI et développeur freelance, je navigue entre le développement web sur-mesure et la photographie.')) ?>
            </span>
            <span class="hidden [html[data-mode='photo']_&]:inline">
                Passionné par l'image sous toutes ses formes, je combine photographie de terrain, étalonnage vidéo sur DaVinci Resolve et création visuelle pour des projets créatifs.
            </span>
        </p>
    </div>

    <!-- Colonne Droite : Timeline du parcours (Dynamique BDD) -->
    <div class="relative pl-6 sm:pl-8 border-l border-border-strong flex flex-col gap-10">

        <?php
        $parcoursList = $content['parcours'] ?? [
                [
                        'period' => '2025 — ACTUEL',
                        'type' => 'FORMATION',
                        'title' => 'BUT Métiers du Multimédia et de l\'Internet (MMI)',
                        'detail' => 'Parcours Développement Web et Dispositifs Interactifs · IUT du Limousin, Limoges'
                ],
                [
                        'period' => '2025 — ACTUEL',
                        'type' => 'EXPÉRIENCE',
                        'title' => 'Freelance développeur & création numérique',
                        'detail' => 'Développement web front & back, design d\'interfaces, hébergement sur-mesure'
                ],
                [
                        'period' => '2022 — 2025',
                        'type' => 'FORMATION',
                        'title' => 'Baccalauréat général — Section bi-nationale Bachibac',
                        'detail' => 'BAC français et espagnol, mention bien · Lycée, Châteauroux'
                ]
        ];

        if (!empty($parcoursList)):
            foreach ($parcoursList as $item):
                ?>
                <div class="relative flex flex-col gap-2">
                    <!-- Puce lumineuse ancrée sur la ligne -->
                    <span class="absolute -left-7.75 sm:-left-9.75 top-1 w-3 h-3 rounded-full bg-background border-2 border-accent [html[data-mode='photo']_&]:border-amber-400"></span>

                    <!-- Meta Date & Badge -->
                    <div class="flex items-center gap-3">
                    <span class="text-xs font-mono text-accent tracking-wider [html[data-mode='photo']_&]:text-amber-400 uppercase">
                        <?= htmlspecialchars($item['period'] ?? '') ?>
                    </span>
                        <span class="px-2 py-0.5 rounded-full border border-border-strong text-[10px] font-mono uppercase tracking-wider text-muted-foreground bg-background-card/40">
                        <?= htmlspecialchars($item['type'] ?? 'FORMATION') ?>
                    </span>
                    </div>

                    <!-- Titre & Détails : Version DEV (Dynamique BDD) -->
                    <div class="[html[data-mode='photo']_&]:hidden">
                        <h3 class="text-base sm:text-lg font-medium text-foreground">
                            <?= htmlspecialchars($item['title'] ?? '') ?>
                        </h3>
                        <p class="text-xs sm:text-sm text-muted-foreground">
                            <?= htmlspecialchars($item['detail'] ?? '') ?>
                        </p>
                    </div>

                    <!-- Titre & Détails : Version PHOTO -->
                    <div class="hidden [html[data-mode='photo']_&]:block">
                        <h3 class="text-base sm:text-lg font-medium text-amber-200 font-serif italic">
                            <?= htmlspecialchars($item['title'] ?? '') ?>
                        </h3>
                        <p class="text-xs sm:text-sm text-muted-foreground">
                            <?= htmlspecialchars($item['detail'] ?? '') ?>
                        </p>
                    </div>
                </div>
            <?php
            endforeach;
        endif;
        ?>

    </div>

</section>