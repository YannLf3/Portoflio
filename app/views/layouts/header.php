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
