<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main id="main" tabindex="-1" class="w-full pt-16 pb-24 flex justify-center">
    <article class="w-full max-w-3xl flex flex-col gap-10">

        <nav aria-label="Fil d'Ariane" class="text-xs font-mono uppercase tracking-widest text-muted-foreground">
            <ol class="flex flex-wrap items-center gap-2">
                <li><a href="/" class="inline-block py-1.5 hover:text-foreground">Accueil</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="/lab" class="inline-block py-1.5 hover:text-foreground">Lab</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-foreground"><?= htmlspecialchars($article['title']) ?></li>
            </ol>
        </nav>

        <header class="flex flex-col items-start gap-4">
            <p class="flex items-center gap-3 text-xs font-mono uppercase tracking-widest text-muted-foreground">
                <span class="text-accent"><?= htmlspecialchars($article['category']) ?></span>
                <span aria-hidden="true">/</span>
                <span><?= htmlspecialchars($article['date']) ?></span>
            </p>
            <h1 class="text-display-md font-display font-normal tracking-tight text-foreground leading-tight">
                <?= htmlspecialchars($article['title']) ?>
            </h1>
        </header>

        <div class="text-base text-muted-foreground leading-relaxed font-body whitespace-pre-line max-w-prose">
            <?= htmlspecialchars($article['content']) ?>
        </div>

        <a href="/lab" class="btn-glow btn-glow--ghost self-start">
            <span>Retour au lab</span>
        </a>

    </article>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
