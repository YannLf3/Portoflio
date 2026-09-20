document.addEventListener('DOMContentLoaded', () => {
    const cursor = document.getElementById('custom-cursor');
    if (!cursor) return;

    // 1. Suivi de la souris
    window.addEventListener('mousemove', (e) => {
        cursor.style.transform = `translate3d(${e.clientX}px, ${e.clientY}px, 0) translate(-50%, -50%)`;
        cursor.style.opacity = '1';
    });

    // 2. Masquer le curseur quand la souris sort de la fenêtre
    document.addEventListener('mouseleave', () => {
        cursor.style.opacity = '0';
    });

    // 3. Effet "magnétique" / Zoom au survol des boutons et liens
    const interactiveElements = document.querySelectorAll('a, button, input, [role="button"]');

    interactiveElements.forEach((el) => {
        el.addEventListener('mouseenter', () => {
            document.body.classList.add('cursor-hover');
        });
        el.addEventListener('mouseleave', () => {
            document.body.classList.remove('cursor-hover');
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const loader = document.getElementById('app-loader');
    const logsContainer = document.getElementById('terminal-logs');
    const progressEl = document.getElementById('loader-progress');

    if (!loader || !logsContainer) return;

    // Évite d'afficher le loader si l'utilisateur l'a déjà vu durant sa session
    if (sessionStorage.getItem('portfolio_loaded')) {
        loader.style.display = 'none';
        return;
    }

    // Liste des lignes avec des délais cumulés (en millisecondes)
    const logs = [
        {text: '> INITIALIZING PORTFOLIO_CORE v2.0...', delay: 200},
        {text: '> LOADING ASSETS & GRAPHIC_ENGINE...', delay: 700},
        {text: '> MOUNTING COMPONENTS [HERO, PROJECTS, GALLERY]...', delay: 1300},
        {text: '> OPTIMIZING LIGHTROOM_PRESETS & STACK...', delay: 1900},
        {text: '> SYSTEM READY. LAUNCHING INTERFACE...', delay: 2500}
    ];

    logs.forEach((item, index) => {
        setTimeout(() => {
            const line = document.createElement('div');
            line.className = 'flex items-center gap-2 text-emerald-400';
            line.innerHTML = `<span class="text-zinc-600">$</span> <span>${item.text}</span>`;
            logsContainer.appendChild(line);

            // Mise à jour du pourcentage de progression
            const progress = Math.round(((index + 1) / logs.length) * 100);
            if (progressEl) progressEl.textContent = `${progress}%`;

            // Une fois la dernière ligne affichée, on attend 800ms avant de fermer le rideau
            if (index === logs.length - 1) {
                setTimeout(() => {
                    loader.classList.add('-translate-y-full');
                    sessionStorage.setItem('portfolio_loaded', 'true');
                }, 800);
            }
        }, item.delay);
    });
});