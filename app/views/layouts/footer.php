<footer class="mt-32 w-full border-t border-border-strong/60 py-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-xs font-mono tracking-widest text-muted-foreground uppercase">

        <div>
            © 2026 YANN LE FLOHIC, CHÂTEAUROUX, FR
        </div>

        <div class="flex items-center gap-4">
            <span>DÉVELOPPÉ & DESIGNÉ MAISON</span>
            <span class="text-border-strong">|</span>
            <div class="flex flex-wrap items-center gap-4">
                <a href="/mentions-legales" class="inline-block py-1.5 hover:text-foreground transition-colors duration-200">
                    Mentions Légales
                </a>
                <a href="/politique-de-confidentialite" class="inline-block py-1.5 hover:text-foreground transition-colors duration-200">
                    Confidentialité
                </a>
                <a href="/cgv" class="inline-block py-1.5 hover:text-foreground transition-colors duration-200">
                    CGV
                </a>
                <button type="button" data-consent-open class="inline-block py-1.5 uppercase hover:text-foreground transition-colors duration-200">
                    Mesure d'audience
                </button>
            </div>
        </div>

    </div>
</footer>

<button type="button" id="cli-launcher" class="cli-launcher"
        aria-haspopup="dialog" aria-controls="cli" aria-expanded="false" aria-label="Ouvrir le terminal interactif">
    <span class="cli-launcher__prompt" aria-hidden="true">&gt;_</span>
    <span>terminal</span>
    <kbd class="cli-launcher__kbd" data-cli-shortcut>Ctrl K</kbd>
</button>

<dialog id="cli" class="cli" closedby="any" aria-labelledby="cli-title">
    <div class="cli__bar">
        <h2 id="cli-title" class="cli__title">visiteur@yannleflohic.fr: ~ (zsh)</h2>
        <button type="button" class="cli__close" data-cli-action="close" aria-label="Fermer le terminal">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
    </div>

    <div id="cli-output" class="cli__output" role="log" aria-live="polite"></div>

    <form id="cli-form" class="cli__prompt" autocomplete="off">
        <label for="cli-input" class="cli__ps1">visiteur<span class="cli__ps1-host">@yannleflohic.fr</span>:~$</label>
        <input id="cli-input" class="cli__input" type="text" name="command"
               autocapitalize="off" autocorrect="off" spellcheck="false" enterkeyhint="send"
               aria-describedby="cli-title" placeholder="help">
    </form>
</dialog>

<template id="tpl-cli-line"><p class="cli__line cli__line--{{type}}">{{content}}</p></template>
<template id="tpl-cli-link"><a class="cli__link" href="{{href}}"{{target}}>{{label}}</a></template>
<template id="tpl-cli-chip"><button type="button" class="cli__chip" data-cli-run="{{command}}">{{label}}</button></template>

<section id="consent-banner" hidden tabindex="-1" aria-labelledby="consent-title"
         class="fixed inset-x-0 bottom-0 z-60 border-t border-border-strong bg-background-2 px-4 py-5 sm:px-6 shadow-2xl">
    <div class="mx-auto flex max-w-360 flex-col gap-5 lg:flex-row lg:items-center lg:gap-10">
        <div class="flex flex-col gap-2 grow">
            <h2 id="consent-title" class="text-base font-medium text-foreground">Mesure d'audience</h2>
            <p class="text-sm text-muted-foreground leading-relaxed max-w-3xl">
                Ce site mesure sa fréquentation de façon anonyme, avec un outil hébergé chez moi : pages vues,
                site d'origine, type d'appareil. Aucun cookie de suivi, aucune adresse IP conservée.
                Refuser ne change rien à votre navigation.
                <a href="/politique-de-confidentialite#mesure-audience" class="text-accent underline underline-offset-4">En savoir plus</a>
            </p>
            <p id="consent-current" role="status" class="text-xs font-mono text-muted-foreground"></p>
        </div>
        <div class="flex flex-wrap gap-3 shrink-0">
            <button type="button" data-consent="denied" class="btn-glow btn-glow--ghost"><span>Refuser</span></button>
            <button type="button" data-consent="granted" class="btn-glow btn-glow--ghost"><span>Accepter</span></button>
        </div>
    </div>
</section>

<div id="custom-cursor" class="cursor" aria-hidden="true">
    <span class="cursor__dot"></span>
    <span class="cursor__label"></span>
</div>

<script src="/js/main.js?v=20261025"></script>
<script src="/js/nav-menu.js?v=20261025" defer></script>
<script src="/js/mobile-cta.js?v=20261025" defer></script>
<script src="/js/animations.js?v=20261025" defer></script>
<script src="/js/terminal.js?v=20261025" defer></script>
<script src="/js/motion-controls.js?v=20261025" defer></script>
<script src="/js/consent.js?v=20261028" defer></script>
<script src="/js/photo-mode.js?v=20261025" defer></script>