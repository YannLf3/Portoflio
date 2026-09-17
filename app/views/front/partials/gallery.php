<section class="mt-24 flex flex-col gap-12 w-full">

    <!-- En-tête de section -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6">
        <div class="flex flex-col items-start gap-3">
            <span class="text-xs font-mono tracking-widest text-accent uppercase">
                04 — L'objectif
            </span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-serif italic font-light tracking-tight text-foreground">
                Regarder autrement
            </h2>
        </div>

        <!-- Description adaptative selon le mode -->
        <p class="text-xs sm:text-sm text-muted-foreground max-w-xs leading-relaxed">
            <span class="mode-dev-content">Bascule en mode Photographe pour révéler la sélection.</span>
            <span class="mode-photo-content">Une sélection de photographies — portraits, scènes de rue et paysages.</span>
        </p>
    </div>

    <!-- Galerie Bento (3 colonnes sur desktop, 1 sur mobile) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">

        <!-- Photo 1 : Verticale (Grand Portrait) -->
        <div class="group relative overflow-hidden rounded-2xl bg-background-card aspect-3/4 lg:row-span-2">
            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=800&q=80"
                 alt="Portrait lumière naturelle"
                 class="h-full w-full object-cover transition-all duration-700 ease-out group-hover:scale-105 grayscale [html[data-mode='photo']_&]:grayscale-0"
                 loading="lazy">

            <!-- Overlay dégradé + Texte d'infos au survol -->
            <div class="absolute inset-0 bg-linear-to-t from-background/90 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 flex items-end p-6">
                <span class="text-[11px] font-mono tracking-widest uppercase text-white/90">
                    PORTRAIT · LUMIÈRE NATURELLE
                </span>
            </div>
        </div>

        <!-- Photo 2 : Horizontale (Paysage) -->
        <div class="group relative overflow-hidden rounded-2xl bg-background-card aspect-16/10">
            <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80"
                 alt="Paysage heure dorée"
                 class="h-full w-full object-cover transition-all duration-700 ease-out group-hover:scale-105 grayscale [html[data-mode='photo']_&]:grayscale-0"
                 loading="lazy">

            <div class="absolute inset-0 bg-linear-to-t from-background/90 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 flex items-end p-6">
                <span class="text-[11px] font-mono tracking-widest uppercase text-white/90">
                    PAYSAGE · HEURE DORÉE
                </span>
            </div>
        </div>

        <!-- Photo 3 : Carrée (Scène de rue) -->
        <div class="group relative overflow-hidden rounded-2xl bg-background-card aspect-square">
            <img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=800&q=80"
                 alt="Portrait de nuit"
                 class="h-full w-full object-cover transition-all duration-700 ease-out group-hover:scale-105 grayscale [html[data-mode='photo']_&]:grayscale-0"
                 loading="lazy">

            <div class="absolute inset-0 bg-linear-to-t from-background/90 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 flex items-end p-6">
                <span class="text-[11px] font-mono tracking-widest uppercase text-white/90">
                    RUE · NUIT
                </span>
            </div>
        </div>

        <!-- Photo 4 : Carrée (Candid) -->
        <div class="group relative overflow-hidden rounded-2xl bg-background-card aspect-square">
            <img src="https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=800&q=80"
                 alt="Expression portrait"
                 class="h-full w-full object-cover transition-all duration-700 ease-out group-hover:scale-105 grayscale [html[data-mode='photo']_&]:grayscale-0"
                 loading="lazy">

            <div class="absolute inset-0 bg-linear-to-t from-background/90 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 flex items-end p-6">
                <span class="text-[11px] font-mono tracking-widest uppercase text-white/90">
                    PORTRAIT · STUDIO
                </span>
            </div>
        </div>

        <!-- Photo 5 : Horizontale / Close-up -->
        <div class="group relative overflow-hidden rounded-2xl bg-background-card aspect-16/10">
            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=800&q=80"
                 alt="Portrait masculin cinématique"
                 class="h-full w-full object-cover transition-all duration-700 ease-out group-hover:scale-105 grayscale [html[data-mode='photo']_&]:grayscale-0"
                 loading="lazy">

            <div class="absolute inset-0 bg-linear-to-t from-background/90 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 flex items-end p-6">
                <span class="text-[11px] font-mono tracking-widest uppercase text-white/90">
                    CINÉMATIQUE · OMBRE & LUMIÈRE
                </span>
            </div>
        </div>

    </div>

</section>