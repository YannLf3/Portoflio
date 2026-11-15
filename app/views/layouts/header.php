<!DOCTYPE html>
<html lang="fr" data-mode="dev" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($title ?? 'Yann Le Flohic | Développeur Web Full-Stack Châteauroux & Limoges') ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDesc ?? 'Portfolio de Yann Le Flohic, étudiant en BUT MMI et développeur web freelance (YannLf3). Découvrez mes projets full-stack, mes créations et mon lab.') ?>">
    <?php if (!empty($noindex)): ?>
        <meta name="robots" content="noindex, follow">
    <?php else: ?>
        <meta name="robots" content="index, follow">
    <?php endif; ?>

    <link rel="canonical" href="<?= htmlspecialchars($canonical ?? 'https://yannleflohic.fr/') ?>">

    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars($canonical ?? 'https://yannleflohic.fr/') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($title ?? 'Yann Le Flohic | Développeur Web Full-Stack') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDesc ?? 'Portfolio de Yann Le Flohic. Découvrez mes projets full-stack et mon lab.') ?>">
    <meta property="og:image" content="https://yannleflohic.fr/assets/images/og-image.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Aperçu du portfolio de Yann Le Flohic, développeur web freelance">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="Yann Le Flohic">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($title ?? 'Yann Le Flohic | Développeur Web Full-Stack') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($pageDesc ?? 'Portfolio de Yann Le Flohic. Découvrez mes projets full-stack et mon lab.') ?>">
    <meta name="twitter:image" content="https://yannleflohic.fr/assets/images/og-image.jpg">
    <meta name="twitter:image:alt" content="Aperçu du portfolio de Yann Le Flohic, développeur web freelance">

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

    <?php if (!empty($jsonLd)): ?>
        <script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@75..125,300..800&family=DM+Sans:ital,opsz,wght@0,9..40,300..700;1,9..40,300..700&family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,300..700&family=JetBrains+Mono:wght@400..600&display=swap">
    <link rel="stylesheet" href="/css/style.css?v=20261027">

    <script nonce="<?= CSP_NONCE ?>">
        // Exécuté AVANT le premier rendu :
        // 1. .fx active les états initiaux des animations (sans JS, rien n'est masqué) ;
        // 2. le mode mémorisé (Dev / Photo) est appliqué tout de suite → aucun flash du mauvais univers.
        (function () {
            var root = document.documentElement;
            root.classList.add('fx');
            try {
                // Loader déjà joué dans l'onglet, ou mouvement réduit demandé : il n'est jamais affiché
                if (sessionStorage.getItem('portfolio_loaded') || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    root.classList.add('loader-done');
                }
                var mode = localStorage.getItem('portfolio-mode');
                if (mode === 'dev' || mode === 'photo') root.dataset.mode = mode;
            } catch (e) { /* stockage indisponible (navigation privée stricte) : mode Dev par défaut */ }
        })();
    </script>
</head>
<body class="font-body bg-background-2 w-[calc(100%-2rem)] sm:w-[calc(100%-3rem)] lg:w-[calc(100%-5rem)] max-w-360 mx-auto">

<a href="#main"
   class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[1000000] focus:px-4 focus:py-2 focus:rounded-full focus:bg-accent focus:text-accent-contrast focus:font-mono focus:text-xs focus:uppercase">
    Aller au contenu
</a>

<noscript><style>#app-loader{display:none !important}</style></noscript>
<div id="app-loader"
     style="background-color: #16181E; z-index: 999999; position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh;"
     class="flex flex-col justify-between p-6 font-mono text-xs md:text-sm text-zinc-400 select-none transition-all duration-700 ease-in-out">

    <div class="flex items-center justify-between border-b border-zinc-800 pb-4">
        <span class="text-zinc-500 text-xs">yann@portfolio:~ (zsh)</span>
        <button type="button" id="loader-skip"
                class="px-3 py-1.5 rounded-full border border-zinc-700 text-zinc-300 hover:border-accent hover:text-accent text-xs uppercase tracking-wider">
            Passer
        </button>
    </div>

    <div class="my-auto max-w-xl mx-auto w-full space-y-2" id="terminal-logs">
    </div>

    <div class="flex items-center justify-between border-t border-zinc-800 pt-4 text-zinc-500 text-xs">
        <span>SYSTEM_STATUS: OK</span>
        <span id="loader-progress">0%</span>
    </div>
</div>

<div class="fixed inset-0 pointer-events-none z-0 bg-grid-pattern opacity-60" aria-hidden="true"></div>

<canvas id="fx-particles" class="fx-particles fx-particles--idle" aria-hidden="true"></canvas>

<div id="theme-transition-overlay"
     class="fixed inset-0 pointer-events-none z-20 transition-all duration-700 ease-in-out opacity-0"></div>

<header class="sticky top-0 flex flex-row justify-between items-center gap-1.5 z-50 bg-background-2/80 backdrop-blur-md border-b border-border py-3 transition-colors duration-300">

    <a href="/" aria-label="YannLf3, retour à l'accueil"
       class="flex items-center shrink-0 hover:opacity-80 transition-opacity">
        <svg viewBox="240 160 1112 320" width="111" height="32" aria-hidden="true" focusable="false"
             class="h-7 sm:h-8 w-auto">
            <path class="fill-accent" d="M255.8,176L335,176L407,258.296L479,176L558.2,176L436.88,314.6L377.12,314.6L255.8,176Z"/>
            <rect class="fill-foreground" x="377.12" y="343.4" width="59.76" height="120.6"/>
            <path class="fill-foreground" d="M623.8,420L623.8,398L677.8,398C681.533,398 683.4,396 683.4,392L683.4,367L679.8,367C678.733,369.267 677.067,371.533 674.8,373.8C672.533,376.067 669.467,377.933 665.6,379.4C661.733,380.867 656.8,381.6 650.8,381.6C643.067,381.6 636.3,379.833 630.5,376.3C624.7,372.767 620.2,367.867 617,361.6C613.8,355.333 612.2,348.133 612.2,340L612.2,280.8L637.4,280.8L637.4,338C637.4,345.467 639.233,351.067 642.9,354.8C646.567,358.533 651.8,360.4 658.6,360.4C666.333,360.4 672.333,357.833 676.6,352.7C680.867,347.567 683,340.4 683,331.2L683,280.8L708.2,280.8L708.2,397.6C708.2,404.4 706.2,409.833 702.2,413.9C698.2,417.967 692.867,420 686.2,420L623.8,420Z"/>
            <path class="fill-foreground" d="M767,382.8C759.933,382.8 753.6,381.567 748,379.1C742.4,376.633 737.967,373.033 734.7,368.3C731.433,363.567 729.8,357.8 729.8,351C729.8,344.2 731.433,338.5 734.7,333.9C737.967,329.3 742.5,325.833 748.3,323.5C754.1,321.167 760.733,320 768.2,320L795.4,320L795.4,314.4C795.4,309.733 793.933,305.9 791,302.9C788.067,299.9 783.4,298.4 777,298.4C770.733,298.4 766.067,299.833 763,302.7C759.933,305.567 757.933,309.267 757,313.8L733.8,306C735.4,300.933 737.967,296.3 741.5,292.1C745.033,287.9 749.767,284.5 755.7,281.9C761.633,279.3 768.867,278 777.4,278C790.467,278 800.8,281.267 808.4,287.8C816,294.333 819.8,303.8 819.8,316.2L819.8,353.2C819.8,357.2 821.667,359.2 825.4,359.2L833.4,359.2L833.4,380L816.6,380C811.667,380 807.6,378.8 804.4,376.4C801.2,374 799.6,370.8 799.6,366.8L799.6,366.6L795.8,366.6C795.267,368.2 794.067,370.3 792.2,372.9C790.333,375.5 787.4,377.8 783.4,379.8C779.4,381.8 773.933,382.8 767,382.8ZM771.4,362.4C778.467,362.4 784.233,360.433 788.7,356.5C793.167,352.567 795.4,347.333 795.4,340.8L795.4,338.8L770,338.8C765.333,338.8 761.667,339.8 759,341.8C756.333,343.8 755,346.6 755,350.2C755,353.8 756.4,356.733 759.2,359C762,361.267 766.067,362.4 771.4,362.4Z"/>
            <path class="fill-foreground" d="M851.8,380L851.8,280.8L876.6,280.8L876.6,293.8L880.2,293.8C881.8,290.333 884.8,287.033 889.2,283.9C893.6,280.767 900.267,279.2 909.2,279.2C916.933,279.2 923.7,280.967 929.5,284.5C935.3,288.033 939.8,292.9 943,299.1C946.2,305.3 947.8,312.533 947.8,320.8L947.8,380L922.6,380L922.6,322.8C922.6,315.333 920.767,309.733 917.1,306C913.433,302.267 908.2,300.4 901.4,300.4C893.667,300.4 887.667,302.967 883.4,308.1C879.133,313.233 877,320.4 877,329.6L877,380L851.8,380Z"/>
            <path class="fill-foreground" d="M975,380L975,280.8L999.8,280.8L999.8,293.8L1003.4,293.8C1005,290.333 1008,287.033 1012.4,283.9C1016.8,280.767 1023.467,279.2 1032.4,279.2C1040.133,279.2 1046.9,280.967 1052.7,284.5C1058.5,288.033 1063,292.9 1066.2,299.1C1069.4,305.3 1071,312.533 1071,320.8L1071,380L1045.8,380L1045.8,322.8C1045.8,315.333 1043.967,309.733 1040.3,306C1036.633,302.267 1031.4,300.4 1024.6,300.4C1016.867,300.4 1010.867,302.967 1006.6,308.1C1002.333,313.233 1000.2,320.4 1000.2,329.6L1000.2,380L975,380Z"/>
            <rect class="fill-foreground" x="1098.2" y="240" width="25.2" height="140"/>
            <path class="fill-foreground" d="M1167.8,380L1167.8,301.6L1142.6,301.6L1142.6,280.8L1167.8,280.8L1167.8,262.4C1167.8,255.6 1169.833,250.167 1173.9,246.1C1177.967,242.033 1183.267,240 1189.8,240L1215.8,240L1215.8,260.8L1198.6,260.8C1194.867,260.8 1193,262.8 1193,266.8L1193,280.8L1219,280.8L1219,301.6L1193,301.6L1193,380L1167.8,380Z"/>
            <path class="fill-accent" d="M1285.4,382.8C1274.867,382.8 1265.533,380.933 1257.4,377.2C1249.267,373.467 1242.9,368.1 1238.3,361.1C1233.7,354.1 1231.4,345.8 1231.4,336.2L1231.4,332.6L1257,332.6L1257,335.4C1257,342.867 1259.6,348.833 1264.8,353.3C1270,357.767 1276.867,360 1285.4,360C1294.067,360 1300.767,357.933 1305.5,353.8C1310.233,349.667 1312.6,344.467 1312.6,338.2L1312.6,336.2C1312.6,331.933 1311.533,328.467 1309.4,325.8C1307.267,323.133 1304.4,321.167 1300.8,319.9C1297.2,318.633 1293.133,318 1288.6,318L1260.6,318L1260.6,290L1305.8,266L1305.8,262.8L1235,262.8L1235,240L1334.2,240L1334.2,274L1292,296.4L1292,299.6L1299.4,299.6C1305.667,299.6 1311.767,300.867 1317.7,303.4C1323.633,305.933 1328.533,309.833 1332.4,315.1C1336.267,320.367 1338.2,327.2 1338.2,335.6L1338.2,338C1338.2,347.067 1336,354.967 1331.6,361.7C1327.2,368.433 1321.033,373.633 1313.1,377.3C1305.167,380.967 1295.933,382.8 1285.4,382.8Z"/>
        </svg>
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
        <a href="/#about"
           class="px-4 py-3 rounded-xl md:px-3 md:py-1 md:rounded-full text-muted-foreground hover:text-foreground hover:bg-background-card/60 transition-all">
            01. Parcours
        </a>
        <a href="/#skills"
           class="px-4 py-3 rounded-xl md:px-3 md:py-1 md:rounded-full text-muted-foreground hover:text-foreground hover:bg-background-card/60 transition-all">
            02. Compétences
        </a>
        <a href="/#projects"
           class="px-4 py-3 rounded-xl md:px-3 md:py-1 md:rounded-full text-muted-foreground hover:text-foreground hover:bg-background-card/60 transition-all">
            03. Projets
        </a>
        <a href="/#gallery"
           class="px-4 py-3 rounded-xl md:px-3 md:py-1 md:rounded-full text-muted-foreground hover:text-foreground hover:bg-background-card/60 transition-all">
            04. Galerie
        </a>
        <a href="/#contact"
           class="px-4 py-3 rounded-xl md:px-3 md:py-1 md:rounded-full text-muted-foreground hover:text-foreground hover:bg-background-card/60 transition-all">
            05. Contact
        </a>

        <span class="h-px mx-2 my-1 bg-border-strong md:w-px md:h-3 md:mx-1 md:my-0" aria-hidden="true"></span>

        <a href="/lab"<?= str_starts_with(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/lab') ? ' aria-current="page"' : '' ?>
           class="flex items-center gap-1.5 px-4 py-3 rounded-xl md:px-3 md:py-1 md:rounded-full text-accent hover:bg-background-card/60 transition-all">
            <span class="w-1.5 h-1.5 rounded-full bg-accent" aria-hidden="true"></span>
            <span>Lab</span>
        </a>
    </nav>

    <button type="button" id="mode-switch" class="mode-switch" role="switch" aria-checked="false"
            aria-label="Mode photographe" data-track="mode_switch">
        <span class="mode-switch__thumb" aria-hidden="true"></span>
        <span class="mode-switch__option mode-switch__option--dev" aria-hidden="true">
            <span class="sm:hidden">Dev</span><span class="hidden sm:inline">Développeur</span>
        </span>
        <span class="mode-switch__option mode-switch__option--photo" aria-hidden="true">
            <span class="sm:hidden">Photo</span><span class="hidden sm:inline">Photographe</span>
        </span>
    </button>

    <script src="/js/theme-toggle.js?v=20261025"></script>
</header>