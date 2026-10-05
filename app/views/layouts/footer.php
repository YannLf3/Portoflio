<footer class="mt-32 w-full border-t border-border-strong/60 py-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-[11px] font-mono tracking-widest text-muted-foreground uppercase">

        <div>
            © 2026 YANN LE FLOHIC — CHÂTEAUROUX, FR
        </div>

        <div class="flex items-center gap-4">
            <span>DÉVELOPPÉ & DESIGNÉ MAISON</span>
            <span class="text-border-strong">|</span>
            <div class="flex items-center gap-4">
                <a href="/mentions-legales" class="hover:text-foreground transition-colors duration-200">
                    Mentions Légales
                </a>
                <a href="/login" class="hover:text-accent transition-colors duration-200">
                    ADMIN
                </a>
            </div>
        </div>

    </div>
    <!-- Badge Status / EXIF discret en bas à gauche -->
    <div class="fixed bottom-15 left-4 z-40 hidden lg:block pointer-events-none">
        <div class="bg-zinc-900/90 backdrop-blur-md border border-zinc-800 px-3 py-1.5 rounded-full font-mono text-[11px] shadow-lg">
            <!-- Statut Dev -->
            <div class="flex items-center gap-2 text-zinc-400 [html[data-mode='photo']_&]:hidden">
                <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
                <span>status: 200 OK</span>
                <span class="text-zinc-600">|</span>
                <span class="text-zinc-500">branch: main</span>
            </div>

            <!-- Statut EXIF Photo -->
            <div class="hidden [html[data-mode='photo']_&]:flex items-center gap-2 text-amber-400">
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                <span>1/1000s</span>
                <span class="text-amber-400/40">•</span>
                <span>f/1.8</span>
                <span class="text-amber-400/40">•</span>
                <span>ISO 100</span>
            </div>
        </div>
    </div>
</footer>

<!-- =====================================================================
     TERMINAL CLI (Easter Egg) — public/js/terminal.js
     Ouverture : bouton flottant ou raccourci ⌘K / Ctrl+K. Données : GET /api/terminal.
     ===================================================================== -->
<button type="button" id="cli-launcher" class="cli-launcher" data-magnetic
        aria-haspopup="dialog" aria-controls="cli" aria-expanded="false" aria-label="Ouvrir le terminal interactif">
    <span class="cli-launcher__prompt" aria-hidden="true">&gt;_</span>
    <span>terminal</span>
    <kbd class="cli-launcher__kbd" data-cli-shortcut>Ctrl K</kbd>
</button>

<!-- closedby="any" : fermeture par Échap, geste retour mobile ET clic hors de la fenêtre (repli JS pour Safari) -->
<dialog id="cli" class="cli" closedby="any" aria-labelledby="cli-title">
    <div class="cli__bar">
        <div class="flex items-center gap-2" aria-hidden="true">
            <span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span>
            <span class="w-3 h-3 rounded-full bg-yellow-500 inline-block"></span>
            <span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span>
        </div>
        <h2 id="cli-title" class="cli__title">visiteur@yannleflohic.fr: ~ (zsh)</h2>
        <button type="button" class="cli__close" data-cli-action="close" aria-label="Fermer le terminal">✕</button>
    </div>

    <!-- role="log" : les nouvelles lignes sont annoncées par les lecteurs d'écran -->
    <div id="cli-output" class="cli__output" role="log" aria-live="polite"></div>

    <form id="cli-form" class="cli__prompt" autocomplete="off">
        <label for="cli-input" class="cli__ps1">visiteur<span class="cli__ps1-host">@yannleflohic.fr</span>:~$</label>
        <input id="cli-input" class="cli__input" type="text" name="command"
               autocapitalize="off" autocorrect="off" spellcheck="false" enterkeyhint="send"
               aria-describedby="cli-title" placeholder="help">
    </form>
</dialog>

<!-- Gabarits du terminal : remplis par remplacement de chaînes ({{…}}) dans terminal.js -->
<template id="tpl-cli-line"><p class="cli__line cli__line--{{type}}">{{content}}</p></template>
<template id="tpl-cli-link"><a class="cli__link" href="{{href}}"{{target}}>{{label}}</a></template>
<template id="tpl-cli-chip"><button type="button" class="cli__chip" data-cli-run="{{command}}">{{label}}</button></template>

<!-- Curseur magnétique (bloc BEM .cursor, positionné par public/js/animations.js).
     Affiché uniquement avec une souris/trackpad : la classe .has-custom-cursor est posée par le JS. -->
<div id="custom-cursor" class="cursor" aria-hidden="true">
    <span class="cursor__dot"></span>
    <!-- Libellé du grand cercle en mode Photo (« Voir », « Glisser »), rempli par animations.js -->
    <span class="cursor__label"></span>
</div>

<script src="/js/main.js?v=20261005"></script>
<!-- Animations & micro-interactions (WebGL, curseur, révélations, tilt) -->
<script src="/js/animations.js?v=20261005" defer></script>
<!-- Terminal CLI interactif (Easter Egg) -->
<script src="/js/terminal.js?v=20261005" defer></script>
<!-- Mode Photo : visionneuse de la galerie + comparateur avant / après -->
<script src="/js/photo-mode.js?v=20261005" defer></script>