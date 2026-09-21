<section id="contact" class="section-divider mt-24 w-full flex flex-col gap-12">

    <!-- En-tête de section -->
    <div class="flex flex-col items-start gap-4">
        <span class="text-xs font-mono tracking-widest text-accent uppercase">
            05 — Travaillons ensemble
        </span>
        <h2 class="text-4xl sm:text-5xl lg:text-6xl font-normal tracking-tight text-foreground leading-tight flex flex-col">
            <span><?= htmlspecialchars($content['contact_title'] ?? 'Un projet web ou photo ?') ?></span>
            <span class="italic font-light text-accent mt-1"><?= htmlspecialchars($content['contact_subtitle'] ?? 'Parlons-en.') ?></span>
        </h2>
    </div>

    <!-- Layout principal : Liens de contact à gauche, Tags d'intérêts à droite -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start pt-4">

        <!-- Colonne Gauche : Liste des coordonnées / réseaux (Dynamique BDD) -->
        <div class="lg:col-span-7 flex flex-col divide-y divide-border-strong border-y">

            <?php
            $contactsList = $content['contacts'] ?? [
                    ['label' => 'EMAIL', 'value' => 'yann.lfhc@icloud.com', 'href' => 'mailto:yann.lfhc@icloud.com'],
                    ['label' => 'TÉLÉPHONE', 'value' => '+33 6 46 13 31 35', 'href' => 'tel:+33646133135'],
                    ['label' => 'GITHUB', 'value' => 'github.com/YannLf3', 'href' => 'https://github.com/YannLf3'],
                    ['label' => 'LINKEDIN', 'value' => 'in/yann-le-flohic', 'href' => 'https://www.linkedin.com/in/yannlf3/'],
                    ['label' => 'MALT', 'value' => 'Mon lien Malt', 'href' => 'https://www.malt.fr/profile/yannleflohic1']
            ];

            if (!empty($contactsList)):
                foreach ($contactsList as $link):
                    $href = $link['href'] ?? '#';
                    $isExternal = str_starts_with($href, 'http');
                    ?>
                    <a href="<?= htmlspecialchars($href) ?>"
                            <?= $isExternal ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
                       class="group flex items-center justify-between py-5 transition-colors">
                    <span class="text-xs font-mono tracking-widest text-muted-foreground uppercase">
                        <?= htmlspecialchars($link['label'] ?? 'CONTACT') ?>
                    </span>
                        <div class="flex items-center gap-2">
                        <span class="text-sm sm:text-base font-sans text-foreground group-hover:text-accent transition-colors">
                            <?= htmlspecialchars($link['value'] ?? '') ?>
                        </span>
                            <svg class="w-4 h-4 text-muted-foreground transition-transform duration-300 group-hover:-rotate-45 group-hover:text-accent"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M7 17L17 7M17 7H7M17 7V17"/>
                            </svg>
                        </div>
                    </a>
                <?php
                endforeach;
            endif;
            ?>

        </div>

        <!-- Colonne Droite : Bloc "J'aime" (Dynamique BDD) -->
        <div class="lg:col-span-5 flex flex-col gap-4">
            <span class="text-xs font-mono tracking-widest text-muted-foreground uppercase">
                J'AIME
            </span>
            <div class="flex flex-wrap gap-2.5">
                <?php
                $interestsRaw = $content['interests'] ?? 'Open Source, Développement web, Photographie, Gaming, Badminton, Apple';
                $interestsTags = array_map('trim', explode(',', $interestsRaw));

                foreach ($interestsTags as $tag):
                    if (!empty($tag)):
                        ?>
                        <span class="px-4 py-2 rounded-full border border-border-strong bg-background-card/20 text-xs text-muted-foreground">
                        <?= htmlspecialchars($tag) ?>
                    </span>
                    <?php
                    endif;
                endforeach;
                ?>
            </div>
        </div>

    </div>

</section>