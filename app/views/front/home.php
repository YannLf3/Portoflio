<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main id="main" tabindex="-1">
    <div class="mt-16 flex flex-col md:flex-row md:items-center md:justify-between gap-12">

        <section class="flex flex-col items-start gap-8 max-w-2xl" aria-labelledby="hero-title">

            <div class="inline-flex items-center gap-3 px-4 py-2 rounded-full border border-border-strong bg-background-card/50 text-xs tracking-wider text-muted-foreground uppercase">
                <span class="w-2 h-2 rounded-full bg-accent" aria-hidden="true"></span>
                <span><?= htmlspecialchars($dispoText) ?></span>
            </div>

            <h1 id="hero-title" class="reveal-title reveal-title--short text-display-lg font-normal tracking-tight leading-none">
                <span class="reveal-title__mask">
                    <span class="reveal-title__inner flex flex-col">
                        <span class="text-foreground"><?= htmlspecialchars($firstName) ?></span>
                        <span class="italic font-light text-accent mt-2"><?= htmlspecialchars($lastName) ?></span>
                    </span>
                </span>
            </h1>

            <div class="text-muted-foreground text-base sm:text-lg leading-relaxed font-body">
                <div id="dev-intro" class="mode-dev-content flex flex-col gap-3">
                    <p><?= nl2br(htmlspecialchars($heroDev)) ?></p>
                </div>
                <div id="photo-intro" class="mode-photo-content flex flex-col gap-3">
                    <p><?= nl2br(htmlspecialchars($heroPhoto)) ?></p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="#contact" class="btn-glow" data-magnetic data-track="contact_click">
                    <span>Me contacter</span>
                    <svg class="btn-glow__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
                <a href="#projects" class="btn-glow btn-glow--ghost">
                    <span>Voir mes projets</span>
                </a>
                <?php if ($cvUrl !== null): ?>
                    <a href="<?= htmlspecialchars($cvUrl) ?>" download="<?= htmlspecialchars($cvFilename) ?>" type="application/pdf"
                       class="btn-glow btn-glow--ghost" data-track="cv_download">
                        <span>Télécharger mon CV<span class="sr-only"> (PDF)</span></span>
                    </a>
                <?php endif; ?>
            </div>

            <button type="button" data-mode-hint="photo"
                    class="mode-dev-content text-sm font-body text-muted-foreground underline underline-offset-4 decoration-border-strong hover:text-accent hover:decoration-accent py-1">
                Je fais aussi de la photo : découvrir mon univers photographe
            </button>
            <button type="button" data-mode-hint="dev"
                    class="mode-photo-content text-sm font-body text-muted-foreground underline underline-offset-4 decoration-border-strong hover:text-accent hover:decoration-accent py-1">
                Je développe aussi des sites : revenir à l'univers développeur
            </button>

            <div class="flex items-center gap-4 text-xs tracking-widest text-muted-foreground uppercase pt-2">
                <span><?= htmlspecialchars($location) ?></span>
                <span class="w-8 h-px bg-border-strong" aria-hidden="true"></span>
                <span><?= htmlspecialchars($status) ?></span>
            </div>

        </section>

        <div class="flex justify-center items-center w-full max-w-56 md:max-w-120 mx-auto md:mx-0 shrink-0 aspect-square relative md:mr-8 lg:mr-16">
            <?php if ($avatar !== null): ?>
                <img src="<?= htmlspecialchars($avatar) ?>" alt="Portrait de Yann Le Flohic" width="1200" height="1200"
                     class="w-full h-full object-contain pointer-events-none select-none">
            <?php else: ?>
                <video autoplay loop muted playsinline poster="/assets/before-video.webp" width="1280" height="720"
                       aria-hidden="true" data-motion-video
                       class="w-full h-full object-contain pointer-events-none select-none">
                    <source src="/assets/videos/animoji.mov" type="video/quicktime; codecs=hvc1">
                    <source src="/assets/videos/animoji.webm" type="video/webm">
                </video>
            <?php endif; ?>
        </div>

    </div>

    <?php require_once __DIR__ . '/partials/about.php'; ?>

    <?php require_once __DIR__ . '/partials/skills.php'; ?>

    <?php require_once __DIR__ . '/partials/projects.php'; ?>

    <?php require_once __DIR__ . '/partials/gallery.php'; ?>

    <?php require_once __DIR__ . '/partials/compare.php'; ?>

    <?php require_once __DIR__ . '/partials/contact.php'; ?>
</main>

<div id="mobile-cta" data-hidden="false"
     class="fixed inset-x-4 bottom-4 z-40 md:hidden rounded-full bg-background-2 transition-all duration-300 data-[hidden=true]:translate-y-24 data-[hidden=true]:opacity-0 data-[hidden=true]:pointer-events-none">
    <a href="#contact" class="btn-glow w-full" data-track="contact_click">
        <span>Me contacter</span>
    </a>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
