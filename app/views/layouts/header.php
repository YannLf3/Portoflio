<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Yann Le Flohic | Portfolio') ?></title>
    <!-- Lien vers le fichier CSS compilé par Tailwind -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="font-mono bg-background-2 mx-4 sm:mx-6 lg:mx-10">
<!-- Loader Terminal -->
<div id="app-loader"
     class="fixed inset-0 z-100 bg-zinc-950 flex flex-col justify-between p-6 font-mono text-xs md:text-sm text-zinc-400 select-none transition-transform duration-700 ease-in-out">

    <!-- En-tête type fenêtre macOS -->
    <div class="flex items-center justify-between border-b border-zinc-800 pb-4">
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-red-500/80 inline-block"></span>
            <span class="w-3 h-3 rounded-full bg-yellow-500/80 inline-block"></span>
            <span class="w-3 h-3 rounded-full bg-green-500/80 inline-block"></span>
        </div>
        <span class="text-zinc-500 text-[11px]">yann@portfolio:~ (zsh)</span>
    </div>

    <!-- Conteneur des lignes de logs -->
    <div class="my-auto max-w-xl mx-auto w-full space-y-2" id="terminal-logs">
        <!-- Les lignes vont s'injecter ici via JS -->
    </div>

    <!-- Pied de loader / Progression -->
    <div class="flex items-center justify-between border-t border-zinc-800 pt-4 text-zinc-500 text-[11px]">
        <span>SYSTEM_STATUS: OK</span>
        <span id="loader-progress">0%</span>
    </div>
</div>

<!-- Calque de fond : Grille & Dégradé radial -->
<div class="fixed inset-0 pointer-events-none z-0 bg-grid-pattern opacity-60"></div>

<div id="theme-transition-overlay"
     class="fixed inset-0 pointer-events-none z-20 transition-all duration-700 ease-in-out opacity-0"></div>

<!-- Début de la barre de navigation -->
<header class="sticky top-0 flex flex-row justify-between z-50">


    <!-- 1. Le Logo -->
    <a class="mt-2" href="/">
        <span class="text-muted-foreground">YannLf3</span>
        <span class="text-muted-foreground"><span class="text-accent">.</span>dev</span>
    </a>


    <!-- 2. Les actions (Changement de thème & Bouton d'appel à l'action) -->
    <!-- Bouton pour basculer entre le mode Dev et Photo -->
    <div class="border border-border-strong backdrop-blur-md rounded-full flex flex-row p-1 mt-4 text-xs"
         id="theme-toggle"
         aria-label="Changer de thème">
        <button id="toggle-dev"
                class="uppercase text-accent-contrast rounded-full p-2 bg-accent cursor-pointer transition-all duration-300 ease-in-out hover:scale-105 transform active:scale-95 ">
            Développeur
        </button>

        <button id="toggle-photo"
                class="uppercase rounded-full p-2 text-muted-foreground cursor-pointer transition-all duration-300 ease-in-out hover:scale-105 transform active:scale-95">
            Photographe
        </button>
    </div>

    <script src="/js/theme-toggle.js"></script>
</header>
