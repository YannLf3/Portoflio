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
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Aperçu du portfolio de Yann Le Flohic, développeur web freelance">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="Yann Le Flohic">

    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="<?= htmlspecialchars($title ?? 'Yann Le Flohic — Développeur Web Full-Stack') ?>">
    <meta property="twitter:description" content="<?= htmlspecialchars($pageDesc ?? 'Portfolio de Yann Le Flohic. Découvre mes projets full-stack et mon lab.') ?>">
    <meta property="twitter:image" content="https://yannleflohic.fr/assets/images/og-image.jpg">
    <meta property="twitter:image:alt" content="Aperçu du portfolio de Yann Le Flohic, développeur web freelance">

    <link rel="icon" href="/favicon.ico" sizes="32x32">
    <link rel="icon" type="image/svg+xml" href="/assets/icons/favicon.svg">
    <link rel="icon" type="image/png" sizes="48x48" href="/assets/icons/favicon-48.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/icons/favicon-180.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#16181E">

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

    <link rel="stylesheet" href="/css/style.css?v=20261019">

    <script nonce="<?= CSP_NONCE ?>">
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

<header class="sticky top-0 flex flex-row justify-between items-center gap-1.5 z-50 bg-background-2/80 backdrop-blur-md border-b border-border py-3 transition-colors duration-300">

    <a href="/" data-magnetic data-glitch="Yann Le Flohic" aria-label="Yann Le Flohic — retour à l'accueil"
       class="glitch font-display font-semibold uppercase text-xs sm:text-sm tracking-[0.14em] sm:tracking-[0.2em] text-foreground whitespace-nowrap">
        <span class="glitch__text">Yann Le Flohic</span>
    </a>

    <button type="button" id="nav-toggle" class="nav-burger ml-auto sm:mr-1.5 md:hidden"
            aria-controls="main-nav" aria-expanded="false" aria-label="Ouvrir le menu">
        <span class="nav-burger__bar" aria-hidden="true"></span>
        <span class="nav-burger__bar" aria-hidden="true"></span>
        <span class="nav-burger__bar" aria-hidden="true"></span>
    </button>

    <nav id="main-nav" data-open="false"
         class="nav-menu absolute top-full inset-x-0 mt-2 hidden data-[open=true]:flex flex-col gap-1 p-2 rounded-2xl border border-border-strong bg-background-2 shadow-lg text-sm font-mono md:static md:mt-0 md:flex md:flex-row md:items-center md:gap-1 md:px-4 md:py-1.5 md:rounded-full md:bg-background-card/40 md:backdrop-blur-md md:shadow-none md:text-xs"
         aria-label="Navigation principale">
        <a href="/#about" data-magnetic
           class="px-4 py-3 rounded-xl md:px-3 md:py-1 md:rounded-full text-muted-foreground hover:text-foreground hover:bg-background-card/60 transition-all">
            01. Parcours
        </a>
        <a href="/#skills" data-magnetic
           class="px-4 py-3 rounded-xl md:px-3 md:py-1 md:rounded-full text-muted-foreground hover:text-foreground hover:bg-background-card/60 transition-all">
            02. Compétences
        </a>
        <a href="/#projects" data-magnetic
           class="px-4 py-3 rounded-xl md:px-3 md:py-1 md:rounded-full text-muted-foreground hover:text-foreground hover:bg-background-card/60 transition-all">
            03. Projets
        </a>
        <a href="/#contact" data-magnetic
           class="px-4 py-3 rounded-xl md:px-3 md:py-1 md:rounded-full text-muted-foreground hover:text-foreground hover:bg-background-card/60 transition-all">
            04. Contact
        </a>

        <span class="h-px mx-2 my-1 bg-border-strong md:w-px md:h-3 md:mx-1 md:my-0" aria-hidden="true"></span>

        <a href="/lab" data-magnetic
           class="flex items-center gap-1.5 px-4 py-3 rounded-xl md:px-3 md:py-1 md:rounded-full text-accent [html[data-mode='photo']_&]:text-amber-400 hover:bg-background-card/60 transition-all">
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