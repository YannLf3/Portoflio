<!DOCTYPE html>
<html lang="fr" data-mode="dev" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($title ?? 'Yann Le Flohic — Développeur Web Full-Stack Châteauroux & Limoges') ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDesc ?? 'Portfolio de Yann Le Flohic, étudiant en BUT MMI et développeur web freelance (YannLf3). Découvre mes projets full-stack, mes créations et mon lab.') ?>">
    <?php if (!empty($noindex)): ?>
        <meta name="robots" content="noindex, follow">
    <?php else: ?>
        <meta name="robots" content="index, follow">
    <?php endif; ?>

    <link rel="canonical" href="<?= htmlspecialchars($canonical ?? 'https://yannleflohic.fr/') ?>">

    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars($canonical ?? 'https://yannleflohic.fr/') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($title ?? 'Yann Le Flohic — Développeur Web Full-Stack') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDesc ?? 'Portfolio de Yann Le Flohic. Découvre mes projets full-stack et mon lab.') ?>">
    <meta property="og:image" content="https://yannleflohic.fr/assets/images/og-image.jpg">

    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="<?= htmlspecialchars($title ?? 'Yann Le Flohic — Développeur Web Full-Stack') ?>">
    <meta property="twitter:description" content="<?= htmlspecialchars($pageDesc ?? 'Portfolio de Yann Le Flohic. Découvre mes projets full-stack et mon lab.') ?>">
    <meta property="twitter:image" content="https://yannleflohic.fr/assets/images/og-image.jpg">

    <link rel="icon" type="image/svg+xml" href="/assets/icons/favicon.svg">
    <link rel="icon" type="image/png" sizes="48x48" href="/assets/icons/favicon-48.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/icons/favicon-180.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/icons/favicon.svg">

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": ["Person", "ProfessionalService"],
          "@id": "https://yannleflohic.fr/#person",
          "name": "Yann Le Flohic",
          "alternateName": "YannLf3",
          "url": "https://yannleflohic.fr",
          "jobTitle": "Développeur Web Freelance",
          "image": "https://yannleflohic.fr/assets/images/og-image.jpg",
          "description": "Développeur web full-stack freelance spécialisé en PHP, JavaScript et Tailwind CSS.",
          "knowsAbout": ["Développement Web", "PHP MVC", "JavaScript", "Photographie", "Tailwind CSS"],
          "address": {
            "@type": "PostalAddress",
            "addressLocality": "Limoges",
            "addressRegion": "Nouvelle-Aquitaine",
            "addressCountry": "FR"
          },
          "areaServed": ["Châteauroux", "Limoges", "Indre", "Haute-Vienne"],
          "sameAs": [
            "https://github.com/YannLf3",
            "https://www.linkedin.com/in/yannlf3/"
          ]
        },
        {
          "@type": "WebSite",
          "@id": "https://yannleflohic.fr/#website",
          "url": "https://yannleflohic.fr",
          "name": "Yann Le Flohic - Portfolio",
          "publisher": {
            "@id": "https://yannleflohic.fr/#person"
          }
        }
      ]
    }
    </script>

    <link rel="stylesheet" href="/css/style.css?v=20261014">

    <script>
        // Exécuté AVANT le premier rendu :
        // 1. .fx active les états initiaux des animations (sans JS, rien n'est masqué) ;
        // 2. le mode mémorisé (Dev / Photo) est appliqué tout de suite → aucun flash du mauvais univers.
        (function () {
            var root = document.documentElement;
            root.classList.add('fx');
            try {
                var mode = localStorage.getItem('portfolio-mode');
                if (mode === 'dev' || mode === 'photo') root.dataset.mode = mode;
            } catch (e) { /* stockage indisponible (navigation privée stricte) : mode Dev par défaut */ }
        })();
    </script>
</head>
<body class="font-body bg-background-2 mx-4 sm:mx-6 lg:mx-10">

<div id="app-loader"
     style="background-color: #16181E; z-index: 999999; position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh;"
     class="flex flex-col justify-between p-6 font-mono text-xs md:text-sm text-zinc-400 select-none transition-all duration-700 ease-in-out">

    <div class="flex items-center justify-between border-b border-zinc-800 pb-4">
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span>
            <span class="w-3 h-3 rounded-full bg-yellow-500 inline-block"></span>
            <span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span>
        </div>
        <span class="text-zinc-500 text-[11px]">yann@portfolio:~ (zsh)</span>
    </div>

    <div class="my-auto max-w-xl mx-auto w-full space-y-2" id="terminal-logs">
    </div>

    <div class="flex items-center justify-between border-t border-zinc-800 pt-4 text-zinc-500 text-[11px]">
        <span>SYSTEM_STATUS: OK</span>
        <span id="loader-progress">0%</span>
    </div>
</div>

<div class="fixed inset-0 pointer-events-none z-0 bg-grid-pattern opacity-60"></div>

<canvas id="fx-particles" class="fx-particles fx-particles--idle" aria-hidden="true"></canvas>

<div id="theme-transition-overlay"
     class="fixed inset-0 pointer-events-none z-20 transition-all duration-700 ease-in-out opacity-0"></div>

<header class="sticky top-0 flex flex-row justify-between items-center z-50 bg-background-2/80 backdrop-blur-md border-b border-border py-3 transition-colors duration-300">

    <a href="/" data-magnetic data-glitch="Yann Le Flohic" aria-label="Yann Le Flohic — retour à l'accueil"
       class="glitch font-display font-semibold uppercase text-xs sm:text-sm tracking-[0.14em] sm:tracking-[0.2em] text-foreground whitespace-nowrap">
        <span class="glitch__text">Yann Le Flohic</span>
    </a>

    <nav class="hidden md:flex items-center gap-1 border border-border-strong bg-background-card/40 backdrop-blur-md rounded-full px-4 py-1.5 text-xs font-mono"
         aria-label="Navigation principale">
        <a href="/#about" data-magnetic
           class="px-3 py-1 rounded-full text-muted-foreground hover:text-foreground hover:bg-background-card/60 transition-all">
            01. Parcours
        </a>
        <a href="/#skills" data-magnetic
           class="px-3 py-1 rounded-full text-muted-foreground hover:text-foreground hover:bg-background-card/60 transition-all">
            02. Compétences
        </a>
        <a href="/#projects" data-magnetic
           class="px-3 py-1 rounded-full text-muted-foreground hover:text-foreground hover:bg-background-card/60 transition-all">
            03. Projets
        </a>
        <a href="/#contact" data-magnetic
           class="px-3 py-1 rounded-full text-muted-foreground hover:text-foreground hover:bg-background-card/60 transition-all">
            04. Contact
        </a>

        <span class="w-px h-3 bg-border-strong mx-1"></span>

        <a href="/lab" data-magnetic
           class="flex items-center gap-1.5 px-3 py-1 rounded-full text-accent [html[data-mode='photo']_&]:text-amber-400 hover:bg-background-card/60 transition-all">
            <span class="w-1.5 h-1.5 rounded-full bg-accent animate-pulse [html[data-mode='photo']_&]:bg-amber-400"></span>
            <span>Lab</span>
        </a>
    </nav>

    <button type="button" id="mode-switch" class="mode-switch" role="switch" aria-checked="false"
            aria-label="Mode photographe" data-magnetic>
        <span class="mode-switch__thumb" aria-hidden="true"></span>
        <span class="mode-switch__option mode-switch__option--dev" aria-hidden="true">
            <span class="sm:hidden">Dev</span><span class="hidden sm:inline">Développeur</span>
        </span>
        <span class="mode-switch__option mode-switch__option--photo" aria-hidden="true">
            <span class="sm:hidden">Photo</span><span class="hidden sm:inline">Photographe</span>
        </span>
    </button>

    <script src="/js/theme-toggle.js?v=20261014"></script>
</header>