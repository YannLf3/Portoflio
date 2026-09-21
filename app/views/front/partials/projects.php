<section id="projects" class="mt-24 flex flex-col gap-12 w-full">

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

        <!-- Boutons de filtrage dynamiques (BDD + JS) -->
        <?php
        $cat1 = $content['project_cat_1'] ?? 'Scolaires';
        $cat2 = $content['project_cat_2'] ?? 'Personnels';
        $cat3 = $content['project_cat_3'] ?? 'Professionnels';

        $categoriesFilter = array_filter([$cat1, $cat2, $cat3]);
        ?>
        <div class="flex flex-wrap gap-2 sm:gap-3" id="project-filters">
            <button type="button"
                    data-filter="all"
                    class="filter-btn active px-4 py-1.5 rounded-full bg-accent text-background text-xs font-mono font-medium tracking-wider uppercase transition-all duration-300">
                Tous
            </button>

            <?php foreach ($categoriesFilter as $catName): ?>
                <button type="button"
                        data-filter="<?= htmlspecialchars(mb_strtolower(trim($catName))) ?>"
                        class="filter-btn px-4 py-1.5 rounded-full border border-border-strong text-muted-foreground text-xs font-mono tracking-wider uppercase hover:border-accent hover:text-foreground transition-all duration-300">
                    <?= htmlspecialchars(trim($catName)) ?>
                </button>
            <?php endforeach; ?>
        </div>

    </div>

    <!-- Grille des Projets (Dynamique BDD) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:grid-cols-3 lg:gap-8" id="projects-grid">

        <?php
        $projectsList = $content['projects'] ?? [
                [
                        'title' => 'Barcelona Feel Good Tours',
                        'meta' => 'Client · Full-Stack',
                        'category' => 'Professionnel',
                        'description' => 'Création d\'une plateforme web multilingue avec espace d\'administration sur-mesure pour les modifications du site partie client.',
                        'image' => '/assets/images/projects/barcelona.webp',
                        'link' => 'https://barcelonafeelgoodtours.eu/',
                        'tags' => 'PHP / MYSQL, HTML / CSS / JS'
                ],
                [
                        'title' => 'Design cinéma landing page',
                        'meta' => 'Scolaire · BUT1',
                        'category' => 'Scolaire',
                        'description' => 'Conception d\'un wireframe haute fidélité pour une landing page de cinéma sous Figma.',
                        'image' => '/assets/images/projects/landingpageCinema.webp',
                        'link' => 'https://www.figma.com/design/qxHqC61TU3nQUqv73qYdEd/LE-FLOHIC-YANN---Science-Fiction?node-id=0-1&t=wcLPSjIyGeyXmyKf-1',
                        'tags' => 'Figma Design, Auto-layout - Nommage propre - Styles'
                ],
                [
                        'title' => 'Design d\'une première version de mon portfolio',
                        'meta' => 'Scolaire · BUT1',
                        'category' => 'Scolaire',
                        'description' => 'Développement de maquettes d\'une première version de mon portfolio dans le cadre des situations d\'apprentissage et d\'évaluation du BUT MMI.',
                        'image' => '/assets/images/projects/portfolioV1.webp',
                        'link' => 'https://www.figma.com/design/EBKSSO41uKu8MKjqOK7Fgk/LE-FLOHIC-Yann?node-id=0-1&t=ajspR0CKdX7v391r-1',
                        'tags' => 'Figma Design, Création numérique'
                ],
                [
                        'title' => 'Intégration HTML CSS d\'une maquette',
                        'meta' => 'Scolaire · BUT1',
                        'category' => 'Scolaire',
                        'description' => 'Intégration web HTML CSS JS avec templating d\'une maquette Figma. Architecture et nomenclature propre avec BEM CSS.',
                        'image' => '/assets/images/projects/sae105.webp',
                        'link' => 'https://leflohic-sae105.mmi-limoges.fr/',
                        'tags' => 'Développement web, HTML / CSS / JS'
                ],
                [
                        'title' => 'Wordpress landing page fictive RockEnSeine',
                        'meta' => 'Scolaire · BUT1',
                        'category' => 'Scolaire',
                        'description' => 'Création d\'une maquette Figma, puis développement d\'un site landing page fictif pour la promotion du festival RockEnSeine.',
                        'image' => '/assets/images/projects/sae202.webp',
                        'link' => 'https://github.com/YannLf3/SAE202_Wordpress',
                        'tags' => 'Développement web & Création graphique, Wordpress & Figma'
                ],
                [
                        'title' => 'Space Invaders Python (NSI)',
                        'meta' => 'Scolaire · NSI Terminal',
                        'category' => 'Scolaire',
                        'description' => 'Création d\'un jeu type Space Invaders en Python.',
                        'image' => '/assets/images/projects/spaceInvaders.webp',
                        'link' => 'https://github.com/YannLf3/NSI_SpaceInvaders-game',
                        'tags' => 'Développement Python, Space Invaders'
                ]
        ];

        if (!empty($projectsList)):
            foreach ($projectsList as $index => $project):
                $num = sprintf('%02d', $index + 1);
                $linkUrl = !empty($project['link']) ? $project['link'] : '#';
                $isExternal = str_starts_with($linkUrl, 'http');
                $imgUrl = !empty($project['image']) ? $project['image'] : '/assets/images/projects/barcelona.webp';

                // Normalisation de la catégorie pour le filtrage JS
                $projectCat = mb_strtolower(trim($project['category'] ?? ''));
                ?>
                <a href="<?= htmlspecialchars($linkUrl) ?>"
                        <?= $isExternal ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
                   data-category="<?= htmlspecialchars($projectCat) ?>"
                   class="project-card group relative flex flex-col rounded-2xl border border-border-strong bg-background-card overflow-hidden transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl hover:border-accent/30 z-10 lg:max-w-130">

                    <!-- Haut : Image & Badges -->
                    <div class="relative h-56 sm:h-64 w-full bg-border-strong/20">
                        <img src="<?= htmlspecialchars($imgUrl) ?>"
                             alt="<?= htmlspecialchars($project['title'] ?? 'Projet') ?>"
                             class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                             loading="lazy">

                        <!-- Dégradé pour lisibilité des textes par-dessus l'image -->
                        <div class="absolute inset-0 bg-gradient-to-b from-background/80 via-transparent to-transparent"></div>

                        <!-- Numéro dynamique -->
                        <span class="absolute top-5 left-6 text-4xl font-serif italic text-white font-light"><?= $num ?></span>

                        <!-- Badge Meta Haut Droite -->
                        <?php if (!empty($project['meta'])): ?>
                            <span class="absolute top-6 right-6 px-3 py-1 rounded-full border border-white/20 bg-background/50 backdrop-blur-md text-[10px] font-mono tracking-widest text-white uppercase">
                                <?= htmlspecialchars($project['meta']) ?>
                            </span>
                        <?php endif; ?>

                        <!-- Pillule Catégorie (à cheval sur l'image et le texte) -->
                        <?php if (!empty($project['category'])): ?>
                            <span class="absolute -bottom-3 left-6 z-20 px-3 py-1 rounded-full bg-accent text-background text-[11px] font-bold tracking-widest uppercase">
                                <?= htmlspecialchars($project['category']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Bas : Contenu texte -->
                    <div class="flex flex-col gap-4 p-6 pt-8 relative grow">

                        <div class="flex items-center justify-between gap-4">
                            <h3 class="text-xl sm:text-2xl font-medium text-foreground">
                                <?= htmlspecialchars($project['title'] ?? '') ?>
                            </h3>

                            <!-- Flèche animée -->
                            <div class="w-10 h-10 shrink-0 rounded-full border border-border-strong flex items-center justify-center transition-all duration-300 group-hover:bg-accent group-hover:border-accent text-muted-foreground group-hover:text-background">
                                <svg class="w-5 h-5 transition-transform duration-300 group-hover:-rotate-45"
                                     fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </div>

                        <p class="text-sm text-muted-foreground leading-relaxed">
                            <?= htmlspecialchars($project['description'] ?? '') ?>
                        </p>

                        <!-- Stack Technique -->
                        <?php if (!empty($project['tags'])): ?>
                            <div class="flex flex-wrap gap-2 mt-auto pt-4">
                                <?php
                                $tags = array_map('trim', explode(',', $project['tags']));
                                foreach ($tags as $tag):
                                    if (!empty($tag)):
                                        ?>
                                        <span class="px-2.5 py-1 rounded bg-background border border-border-strong text-[10px] font-mono tracking-wider text-accent uppercase">
                                            <?= htmlspecialchars($tag) ?>
                                        </span>
                                    <?php
                                    endif;
                                endforeach;
                                ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </a>
            <?php
            endforeach;
        endif;
        ?>

    </div>

</section>

<!-- Script JS de Filtrage des Projets -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const filterBtns = document.querySelectorAll('#project-filters .filter-btn');
        const projectCards = document.querySelectorAll('#projects-grid .project-card');

        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const filterValue = btn.getAttribute('data-filter');

                // Mise à jour de l'état visuel des boutons
                filterBtns.forEach(b => {
                    b.classList.remove('bg-accent', 'text-background', 'font-medium');
                    b.classList.add('border', 'border-border-strong', 'text-muted-foreground');
                });

                btn.classList.remove('border', 'border-border-strong', 'text-muted-foreground');
                btn.classList.add('bg-accent', 'text-background', 'font-medium');

                // Filtrage des cartes
                projectCards.forEach(card => {
                    const cardCategory = card.getAttribute('data-category') || '';

                    // Si 'all' ou si la catégorie correspond (inclut partiellement la chaîne)
                    if (filterValue === 'all' || cardCategory.includes(filterValue) || filterValue.includes(cardCategory)) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    });
</script>