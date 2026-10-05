<?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <!-- Conteneur : texte à gauche, animoji/avatar à droite -->
    <div class="mt-16 flex flex-col md:flex-row md:items-center md:justify-between gap-12">

        <section class="flex flex-col items-start gap-8 max-w-2xl">

            <!-- 1. Badge de disponibilité -->
            <div class="inline-flex items-center gap-3 px-4 py-2 rounded-full border border-border-strong bg-background-card/50 backdrop-blur-md text-xs tracking-wider text-muted-foreground uppercase">
                <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
                <span><?= htmlspecialchars($content['dispo_text'] ?? 'Disponible pour un stage · 8-12 semaines') ?></span>
            </div>

            <!-- 2. Titre principal (Nom dynamique séparé Prénom / Nom) -->
            <?php
            $fullName = trim($content['hero_name'] ?? 'Yann Le Flohic');
            $nameParts = explode(' ', $fullName, 2);
            $firstName = $nameParts[0] ?? 'Yann';
            $lastName = $nameParts[1] ?? 'Le Flohic';
            ?>
            <!-- .reveal-title : masque (overflow) + glissement vers le haut + trait Lime (animations.js) -->
            <h1 class="reveal-title reveal-title--short text-5xl sm:text-6xl font-normal tracking-tight leading-none">
                <span class="reveal-title__mask">
                    <span class="reveal-title__inner flex flex-col">
                        <span class="text-foreground"><?= htmlspecialchars($firstName) ?></span>
                        <span class="italic font-light text-accent mt-2"><?= htmlspecialchars($lastName) ?></span>
                    </span>
                </span>
            </h1>

            <!-- 3. Description adaptative selon le mode -->
            <div class="text-muted-foreground text-base sm:text-lg leading-relaxed font-body">
                <!-- Version Dev -->
                <div id="dev-intro" class="mode-dev-content flex flex-col gap-3">
                    <p><?= nl2br(htmlspecialchars($content['hero_dev'] ?? "Développeur web full-stack — je conçois et code des interfaces sur-mesure, du front au back, jusqu'à l'hébergement.")) ?></p>
                    <p>Vous avez un projet en tête ? <a href="#contact" class="text-accent hover:underline">Contactez-moi
                            !</a></p>
                </div>
                <!-- Version Photo -->
                <div id="photo-intro" class="mode-photo-content flex flex-col gap-3">
                    <p><?= nl2br(htmlspecialchars($content['hero_photo'] ?? "Photographe — je capture la lumière, la matière et l'instant, entre portraits, rue et paysages cinématographiques.")) ?></p>
                    <p>Vous avez un projet en tête ? <a href="#contact" class="text-accent hover:underline">Contactez-moi
                            !</a></p>
                </div>
            </div>

            <!-- 4. Appels à l'action : boutons Glow (bordure au repos, halo Lime au survol) + effet magnétique -->
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

            <!-- 5. Localisation & Études -->
            <div class="flex items-center gap-4 text-xs tracking-widest text-muted-foreground uppercase pt-2">
                <span><?= htmlspecialchars($content['location'] ?? 'Châteauroux — FR') ?></span>
                <span class="w-8 h-px bg-border-strong"></span>
                <span><?= htmlspecialchars($content['status'] ?? '2° Année MMI') ?></span>
            </div>

        </section>

        <!-- Video Animoji / Avatar -->
        <div class="hidden md:flex justify-center items-center w-full max-w-120 shrink-0 aspect-square relative md:mr-8 lg:mr-16">
            <?php
            $avatar = $content['hero_avatar'] ?? '';
            // Affiche l'image uniquement si un fichier image (png/jpg/webp) est renseigné
            if (!empty($avatar) && preg_match('/\.(png|jpg|jpeg|webp)$/i', $avatar)):
                ?>
                <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar Yann Le Flohic"
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

    <!-- Inclusions des partials (ils piocheront aussi dans $content) -->
<?php require_once __DIR__ . '/partials/about.php'; ?>

<?php require_once __DIR__ . '/partials/skills.php'; ?>

<?php require_once __DIR__ . '/partials/projects.php'; ?>

<?php require_once __DIR__ . '/partials/gallery.php'; ?>

<?php require_once __DIR__ . '/partials/compare.php'; ?>

<?php require_once __DIR__ . '/partials/contact.php'; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>