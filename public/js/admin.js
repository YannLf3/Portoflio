document.addEventListener('DOMContentLoaded', () => {

    // ==========================================
    // 1. BASCULE DES ONGLETS (TABS)
    // ==========================================
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => {
                b.classList.remove('active', 'border-accent', 'bg-accent/10', 'text-accent');
                b.classList.add('border-border-strong', 'text-muted-foreground');
            });

            tabContents.forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('flex');
            });

            btn.classList.add('active', 'border-accent', 'bg-accent/10', 'text-accent');
            btn.classList.remove('border-border-strong', 'text-muted-foreground');

            const targetId = btn.getAttribute('data-target');
            const targetContent = document.getElementById(targetId);
            if (targetContent) {
                targetContent.classList.remove('hidden');
                targetContent.classList.add('flex');
            }
        });
    });

    // ==========================================
    // 2. ÉDITEUR WYSIWYG
    // ==========================================
    const wysiwygBtns = document.querySelectorAll('.wysiwyg-btn');
    const wysiwygSelects = document.querySelectorAll('.wysiwyg-select');
    const editor = document.getElementById('editor');
    const hiddenInput = document.getElementById('article_content_hidden');
    const form = document.getElementById('admin-form');

    wysiwygBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const cmd = btn.getAttribute('data-cmd');
            document.execCommand(cmd, false, null);
            if (editor) editor.focus();
        });
    });

    wysiwygSelects.forEach(select => {
        select.addEventListener('change', (e) => {
            const cmd = select.getAttribute('data-cmd');
            const val = e.target.value;
            document.execCommand(cmd, false, val);
            if (editor) editor.focus();
        });
    });

    if (form && editor && hiddenInput) {
        form.addEventListener('submit', () => {
            hiddenInput.value = editor.innerHTML;
        });
    }

    // ==========================================
    // 3. SUPPRESSION UNIVERSELLE D'ÉLÉMENTS
    // ==========================================
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-item-btn')) {
            if (confirm('Voulez-vous vraiment supprimer cet élément ?')) {
                const item = e.target.closest('.snippet-item, .article-item, .project-item, .photo-item, .parcours-item, .langue-item, .contact-item');
                if (item) item.remove();
            }
        }
    });

    // ==========================================
    // 4. AJOUT DYNAMIQUE DE SNIPPETS (LAB)
    // ==========================================
    const addSnippetBtn = document.getElementById('add-snippet-btn');
    const snippetsContainer = document.getElementById('snippets-container');

    if (addSnippetBtn && snippetsContainer) {
        addSnippetBtn.addEventListener('click', () => {
            const index = snippetsContainer.children.length;
            const html = `
                <div class="snippet-item border border-border-strong bg-background/40 p-5 rounded-xl flex flex-col gap-6 relative">
                    <div class="flex justify-between items-center border-b border-border-strong pb-3">
                        <span class="text-xs font-mono text-accent">Nouveau Snippet #${index + 1}</span>
                        <button type="button" class="remove-item-btn text-[10px] font-mono border border-border-strong px-2.5 py-1 rounded hover:text-red-400 hover:border-red-400">SUPPRIMER</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-[10px] font-mono uppercase text-muted-foreground">Titre de l'expérience</label>
                            <input type="text" name="snippets[${index}][title]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                        </div>
                        <div>
                            <label class="text-[10px] font-mono uppercase text-muted-foreground">Tag / Catégorie</label>
                            <input type="text" name="snippets[${index}][category]" placeholder="CSS Modern" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                        </div>
                        <div>
                            <label class="text-[10px] font-mono uppercase text-muted-foreground">Note bas de carte</label>
                            <input type="text" name="snippets[${index}][badge]" placeholder="Tested on Chrome" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-[10px] font-mono uppercase text-muted-foreground">HTML</label>
                            <textarea name="snippets[${index}][html]" rows="6" class="w-full bg-zinc-950 border border-border-strong rounded p-3 font-mono text-xs text-zinc-300 focus:border-accent outline-none resize-y"></textarea>
                        </div>
                        <div>
                            <label class="text-[10px] font-mono uppercase text-muted-foreground">CSS</label>
                            <textarea name="snippets[${index}][css]" rows="6" class="w-full bg-zinc-950 border border-border-strong rounded p-3 font-mono text-xs text-zinc-300 focus:border-accent outline-none resize-y"></textarea>
                        </div>
                        <div>
                            <label class="text-[10px] font-mono uppercase text-muted-foreground">JS</label>
                            <textarea name="snippets[${index}][js]" rows="6" class="w-full bg-zinc-950 border border-border-strong rounded p-3 font-mono text-xs text-zinc-300 focus:border-accent outline-none resize-y"></textarea>
                        </div>
                    </div>
                </div>`;
            snippetsContainer.insertAdjacentHTML('beforeend', html);
        });
    }

    // ==========================================
    // 5. AJOUT DYNAMIQUE D'ARTICLES (LAB)
    // ==========================================
    const addArticleBtn = document.getElementById('add-article-btn');
    const articlesContainer = document.getElementById('articles-container');

    if (addArticleBtn && articlesContainer) {
        addArticleBtn.addEventListener('click', () => {
            const index = articlesContainer.children.length;
            const html = `
                <div class="article-item border border-border-strong bg-background/40 p-5 rounded-xl flex flex-col gap-6 relative">
                    <div class="flex justify-between items-center border-b border-border-strong pb-3">
                        <span class="text-xs font-mono text-amber-400">Nouvel Article #${index + 1}</span>
                        <button type="button" class="remove-item-btn text-[10px] font-mono border border-border-strong px-2.5 py-1 rounded hover:text-red-400 hover:border-red-400">SUPPRIMER</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-[10px] font-mono uppercase text-muted-foreground">Titre de l'article</label>
                            <input type="text" name="articles[${index}][title]" class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-amber-400 outline-none">
                        </div>
                        <div>
                            <label class="text-[10px] font-mono uppercase text-muted-foreground">Catégorie & Date</label>
                            <input type="text" name="articles[${index}][meta]" placeholder="MACOS / DEV — 2026" class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-amber-400 outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-mono uppercase text-muted-foreground">Contenu</label>
                        <textarea name="articles[${index}][content]" rows="6" class="w-full bg-background-2/50 border border-border-strong rounded-lg p-4 text-sm focus:border-amber-400 outline-none resize-y leading-relaxed"></textarea>
                    </div>
                </div>`;
            articlesContainer.insertAdjacentHTML('beforeend', html);
        });
    }

    // ==========================================
    // 6. AJOUT DYNAMIQUE DE PROJETS
    // ==========================================
    const addProjectBtn = document.getElementById('add-project-btn');
    const projectsContainer = document.getElementById('projects-container');

    if (addProjectBtn && projectsContainer) {
        addProjectBtn.addEventListener('click', () => {
            const index = projectsContainer.children.length;
            const html = `
                <div class="project-item border border-border-strong bg-background-2/20 p-6 rounded-xl flex flex-col gap-6 relative">
                    <div class="flex justify-between items-center border-b border-border-strong pb-4">
                        <h3 class="font-serif text-xl text-accent italic">Nouveau Projet #${index + 1}</h3>
                        <button type="button" class="remove-item-btn text-[10px] font-mono border border-border-strong px-3 py-1 rounded hover:text-red-400 hover:border-red-400 transition-colors">SUPPRIMER</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-[10px] font-mono uppercase text-muted-foreground">Titre du projet</label>
                            <input type="text" name="projects[${index}][title]" class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                        </div>
                        <div>
                            <label class="text-[10px] font-mono uppercase text-muted-foreground">Meta</label>
                            <input type="text" name="projects[${index}][meta]" class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-mono uppercase text-muted-foreground">Description</label>
                        <textarea name="projects[${index}][description]" rows="3" class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none resize-y"></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="flex flex-col gap-2">
                            <label class="text-[10px] font-mono uppercase text-muted-foreground">Image (URL ou Upload)</label>
                            <div class="flex flex-col gap-3">
                                <input type="text" name="projects[${index}][image]" placeholder="URL..." class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                                <input type="file" name="projects_image_file[${index}]" accept="image/*" class="w-full text-xs text-muted-foreground file:mr-2 file:py-1 file:px-3 file:rounded border file:border-0 file:bg-zinc-800 file:text-zinc-300 cursor-pointer">
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-mono uppercase text-muted-foreground">Lien (URL)</label>
                            <input type="text" name="projects[${index}][link]" class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                        </div>
                        <div>
                            <label class="text-[10px] font-mono uppercase text-muted-foreground">Catégorie</label>
                            <input type="text" name="projects[${index}][category]" value="Web" class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-mono uppercase text-muted-foreground">Tags (séparés par des virgules)</label>
                        <input type="text" name="projects[${index}][tags]" class="w-full bg-background-2/50 border border-border-strong rounded-lg px-4 py-3 text-sm focus:border-accent outline-none">
                    </div>
                </div>`;
            projectsContainer.insertAdjacentHTML('beforeend', html);
        });
    }

    // ==========================================
    // 7. AJOUT DYNAMIQUE DE PHOTOS (GALERIE)
    // ==========================================
    const addPhotoBtn = document.getElementById('add-photo-btn');
    const galerieContainer = document.getElementById('galerie-container');

    if (addPhotoBtn && galerieContainer) {
        addPhotoBtn.addEventListener('click', () => {
            const index = galerieContainer.children.length;
            const html = `
                <div class="photo-item border border-border-strong bg-background-2/20 p-5 rounded-xl flex flex-col gap-4 relative">
                    <div class="flex justify-between items-center border-b border-border-strong pb-3">
                        <span class="text-xs font-mono text-accent">Nouvelle Photo #${index + 1}</span>
                        <button type="button" class="remove-item-btn text-[10px] font-mono border border-border-strong px-2.5 py-1 rounded hover:text-red-400 hover:border-red-400">SUPPRIMER</button>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-mono uppercase text-muted-foreground">URL Image (Prioritaire ou Upload)</label>
                        <div class="flex flex-col gap-3">
                            <input type="text" name="photos[${index}][url]" placeholder="URL..." class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                            <input type="file" name="photos_url_file[${index}]" accept="image/*" class="w-full text-[10px] text-muted-foreground file:mr-2 file:py-1 file:px-2 file:rounded border file:border-0 file:bg-zinc-800 file:text-zinc-300 cursor-pointer">
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-mono uppercase text-muted-foreground">ID Unsplash (Optionnel)</label>
                        <input type="text" name="photos[${index}][unsplash_id]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                    </div>
                    <div>
                        <label class="text-[10px] font-mono uppercase text-muted-foreground">Légende</label>
                        <input type="text" name="photos[${index}][legend]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                    </div>
                    <div>
                        <label class="text-[10px] font-mono uppercase text-muted-foreground">Texte Alt</label>
                        <input type="text" name="photos[${index}][alt]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                    </div>
                    <div>
                        <label class="text-[10px] font-mono uppercase text-muted-foreground">Format</label>
                        <select name="photos[${index}][format]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                            <option value="portrait">Grand (Portrait)</option>
                            <option value="landscape" selected>Standard (Paysage)</option>
                        </select>
                    </div>
                </div>`;
            galerieContainer.insertAdjacentHTML('beforeend', html);
        });
    }

    // ==========================================
    // 8. AJOUT DYNAMIQUE DE PARCOURS
    // ==========================================
    const addParcoursBtn = document.getElementById('add-parcours-btn');
    const parcoursContainer = document.getElementById('parcours-container');

    if (addParcoursBtn && parcoursContainer) {
        addParcoursBtn.addEventListener('click', () => {
            const index = parcoursContainer.children.length;
            const html = `
                <div class="parcours-item border border-border-strong bg-background/40 p-4 rounded-lg flex flex-col gap-4 relative">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-mono text-accent">Nouvelle Étape #${index + 1}</span>
                        <button type="button" class="remove-item-btn text-[10px] font-mono border border-border-strong px-2.5 py-1 rounded hover:text-red-400 hover:border-red-400">SUPPRIMER</button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-mono text-muted-foreground uppercase">Période</label>
                            <input type="text" name="parcours[${index}][period]" placeholder="2026 — Actuel" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                        </div>
                        <div>
                            <label class="text-[10px] font-mono text-muted-foreground uppercase">Type</label>
                            <input type="text" name="parcours[${index}][type]" placeholder="Formation / Expérience" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-mono text-muted-foreground uppercase">Titre</label>
                        <input type="text" name="parcours[${index}][title]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                    </div>
                    <div>
                        <label class="text-[10px] font-mono text-muted-foreground uppercase">Détail</label>
                        <textarea name="parcours[${index}][detail]" rows="2" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent resize-y"></textarea>
                    </div>
                </div>`;
            parcoursContainer.insertAdjacentHTML('beforeend', html);
        });
    }

    // ==========================================
    // 9. AJOUT DYNAMIQUE DE LANGUES
    // ==========================================
    const addLangueBtn = document.getElementById('add-langue-btn');
    const languesContainer = document.getElementById('langues-container');

    if (addLangueBtn && languesContainer) {
        addLangueBtn.addEventListener('click', () => {
            const index = languesContainer.children.length;
            const html = `
                <div class="langue-item border border-border-strong bg-background/40 p-4 rounded-lg flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full">
                        <div>
                            <label class="text-[10px] font-mono text-muted-foreground uppercase">Langue</label>
                            <input type="text" name="langues[${index}][name]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                        </div>
                        <div>
                            <label class="text-[10px] font-mono text-muted-foreground uppercase">Niveau</label>
                            <input type="text" name="langues[${index}][level]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                        </div>
                        <div>
                            <label class="text-[10px] font-mono text-muted-foreground uppercase">Maîtrise (%)</label>
                            <input type="number" name="langues[${index}][percent]" value="80" min="0" max="100" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                        </div>
                    </div>
                    <button type="button" class="remove-item-btn text-[10px] font-mono border border-border-strong px-2 py-1.5 rounded hover:text-red-400 hover:border-red-400 self-end sm:self-center">Suppr.</button>
                </div>`;
            languesContainer.insertAdjacentHTML('beforeend', html);
        });
    }

    // ==========================================
    // 10. AJOUT DYNAMIQUE DE CONTACTS
    // ==========================================
    const addContactBtn = document.getElementById('add-contact-btn');
    const contactContainer = document.getElementById('contact-container');

    if (addContactBtn && contactContainer) {
        addContactBtn.addEventListener('click', () => {
            const index = contactContainer.children.length;
            const html = `
                <div class="contact-item border border-border-strong bg-background/40 p-4 rounded-lg flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full">
                        <div>
                            <label class="text-[10px] font-mono text-muted-foreground uppercase">Libellé</label>
                            <input type="text" name="contacts[${index}][label]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                        </div>
                        <div>
                            <label class="text-[10px] font-mono text-muted-foreground uppercase">Valeur affichée</label>
                            <input type="text" name="contacts[${index}][value]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                        </div>
                        <div>
                            <label class="text-[10px] font-mono text-muted-foreground uppercase">Lien (href)</label>
                            <input type="text" name="contacts[${index}][href]" class="w-full bg-background-2/50 border border-border-strong rounded px-3 py-2 text-xs outline-none focus:border-accent">
                        </div>
                    </div>
                    <button type="button" class="remove-item-btn text-[10px] font-mono border border-border-strong px-2 py-1.5 rounded hover:text-red-400 hover:border-red-400 self-end sm:self-center">Suppr.</button>
                </div>`;
            contactContainer.insertAdjacentHTML('beforeend', html);
        });
    }
});