<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Yann Le Flohic | Portfolio') ?></title>
    <!-- Lien vers le fichier CSS compilé par Tailwind -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="font-mono bg-background-2 mx-10 [data-mode='dev']">

<!-- Début de la barre de navigation -->
<header class="flex flex-row justify-between">


    <!-- 1. Le Logo -->
    <a class="mt-2" href="/">
        <span class="text-muted-foreground">YannLf3</span>
        <span class="text-muted-foreground"><span class="text-accent">.</span>dev</span>
    </a>


    <!-- 2. Les actions (Changement de thème & Bouton d'appel à l'action) -->
    <!-- Bouton pour basculer entre le mode Dev et Photo -->
    <div class="border-1 border-border-strong rounded-full flex flex-row p-1 mt-4 text-xs" id="theme-toggle"
         aria-label="Changer de thème">
        <button class="uppercase text-accent-contrast rounded-full p-2 bg-accent cursor-pointer"> Développeur</button>

        <button class="uppercase rounded-full p-2 text-muted-foreground cursor-pointer">Photographe</button>
    </div>


</header>