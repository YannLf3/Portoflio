document.addEventListener('DOMContentLoaded', () => {

    // ----------------------------------------------------
    // 1. GESTION DU LOADER (Compatible Safari & WebKit)
    // ----------------------------------------------------
    const loader = document.getElementById('app-loader');
    const logsContainer = document.getElementById('terminal-logs');
    const progressEl = document.getElementById('loader-progress');

    if (loader && logsContainer) {
        if (sessionStorage.getItem('portfolio_loaded')) {
            loader.style.display = 'none';
        } else {
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

                    // Clôture du loader après la dernière ligne
                    if (index === logs.length - 1) {
                        setTimeout(() => {
                            loader.style.transform = 'translateY(-100%)';
                            loader.style.opacity = '0';

                            setTimeout(() => {
                                loader.style.display = 'none';
                                sessionStorage.setItem('portfolio_loaded', 'true');
                            }, 700);
                        }, 800);
                    }
                }, item.delay);
            });
        }
    }

    // ----------------------------------------------------
    // 2. GESTION DU CURSEUR PERSONNALISÉ
    // ----------------------------------------------------
    const cursor = document.getElementById('custom-cursor');

    if (cursor) {
        // Active le masquage du curseur natif uniquement sur ordinateur (pas de tactile)
        const isDesktop = window.innerWidth >= 768 && !('ontouchstart' in window);
        if (isDesktop) {
            document.body.classList.add('custom-cursor-active');
        }

        // Suivi de la souris
        window.addEventListener('mousemove', (e) => {
            cursor.style.left = `${e.clientX}px`;
            cursor.style.top = `${e.clientY}px`;
            cursor.style.opacity = '1';
        });

        // Masquer si la souris quitte la fenêtre
        document.addEventListener('mouseleave', () => {
            cursor.style.opacity = '0';
        });

        // Effet de zoom au survol des éléments cliquables
        const interactiveElements = document.querySelectorAll('a, button, input, [role="button"]');
        interactiveElements.forEach((el) => {
            el.addEventListener('mouseenter', () => {
                cursor.classList.add('scale-125');
            });
            el.addEventListener('mouseleave', () => {
                cursor.classList.remove('scale-125');
            });
        });
    }

});