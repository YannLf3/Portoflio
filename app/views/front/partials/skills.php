<section id="skills" class="mt-24 w-full flex flex-col gap-12">

    <!-- En-tête de section -->
    <div class="flex flex-col items-start gap-2">
        <span class="text-xs font-mono tracking-widest text-accent uppercase [html[data-mode='photo']_&]:text-amber-400">
            02 — Compétences &amp; Outils
        </span>
    </div>

    <!-- Bandeau défilant infini (Marquee) -->
    <div class="w-full overflow-hidden border-y border-border-strong py-4 relative group">
        <!-- Masque de dégradé latéral -->
        <div class="absolute left-0 top-0 bottom-0 w-12 bg-linear-to-r from-background to-transparent z-10 pointer-events-none"></div>
        <div class="absolute right-0 top-0 bottom-0 w-12 bg-linear-to-l from-background to-transparent z-10 pointer-events-none"></div>

        <div class="flex whitespace-nowrap animate-marquee gap-8 items-center text-xl sm:text-2xl font-serif italic">

            <!-- CONTENU MODE DEV -->
            <div class="contents [html[data-mode='photo']_&]:hidden text-foreground/80">
                <!-- Bloc Dev 1 -->
                <div class="flex items-center gap-8 shrink-0">
                    <span>Mac</span> <span class="text-accent text-xs">✦</span>
                    <span>HTML / CSS</span> <span class="text-accent text-xs">✦</span>
                    <span>JavaScript</span> <span class="text-accent text-xs">✦</span>
                    <span>React</span> <span class="text-accent text-xs">✦</span>
                    <span>PHP</span> <span class="text-accent text-xs">✦</span>
                    <span>SQL</span> <span class="text-accent text-xs">✦</span>
                    <span>Python</span> <span class="text-accent text-xs">✦</span>
                    <span>VS Code</span> <span class="text-accent text-xs">✦</span>
                    <span>Figma</span> <span class="text-accent text-xs">✦</span>
                </div>
                <!-- Duplication Bloc Dev pour boucle infinie -->
                <div class="flex items-center gap-8 shrink-0" aria-hidden="true">
                    <span>Mac</span> <span class="text-accent text-xs">✦</span>
                    <span>HTML / CSS</span> <span class="text-accent text-xs">✦</span>
                    <span>JavaScript</span> <span class="text-accent text-xs">✦</span>
                    <span>React</span> <span class="text-accent text-xs">✦</span>
                    <span>PHP</span> <span class="text-accent text-xs">✦</span>
                    <span>SQL</span> <span class="text-accent text-xs">✦</span>
                    <span>Python</span> <span class="text-accent text-xs">✦</span>
                    <span>VS Code</span> <span class="text-accent text-xs">✦</span>
                    <span>Figma</span> <span class="text-accent text-xs">✦</span>
                </div>
            </div>

            <!-- CONTENU MODE PHOTO -->
            <div class="hidden [html[data-mode='photo']_&]:flex items-center gap-8 text-amber-400/90">
                <!-- Bloc Photo 1 -->
                <div class="flex items-center gap-8 shrink-0">
                    <span>Shooting Portrait</span> <span class="text-amber-400 text-xs">✦</span>
                    <span>Color Grading</span> <span class="text-amber-400 text-xs">✦</span>
                    <span>Lightroom</span> <span class="text-amber-400 text-xs">✦</span>
                    <span>Montage DaVinci</span> <span class="text-amber-400 text-xs">✦</span>
                    <span>Affinity by Canva</span> <span class="text-amber-400 text-xs">✦</span>
                    <span>Panasonic Lumix</span> <span class="text-amber-400 text-xs">✦</span>
                </div>
                <!-- Duplication Bloc Photo pour boucle infinie -->
                <div class="flex items-center gap-8 shrink-0" aria-hidden="true">
                    <span>Shooting Portrait</span> <span class="text-amber-400 text-xs">✦</span>
                    <span>Color Grading</span> <span class="text-amber-400 text-xs">✦</span>
                    <span>Lightroom</span> <span class="text-amber-400 text-xs">✦</span>
                    <span>Montage DaVinci</span> <span class="text-amber-400 text-xs">✦</span>
                    <span>Affinity by Canva</span> <span class="text-amber-400 text-xs">✦</span>
                    <span>Panasonic Lumix</span> <span class="text-amber-400 text-xs">✦</span>
                </div>
            </div>

        </div>
    </div>

    <!-- Contenu principal : Grille Tags + Langues -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start">

        <!-- Colonne Gauche : Tags Compétences & Outils -->
        <div class="lg:col-span-7 flex flex-col gap-6">

            <!-- MODE DEV : Langages & Frameworks -->
            <div class="[html[data-mode='photo']_&]:hidden flex flex-wrap gap-2.5">
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">HTML / CSS</span>
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">JavaScript</span>
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">React</span>
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">PHP</span>
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">SQL</span>
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">Python</span>
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">Sass</span>
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">BEM CSS</span>
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">Tailwind</span>
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">Bootstrap</span>
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">Git / Github</span>
            </div>

            <!-- MODE DEV : Outils & Environnement -->
            <div class="[html[data-mode='photo']_&]:hidden flex flex-wrap gap-2 pt-2">
                <span class="px-3 py-1 rounded-md border border-border-strong bg-background-card/20 text-[11px] font-mono uppercase tracking-wider text-muted-foreground">VS CODE</span>
                <span class="px-3 py-1 rounded-md border border-border-strong bg-background-card/20 text-[11px] font-mono uppercase tracking-wider text-muted-foreground">JETBRAINS IDE</span>
                <span class="px-3 py-1 rounded-md border border-border-strong bg-background-card/20 text-[11px] font-mono uppercase tracking-wider text-muted-foreground">FIGMA</span>
                <span class="px-3 py-1 rounded-md border border-border-strong bg-background-card/20 text-[11px] font-mono uppercase tracking-wider text-muted-foreground">SHELL</span>
                <span class="px-3 py-1 rounded-md border border-border-strong bg-background-card/20 text-[11px] font-mono uppercase tracking-wider text-muted-foreground">ADOBE</span>
                <span class="px-3 py-1 rounded-md border border-border-strong bg-background-card/20 text-[11px] font-mono uppercase tracking-wider text-muted-foreground">AFFINITY</span>
                <span class="px-3 py-1 rounded-md border border-border-strong bg-background-card/20 text-[11px] font-mono uppercase tracking-wider text-muted-foreground">APACHE</span>
                <span class="px-3 py-1 rounded-md border border-border-strong bg-background-card/20 text-[11px] font-mono uppercase tracking-wider text-muted-foreground">MAC</span>
            </div>

            <!-- MODE PHOTO : Matériel & Prestations Visuelles -->
            <div class="hidden [html[data-mode='photo']_&]:flex flex-col gap-6">
                <div class="flex flex-wrap gap-2.5">
                    <span class="px-3.5 py-1.5 rounded-full border border-amber-400/30 bg-amber-950/20 text-xs font-sans text-amber-200">Portrait &amp; Street Photography</span>
                    <span class="px-3.5 py-1.5 rounded-full border border-amber-400/30 bg-amber-950/20 text-xs font-sans text-amber-200">Color Grading</span>
                    <span class="px-3.5 py-1.5 rounded-full border border-amber-400/30 bg-amber-950/20 text-xs font-sans text-amber-200">Montage vidéo dynamique</span>
                    <span class="px-3.5 py-1.5 rounded-full border border-amber-400/30 bg-amber-950/20 text-xs font-sans text-amber-200">Retouche photo avancée</span>
                    <span class="px-3.5 py-1.5 rounded-full border border-amber-400/30 bg-amber-950/20 text-xs font-sans text-amber-200">Création de contenu réseaux</span>
                </div>

                <div class="flex flex-wrap gap-2 pt-2">
                    <span class="px-3 py-1 rounded-md border border-amber-400/20 bg-amber-950/10 text-[11px] font-mono uppercase tracking-wider text-amber-400/80">LIGHTROOM</span>
                    <span class="px-3 py-1 rounded-md border border-amber-400/20 bg-amber-950/10 text-[11px] font-mono uppercase tracking-wider text-amber-400/80">DAVINCI RESOLVE</span>
                    <span class="px-3 py-1 rounded-md border border-amber-400/20 bg-amber-950/10 text-[11px] font-mono uppercase tracking-wider text-amber-400/80">PHOTOSHOP</span>
                    <span class="px-3 py-1 rounded-md border border-amber-400/20 bg-amber-950/10 text-[11px] font-mono uppercase tracking-wider text-amber-400/80">AFFINITY BY CANVA</span>
                    <span class="px-3 py-1 rounded-md border border-amber-400/20 bg-amber-950/10 text-[11px] font-mono uppercase tracking-wider text-amber-400/80">BOÎTIER PANASONIC</span>
                </div>
            </div>

        </div>

        <!-- Colonne Droite : Langues -->
        <div class="lg:col-span-5 flex flex-col gap-6 pt-2">

            <span class="text-xs font-mono tracking-widest text-muted-foreground uppercase">
                LANGUES
            </span>

            <div class="flex flex-col gap-6">

                <!-- Français -->
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between items-center text-sm font-sans">
                        <span class="text-foreground">Français</span>
                        <span class="text-xs font-mono text-muted-foreground">Natif</span>
                    </div>
                    <div class="w-full h-px bg-border-strong relative">
                        <div class="absolute left-0 top-0 bottom-0 w-full bg-accent [html[data-mode='photo']_&]:bg-amber-400"></div>
                    </div>
                </div>

                <!-- Espagnol -->
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between items-center text-sm font-sans">
                        <span class="text-foreground">Espagnol</span>
                        <span class="text-xs font-mono text-muted-foreground">B2+ / C1</span>
                    </div>
                    <div class="w-full h-px bg-border-strong relative">
                        <div class="absolute left-0 top-0 bottom-0 w-[85%] bg-accent [html[data-mode='photo']_&]:bg-amber-400"></div>
                    </div>
                </div>

                <!-- Anglais -->
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between items-center text-sm font-sans">
                        <span class="text-foreground">Anglais</span>
                        <span class="text-xs font-mono text-muted-foreground">B1</span>
                    </div>
                    <div class="w-full h-px bg-border-strong relative">
                        <div class="absolute left-0 top-0 bottom-0 w-[60%] bg-accent [html[data-mode='photo']_&]:bg-amber-400"></div>
                    </div>
                </div>

            </div>

        </div>

    </div>

</section>