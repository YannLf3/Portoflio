<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main id="main" tabindex="-1" class="w-full min-h-screen px-6 sm:px-12 pt-32 pb-24 flex justify-center">
    <div class="w-full max-w-3xl flex flex-col items-start gap-8">

        <span class="text-xs font-mono tracking-widest text-accent uppercase">
            Erreur 500
        </span>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-normal tracking-tight text-foreground leading-tight">
            Une erreur <span class="italic font-light text-accent">technique</span> est survenue
        </h1>

        <p class="text-muted-foreground text-base sm:text-lg leading-relaxed max-w-xl">
            Le site rencontre un problème de mon côté, rien de ce que vous avez fait ne l'a provoqué. Réessayez dans
            quelques instants, ou écrivez-moi directement par e-mail si le problème persiste.
        </p>

        <div class="flex flex-wrap items-center gap-3">
            <a href="/" class="btn-glow" data-magnetic>
                <span>Retour à l'accueil</span>
            </a>
            <a href="mailto:yann.lfhc@icloud.com" class="btn-glow btn-glow--ghost">
                <span>M'écrire</span>
            </a>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
