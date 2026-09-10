<main class="max-w-6xl mx-auto px-4 py-16">
    <!-- Hero Section avec Tailwind -->
    <section class="text-center py-12">
        <h1 class="text-4xl md:text-6xl font-extrabold text-white tracking-tight mb-4">
            Développeur Web Full-Stack & Designer
        </h1>
        <p class="text-lg text-slate-400 max-w-2xl mx-auto mb-8">
            Bienvenue sur mon portfolio. Découvre mes projets récents et mes réalisations.
        </p>
        <a href="#projects" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-3 rounded-lg shadow-md transition">
            Voir mes travaux
        </a>
    </section>

    <!-- Exemples d'assets dynamiques/statiques -->
    <section id="projects" class="py-12">
        <h2 class="text-2xl font-bold mb-6 text-indigo-400">Projets récents</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-slate-800 rounded-xl overflow-hidden border border-slate-700 shadow-lg">
                <!-- Les images doivent pointer vers /assets/... -->
                <img src="/assets/images/project-placeholder.jpg" alt="Aperçu projet" class="w-full h-48 object-cover" />
                <div class="p-5">
                    <h3 class="text-xl font-bold text-white mb-2">Nom du projet</h3>
                    <p class="text-slate-400 text-sm mb-4">Description courte du projet réalisé en PHP MVC et Tailwind.</p>
                </div>
            </div>
        </div>
    </section>
</main>
