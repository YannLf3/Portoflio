let btn_toggle_dev = document.getElementById("toggle-dev");
let btn_toggle_photo = document.getElementById("toggle-photo");
let theme_transition_overlay = document.getElementById("theme-transition-overlay");

//au click on doit cibler le body qui contient le data-mode="dev" et le changer en photo et inversement si on clique sur dev
btn_toggle_dev.addEventListener("click", () => {
    theme_transition_overlay.classList.add("active");
    document.documentElement.setAttribute("data-mode", "dev");

    // Tes classes de boutons
    btn_toggle_dev.classList.remove("text-muted-foreground");
    btn_toggle_dev.classList.add("text-accent-contrast", "bg-accent", "shadow-sm", "scale-95", "shadow-lg", "shadow-accent/20");
    btn_toggle_photo.classList.remove("text-accent-contrast", "bg-accent", "shadow-sm", "scale-95", "shadow-lg", "shadow-accent/20");
    btn_toggle_photo.classList.add("text-muted-foreground");

    // 1. Attendre la fin de l'expansion de l'onde (700ms)
    setTimeout(() => {
        // 2. Faire un fondu de sortie de l'overlay (il reste grand mais devient transparent)
        theme_transition_overlay.style.opacity = "0";

        // 3. Une fois le fondu terminé (400ms plus tard), on retire .active en secret
        setTimeout(() => {
            theme_transition_overlay.classList.remove("active");
            theme_transition_overlay.style.opacity = ""; // On remet l'attribut à l'état initial
        }, 300);

    }, 500);
});

btn_toggle_photo.addEventListener("click", () => {
    theme_transition_overlay.classList.add("active");
    document.documentElement.setAttribute("data-mode", "photo");

    // Tes classes de boutons
    btn_toggle_photo.classList.remove("text-muted-foreground");
    btn_toggle_photo.classList.add("text-accent-contrast", "bg-accent", "shadow-sm", "scale-95", "shadow-lg", "shadow-accent/20");
    btn_toggle_dev.classList.remove("text-accent-contrast", "bg-accent", "shadow-sm", "scale-95", "shadow-lg", "shadow-accent/20");
    btn_toggle_dev.classList.add("text-muted-foreground");

    setTimeout(() => {
        theme_transition_overlay.style.opacity = "0";

        setTimeout(() => {
            theme_transition_overlay.classList.remove("active");
            theme_transition_overlay.style.opacity = "";
        }, 300);

    }, 500);
});