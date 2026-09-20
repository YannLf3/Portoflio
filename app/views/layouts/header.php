<!DOCTYPE html>
<html lang="fr" data-mode="dev" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Yann Le Flohic | Portfolio') ?></title>
    <!-- Lien vers le fichier CSS compilé par Tailwind -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="font-mono bg-background-2 mx-4 sm:mx-6 lg:mx-10">

<!-- Loader Terminal (Opaque & Sécurisé Safari/WebKit) -->
<!-- Loader Terminal (Plein écran absolu) -->
<div id="app-loader"
     style="background-color: #09090b; z-index: 999999; position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh;"
     class="flex flex-col justify-between p-6 font-mono text-xs md:text-sm text-zinc-400 select-none transition-all duration-700 ease-in-out">

    <!-- En-tête type fenêtre macOS -->
    <div class="flex items-center justify-between border-b border-zinc-800 pb-4">
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span>
            <span class="w-3 h-3 rounded-full bg-yellow-500 inline-block"></span>
            <span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span>
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

<!-- Début de la barre de navigation sticky -->
<header class="sticky top-0 flex flex-row justify-between items-center z-50 bg-background-2/80 backdrop-blur-md border-b border-border py-3 transition-colors duration-300">

    <!-- 1. Le Logo -->
    <a href="/" class="flex items-center gap-1 font-mono text-sm hover:opacity-80 transition-opacity">
        <span class="text-muted-foreground">YannLf3</span>
        <span class="text-muted-foreground"><span
                    class="text-accent [html[data-mode='photo']_&]:text-amber-400">.</span>dev</span>
    </a>

    <!-- 2. Navigation vers les sections de la page (Barre centrale) -->
    <nav class="hidden md:flex items-center gap-1 border border-border-strong bg-background-card/40 backdrop-blur-md rounded-full px-4 py-1.5 text-xs font-mono"
         aria-label="Navigation principale">
        <a href="/#about"
           class="px-3 py-1 rounded-full text-muted-foreground hover:text-foreground hover:bg-background-card/60 transition-all">
            01. Parcours
        </a>
        <a href="/#skills"
           class="px-3 py-1 rounded-full text-muted-foreground hover:text-foreground hover:bg-background-card/60 transition-all">
            02. Compétences
        </a>
        <a href="/#projects"
           class="px-3 py-1 rounded-full text-muted-foreground hover:text-foreground hover:bg-background-card/60 transition-all">
            03. Projets
        </a>
        <a href="/#contact"
           class="px-3 py-1 rounded-full text-muted-foreground hover:text-foreground hover:bg-background-card/60 transition-all">
            04. Contact
        </a>

        <!-- Séparateur discret -->
        <span class="w-px h-3 bg-border-strong mx-1"></span>

        <!-- Lien vers la page Lab -->
        <a href="/lab"
           class="flex items-center gap-1.5 px-3 py-1 rounded-full text-accent [html[data-mode='photo']_&]:text-amber-400 hover:bg-background-card/60 transition-all">
            <span class="w-1.5 h-1.5 rounded-full bg-accent animate-pulse [html[data-mode='photo']_&]:bg-amber-400"></span>
            <span>Lab</span>
        </a>
    </nav>

    <!-- 3. Actions : Bascule de thème Dev / Photo -->
    <div class="border border-border-strong backdrop-blur-md rounded-full flex flex-row p-1 text-xs"
         id="theme-toggle"
         aria-label="Changer de thème">
        <button id="toggle-dev"
                class="uppercase text-accent-contrast rounded-full p-2 bg-accent cursor-pointer transition-all duration-300 ease-in-out hover:scale-105 transform active:scale-95">
            Développeur
        </button>

        <button id="toggle-photo"
                class="uppercase rounded-full p-2 text-muted-foreground cursor-pointer transition-all duration-300 ease-in-out hover:scale-105 transform active:scale-95">
            Photographe
        </button>
    </div>

    <script src="/js/theme-toggle.js"></script>
</header>