<?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <section class="mt-16 flex flex-col items-start gap-8 max-w-2xl">

        <!-- 1. Badge de disponibilité (Stage) -->
        <div class="inline-flex items-center gap-3 px-4 py-2 rounded-full border border-border-strong bg-background-card/50 backdrop-blur-md text-xs tracking-wider text-muted-foreground uppercase">
            <!-- Petit point lumineux (prend la couleur de l'accent grâce à bg-accent) -->
            <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
            <span>Disponible pour un stage · 8-12 semaines</span>
        </div>

        <!-- 2. Titre principal (Yann Le Flohic) -->
        <h1 class="text-5xl sm:text-6xl font-normal tracking-tight flex flex-col leading-none">
            <span class="text-foreground">Yann</span>
            <!-- Le nom de famille passe en italique et adopte la couleur d'accent -->
            <span class="italic font-light text-accent mt-2">Le Flohic</span>
        </h1>

        <!-- 3. Description adaptative selon le mode -->
        <p class="text-muted-foreground text-base sm:text-lg leading-relaxed font-sans">
            <!-- Version Dev (affichée par défaut ou gérée en PHP/CSS) -->
            <span id="dev-intro" class="mode-dev-content">
            Développeur web full-stack — je conçois et code des interfaces sur-mesure, du front au back, jusqu'à l'hébergement.
        </span>
            <!-- Version Photo -->
            <span id="photo-intro" class="mode-photo-content">
            Photographe — je capture la lumière, la matière et l'instant, entre portraits, rue et paysages cinématographiques.
        </span>
        </p>

        <!-- 4. Informations de localisation / Études -->
        <div class="flex items-center gap-4 text-xs tracking-widest text-muted-foreground uppercase pt-2">
            <span>Châteauroux — FR</span>
            <span class="w-8 h-px bg-border-strong"></span>
            <span>2° Année MMI</span>
        </div>

    </section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>