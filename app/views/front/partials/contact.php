<section id="contact" aria-labelledby="contact-title" class="section-divider mt-24 w-full flex flex-col gap-12">

    <div class="flex flex-col items-start gap-4">
        <span class="text-xs font-mono tracking-widest text-accent uppercase">
            05 — Travaillons ensemble
        </span>
        <h2 id="contact-title" class="reveal-title text-4xl sm:text-5xl lg:text-6xl font-normal tracking-tight text-foreground leading-tight">
            <span class="reveal-title__mask">
                <span class="reveal-title__inner flex flex-col">
                    <span><?= htmlspecialchars($contactTitle) ?></span>
                    <span class="italic font-light text-accent mt-1"><?= htmlspecialchars($contactSubtitle) ?></span>
                </span>
            </span>
        </h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start pt-4">

        <div class="lg:col-span-7 flex flex-col gap-6">

            <form id="contact-form" action="/api/contact" method="post" novalidate class="flex flex-col gap-6">
                <?= Csrf::field() ?>

                <p class="text-sm text-muted-foreground">
                    Décrivez votre projet en quelques lignes. Je vous réponds par e-mail.
                    Les champs marqués d'un astérisque sont obligatoires.
                </p>

                <div class="flex flex-col gap-2">
                    <label for="contact-name" class="text-sm text-foreground">Nom <span aria-hidden="true">*</span></label>
                    <input id="contact-name" name="name" type="text" required autocomplete="name" maxlength="100"
                           aria-describedby="contact-name-error"
                           class="w-full min-h-12 px-4 py-3 rounded-xl border border-border-strong bg-background-card/30 text-foreground text-base aria-[invalid=true]:border-(--cli-error)">
                    <p id="contact-name-error" class="text-sm text-(--cli-error)" hidden></p>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="contact-email" class="text-sm text-foreground">Adresse e-mail <span aria-hidden="true">*</span></label>
                    <input id="contact-email" name="email" type="email" required autocomplete="email" maxlength="150"
                           aria-describedby="contact-email-error"
                           class="w-full min-h-12 px-4 py-3 rounded-xl border border-border-strong bg-background-card/30 text-foreground text-base aria-[invalid=true]:border-(--cli-error)">
                    <p id="contact-email-error" class="text-sm text-(--cli-error)" hidden></p>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="contact-message" class="text-sm text-foreground">Votre message <span aria-hidden="true">*</span></label>
                    <textarea id="contact-message" name="message" rows="6" required maxlength="3000"
                              aria-describedby="contact-message-error"
                              class="w-full px-4 py-3 rounded-xl border border-border-strong bg-background-card/30 text-foreground text-base resize-y aria-[invalid=true]:border-(--cli-error)"></textarea>
                    <p id="contact-message-error" class="text-sm text-(--cli-error)" hidden></p>
                </div>

                <div class="absolute left-[-9999px] w-px h-px overflow-hidden" aria-hidden="true">
                    <label for="contact-website">Ne pas remplir ce champ</label>
                    <input id="contact-website" name="website" type="text" tabindex="-1" autocomplete="off">
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <button type="submit" id="contact-submit" class="btn-glow" data-magnetic>
                        <span data-contact-label>Envoyer le message</span>
                    </button>
                    <p id="contact-status" role="status" class="text-sm text-muted-foreground"></p>
                </div>
            </form>

            <div id="contact-success" tabindex="-1" hidden
                 class="flex flex-col gap-3 p-6 rounded-2xl border border-accent/40 bg-accent-soft">
                <h3 class="text-xl font-medium text-foreground">Message envoyé</h3>
                <p class="text-sm text-muted-foreground">Merci, je vous réponds par e-mail dès que possible.</p>
                <button type="button" id="contact-reset" class="self-start text-sm text-accent underline underline-offset-4 py-1">
                    Envoyer un autre message
                </button>
            </div>

        </div>

        <div class="lg:col-span-5 flex flex-col gap-10">

            <div class="flex flex-col divide-y divide-border-strong border-y border-border-strong">

                <?php array_walk($contacts, static function (array $contact): void { ?>
                    <a href="<?= htmlspecialchars($contact['href']) ?>" data-track="<?= htmlspecialchars($contact['track']) ?>"
                            <?= $contact['isExternal'] ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
                       class="group flex items-center justify-between gap-4 py-5 transition-colors">
                        <span class="text-xs font-mono tracking-widest text-muted-foreground uppercase">
                            <?= htmlspecialchars($contact['label']) ?>
                        </span>
                        <span class="text-sm sm:text-base font-body text-foreground group-hover:text-accent transition-colors text-right break-all">
                            <?= htmlspecialchars($contact['value']) ?>
                            <?php if ($contact['isExternal']): ?>
                                <span class="sr-only">(s'ouvre dans un nouvel onglet)</span>
                            <?php endif; ?>
                        </span>
                    </a>
                <?php }); ?>

            </div>

            <div class="flex flex-col gap-3">
                <h3 class="text-xs font-mono tracking-widest text-muted-foreground uppercase">J'aime</h3>
                <p class="text-sm text-muted-foreground leading-relaxed">
                    <?= htmlspecialchars(implode(', ', $interests)) ?>
                </p>
            </div>

        </div>

    </div>

</section>

<script src="/js/contact.js?v=20261025" defer></script>
