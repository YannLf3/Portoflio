document.addEventListener('DOMContentLoaded', () => {

    /**
     * Signale que l'interface est visible (loader terminé ou ignoré).
     * Le drapeau data-app-ready couvre le cas où animations.js s'initialise après l'événement ;
     * l'événement app:ready couvre le cas inverse. animations.js lance alors les révélations au scroll.
     * @returns {void}
     */
    const markAppReady = () => {
        document.documentElement.dataset.appReady = 'true';
        document.dispatchEvent(new CustomEvent('app:ready'));
    };

    // ----------------------------------------------------
    // 1. GESTION DU LOADER (Compatible Safari & WebKit)
    // ----------------------------------------------------
    const loader = document.getElementById('app-loader');
    const logsContainer = document.getElementById('terminal-logs');
    const progressEl = document.getElementById('loader-progress');

    if (loader && logsContainer) {
        if (sessionStorage.getItem('portfolio_loaded')) {
            loader.style.display = 'none';
            markAppReady();
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
                    // text-accent : Lime Spark du design system (le loader s'affiche toujours en mode dev)
                    line.className = 'flex items-center gap-2 text-accent';
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
                            // Les titres se révèlent pendant que le loader remonte
                            markAppReady();

                            setTimeout(() => {
                                loader.style.display = 'none';
                                sessionStorage.setItem('portfolio_loaded', 'true');
                            }, 700);
                        }, 800);
                    }
                }, item.delay);
            });
        }
    } else {
        markAppReady();
    }

    // ----------------------------------------------------
    // 2. CURSEUR PERSONNALISÉ
    // ----------------------------------------------------
    // Déplacé dans public/js/animations.js (curseur magnétique M/V/C, boucle rAF partagée
    // avec le fond WebGL). Ne pas réintroduire d'écouteur mousemove ici : double suivi.

});