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

<!-- Curseur personnalisé (masqué sur mobile via hidden md:block) -->
<div id="custom-cursor"
     class="pointer-events-none fixed top-0 left-0 z-50 -translate-x-1/2 -translate-y-1/2 transition-opacity duration-300 opacity-0 hidden md:block">

    <!-- Design Mode DEV : Curseurs Terminal (|) -->
    <div class="mode-dev-cursor flex items-center gap-1 font-mono text-accent text-xs font-bold tracking-widest select-none">
        <span>&lt;</span><span class="w-1 h-1 bg-accent rounded-full"></span><span>&gt;</span>
    </div>

    <!-- Design Mode PHOTO : Collimateur d'autofocus -->
    <div class="mode-photo-cursor relative w-7 h-5 border border-amber-400/70 flex items-center justify-center select-none">
        <div class="w-1 h-1 bg-amber-400 rounded-full"></div>
    </div>

</div>

<script src="/js/main.js"></script>