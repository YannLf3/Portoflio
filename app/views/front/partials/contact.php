<section id="contact" class="section-divider mt-24 w-full flex flex-col gap-12">

    <div class="flex flex-col items-start gap-4">
        <span class="text-xs font-mono tracking-widest text-accent uppercase">
            05 — Travaillons ensemble
        </span>
        <h2 class="reveal-title text-4xl sm:text-5xl lg:text-6xl font-normal tracking-tight text-foreground leading-tight">
            <span class="reveal-title__mask">
                <span class="reveal-title__inner flex flex-col">
                    <span><?= htmlspecialchars($contactTitle) ?></span>
                    <span class="italic font-light text-accent mt-1"><?= htmlspecialchars($contactSubtitle) ?></span>
                </span>
            </span>
        </h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start pt-4">

        <div class="lg:col-span-7 flex flex-col divide-y divide-border-strong border-y">

            <?php array_walk($contacts, static function (array $contact): void { ?>
                <a href="<?= htmlspecialchars($contact['href']) ?>"
                        <?= $contact['isExternal'] ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
                   class="group flex items-center justify-between py-5 transition-colors">
                    <span class="text-xs font-mono tracking-widest text-muted-foreground uppercase">
                        <?= htmlspecialchars($contact['label']) ?>
                    </span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm sm:text-base font-body text-foreground group-hover:text-accent transition-colors">
                            <?= htmlspecialchars($contact['value']) ?>
                        </span>
                        <svg class="w-4 h-4 text-muted-foreground transition-transform duration-300 group-hover:-rotate-45 group-hover:text-accent"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M7 17L17 7M17 7H7M17 7V17"/>
                        </svg>
                    </div>
                </a>
            <?php }); ?>

        </div>

        <div class="lg:col-span-5 flex flex-col gap-4">
            <span class="text-xs font-mono tracking-widest text-muted-foreground uppercase">
                J'AIME
            </span>
            <div class="flex flex-wrap gap-2.5">
                <?php array_walk($interests, static function (string $interest): void { ?>
                    <span class="px-4 py-2 rounded-full border border-border-strong bg-background-card/20 text-xs text-muted-foreground">
                        <?= htmlspecialchars($interest) ?>
                    </span>
                <?php }); ?>
            </div>
        </div>

    </div>

</section>
