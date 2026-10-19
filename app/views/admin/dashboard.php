<!DOCTYPE html>
<html lang="fr" data-mode="dev" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="/js/admin.js?v=20261014" defer></script>
</head>
<body class="font-body bg-background text-foreground min-h-screen flex flex-col">

<header class="sticky top-0 z-50 bg-background/95 backdrop-blur-md border-b border-border-strong px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex flex-col">
        <span class="text-xs font-mono tracking-widest text-accent uppercase">YLF.36 — Admin</span>
        <h1 class="text-2xl sm:text-3xl font-display mt-0.5">Contenu du site</h1>
    </div>

    <div class="flex items-center gap-2 sm:gap-3 text-xs font-mono">
        <a href="/" target="_blank"
           class="px-3 sm:px-4 py-2 rounded-full border border-border-strong text-muted-foreground hover:text-foreground hover:bg-background-card transition-all">
            Voir le site
        </a>
        <form action="/logout" method="POST" class="contents">
            <?= Csrf::field() ?>
            <button type="submit"
                    class="px-3 sm:px-4 py-2 rounded-full border border-border-strong text-muted-foreground hover:text-foreground hover:bg-background-card transition-all">
                Déconnexion
            </button>
        </form>
        <button type="submit" form="admin-form"
                class="px-4 sm:px-5 py-2 rounded-full bg-accent text-accent-contrast tracking-wide hover:scale-105 active:scale-95 transition-all font-bold">
            Enregistrer
        </button>
    </div>
</header>

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

<main class="flex-grow p-4 sm:p-6 max-w-5xl mx-auto w-full">

    <?php if ($flash !== null): ?>
        <div role="<?= $flash['role'] ?>"
             class="mb-8 rounded-lg border px-4 py-3 text-sm <?= $flash['class'] ?>">
            <p><?= htmlspecialchars($flash['message']) ?></p>
            <?php if ($flash['details'] !== []): ?>
                <ul class="mt-2 list-disc pl-5 text-xs opacity-90">
                    <?php array_walk($flash['details'], static function (string $detail): void { ?>
                        <li><?= htmlspecialchars($detail) ?></li>
                    <?php }); ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <form id="admin-form" action="/admin/save" method="POST" enctype="multipart/form-data" class="flex flex-col gap-12">
        <?= Csrf::field() ?>

        <section id="dispo" class="tab-content flex flex-col gap-8">
            <div class="border border-border-strong bg-background-2/20 p-5 sm:p-6 rounded-xl flex flex-col gap-6">
                <h2 class="font-display text-sm text-accent">En-tête &amp; Textes d'accroche</h2>

                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-mono tracking-widest uppercase text-muted-foreground">Badge de
                        disponibilité</label>
                    <input type="text" name="dispo_text"
                           value="<?= htmlspecialchars($fields['dispo_text']) ?>"
                           class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-mono tracking-widest uppercase text-muted-foreground">Prénom
                            &amp; Nom (Titre Hero)</label>
                        <input type="text" name="hero_name"
                               value="<?= htmlspecialchars($fields['hero_name']) ?>"
                               class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-mono tracking-widest uppercase text-muted-foreground">Avatar /
                            Image Memoji (URL ou Upload)</label>
                        <div class="flex flex-col gap-3">
                            <input type="text" name="hero_avatar"
                                   value="<?= htmlspecialchars($fields['hero_avatar']) ?>"
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
                              class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none resize-y"><?= htmlspecialchars($fields['hero_dev']) ?></textarea>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-mono tracking-widest uppercase text-muted-foreground">Accroche
                        alternative (Mode Photographe)</label>
                    <textarea name="hero_photo" rows="3"
                              class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none resize-y"><?= htmlspecialchars($fields['hero_photo']) ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-mono tracking-widest uppercase text-muted-foreground">Localisation</label>
                        <input type="text" name="location"
                               value="<?= htmlspecialchars($fields['location']) ?>"
                               class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-mono tracking-widest uppercase text-muted-foreground">Statut /
                            Études</label>
                        <input type="text" name="status"
                               value="<?= htmlspecialchars($fields['status']) ?>"
                               class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                    </div>
                </div>
            </div>
        </section>

        <section id="parcours" class="tab-content hidden flex-col gap-8">
            <div class="border border-border-strong bg-background-2/20 p-5 sm:p-6 rounded-xl flex flex-col gap-6">
                <div class="flex justify-between items-center border-b border-border-strong pb-4">
                    <h2 class="font-display text-sm text-accent">Section Parcours &amp; Expériences</h2>
                    <button type="button" id="add-parcours-btn"
                            class="px-3 py-1 font-mono text-xs border border-border-strong rounded-lg hover:border-accent transition-colors">
                        + Ajouter une étape
                    </button>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Texte d'introduction de la
                        section</label>
                    <textarea name="parcours_intro" rows="3"
                              class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none resize-y"><?= htmlspecialchars($fields['parcours_intro']) ?></textarea>
                </div>

                <div id="parcours-container" class="flex flex-col gap-6">
                    <?php array_walk($parcours, static function (array $item, int|string $idx): void { ?>
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
                                           value="<?= htmlspecialchars($item['period']) ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                                <div>
                                    <label class="text-[10px] font-mono text-muted-foreground uppercase">Type (Formation
                                        / Expérience)</label>
                                    <input type="text" name="parcours[<?= $idx ?>][type]"
                                           value="<?= htmlspecialchars($item['type']) ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] font-mono text-muted-foreground uppercase">Titre</label>
                                <input type="text" name="parcours[<?= $idx ?>][title]"
                                       value="<?= htmlspecialchars($item['title']) ?>"
                                       class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                            </div>
                            <div>
                                <label class="text-[10px] font-mono text-muted-foreground uppercase">Détail /
                                    Établissement</label>
                                <textarea name="parcours[<?= $idx ?>][detail]" rows="2"
                                          class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent resize-y"><?= htmlspecialchars($item['detail']) ?></textarea>
                            </div>
                        </div>
                    <?php }); ?>
                </div>
            </div>
        </section>

        <section id="skills" class="tab-content hidden flex-col gap-8">
            <div class="border border-border-strong bg-background-2/20 p-5 sm:p-6 rounded-xl flex flex-col gap-6">
                <h2 class="font-display text-sm text-accent">Compétences Techniques &amp; Outils</h2>

                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-mono tracking-widest uppercase text-muted-foreground">Stack Technique
                        (Séparés par des virgules)</label>
                    <input type="text" name="skills_tech"
                           value="<?= htmlspecialchars($fields['skills_tech']) ?>"
                           class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-mono tracking-widest uppercase text-muted-foreground">Outils &amp;
                        Logiciels (Séparés par des virgules)</label>
                    <input type="text" name="skills_tools"
                           value="<?= htmlspecialchars($fields['skills_tools']) ?>"
                           class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                </div>
            </div>

            <div class="border border-border-strong bg-background-2/20 p-5 sm:p-6 rounded-xl flex flex-col gap-6">
                <div class="flex justify-between items-center border-b border-border-strong pb-4">
                    <h2 class="font-display text-sm text-accent">Langues parlées</h2>
                    <button type="button" id="add-langue-btn"
                            class="px-3 py-1 font-mono text-xs border border-border-strong rounded-lg hover:border-accent transition-colors">
                        + Ajouter une langue
                    </button>
                </div>

                <div id="langues-container" class="flex flex-col gap-4">
                    <?php array_walk($langues, static function (array $lang, int|string $idx): void { ?>
                        <div class="langue-item border border-border-strong bg-background/40 p-4 rounded-lg flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full">
                                <div>
                                    <label class="text-[10px] font-mono text-muted-foreground uppercase">Langue</label>
                                    <input type="text" name="langues[<?= $idx ?>][name]"
                                           value="<?= htmlspecialchars($lang['name']) ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                                <div>
                                    <label class="text-[10px] font-mono text-muted-foreground uppercase">Niveau</label>
                                    <input type="text" name="langues[<?= $idx ?>][level]"
                                           value="<?= htmlspecialchars($lang['level']) ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                                <div>
                                    <label class="text-[10px] font-mono text-muted-foreground uppercase">Maîtrise
                                        (%)</label>
                                    <input type="number" name="langues[<?= $idx ?>][percent]"
                                           value="<?= htmlspecialchars($lang['percent']) ?>" min="0" max="100"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                            </div>
                            <button type="button"
                                    class="remove-item-btn text-[10px] font-mono border border-border-strong px-2 py-1.5 rounded hover:text-red-400 hover:border-red-400 self-end sm:self-center">
                                Suppr.
                            </button>
                        </div>
                    <?php }); ?>
                </div>
            </div>
        </section>

        <section id="projects" class="tab-content hidden flex-col gap-8">
            <div class="flex justify-between items-center border-b border-border-strong pb-4">
                <h2 class="font-display text-sm text-accent">Gestion des Projets du Portfolio</h2>
                <button type="button" id="add-project-btn"
                        class="px-4 py-2 font-mono text-xs border border-border-strong rounded-lg hover:border-accent transition-colors">
                    + Ajouter un projet
                </button>
            </div>

            <div class="border border-border-strong bg-background-2/20 p-4 rounded-xl flex flex-col gap-3">
                <label class="text-[10px] font-mono uppercase text-muted-foreground">Catégories de filtres
                    principaux</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <input type="text" name="project_cat_1"
                           value="<?= htmlspecialchars($fields['project_cat_1']) ?>"
                           class="bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none">
                    <input type="text" name="project_cat_2"
                           value="<?= htmlspecialchars($fields['project_cat_2']) ?>"
                           class="bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none">
                    <input type="text" name="project_cat_3"
                           value="<?= htmlspecialchars($fields['project_cat_3']) ?>"
                           class="bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none">
                </div>
            </div>

            <div id="projects-container" class="flex flex-col gap-8">
                <?php array_walk($projects, static function (array $project, int|string $index): void { ?>
                        <div class="project-item border border-border-strong bg-background-2/20 p-6 rounded-xl flex flex-col gap-6 relative">
                            <input type="hidden" name="projects[<?= $index ?>][id]" value="<?= $project['id'] ?>">

                            <div class="flex justify-between items-center border-b border-border-strong pb-4">
                                <h3 class="font-display text-xl text-accent italic">Projet
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
                                           value="<?= htmlspecialchars($project['title']) ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Meta (ex:
                                        Client · Full-stack)</label>
                                    <input type="text" name="projects[<?= $index ?>][meta]"
                                           value="<?= htmlspecialchars($project['meta']) ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="text-[10px] font-mono uppercase text-muted-foreground">Description</label>
                                <textarea name="projects[<?= $index ?>][description]" rows="3"
                                          class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none resize-y"><?= htmlspecialchars($project['description']) ?></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Image (URL ou
                                        Upload)</label>
                                    <div class="flex flex-col gap-3">
                                        <input type="text" name="projects[<?= $index ?>][image]"
                                               value="<?= htmlspecialchars($project['image']) ?>"
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
                                           value="<?= htmlspecialchars($project['link']) ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Catégorie de
                                        filtre</label>
                                    <input type="text" name="projects[<?= $index ?>][category]"
                                           value="<?= htmlspecialchars($project['category']) ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="text-[10px] font-mono uppercase text-muted-foreground">Tags techniques
                                    (séparés par des virgules)</label>
                                <input type="text" name="projects[<?= $index ?>][tags]"
                                       value="<?= htmlspecialchars($project['tags']) ?>"
                                       class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                            </div>
                        </div>
                <?php }); ?>
            </div>
        </section>

        <section id="galerie" class="tab-content hidden flex-col gap-8">
            <div class="flex justify-between items-center border-b border-border-strong pb-4">
                <div>
                    <h2 class="font-display text-sm text-accent">Galerie "Regarder Autrement"</h2>
                    <p class="text-xs text-muted-foreground mt-1">Gérez les photos, leurs légendes et leur format de
                        grille.</p>
                </div>
                <button type="button" id="add-photo-btn"
                        class="px-4 py-2 font-mono text-xs border border-border-strong rounded-lg hover:border-accent transition-colors">
                    + Ajouter une photo
                </button>
            </div>

            <div id="galerie-container" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php array_walk($photos, static function (array $photo, int|string $idx): void { ?>
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
                                       value="<?= htmlspecialchars($photo['url']) ?>"
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
                                   value="<?= htmlspecialchars($photo['unsplash_id']) ?>"
                                   class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-mono uppercase text-muted-foreground">Légende (Au
                                survol)</label>
                            <input type="text" name="photos[<?= $idx ?>][legend]"
                                   value="<?= htmlspecialchars($photo['legend']) ?>"
                                   class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-mono uppercase text-muted-foreground">Texte alternatif
                                (Alt)</label>
                            <input type="text" name="photos[<?= $idx ?>][alt]"
                                   value="<?= htmlspecialchars($photo['alt']) ?>"
                                   class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-mono uppercase text-muted-foreground">Format
                                d'affichage</label>
                            <select name="photos[<?= $idx ?>][format]"
                                    class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                <option value="portrait" <?= ($photo['format']) === 'portrait' ? 'selected' : '' ?>>
                                    Portrait (3:4 Dev · 4:5 Photo)
                                </option>
                                <option value="landscape" <?= ($photo['format']) === 'landscape' ? 'selected' : '' ?>>
                                    Paysage (16:10 Dev · 16:9 Photo)
                                </option>
                                <option value="panorama" <?= ($photo['format']) === 'panorama' ? 'selected' : '' ?>>
                                    Panorama cinéma (21:9 Photo, pleine largeur)
                                </option>
                            </select>
                        </div>
                    </div>
                <?php }); ?>
            </div>

            <div class="border border-border-strong bg-background-2/20 p-5 sm:p-6 rounded-xl flex flex-col gap-6">
                <div>
                    <h2 class="font-display text-sm text-accent">Comparateur Avant / Après (Mode Photo)</h2>
                    <p class="text-xs text-muted-foreground mt-1">
                        Photo brute (RAW exporté en JPEG / WebP) et rendu final étalonné, de même cadrage.
                        Sans image brute, un aperçu brut est simulé (et signalé comme tel) à partir de l'image finale.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-mono uppercase text-muted-foreground">Image AVANT (brute) : URL ou upload</label>
                        <input type="text" name="compare_before"
                               value="<?= htmlspecialchars($fields['compare_before']) ?>"
                               placeholder="Vide = brut simulé"
                               class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                        <input type="file" name="compare_before_file" accept="image/jpeg,image/png,image/webp"
                               class="w-full text-[10px] text-muted-foreground file:mr-2 file:py-1 file:px-2 file:rounded border file:border-0 file:bg-zinc-800 file:text-zinc-300 cursor-pointer">
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-mono uppercase text-muted-foreground">Image APRÈS (étalonnée) : URL ou upload</label>
                        <input type="text" name="compare_after"
                               value="<?= htmlspecialchars($fields['compare_after']) ?>"
                               placeholder="/assets/images/gallery/gallery-2.webp"
                               class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                        <input type="file" name="compare_after_file" accept="image/jpeg,image/png,image/webp"
                               class="w-full text-[10px] text-muted-foreground file:mr-2 file:py-1 file:px-2 file:rounded border file:border-0 file:bg-zinc-800 file:text-zinc-300 cursor-pointer">
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Titre de la section</label>
                    <input type="text" name="compare_title"
                           value="<?= htmlspecialchars($fields['compare_title']) ?>"
                           placeholder="Du RAW à l'image finale."
                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Texte d'accompagnement</label>
                    <textarea name="compare_caption" rows="2"
                              class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent resize-y"><?= htmlspecialchars($fields['compare_caption']) ?></textarea>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Outils (séparés par des virgules)</label>
                    <input type="text" name="compare_tools"
                           value="<?= htmlspecialchars($fields['compare_tools']) ?>"
                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                </div>
            </div>
        </section>

        <section id="contact" class="tab-content hidden flex-col gap-8">
            <div class="border border-border-strong bg-background-2/20 p-5 sm:p-6 rounded-xl flex flex-col gap-6">
                <h2 class="font-display text-sm text-accent">Titres &amp; Textes de Contact</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-mono uppercase text-muted-foreground">Titre de section</label>
                        <input type="text" name="contact_title"
                               value="<?= htmlspecialchars($fields['contact_title']) ?>"
                               class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-mono uppercase text-muted-foreground">Sous-titre / Appel à
                            l'action</label>
                        <input type="text" name="contact_subtitle"
                               value="<?= htmlspecialchars($fields['contact_subtitle']) ?>"
                               class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                    </div>
                </div>
            </div>

            <div class="border border-border-strong bg-background-2/20 p-5 sm:p-6 rounded-xl flex flex-col gap-6">
                <div class="flex justify-between items-center border-b border-border-strong pb-4">
                    <h2 class="font-display text-sm text-accent">Coordonnées &amp; Liens Réseaux</h2>
                    <button type="button" id="add-contact-btn"
                            class="px-3 py-1 font-mono text-xs border border-border-strong rounded-lg hover:border-accent transition-colors">
                        + Ajouter un lien
                    </button>
                </div>

                <div id="contact-container" class="flex flex-col gap-4">
                    <?php array_walk($contacts, static function (array $link, int|string $idx): void { ?>
                        <div class="contact-item border border-border-strong bg-background/40 p-4 rounded-lg flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full">
                                <div>
                                    <label class="text-[10px] font-mono text-muted-foreground uppercase">Libellé</label>
                                    <input type="text" name="contacts[<?= $idx ?>][label]"
                                           value="<?= htmlspecialchars($link['label']) ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                                <div>
                                    <label class="text-[10px] font-mono text-muted-foreground uppercase">Valeur
                                        affichée</label>
                                    <input type="text" name="contacts[<?= $idx ?>][value]"
                                           value="<?= htmlspecialchars($link['value']) ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                                <div>
                                    <label class="text-[10px] font-mono text-muted-foreground uppercase">Lien
                                        (href)</label>
                                    <input type="text" name="contacts[<?= $idx ?>][href]"
                                           value="<?= htmlspecialchars($link['href']) ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                            </div>
                            <button type="button"
                                    class="remove-item-btn text-[10px] font-mono border border-border-strong px-2 py-1.5 rounded hover:text-red-400 hover:border-red-400 self-end sm:self-center">
                                Suppr.
                            </button>
                        </div>
                    <?php }); ?>
                </div>
            </div>

            <div class="border border-border-strong bg-background-2/20 p-5 sm:p-6 rounded-xl flex flex-col gap-4">
                <h2 class="font-display text-sm text-accent">Centres d'intérêt (Footer)</h2>
                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Tags séparés par des
                        virgules</label>
                    <input type="text" name="interests"
                           value="<?= htmlspecialchars($fields['interests']) ?>"
                           class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                </div>
            </div>
        </section>

        <section id="lab" class="tab-content hidden flex-col gap-12">

            <div class="flex flex-col gap-6 border border-border-strong bg-background-2/20 p-5 sm:p-6 rounded-xl">
                <div class="flex justify-between items-center border-b border-border-strong pb-4">
                    <div>
                        <h2 class="font-display text-sm text-accent">Expérimentations &amp; Snippets Console</h2>
                        <p class="text-xs text-muted-foreground mt-1">Ajoutez ou modifiez vos snippets de code
                            interactifs.</p>
                    </div>
                    <button type="button" id="add-snippet-btn"
                            class="px-3 py-1 font-mono text-xs border border-border-strong rounded-lg hover:border-accent transition-colors">
                        + Ajouter un snippet
                    </button>
                </div>

                <div id="snippets-container" class="flex flex-col gap-8">
                    <?php array_walk($snippets, static function (array $snip, int|string $idx): void { ?>
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
                                           value="<?= htmlspecialchars($snip['title']) ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Tag / Catégorie
                                        (ex: CSS Modern)</label>
                                    <input type="text" name="snippets[<?= $idx ?>][category]"
                                           value="<?= htmlspecialchars($snip['category']) ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Note bas de
                                        carte (ex: Performance 60 FPS)</label>
                                    <input type="text" name="snippets[<?= $idx ?>][badge]"
                                           value="<?= htmlspecialchars($snip['badge']) ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground flex justify-between">
                                        <span>Code HTML</span><span class="text-orange-400">&lt;/&gt;</span>
                                    </label>
                                    <textarea name="snippets[<?= $idx ?>][html]" rows="6"
                                              class="w-full bg-zinc-950 border border-border-strong rounded p-3 font-mono text-xs text-zinc-300 focus:border-accent outline-none resize-y"><?= htmlspecialchars($snip['html']) ?></textarea>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground flex justify-between">
                                        <span>Code CSS</span><span class="text-blue-400">#</span>
                                    </label>
                                    <textarea name="snippets[<?= $idx ?>][css]" rows="6"
                                              class="w-full bg-zinc-950 border border-border-strong rounded p-3 font-mono text-xs text-zinc-300 focus:border-accent outline-none resize-y"><?= htmlspecialchars($snip['css']) ?></textarea>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground flex justify-between">
                                        <span>Code JS</span><span class="text-yellow-400">{}</span>
                                    </label>
                                    <textarea name="snippets[<?= $idx ?>][js]" rows="6"
                                              class="w-full bg-zinc-950 border border-border-strong rounded p-3 font-mono text-xs text-zinc-300 focus:border-accent outline-none resize-y"><?= htmlspecialchars($snip['js']) ?></textarea>
                                </div>
                            </div>
                        </div>
                    <?php }); ?>
                </div>
            </div>

            <div class="flex flex-col gap-6 border border-border-strong bg-background-2/20 p-5 sm:p-6 rounded-xl">
                <div class="flex justify-between items-center border-b border-border-strong pb-4">
                    <div>
                        <h2 class="font-display text-sm text-amber-400">Articles de Blog &amp; Notes d'expérience</h2>
                        <p class="text-xs text-muted-foreground mt-1">Rédigez et organisez vos publications.</p>
                    </div>
                    <button type="button" id="add-article-btn"
                            class="px-3 py-1 font-mono text-xs border border-border-strong rounded-lg hover:border-amber-400 transition-colors">
                        + Ajouter un article
                    </button>
                </div>

                <div id="articles-container" class="flex flex-col gap-8">
                    <?php array_walk($articles, static function (array $art, int|string $idx): void { ?>
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
                                           value="<?= htmlspecialchars($art['title']) ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-amber-400 outline-none">
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-[10px] font-mono uppercase text-muted-foreground">Catégorie &amp;
                                        Date (ex: MACOS / DEV — 02 SEP 2026)</label>
                                    <input type="text" name="articles[<?= $idx ?>][meta]"
                                           value="<?= htmlspecialchars($art['meta']) ?>"
                                           class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-amber-400 outline-none">
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="text-[10px] font-mono uppercase text-muted-foreground">Contenu de
                                    l'article</label>
                                <textarea name="articles[<?= $idx ?>][content]" rows="6"
                                          class="w-full bg-background-2/50 border border-border-strong rounded-lg p-4 text-sm focus:border-amber-400 outline-none resize-y leading-relaxed"><?= htmlspecialchars($art['content']) ?></textarea>
                            </div>
                        </div>
                    <?php }); ?>
                </div>
            </div>
        </section>

    </form>
</main>

<template id="tpl-admin-snippet">
    <div class="snippet-item border border-border-strong bg-background/40 p-5 rounded-xl flex flex-col gap-6 relative">
        <div class="flex justify-between items-center border-b border-border-strong pb-3">
            <span class="text-xs font-mono text-accent">Nouveau Snippet #{{number}}</span>
            <button type="button" class="remove-item-btn text-[10px] font-mono border border-border-strong px-2.5 py-1 rounded hover:text-red-400 hover:border-red-400">SUPPRIMER</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="text-[10px] font-mono uppercase text-muted-foreground">Titre de l'expérience</label>
                <input type="text" name="snippets[{{index}}][title]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
            </div>
            <div>
                <label class="text-[10px] font-mono uppercase text-muted-foreground">Tag / Catégorie</label>
                <input type="text" name="snippets[{{index}}][category]" placeholder="CSS Modern" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
            </div>
            <div>
                <label class="text-[10px] font-mono uppercase text-muted-foreground">Note bas de carte</label>
                <input type="text" name="snippets[{{index}}][badge]" placeholder="Tested on Chrome" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="text-[10px] font-mono uppercase text-muted-foreground">HTML</label>
                <textarea name="snippets[{{index}}][html]" rows="6" class="w-full bg-zinc-950 border border-border-strong rounded p-3 font-mono text-xs text-zinc-300 focus:border-accent outline-none resize-y"></textarea>
            </div>
            <div>
                <label class="text-[10px] font-mono uppercase text-muted-foreground">CSS</label>
                <textarea name="snippets[{{index}}][css]" rows="6" class="w-full bg-zinc-950 border border-border-strong rounded p-3 font-mono text-xs text-zinc-300 focus:border-accent outline-none resize-y"></textarea>
            </div>
            <div>
                <label class="text-[10px] font-mono uppercase text-muted-foreground">JS</label>
                <textarea name="snippets[{{index}}][js]" rows="6" class="w-full bg-zinc-950 border border-border-strong rounded p-3 font-mono text-xs text-zinc-300 focus:border-accent outline-none resize-y"></textarea>
            </div>
        </div>
    </div>
</template>
<template id="tpl-admin-article">
    <div class="article-item border border-border-strong bg-background/40 p-5 rounded-xl flex flex-col gap-6 relative">
        <div class="flex justify-between items-center border-b border-border-strong pb-3">
            <span class="text-xs font-mono text-amber-400">Nouvel Article #{{number}}</span>
            <button type="button" class="remove-item-btn text-[10px] font-mono border border-border-strong px-2.5 py-1 rounded hover:text-red-400 hover:border-red-400">SUPPRIMER</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="text-[10px] font-mono uppercase text-muted-foreground">Titre de l'article</label>
                <input type="text" name="articles[{{index}}][title]" class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-amber-400 outline-none">
            </div>
            <div>
                <label class="text-[10px] font-mono uppercase text-muted-foreground">Catégorie & Date</label>
                <input type="text" name="articles[{{index}}][meta]" placeholder="MACOS / DEV — 2026" class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-amber-400 outline-none">
            </div>
        </div>
        <div>
            <label class="text-[10px] font-mono uppercase text-muted-foreground">Contenu</label>
            <textarea name="articles[{{index}}][content]" rows="6" class="w-full bg-background-2/50 border border-border-strong rounded-lg p-4 text-sm focus:border-amber-400 outline-none resize-y leading-relaxed"></textarea>
        </div>
    </div>
</template>
<template id="tpl-admin-project">
    <div class="project-item border border-border-strong bg-background-2/20 p-6 rounded-xl flex flex-col gap-6 relative">
        <div class="flex justify-between items-center border-b border-border-strong pb-4">
            <h3 class="font-serif text-xl text-accent italic">Nouveau Projet #{{number}}</h3>
            <button type="button" class="remove-item-btn text-[10px] font-mono border border-border-strong px-3 py-1 rounded hover:text-red-400 hover:border-red-400 transition-colors">SUPPRIMER</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="text-[10px] font-mono uppercase text-muted-foreground">Titre du projet</label>
                <input type="text" name="projects[{{index}}][title]" class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
            </div>
            <div>
                <label class="text-[10px] font-mono uppercase text-muted-foreground">Meta</label>
                <input type="text" name="projects[{{index}}][meta]" class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
            </div>
        </div>
        <div>
            <label class="text-[10px] font-mono uppercase text-muted-foreground">Description</label>
            <textarea name="projects[{{index}}][description]" rows="3" class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none resize-y"></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex flex-col gap-2">
                <label class="text-[10px] font-mono uppercase text-muted-foreground">Image (URL ou Upload)</label>
                <div class="flex flex-col gap-3">
                    <input type="text" name="projects[{{index}}][image]" placeholder="URL..." class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                    <input type="file" name="projects_image_file[{{index}}]" accept="image/*" class="w-full text-xs text-muted-foreground file:mr-2 file:py-1 file:px-3 file:rounded border file:border-0 file:bg-zinc-800 file:text-zinc-300 cursor-pointer">
                </div>
            </div>
            <div>
                <label class="text-[10px] font-mono uppercase text-muted-foreground">Lien (URL)</label>
                <input type="text" name="projects[{{index}}][link]" class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
            </div>
            <div>
                <label class="text-[10px] font-mono uppercase text-muted-foreground">Catégorie</label>
                <input type="text" name="projects[{{index}}][category]" value="Web" class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
            </div>
        </div>
        <div>
            <label class="text-[10px] font-mono uppercase text-muted-foreground">Tags (séparés par des virgules)</label>
            <input type="text" name="projects[{{index}}][tags]" class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
        </div>
    </div>
</template>
<template id="tpl-admin-photo">
    <div class="photo-item border border-border-strong bg-background-2/20 p-5 rounded-xl flex flex-col gap-4 relative">
        <div class="flex justify-between items-center border-b border-border-strong pb-3">
            <span class="text-xs font-mono text-accent">Nouvelle Photo #{{number}}</span>
            <button type="button" class="remove-item-btn text-[10px] font-mono border border-border-strong px-2.5 py-1 rounded hover:text-red-400 hover:border-red-400">SUPPRIMER</button>
        </div>
        <div class="flex flex-col gap-2">
            <label class="text-[10px] font-mono uppercase text-muted-foreground">URL Image (Prioritaire ou Upload)</label>
            <div class="flex flex-col gap-3">
                <input type="text" name="photos[{{index}}][url]" placeholder="URL..." class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                <input type="file" name="photos_url_file[{{index}}]" accept="image/*" class="w-full text-[10px] text-muted-foreground file:mr-2 file:py-1 file:px-2 file:rounded border file:border-0 file:bg-zinc-800 file:text-zinc-300 cursor-pointer">
            </div>
        </div>
        <div>
            <label class="text-[10px] font-mono uppercase text-muted-foreground">ID Unsplash (Optionnel)</label>
            <input type="text" name="photos[{{index}}][unsplash_id]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
        </div>
        <div>
            <label class="text-[10px] font-mono uppercase text-muted-foreground">Légende</label>
            <input type="text" name="photos[{{index}}][legend]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
        </div>
        <div>
            <label class="text-[10px] font-mono uppercase text-muted-foreground">Texte Alt</label>
            <input type="text" name="photos[{{index}}][alt]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
        </div>
        <div>
            <label class="text-[10px] font-mono uppercase text-muted-foreground">Format</label>
            <select name="photos[{{index}}][format]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                <option value="portrait">Portrait (3:4 Dev · 4:5 Photo)</option>
                <option value="landscape" selected>Paysage (16:10 Dev · 16:9 Photo)</option>
                <option value="panorama">Panorama cinéma (21:9 Photo, pleine largeur)</option>
            </select>
        </div>
    </div>
</template>
<template id="tpl-admin-parcours">
    <div class="parcours-item border border-border-strong bg-background/40 p-4 rounded-lg flex flex-col gap-4 relative">
        <div class="flex justify-between items-center">
            <span class="text-xs font-mono text-accent">Nouvelle Étape #{{number}}</span>
            <button type="button" class="remove-item-btn text-[10px] font-mono border border-border-strong px-2.5 py-1 rounded hover:text-red-400 hover:border-red-400">SUPPRIMER</button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-[10px] font-mono text-muted-foreground uppercase">Période</label>
                <input type="text" name="parcours[{{index}}][period]" placeholder="2026 — Actuel" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
            </div>
            <div>
                <label class="text-[10px] font-mono text-muted-foreground uppercase">Type</label>
                <input type="text" name="parcours[{{index}}][type]" placeholder="Formation / Expérience" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
            </div>
        </div>
        <div>
            <label class="text-[10px] font-mono text-muted-foreground uppercase">Titre</label>
            <input type="text" name="parcours[{{index}}][title]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
        </div>
        <div>
            <label class="text-[10px] font-mono text-muted-foreground uppercase">Détail</label>
            <textarea name="parcours[{{index}}][detail]" rows="2" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent resize-y"></textarea>
        </div>
    </div>
</template>
<template id="tpl-admin-langue">
    <div class="langue-item border border-border-strong bg-background/40 p-4 rounded-lg flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full">
            <div>
                <label class="text-[10px] font-mono text-muted-foreground uppercase">Langue</label>
                <input type="text" name="langues[{{index}}][name]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
            </div>
            <div>
                <label class="text-[10px] font-mono text-muted-foreground uppercase">Niveau</label>
                <input type="text" name="langues[{{index}}][level]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
            </div>
            <div>
                <label class="text-[10px] font-mono text-muted-foreground uppercase">Maîtrise (%)</label>
                <input type="number" name="langues[{{index}}][percent]" value="80" min="0" max="100" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
            </div>
        </div>
        <button type="button" class="remove-item-btn text-[10px] font-mono border border-border-strong px-2 py-1.5 rounded hover:text-red-400 hover:border-red-400 self-end sm:self-center">Suppr.</button>
    </div>
</template>
<template id="tpl-admin-contact">
    <div class="contact-item border border-border-strong bg-background/40 p-4 rounded-lg flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full">
            <div>
                <label class="text-[10px] font-mono text-muted-foreground uppercase">Libellé</label>
                <input type="text" name="contacts[{{index}}][label]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
            </div>
            <div>
                <label class="text-[10px] font-mono text-muted-foreground uppercase">Valeur affichée</label>
                <input type="text" name="contacts[{{index}}][value]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
            </div>
            <div>
                <label class="text-[10px] font-mono text-muted-foreground uppercase">Lien (href)</label>
                <input type="text" name="contacts[{{index}}][href]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
            </div>
        </div>
        <button type="button" class="remove-item-btn text-[10px] font-mono border border-border-strong px-2 py-1.5 rounded hover:text-red-400 hover:border-red-400 self-end sm:self-center">Suppr.</button>
    </div>
</template>

</body>
</html>
