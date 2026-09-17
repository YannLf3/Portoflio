<section class="mt-24 flex flex-col gap-12 w-full">

    <!-- En-tête et Filtres -->
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-8">

        <div class="flex flex-col items-start gap-3">
            <span class="text-xs font-mono tracking-widest text-accent uppercase">
                03 — Projets sélectionnés
            </span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-serif font-normal tracking-tight text-foreground leading-tight">
                Mes projets
            </h2>
        </div>


        <!-- Boutons de filtrage
        <div class="flex flex-wrap gap-3">
            <button class="px-4 py-1.5 rounded-full bg-accent text-background text-xs font-mono font-medium tracking-wider uppercase transition-colors">
                Tous
            </button>
            <button class="px-4 py-1.5 rounded-full border border-border-strong text-muted-foreground text-xs font-mono tracking-wider uppercase hover:border-accent hover:text-foreground transition-colors">
                Scolaires
            </button>
            <button class="px-4 py-1.5 rounded-full border border-border-strong text-muted-foreground text-xs font-mono tracking-wider uppercase hover:border-accent hover:text-foreground transition-colors">
                Personnels
            </button>
            <button class="px-4 py-1.5 rounded-full border border-border-strong text-muted-foreground text-xs font-mono tracking-wider uppercase hover:border-accent hover:text-foreground transition-colors">
                Professionnels
            </button>
        </div> -->

    </div>

    <!-- Grille des Projets -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:grid-cols-3 lg:gap-8">

        <!-- Projet 1 : Client (Lien cliquable sur toute la carte) -->
        <a href="https://barcelonafeelgoodtours.eu/" target="_blank" rel="noopener noreferrer"
           class="group relative flex flex-col rounded-2xl border border-border-strong bg-background-card overflow-hidden transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl hover:border-accent/30 z-10 lg:max-w-130">

            <!-- Haut : Image & Badges -->
            <div class="relative h-56 sm:h-64 w-full bg-border-strong/20">
                <img src="/assets/images/projects/barcelona.webp" alt="Projet Barcelona Feel Good Tours"
                     class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                     loading="lazy">

                <!-- Dégradé pour lisibilité des textes par-dessus l'image -->
                <div class="absolute inset-0 bg-linear-to-b from-background/80 via-transparent to-transparent"></div>

                <!-- Numéro -->
                <span class="absolute top-5 left-6 text-4xl font-serif italic text-white font-light">01</span>

                <!-- Badge Catégorie Haut Droite -->
                <span class="absolute top-6 right-6 px-3 py-1 rounded-full border border-white/20 bg-background/50 backdrop-blur-md text-[10px] font-mono tracking-widest text-white uppercase">
                    Client · Full-Stack
                </span>

                <!-- Pillule Verte (à cheval sur l'image et le texte) -->
                <span class="absolute -bottom-3 left-6 z-20 px-3 py-1 rounded-full bg-accent text-background text-[11px] font-bold tracking-widest uppercase">
                    Professionnel
                </span>
            </div>

            <!-- Bas : Contenu texte -->
            <div class="flex flex-col gap-4 p-6 pt-8 relative grow">

                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-xl sm:text-2xl font-medium text-foreground">Barcelona Feel Good Tours</h3>

                    <!-- Flèche animée -->
                    <div class="w-10 h-10 shrink-0 rounded-full border border-border-strong flex items-center justify-center transition-all duration-300 group-hover:bg-accent group-hover:border-accent text-muted-foreground group-hover:text-background">
                        <svg class="w-5 h-5 transition-transform duration-300 group-hover:-rotate-45" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>

                <p class="text-sm text-muted-foreground leading-relaxed">
                    Création d'une plateforme web multilingue avec espace d'administration sur-mesure pour les
                    modifications du site partie client.
                </p>

                <!-- Stack Technique -->
                <div class="flex flex-wrap gap-2 mt-auto pt-4">
                    <span class="px-2.5 py-1 rounded bg-background border border-border-strong text-[10px] font-mono tracking-wider text-accent uppercase">PHP / MYSQL</span>
                    <span class="px-2.5 py-1 rounded bg-background border border-border-strong text-[10px] font-mono tracking-wider text-accent uppercase">HTML / CSS / JS</span>
                </div>
            </div>
        </a>

        <!-- Projet 2 : Scolaire -->
        <a href="https://www.figma.com/design/qxHqC61TU3nQUqv73qYdEd/LE-FLOHIC-YANN---Science-Fiction?node-id=0-1&t=wcLPSjIyGeyXmyKf-1"
           target="_blank" rel="noopener noreferrer"
           class="group relative flex flex-col rounded-2xl border border-border-strong bg-background-card overflow-hidden transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl hover:border-accent/30 z-10 lg:max-w-130">

            <div class="relative h-56 sm:h-64 w-full bg-border-strong/20">
                <img src="/assets/images/projects/landingpageCinema.webp" alt="Projet de cours"
                     class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                     loading="lazy">
                <div class="absolute inset-0 bg-linear-to-b from-background/80 via-transparent to-transparent"></div>

                <span class="absolute top-5 left-6 text-4xl font-serif italic text-white font-light">02</span>
                <span class="absolute top-6 right-6 px-3 py-1 rounded-full border border-white/20 bg-background/50 backdrop-blur-md text-[10px] font-mono tracking-widest text-white uppercase">
                    Scolaire · BUT1
                </span>
                <span class="absolute -bottom-3 left-6 z-20 px-3 py-1 rounded-full bg-accent text-background text-[11px] font-bold tracking-widest uppercase">
                    Scolaire
                </span>
            </div>

            <div class="flex flex-col gap-4 p-6 pt-8 relative grow">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-xl sm:text-2xl font-medium text-foreground">Design cinéma landing page</h3>
                    <div class="w-10 h-10 shrink-0 rounded-full border border-border-strong flex items-center justify-center transition-all duration-300 group-hover:bg-accent group-hover:border-accent text-muted-foreground group-hover:text-background">
                        <svg class="w-5 h-5 transition-transform duration-300 group-hover:-rotate-45" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>

                <p class="text-sm text-muted-foreground leading-relaxed">
                    Conception d'un wireframe haute fidélité pour une landing page de cinéma sous Figma.
                </p>

                <div class="flex flex-wrap gap-2 mt-auto pt-4">
                    <span class="px-2.5 py-1 rounded bg-background border border-border-strong text-[10px] font-mono tracking-wider text-accent uppercase">Figma Design</span>
                    <span class="px-2.5 py-1 rounded bg-background border border-border-strong text-[10px] font-mono tracking-wider text-accent uppercase">Auto-layout - Nommage propre - Styles</span>
                </div>
            </div>
        </a>

        <!-- Projet 3 : Scolaire -->
        <a href="https://www.figma.com/design/EBKSSO41uKu8MKjqOK7Fgk/LE-FLOHIC-Yann?node-id=0-1&t=ajspR0CKdX7v391r-1"
           target="_blank" rel="noopener noreferrer"
           class="group relative flex flex-col rounded-2xl border border-border-strong bg-background-card overflow-hidden transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl hover:border-accent/30 z-10 lg:max-w-130">

            <div class="relative h-56 sm:h-64 w-full bg-border-strong/20">
                <img src="/assets/images/projects/portfolioV1.webp" alt="Projets SAÉ MMI"
                     class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                     loading="lazy">
                <div class="absolute inset-0 bg-linear-to-b from-background/80 via-transparent to-transparent"></div>

                <span class="absolute top-5 left-6 text-4xl font-serif italic text-white font-light">03</span>
                <span class="absolute top-6 right-6 px-3 py-1 rounded-full border border-white/20 bg-background/50 backdrop-blur-md text-[10px] font-mono tracking-widest text-white uppercase">
                    Scolaire · BUT1
                </span>
                <span class="absolute -bottom-3 left-6 z-20 px-3 py-1 rounded-full bg-accent text-background text-[11px] font-bold tracking-widest uppercase">
                    Scolaire
                </span>
            </div>

            <div class="flex flex-col gap-4 p-6 pt-8 relative grow">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-xl sm:text-2xl font-medium text-foreground">Design d'une première version de mon
                        portfolio</h3>
                    <div class="w-10 h-10 shrink-0 rounded-full border border-border-strong flex items-center justify-center transition-all duration-300 group-hover:bg-accent group-hover:border-accent text-muted-foreground group-hover:text-background">
                        <svg class="w-5 h-5 transition-transform duration-300 group-hover:-rotate-45" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>

                <p class="text-sm text-muted-foreground leading-relaxed">
                    Développement de maquettes d'une première version de mon portfolio dans le cadre des situations
                    d'apprentissage et
                    d'évaluation du BUT MMI.
                </p>

                <div class="flex flex-wrap gap-2 mt-auto pt-4">
                    <span class="px-2.5 py-1 rounded bg-background border border-border-strong text-[10px] font-mono tracking-wider text-accent uppercase">Figma Design</span>
                    <span class="px-2.5 py-1 rounded bg-background border border-border-strong text-[10px] font-mono tracking-wider text-accent uppercase">Création numérique</span>
                </div>
            </div>
        </a>

        <!-- Projet 4 : Scolaire -->
        <a href="https://leflohic-sae105.mmi-limoges.fr/" target="_blank" rel="noopener noreferrer"
           class="group relative flex flex-col rounded-2xl border border-border-strong bg-background-card overflow-hidden transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl hover:border-accent/30 z-10 lg:max-w-130">

            <div class="relative h-56 sm:h-64 w-full bg-border-strong/20">
                <img src="/assets/images/projects/sae105.webp" alt="Projets SAÉ MMI"
                     class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                     loading="lazy">
                <div class="absolute inset-0 bg-linear-to-b from-background/80 via-transparent to-transparent"></div>

                <span class="absolute top-5 left-6 text-4xl font-serif italic text-white font-light">04</span>
                <span class="absolute top-6 right-6 px-3 py-1 rounded-full border border-white/20 bg-background/50 backdrop-blur-md text-[10px] font-mono tracking-widest text-white uppercase">
                    Scolaire · BUT1
                </span>
                <span class="absolute -bottom-3 left-6 z-20 px-3 py-1 rounded-full bg-accent text-background text-[11px] font-bold tracking-widest uppercase">
                    Scolaire
                </span>
            </div>

            <div class="flex flex-col gap-4 p-6 pt-8 relative grow">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-xl sm:text-2xl font-medium text-foreground">Intégration HTML CSS d'une maquette</h3>
                    <div class="w-10 h-10 shrink-0 rounded-full border border-border-strong flex items-center justify-center transition-all duration-300 group-hover:bg-accent group-hover:border-accent text-muted-foreground group-hover:text-background">
                        <svg class="w-5 h-5 transition-transform duration-300 group-hover:-rotate-45" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>

                <p class="text-sm text-muted-foreground leading-relaxed">
                    Intégration web HTML CSS JS avec templating d'une maquette Figma. Architecture et nomenclature
                    propre avec BEM CSS.
                </p>

                <div class="flex flex-wrap gap-2 mt-auto pt-4">
                    <span class="px-2.5 py-1 rounded bg-background border border-border-strong text-[10px] font-mono tracking-wider text-accent uppercase">Développement web</span>
                    <span class="px-2.5 py-1 rounded bg-background border border-border-strong text-[10px] font-mono tracking-wider text-accent uppercase">HTML / CSS / JS</span>
                </div>
            </div>
        </a>

        <!-- Projet 5 : Scolaire -->
        <a href="https://github.com/YannLf3/SAE202_Wordpress" target="_blank" rel="noopener noreferrer"
           class="group relative flex flex-col rounded-2xl border border-border-strong bg-background-card overflow-hidden transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl hover:border-accent/30 z-10 lg:max-w-130">

            <div class="relative h-56 sm:h-64 w-full bg-border-strong/20">
                <img src="/assets/images/projects/sae202.webp" alt="Projets SAÉ MMI"
                     class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                     loading="lazy">
                <div class="absolute inset-0 bg-linear-to-b from-background/80 via-transparent to-transparent"></div>

                <span class="absolute top-5 left-6 text-4xl font-serif italic text-white font-light">05</span>
                <span class="absolute top-6 right-6 px-3 py-1 rounded-full border border-white/20 bg-background/50 backdrop-blur-md text-[10px] font-mono tracking-widest text-white uppercase">
                    Scolaire · BUT1
                </span>
                <span class="absolute -bottom-3 left-6 z-20 px-3 py-1 rounded-full bg-accent text-background text-[11px] font-bold tracking-widest uppercase">
                    Scolaire
                </span>
            </div>

            <div class="flex flex-col gap-4 p-6 pt-8 relative grow">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-xl sm:text-2xl font-medium text-foreground">Wordpress landing page fictive
                        RockEnSeine</h3>
                    <div class="w-10 h-10 shrink-0 rounded-full border border-border-strong flex items-center justify-center transition-all duration-300 group-hover:bg-accent group-hover:border-accent text-muted-foreground group-hover:text-background">
                        <svg class="w-5 h-5 transition-transform duration-300 group-hover:-rotate-45" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>

                <p class="text-sm text-muted-foreground leading-relaxed">
                    Création d'une maquette Figma, puis développement d'un site landing page fictif pour la promotion du
                    festival RockEnSeine.
                </p>

                <div class="flex flex-wrap gap-2 mt-auto pt-4">
                    <span class="px-2.5 py-1 rounded bg-background border border-border-strong text-[10px] font-mono tracking-wider text-accent uppercase">Développement web & Création graphique</span>
                    <span class="px-2.5 py-1 rounded bg-background border border-border-strong text-[10px] font-mono tracking-wider text-accent uppercase">Wordpress & Figma</span>
                </div>
            </div>
        </a>

        <!-- Projet 6 : Scolaire -->
        <a href="https://github.com/YannLf3/NSI_SpaceInvaders-game" target="_blank" rel="noopener noreferrer"
           class="group relative flex flex-col rounded-2xl border border-border-strong bg-background-card overflow-hidden transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl hover:border-accent/30 z-10 lg:max-w-130">

            <div class="relative h-56 sm:h-64 w-full bg-border-strong/20">
                <img src="/assets/images/projects/spaceInvaders.webp" alt="Projets Terminal NSI"
                     class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                     loading="lazy">
                <div class="absolute inset-0 bg-linear-to-b from-background/80 via-transparent to-transparent"></div>

                <span class="absolute top-5 left-6 text-4xl font-serif italic text-white font-light">06</span>
                <span class="absolute top-6 right-6 px-3 py-1 rounded-full border border-white/20 bg-background/50 backdrop-blur-md text-[10px] font-mono tracking-widest text-white uppercase">
                    Scolaire · NSI Terminal
                </span>
                <span class="absolute -bottom-3 left-6 z-20 px-3 py-1 rounded-full bg-accent text-background text-[11px] font-bold tracking-widest uppercase">
                    Scolaire
                </span>
            </div>

            <div class="flex flex-col gap-4 p-6 pt-8 relative grow">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-xl sm:text-2xl font-medium text-foreground">Space Invaders Python (NSI)</h3>
                    <div class="w-10 h-10 shrink-0 rounded-full border border-border-strong flex items-center justify-center transition-all duration-300 group-hover:bg-accent group-hover:border-accent text-muted-foreground group-hover:text-background">
                        <svg class="w-5 h-5 transition-transform duration-300 group-hover:-rotate-45" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>

                <p class="text-sm text-muted-foreground leading-relaxed">
                    Création d'un jeu type Space Invaders en Python.
                </p>

                <div class="flex flex-wrap gap-2 mt-auto pt-4">
                    <span class="px-2.5 py-1 rounded bg-background border border-border-strong text-[10px] font-mono tracking-wider text-accent uppercase">Développement Python</span>
                    <span class="px-2.5 py-1 rounded bg-background border border-border-strong text-[10px] font-mono tracking-wider text-accent uppercase">Space Invaders</span>
                </div>
            </div>
        </a>

    </div>

</section>