<section class="mt-24 w-full flex flex-col gap-12">

    <!-- En-tête de section -->
    <div class="flex flex-col items-start gap-2">
        <span class="text-xs font-mono tracking-widest text-accent uppercase">
            02 — Compétences & Outils
        </span>
    </div>

    <!-- Bandeau défilant infini (Marquee) -->
    <div class="w-full overflow-hidden border-y border-border-strong py-4 relative group">
        <!-- Masque de dégradé latéral pour adoucir l'apparition/disparition -->
        <div class="absolute left-0 top-0 bottom-0 w-12 bg-linear-to-r from-background to-transparent z-10 pointer-events-none"></div>
        <div class="absolute right-0 top-0 bottom-0 w-12 bg-linear-to-l from-background to-transparent z-10 pointer-events-none"></div>

        <div class="flex whitespace-nowrap animate-marquee gap-8 items-center text-xl sm:text-2xl font-serif italic text-foreground/80">
            <!-- Bloc 1 -->
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
            <!-- Duplication exacte du bloc pour créer la boucle infinie sans saut -->
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
    </div>

    <!-- Contenu principal : Grille Tags + Langues -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start">

        <!-- Colonne Gauche : Tags Compétences & Outils -->
        <div class="lg:col-span-7 flex flex-col gap-6">

            <!-- Groupe 1 : Langages & Frameworks -->
            <div class="flex flex-wrap gap-2.5">
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">HTML / CSS</span>
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">JavaScript</span>
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">React</span>
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">PHP</span>
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">SQL</span>
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">Python</span>
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">Sass</span>
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">BEM CSS</span>
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">Tailwind</span>
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">Sass</span>
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">Bootstrap</span>
                <span class="px-3.5 py-1.5 rounded-full border border-border-strong bg-background-card/40 text-xs font-sans text-foreground">Git / Github</span>
            </div>

            <!-- Groupe 2 : Outils & Environnement -->
            <div class="flex flex-wrap gap-2 pt-2">
                <span class="px-3 py-1 rounded-md border border-border-strong bg-background-card/20 text-[11px] font-mono uppercase tracking-wider text-muted-foreground">VS CODE</span>
                <span class="px-3 py-1 rounded-md border border-border-strong bg-background-card/20 text-[11px] font-mono uppercase tracking-wider text-muted-foreground">JETBRAINS IDE</span>
                <span class="px-3 py-1 rounded-md border border-border-strong bg-background-card/20 text-[11px] font-mono uppercase tracking-wider text-muted-foreground">FIGMA</span>
                <span class="px-3 py-1 rounded-md border border-border-strong bg-background-card/20 text-[11px] font-mono uppercase tracking-wider text-muted-foreground">SHELL</span>
                <span class="px-3 py-1 rounded-md border border-border-strong bg-background-card/20 text-[11px] font-mono uppercase tracking-wider text-muted-foreground">ADOBE</span>
                <span class="px-3 py-1 rounded-md border border-border-strong bg-background-card/20 text-[11px] font-mono uppercase tracking-wider text-muted-foreground">AFFINITY</span>
                <span class="px-3 py-1 rounded-md border border-border-strong bg-background-card/20 text-[11px] font-mono uppercase tracking-wider text-muted-foreground">APACHE</span>
                <span class="px-3 py-1 rounded-md border border-border-strong bg-background-card/20 text-[11px] font-mono uppercase tracking-wider text-muted-foreground">MAC</span>
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
                        <div class="absolute left-0 top-0 bottom-0 w-full bg-accent"></div>
                    </div>
                </div>

                <!-- Espagnol -->
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between items-center text-sm font-sans">
                        <span class="text-foreground">Espagnol</span>
                        <span class="text-xs font-mono text-muted-foreground">B2+ / C1</span>
                    </div>
                    <div class="w-full h-px bg-border-strong relative">
                        <div class="absolute left-0 top-0 bottom-0 w-[85%] bg-accent"></div>
                    </div>
                </div>

                <!-- Anglais -->
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between items-center text-sm font-sans">
                        <span class="text-foreground">Anglais</span>
                        <span class="text-xs font-mono text-muted-foreground">B1</span>
                    </div>
                    <div class="w-full h-px bg-border-strong relative">
                        <div class="absolute left-0 top-0 bottom-0 w-[60%] bg-accent"></div>
                    </div>
                </div>

            </div>

        </div>

    </div>

</section>