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
            <h1 class="text-5xl sm:text-6xl font-normal tracking-tight flex flex-col leading-none">
                <span class="text-foreground"><?= htmlspecialchars($firstName) ?></span>
                <span class="italic font-light text-accent mt-2"><?= htmlspecialchars($lastName) ?></span>
            </h1>

            <!-- 3. Description adaptative selon le mode -->
            <div class="text-muted-foreground text-base sm:text-lg leading-relaxed font-sans">
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

            <!-- 4. Localisation & Études -->
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
                <video autoplay loop muted playsinline
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

<?php require_once __DIR__ . '/partials/contact.php'; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>