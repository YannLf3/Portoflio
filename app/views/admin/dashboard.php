<!DOCTYPE html>
<html lang="fr" data-mode="dev" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Back-Office Admin') ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="/js/admin.js" defer></script>
</head>
<body class="font-sans bg-background text-foreground min-h-screen flex flex-col">

<!-- HEADER ADMIN -->
<header class="sticky top-0 z-50 bg-background/95 backdrop-blur-md border-b border-border-strong px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex flex-col">
        <span class="text-xs font-mono tracking-widest text-accent uppercase">YLF.36 — Admin</span>
        <h1 class="text-2xl sm:text-3xl font-serif mt-0.5">Contenu du site</h1>
    </div>

    <div class="flex items-center gap-2 sm:gap-3 text-xs font-mono">
        <a href="/" target="_blank"
           class="px-3 sm:px-4 py-2 rounded-full border border-border-strong text-muted-foreground hover:text-foreground hover:bg-background-card transition-all">
            Voir le site
        </a>
        <a href="/logout"
           class="px-3 sm:px-4 py-2 rounded-full border border-border-strong text-muted-foreground hover:text-foreground hover:bg-background-card transition-all">
            Déconnexion
        </a>
        <button type="submit" form="admin-form"
                class="px-4 sm:px-5 py-2 rounded-full bg-accent text-accent-contrast tracking-wide hover:scale-105 active:scale-95 transition-all font-bold">
            Enregistrer
        </button>
    </div>
</header>

<!-- NAVIGATION DES ONGLETS -->
<nav class="px-4 sm:px-6 py-4 border-b border-border-strong overflow-x-auto whitespace-nowrap scrollbar-hide">
    <div class="flex gap-2 font-mono text-xs uppercase tracking-wider" id="admin-tabs">
        <button type="button"
                class="tab-btn active px-4 py-2 rounded-full border border-accent bg-accent/10 text-accent transition-all"
                data-target="dispo">
            Disponibilité &amp; Hero
        </button>
        <button type="button"
                class="tab-btn px-4 py-2 rounded-full border border-border-strong text-muted-foreground hover:border-muted-foreground transition-all"
                data-target="parcours">
            Parcours
        </button>
        <button type="button"
                class="tab-btn px-4 py-2 rounded-full border border-border-strong text-muted-foreground hover:border-muted-foreground transition-all"
                data-target="skills">
            Compétences
        </button>
        <button type="button"
                class="tab-btn px-4 py-2 rounded-full border border-border-strong text-muted-foreground hover:border-muted-foreground transition-all"
                data-target="projects">
            Projets
        </button>
        <button type="button"
                class="tab-btn px-4 py-2 rounded-full border border-border-strong text-muted-foreground hover:border-muted-foreground transition-all"
                data-target="galerie">
            Galerie Photo
        </button>
        <button type="button"
                class="tab-btn px-4 py-2 rounded-full border border-border-strong text-muted-foreground hover:border-muted-foreground transition-all"
                data-target="contact">
            Contact
        </button>
        <button type="button"
                class="tab-btn px-4 py-2 rounded-full border border-border-strong text-muted-foreground hover:border-muted-foreground transition-all"
                data-target="lab">
            Lab / Veille
        </button>
    </div>
</nav>

<!-- FORMULAIRE GLOBAL -->
<main class="flex-grow p-4 sm:p-6 max-w-5xl mx-auto w-full">
    <form id="admin-form" action="/admin/save" method="POST" enctype="multipart/form-data" class="flex flex-col gap-12">

        <!-- ========================================== -->
        <!-- 1. ONGLET : DISPONIBILITÉ & HERO           -->
        <!-- ========================================== -->
        <section id="dispo" class="tab-content flex flex-col gap-8">
            <div class="border border-border-strong bg-background-2/20 p-5 sm:p-6 rounded-xl flex flex-col gap-6">
                <h2 class="font-mono text-sm text-accent">En-tête &amp; Textes d'accroche</h2>

                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-mono tracking-widest uppercase text-muted-foreground">Badge de
                        disponibilité</label>
                    <input type="text" name="dispo_text"
                           value="<?= htmlspecialchars($content['dispo_text'] ?? 'Disponible pour un stage · 8-12 semaines') ?>"
                           class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-mono tracking-widest uppercase text-muted-foreground">Prénom
                            &amp; Nom (Titre Hero)</label>
                        <input type="text" name="hero_name"
                               value="<?= htmlspecialchars($content['hero_name'] ?? 'Yann Le Flohic') ?>"
                               class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-mono tracking-widest uppercase text-muted-foreground">Avatar /
                            Image Memoji (URL ou Upload)</label>
                        <div class="flex flex-col gap-3">
                            <input type="text" name="hero_avatar"
                                   value="<?= htmlspecialchars($content['hero_avatar'] ?? '') ?>"
                                   placeholder="URL de l'image..."
                                   class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                            <input type="file" name="hero_avatar_file" accept="image/*"
                                   class="w-full text-xs text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-accent/10 file:text-accent hover:file:bg-accent/20 cursor-pointer">
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-mono tracking-widest uppercase text-muted-foreground">Accroche
                        principale (Mode Dev)</label>
                    <textarea name="hero_dev" rows="3"
                              class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none resize-y"><?= htmlspecialchars($content['hero_dev'] ?? 'Développeur web full-stack — je conçois et code des interfaces sur-mesure, du front au back, jusqu\'à l\'hébergement.') ?></textarea>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-mono tracking-widest uppercase text-muted-foreground">Accroche
                        alternative (Mode Photographe)</label>
                    <textarea name="hero_photo" rows="3"
                              class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none resize-y"><?= htmlspecialchars($content['hero_photo'] ?? 'Photographe — je capture la lumière, la matière et l\'instant, entre portraits, rue et paysages cinématographiques.') ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-mono tracking-widest uppercase text-muted-foreground">Localisation</label>
                        <input type="text" name="location"
                               value="<?= htmlspecialchars($content['location'] ?? 'Châteauroux — FR') ?>"
                               class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-mono tracking-widest uppercase text-muted-foreground">Statut /
                            Études</label>
                        <input type="text" name="status"
                               value="<?= htmlspecialchars($content['status'] ?? '2° année MMI') ?>"
                               class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- 2. ONGLET : PARCOURS (TIMELINE)            -->
        <!-- ========================================== -->
        <section id="parcours" class="tab-content hidden flex-col gap-8">
            <div class="border border-border-strong bg-background-2/20 p-5 sm:p-6 rounded-xl flex flex-col gap-6">
                <div class="flex justify-between items-center border-b border-border-strong pb-4">
                    <h2 class="font-mono text-sm text-accent">Section Parcours &amp; Expériences</h2>
                    <button type="button" id="add-parcours-btn"
                            class="px-3 py-1 font-mono text-xs border border-border-strong rounded-lg hover:border-accent transition-colors">
                        + Ajouter une étape
                    </button>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Texte d'introduction de la
                        section</label>
                    <textarea name="parcours_intro" rows="3"
                              class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none resize-y"><?= htmlspecialchars($content['parcours_intro'] ?? 'Étudiant en deuxième année de BUT MMI et développeur freelance, je navigue entre le développement web sur-mesure et la photographie.') ?></textarea>
                </div>

                <!-- Liste des étapes -->
                <div id="parcours-container" class="flex flex-col gap-6">
                    <?php
                    $parcoursList = $parcours ?? [];
                    if (is_string($parcoursList)) {
                        $parcoursList = json_decode($parcoursList, true) ?? [];
                    }
                    if (!is_array($parcoursList) || empty($parcoursList)) {
                        $parcoursList = [
                                ['period' => '2025 — Actuel', 'type' => 'Formation', 'title' => 'BUT Métiers du Multimédia et de l\'Internet (MMI)', 'detail' => 'Parcours Web et dispositifs interactifs · IUT du Limousin, Limoges'],
                                ['period' => '2025 — Actuel', 'type' => 'Expérience', 'title' => 'Freelance développeur Full-stack', 'detail' => 'Développement web sur-mesure, gestion de projets clients et intégration UI/UX.']
                        ];
                    }
                    foreach ($parcoursList as $idx => $item):
                        ?>
                        <div class="parcours-item border border-border-strong bg-background/40 p-4 rounded-lg flex flex-col gap-4 relative">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-mono text-accent">Étape #<?= $idx + 1 ?></span>
                                <button type="button"
                                        class="remove-item-btn text-[10px] font-mono border border-border-strong px-2.5 py-1 rounded hover:text-red-400 hover:border-red-400">
                                    SUPPRIMER
                                </button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-[10px] font-mono text-muted-foreground uppercase">Période</label>
                                    <input type="text" name="parcours[<?= $idx ?>][period]"
                                           value="<?= htmlspecialchars($item['period'] ?? '') ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                                <div>
                                    <label class="text-[10px] font-mono text-muted-foreground uppercase">Type (Formation
                                        / Expérience)</label>
                                    <input type="text" name="parcours[<?= $idx ?>][type]"
                                           value="<?= htmlspecialchars($item['type'] ?? '') ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] font-mono text-muted-foreground uppercase">Titre</label>
                                <input type="text" name="parcours[<?= $idx ?>][title]"
                                       value="<?= htmlspecialchars($item['title'] ?? '') ?>"
                                       class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                            </div>
                            <div>
                                <label class="text-[10px] font-mono text-muted-foreground uppercase">Détail /
                                    Établissement</label>
                                <textarea name="parcours[<?= $idx ?>][detail]" rows="2"
                                          class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent resize-y"><?= htmlspecialchars($item['detail'] ?? '') ?></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- 3. ONGLET : COMPÉTENCES & LANGUES          -->
        <!-- ========================================== -->
        <section id="skills" class="tab-content hidden flex-col gap-8">
            <!-- Badges Tech -->
            <div class="border border-border-strong bg-background-2/20 p-5 sm:p-6 rounded-xl flex flex-col gap-6">
                <h2 class="font-mono text-sm text-accent">Compétences Techniques &amp; Outils</h2>

                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-mono tracking-widest uppercase text-muted-foreground">Stack Technique
                        (Séparés par des virgules)</label>
                    <input type="text" name="skills_tech"
                           value="<?= htmlspecialchars($content['skills_tech'] ?? 'HTML / CSS, JavaScript, React, PHP, SQL, Python, Sass, BEM CSS, Tailwind, Bootstrap, Git / Github') ?>"
                           class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-mono tracking-widest uppercase text-muted-foreground">Outils &amp;
                        Logiciels (Séparés par des virgules)</label>
                    <input type="text" name="skills_tools"
                           value="<?= htmlspecialchars($content['skills_tools'] ?? 'VS Code, Figma, Shell, Adobe, Affinity, Apache, Mac') ?>"
                           class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                </div>
            </div>

            <!-- Langues -->
            <div class="border border-border-strong bg-background-2/20 p-5 sm:p-6 rounded-xl flex flex-col gap-6">
                <div class="flex justify-between items-center border-b border-border-strong pb-4">
                    <h2 class="font-mono text-sm text-accent">Langues parlées</h2>
                    <button type="button" id="add-langue-btn"
                            class="px-3 py-1 font-mono text-xs border border-border-strong rounded-lg hover:border-accent transition-colors">
                        + Ajouter une langue
                    </button>
                </div>

                <div id="langues-container" class="flex flex-col gap-4">
                    <?php
                    $languesList = $langues ?? [];
                    if (is_string($languesList)) {
                        $languesList = json_decode($languesList, true) ?? [];
                    }
                    if (!is_array($languesList) || empty($languesList)) {
                        $languesList = [
                                ['name' => 'Français', 'level' => 'Natif', 'percent' => 100],
                                ['name' => 'Espagnol', 'level' => 'B2+ / C1', 'percent' => 82]
                        ];
                    }
                    foreach ($languesList as $idx => $lang):
                        ?>
                        <div class="langue-item border border-border-strong bg-background/40 p-4 rounded-lg flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full">
                                <div>
                                    <label class="text-[10px] font-mono text-muted-foreground uppercase">Langue</label>
                                    <input type="text" name="langues[<?= $idx ?>][name]"
                                           value="<?= htmlspecialchars($lang['name'] ?? '') ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                                <div>
                                    <label class="text-[10px] font-mono text-muted-foreground uppercase">Niveau</label>
                                    <input type="text" name="langues[<?= $idx ?>][level]"
                                           value="<?= htmlspecialchars($lang['level'] ?? '') ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                                <div>
                                    <label class="text-[10px] font-mono text-muted-foreground uppercase">Maîtrise
                                        (%)</label>
                                    <input type="number" name="langues[<?= $idx ?>][percent]"
                                           value="<?= htmlspecialchars($lang['percent'] ?? 80) ?>" min="0" max="100"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                            </div>
                            <button type="button"
                                    class="remove-item-btn text-[10px] font-mono border border-border-strong px-2 py-1.5 rounded hover:text-red-400 hover:border-red-400 self-end sm:self-center">
                                Suppr.
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- 4. ONGLET : PROJETS                        -->
        <!-- ========================================== -->
        <section id="projects" class="tab-content hidden flex-col gap-8">
            <div class="flex justify-between items-center border-b border-border-strong pb-4">
                <h2 class="font-mono text-sm text-accent">Gestion des Projets du Portfolio</h2>
                <button type="button" id="add-project-btn"
                        class="px-4 py-2 font-mono text-xs border border-border-strong rounded-lg hover:border-accent transition-colors">
                    + Ajouter un projet
                </button>
            </div>

            <!-- Filtres de projets -->
            <div class="border border-border-strong bg-background-2/20 p-4 rounded-xl flex flex-col gap-3">
                <label class="text-[10px] font-mono uppercase text-muted-foreground">Catégories de filtres
                    principaux</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <input type="text" name="project_cat_1"
                           value="<?= htmlspecialchars($content['project_cat_1'] ?? 'Scolaires') ?>"
                           class="bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none">
                    <input type="text" name="project_cat_2"
                           value="<?= htmlspecialchars($content['project_cat_2'] ?? 'Personnels') ?>"
                           class="bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none">
                    <input type="text" name="project_cat_3"
                           value="<?= htmlspecialchars($content['project_cat_3'] ?? 'Professionnels') ?>"
                           class="bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none">
                </div>
            </div>

            <div id="projects-container" class="flex flex-col gap-8">
                <?php
                $projectsList = $projects ?? [];
                if (is_string($projectsList)) {
                    $projectsList = json_decode($projectsList, true) ?? [];
                }
                if (is_array($projectsList) && !empty($projectsList)):
                    foreach ($projectsList as $index => $project):
                        ?>
                        <div class="project-item border border-border-strong bg-background-2/20 p-6 rounded-xl flex flex-col gap-6 relative">
                            <input type="hidden" name="projects[<?= $index ?>][id]" value="<?= $project['id'] ?? '' ?>">

                            <div class="flex justify-between items-center border-b border-border-strong pb-4">
                                <h3 class="font-serif text-xl text-accent italic">Projet
                                    #<?= sprintf('%02d', $index + 1) ?></h3>
                                <button type="button"
                                        class="remove-item-btn text-[10px] font-mono border border-border-strong px-3 py-1 rounded hover:text-red-400 hover:border-red-400 transition-colors">
                                    SUPPRIMER
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Titre du
                                        projet</label>
                                    <input type="text" name="projects[<?= $index ?>][title]"
                                           value="<?= htmlspecialchars($project['title'] ?? '') ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Meta (ex:
                                        Client · Full-stack)</label>
                                    <input type="text" name="projects[<?= $index ?>][meta]"
                                           value="<?= htmlspecialchars($project['meta'] ?? '') ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="text-[10px] font-mono uppercase text-muted-foreground">Description</label>
                                <textarea name="projects[<?= $index ?>][description]" rows="3"
                                          class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none resize-y"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Image (URL ou
                                        Upload)</label>
                                    <div class="flex flex-col gap-3">
                                        <input type="text" name="projects[<?= $index ?>][image]"
                                               value="<?= htmlspecialchars($project['image'] ?? '') ?>"
                                               placeholder="URL..."
                                               class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                                        <input type="file" name="projects_image_file[<?= $index ?>]" accept="image/*"
                                               class="w-full text-xs text-muted-foreground file:mr-2 file:py-1 file:px-3 file:rounded border file:border-0 file:bg-zinc-800 file:text-zinc-300 cursor-pointer">
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Lien du projet
                                        (URL)</label>
                                    <input type="text" name="projects[<?= $index ?>][link]"
                                           value="<?= htmlspecialchars($project['link'] ?? '') ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Catégorie de
                                        filtre</label>
                                    <input type="text" name="projects[<?= $index ?>][category]"
                                           value="<?= htmlspecialchars($project['category'] ?? 'Web') ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="text-[10px] font-mono uppercase text-muted-foreground">Tags techniques
                                    (séparés par des virgules)</label>
                                <input type="text" name="projects[<?= $index ?>][tags]"
                                       value="<?= htmlspecialchars($project['tags'] ?? '') ?>"
                                       class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                            </div>
                        </div>
                    <?php
                    endforeach;
                endif;
                ?>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- 5. ONGLET : GALERIE PHOTO (REGARDER AUTREMENT) -->
        <!-- ========================================== -->
        <section id="galerie" class="tab-content hidden flex-col gap-8">
            <div class="flex justify-between items-center border-b border-border-strong pb-4">
                <div>
                    <h2 class="font-mono text-sm text-accent">Galerie "Regarder Autrement"</h2>
                    <p class="text-xs text-muted-foreground mt-1">Gérez les photos, leurs légendes et leur format de
                        grille.</p>
                </div>
                <button type="button" id="add-photo-btn"
                        class="px-4 py-2 font-mono text-xs border border-border-strong rounded-lg hover:border-accent transition-colors">
                    + Ajouter une photo
                </button>
            </div>

            <div id="galerie-container" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php
                $photosList = $photos ?? [];
                if (is_string($photosList)) {
                    $photosList = json_decode($photosList, true) ?? [];
                }
                if (!is_array($photosList) || empty($photosList)) {
                    $photosList = [
                            ['url' => '/assets/images/gallery/gallery-6.webp', 'unsplash_id' => '', 'legend' => 'FLEURS ROSES · LUMIÈRE NATURELLE', 'alt' => 'Fleurs du jardin roses', 'format' => 'portrait'],
                            ['url' => '/assets/images/gallery/gallery-1.webp', 'unsplash_id' => '', 'legend' => 'PAYSAGE · MARMOTTES', 'alt' => 'Paysage Marmottes', 'format' => 'landscape']
                    ];
                }
                foreach ($photosList as $idx => $photo):
                    ?>
                    <div class="photo-item border border-border-strong bg-background-2/20 p-5 rounded-xl flex flex-col gap-4 relative">
                        <div class="flex justify-between items-center border-b border-border-strong pb-3">
                            <span class="text-xs font-mono text-accent">Photo #<?= $idx + 1 ?></span>
                            <button type="button"
                                    class="remove-item-btn text-[10px] font-mono border border-border-strong px-2.5 py-1 rounded hover:text-red-400 hover:border-red-400">
                                SUPPRIMER
                            </button>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-mono uppercase text-muted-foreground">URL de l'image
                                (Prioritaire ou Upload)</label>
                            <div class="flex flex-col gap-3">
                                <input type="text" name="photos[<?= $idx ?>][url]"
                                       value="<?= htmlspecialchars($photo['url'] ?? '') ?>"
                                       placeholder="URL..."
                                       class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                <input type="file" name="photos_url_file[<?= $idx ?>]" accept="image/*"
                                       class="w-full text-[10px] text-muted-foreground file:mr-2 file:py-1 file:px-2 file:rounded border file:border-0 file:bg-zinc-800 file:text-zinc-300 cursor-pointer">
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-mono uppercase text-muted-foreground">ID Unsplash
                                (Optionnel)</label>
                            <input type="text" name="photos[<?= $idx ?>][unsplash_id]"
                                   value="<?= htmlspecialchars($photo['unsplash_id'] ?? '') ?>"
                                   class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-mono uppercase text-muted-foreground">Légende (Au
                                survol)</label>
                            <input type="text" name="photos[<?= $idx ?>][legend]"
                                   value="<?= htmlspecialchars($photo['legend'] ?? '') ?>"
                                   class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-mono uppercase text-muted-foreground">Texte alternatif
                                (Alt)</label>
                            <input type="text" name="photos[<?= $idx ?>][alt]"
                                   value="<?= htmlspecialchars($photo['alt'] ?? '') ?>"
                                   class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-mono uppercase text-muted-foreground">Format
                                d'affichage</label>
                            <select name="photos[<?= $idx ?>][format]"
                                    class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                <option value="portrait" <?= ($photo['format'] ?? '') === 'portrait' ? 'selected' : '' ?>>
                                    Grand (Portrait / 2 rangées)
                                </option>
                                <option value="landscape" <?= ($photo['format'] ?? '') === 'landscape' ? 'selected' : '' ?>>
                                    Standard (Paysage)
                                </option>
                            </select>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- 6. ONGLET : CONTACT & SOCIAUX             -->
        <!-- ========================================== -->
        <section id="contact" class="tab-content hidden flex-col gap-8">
            <div class="border border-border-strong bg-background-2/20 p-5 sm:p-6 rounded-xl flex flex-col gap-6">
                <h2 class="font-mono text-sm text-accent">Titres &amp; Textes de Contact</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-mono uppercase text-muted-foreground">Titre de section</label>
                        <input type="text" name="contact_title"
                               value="<?= htmlspecialchars($content['contact_title'] ?? 'Un projet web ou photo ?') ?>"
                               class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-mono uppercase text-muted-foreground">Sous-titre / Appel à
                            l'action</label>
                        <input type="text" name="contact_subtitle"
                               value="<?= htmlspecialchars($content['contact_subtitle'] ?? 'Parlons-en.') ?>"
                               class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                    </div>
                </div>
            </div>

            <!-- Liens de contact -->
            <div class="border border-border-strong bg-background-2/20 p-5 sm:p-6 rounded-xl flex flex-col gap-6">
                <div class="flex justify-between items-center border-b border-border-strong pb-4">
                    <h2 class="font-mono text-sm text-accent">Coordonnées &amp; Liens Réseaux</h2>
                    <button type="button" id="add-contact-btn"
                            class="px-3 py-1 font-mono text-xs border border-border-strong rounded-lg hover:border-accent transition-colors">
                        + Ajouter un lien
                    </button>
                </div>

                <div id="contact-container" class="flex flex-col gap-4">
                    <?php
                    $contactsList = $contacts ?? [];
                    if (is_string($contactsList)) {
                        $contactsList = json_decode($contactsList, true) ?? [];
                    }
                    if (!is_array($contactsList) || empty($contactsList)) {
                        $contactsList = [
                                ['label' => 'Email', 'value' => 'yann.lfhc@icloud.com', 'href' => 'mailto:yann.lfhc@icloud.com'],
                                ['label' => 'Téléphone', 'value' => '+33 6 46 13 31 35', 'href' => 'tel:+33646133135'],
                                ['label' => 'GitHub', 'value' => 'github.com/YannLf3', 'href' => 'https://github.com/YannLf3'],
                                ['label' => 'LinkedIn', 'value' => 'in/yann-le-flohic', 'href' => 'https://linkedin.com/in/yann-le-flohic']
                        ];
                    }
                    foreach ($contactsList as $idx => $link):
                        ?>
                        <div class="contact-item border border-border-strong bg-background/40 p-4 rounded-lg flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full">
                                <div>
                                    <label class="text-[10px] font-mono text-muted-foreground uppercase">Libellé</label>
                                    <input type="text" name="contacts[<?= $idx ?>][label]"
                                           value="<?= htmlspecialchars($link['label'] ?? '') ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                                <div>
                                    <label class="text-[10px] font-mono text-muted-foreground uppercase">Valeur
                                        affichée</label>
                                    <input type="text" name="contacts[<?= $idx ?>][value]"
                                           value="<?= htmlspecialchars($link['value'] ?? '') ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                                <div>
                                    <label class="text-[10px] font-mono text-muted-foreground uppercase">Lien
                                        (href)</label>
                                    <input type="text" name="contacts[<?= $idx ?>][href]"
                                           value="<?= htmlspecialchars($link['href'] ?? '') ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                            </div>
                            <button type="button"
                                    class="remove-item-btn text-[10px] font-mono border border-border-strong px-2 py-1.5 rounded hover:text-red-400 hover:border-red-400 self-end sm:self-center">
                                Suppr.
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Centres d'intérêt -->
            <div class="border border-border-strong bg-background-2/20 p-5 sm:p-6 rounded-xl flex flex-col gap-4">
                <h2 class="font-mono text-sm text-accent">Centres d'intérêt (Footer)</h2>
                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Tags séparés par des
                        virgules</label>
                    <input type="text" name="interests"
                           value="<?= htmlspecialchars($content['interests'] ?? 'Open Source, Développement web, Photographie, Gaming, Badminton, Apple') ?>"
                           class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- 7. ONGLET : LAB & VEILLE (DYNAMIQUE)       -->
        <!-- ========================================== -->
        <section id="lab" class="tab-content hidden flex-col gap-12">

            <!-- SECTION SNIPPETS / EXPÉRIMENTATIONS -->
            <div class="flex flex-col gap-6 border border-border-strong bg-background-2/20 p-5 sm:p-6 rounded-xl">
                <div class="flex justify-between items-center border-b border-border-strong pb-4">
                    <div>
                        <h3 class="font-mono text-sm text-accent">Expérimentations &amp; Snippets Console</h3>
                        <p class="text-xs text-muted-foreground mt-1">Ajoutez ou modifiez vos snippets de code
                            interactifs.</p>
                    </div>
                    <button type="button" id="add-snippet-btn"
                            class="px-3 py-1 font-mono text-xs border border-border-strong rounded-lg hover:border-accent transition-colors">
                        + Ajouter un snippet
                    </button>
                </div>

                <div id="snippets-container" class="flex flex-col gap-8">
                    <?php
                    $snippetsList = $snippets ?? [];
                    if (is_string($snippetsList)) {
                        $snippetsList = json_decode($snippetsList, true) ?? [];
                    }
                    if (!is_array($snippetsList) || empty($snippetsList)) {
                        $snippetsList = [
                                [
                                        'title' => 'Glow Effect & Dynamic Border',
                                        'category' => 'CSS Modern',
                                        'badge' => 'Tested on Chrome / Safari',
                                        'html' => '<button class="glow-button">Survole-moi !</button>',
                                        'css' => ".glow-button {\n  background: #18181b;\n  border: 1px solid transparent;\n}",
                                        'js' => "// Pas de JavaScript requis"
                                ]
                        ];
                    }
                    foreach ($snippetsList as $idx => $snip):
                        ?>
                        <div class="snippet-item border border-border-strong bg-background/40 p-5 rounded-xl flex flex-col gap-6 relative">
                            <div class="flex justify-between items-center border-b border-border-strong pb-3">
                                <span class="text-xs font-mono text-accent">Snippet #<?= $idx + 1 ?></span>
                                <button type="button"
                                        class="remove-item-btn text-[10px] font-mono border border-border-strong px-2.5 py-1 rounded hover:text-red-400 hover:border-red-400">
                                    SUPPRIMER
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Titre de
                                        l'expérience</label>
                                    <input type="text" name="snippets[<?= $idx ?>][title]"
                                           value="<?= htmlspecialchars($snip['title'] ?? '') ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Tag / Catégorie
                                        (ex: CSS Modern)</label>
                                    <input type="text" name="snippets[<?= $idx ?>][category]"
                                           value="<?= htmlspecialchars($snip['category'] ?? '') ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Note bas de
                                        carte (ex: Performance 60 FPS)</label>
                                    <input type="text" name="snippets[<?= $idx ?>][badge]"
                                           value="<?= htmlspecialchars($snip['badge'] ?? '') ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground flex justify-between">
                                        <span>Code HTML</span><span class="text-orange-400">&lt;/&gt;</span>
                                    </label>
                                    <textarea name="snippets[<?= $idx ?>][html]" rows="6"
                                              class="w-full bg-zinc-950 border border-border-strong rounded p-3 font-mono text-xs text-zinc-300 focus:border-accent outline-none resize-y"><?= htmlspecialchars($snip['html'] ?? '') ?></textarea>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground flex justify-between">
                                        <span>Code CSS</span><span class="text-blue-400">#</span>
                                    </label>
                                    <textarea name="snippets[<?= $idx ?>][css]" rows="6"
                                              class="w-full bg-zinc-950 border border-border-strong rounded p-3 font-mono text-xs text-zinc-300 focus:border-accent outline-none resize-y"><?= htmlspecialchars($snip['css'] ?? '') ?></textarea>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground flex justify-between">
                                        <span>Code JS</span><span class="text-yellow-400">{}</span>
                                    </label>
                                    <textarea name="snippets[<?= $idx ?>][js]" rows="6"
                                              class="w-full bg-zinc-950 border border-border-strong rounded p-3 font-mono text-xs text-zinc-300 focus:border-accent outline-none resize-y"><?= htmlspecialchars($snip['js'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- SECTION ARTICLES DE BLOG -->
            <div class="flex flex-col gap-6 border border-border-strong bg-background-2/20 p-5 sm:p-6 rounded-xl">
                <div class="flex justify-between items-center border-b border-border-strong pb-4">
                    <div>
                        <h3 class="font-mono text-sm text-amber-400">Articles de Blog &amp; Notes d'expérience</h3>
                        <p class="text-xs text-muted-foreground mt-1">Rédigez et organisez vos publications.</p>
                    </div>
                    <button type="button" id="add-article-btn"
                            class="px-3 py-1 font-mono text-xs border border-border-strong rounded-lg hover:border-amber-400 transition-colors">
                        + Ajouter un article
                    </button>
                </div>

                <div id="articles-container" class="flex flex-col gap-8">
                    <?php
                    $articlesList = $articles ?? [];
                    if (is_string($articlesList)) {
                        $articlesList = json_decode($articlesList, true) ?? [];
                    }
                    if (!is_array($articlesList) || empty($articlesList)) {
                        $articlesList = [
                                [
                                        'title' => "Passer d'Illustrator à Affinity V3 : Mon retour d'expérience en vectoriel",
                                        'meta' => 'AFFINITY DESIGNER — 12 SEP 2026',
                                        'content' => "Après plusieurs mois d'utilisation intensive d'Affinity Designer V3 dans mes projets web..."
                                ]
                        ];
                    }
                    foreach ($articlesList as $idx => $art):
                        ?>
                        <div class="article-item border border-border-strong bg-background/40 p-5 rounded-xl flex flex-col gap-6 relative">
                            <div class="flex justify-between items-center border-b border-border-strong pb-3">
                                <span class="text-xs font-mono text-amber-400">Article #<?= $idx + 1 ?></span>
                                <button type="button"
                                        class="remove-item-btn text-[10px] font-mono border border-border-strong px-2.5 py-1 rounded hover:text-red-400 hover:border-red-400">
                                    SUPPRIMER
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Titre de
                                        l'article</label>
                                    <input type="text" name="articles[<?= $idx ?>][title]"
                                           value="<?= htmlspecialchars($art['title'] ?? '') ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-amber-400 outline-none">
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Catégorie &amp;
                                        Date (ex: MACOS / DEV — 02 SEP 2026)</label>
                                    <input type="text" name="articles[<?= $idx ?>][meta]"
                                           value="<?= htmlspecialchars($art['meta'] ?? '') ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-amber-400 outline-none">
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="text-[10px] font-mono uppercase text-muted-foreground">Contenu de
                                    l'article</label>
                                <textarea name="articles[<?= $idx ?>][content]" rows="6"
                                          class="w-full bg-background-2/50 border border-border-strong rounded-lg p-4 text-sm focus:border-amber-400 outline-none resize-y leading-relaxed"><?= htmlspecialchars($art['content'] ?? '') ?></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

    </form>
</main>

</body>
</html>