<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main>
    <div class="mt-16 flex flex-col md:flex-row md:items-center md:justify-between gap-12">

        <section class="flex flex-col items-start gap-8 max-w-2xl">

            <div class="inline-flex items-center gap-3 px-4 py-2 rounded-full border border-border-strong bg-background-card/50 backdrop-blur-md text-xs tracking-wider text-muted-foreground uppercase">
                <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
                <span><?= htmlspecialchars($dispoText) ?></span>
            </div>

            <h1 class="reveal-title reveal-title--short text-5xl sm:text-6xl font-normal tracking-tight leading-none">
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
                    <p>Vous avez un projet en tête ? <a href="#contact" class="text-accent hover:underline">Contactez-moi
                            !</a></p>
                </div>
                <div id="photo-intro" class="mode-photo-content flex flex-col gap-3">
                    <p><?= nl2br(htmlspecialchars($heroPhoto)) ?></p>
                    <p>Vous avez un projet en tête ? <a href="#contact" class="text-accent hover:underline">Contactez-moi
                            !</a></p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="#projects" class="btn-glow" data-magnetic>
                    <span>Voir mes projets</span>
                    <svg class="btn-glow__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
                <a href="#contact" class="btn-glow" data-magnetic>
                    <span>Me contacter</span>
                </a>
            </div>

            <div class="flex items-center gap-4 text-xs tracking-widest text-muted-foreground uppercase pt-2">
                <span><?= htmlspecialchars($location) ?></span>
                <span class="w-8 h-px bg-border-strong"></span>
                <span><?= htmlspecialchars($status) ?></span>
            </div>

        </section>

        <div class="hidden md:flex justify-center items-center w-full max-w-120 shrink-0 aspect-square relative md:mr-8 lg:mr-16">
            <?php if ($avatar !== null): ?>
                <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar Yann Le Flohic" width="1200" height="1200"
                     class="w-full h-full object-contain pointer-events-none select-none">
            <?php else: ?>
                <video autoplay loop muted playsinline poster="/assets/before-video.webp" width="1280" height="720"
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
