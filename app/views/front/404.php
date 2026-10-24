<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main id="main" tabindex="-1" class="w-full min-h-screen px-6 sm:px-12 pt-32 pb-24 flex justify-center">
    <div class="w-full max-w-3xl flex flex-col items-start gap-8">

        <span class="text-xs font-mono tracking-widest text-accent uppercase">
            Erreur 404
        </span>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-normal tracking-tight text-foreground leading-tight">
            Page <span class="italic font-light text-accent">introuvable</span>
        </h1>

        <p class="text-muted-foreground text-base sm:text-lg leading-relaxed max-w-xl">
            La page que vous cherchez n'existe pas ou a été déplacée. Retournez à l'accueil, découvrez mes projets
            ou écrivez-moi si vous pensiez trouver autre chose ici.
        </p>

        <div class="flex flex-wrap items-center gap-3">
            <a href="/" class="btn-glow" data-magnetic>
                <span>Retour à l'accueil</span>
            </a>
            <a href="/#projects" class="btn-glow btn-glow--ghost">
                <span>Voir mes projets</span>
            </a>
            <a href="/#contact" class="btn-glow btn-glow--ghost">
                <span>Me contacter</span>
            </a>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
