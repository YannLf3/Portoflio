<!-- Intégration du Header -->
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main class="w-full min-h-screen px-6 sm:px-12 pt-32 pb-24 flex justify-center">
    <div class="w-full max-w-3xl flex flex-col gap-16">

        <!-- En-tête -->
        <div class="flex flex-col items-start gap-4">
            <span class="text-xs font-mono tracking-widest text-accent uppercase">
                Légal
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-normal tracking-tight text-foreground leading-tight">
                Mentions <span class="italic font-light text-accent">légales</span>
            </h1>
        </div>

        <!-- Contenu Légal -->
        <div class="flex flex-col gap-12 text-sm sm:text-base text-muted-foreground leading-relaxed">

            <!-- 1. Éditeur & Directeur de la publication -->
            <section class="flex flex-col gap-4">
                <h2 class="text-lg font-medium text-foreground">1. Éditeur et Directeur de la publication</h2>
                <div class="flex flex-col gap-2">
                    <p>
                        Le présent site est édité par <strong>Yann Le Flohic</strong>, agissant en tant qu'Entrepreneur
                        Individuel (EI) sous le nom commercial <strong>YannLf3</strong>.
                    </p>
                    <ul class="flex flex-col gap-1 list-none font-mono text-xs mt-2 p-4 bg-background-card/20 border border-border-strong rounded-lg">
                        <li><span class="text-foreground">Statut :</span> Micro-entrepreneur</li>
                        <li><span class="text-foreground">Siège social :</span> 89 route de Châteauroux, 36250
                            Saint-Maur
                        </li>
                        <li><span class="text-foreground">Email :</span> yann.lfhc@icloud.com</li>
                        <li><span class="text-foreground">Téléphone :</span> +33 6 46 13 31 35</li>
                        <li><span class="text-foreground">SIRET :</span> 10632173000017</li>
                        <li><span class="text-foreground">TVA :</span> TVA non applicable, art. 293 B du CGI</li>
                    </ul>
                    <p class="mt-2">
                        <strong>Directeur de la publication :</strong> Yann Le Flohic.
                    </p>
                </div>
            </section>

            <!-- 2. Hébergement -->
            <section class="flex flex-col gap-4">
                <h2 class="text-lg font-medium text-foreground">2. Hébergement du site</h2>
                <p>
                    Ce site est hébergé par <strong>Hostinger</strong>.
                </p>
                <ul class="flex flex-col gap-1 list-none font-mono text-xs mt-2 p-4 bg-background-card/20 border border-border-strong rounded-lg">
                    <li><span class="text-foreground">Raison sociale :</span> Hostinger International Ltd.</li>
                    <li><span class="text-foreground">Adresse :</span> 61 Lordou Vironos Street, 6023 Larnaca, Chypre
                    </li>
                    <li><span class="text-foreground">Contact :</span> https://www.hostinger.fr/contact</li>
                </ul>
            </section>

            <!-- 3. Propriété Intellectuelle -->
            <section class="flex flex-col gap-4">
                <h2 class="text-lg font-medium text-foreground">3. Propriété intellectuelle et Droits d'auteur</h2>
                <p>
                    L'ensemble de ce site relève de la législation française et internationale sur le droit d'auteur et
                    la propriété intellectuelle. Tous les droits de reproduction sont réservés.
                </p>
                <p>
                    Sauf mention contraire, les photographies, textes, éléments graphiques, codes sources et designs
                    (notamment liés à l'activité de développement web, de création visuelle et aux projets réalisés dans
                    le cadre du BUT MMI à l'IUT du Limousin) sont la propriété exclusive de Yann Le Flohic.
                </p>
                <p>
                    Toute reproduction, représentation, modification, publication, adaptation de tout ou partie des
                    éléments du site, quel que soit le moyen ou le procédé utilisé, est interdite sans l'autorisation
                    écrite préalable de l'auteur. Les projets réalisés pour le compte de clients tiers sont présentés
                    avec leur accord et demeurent leur propriété respective.
                </p>
            </section>

            <!-- 4. Données personnelles & Cookies -->
            <section class="flex flex-col gap-4">
                <h2 class="text-lg font-medium text-foreground">4. Données personnelles (RGPD) et Cookies</h2>
                <p>
                    Dans le cadre de l'utilisation de ce site, et notamment via les liens de contact (email, téléphone),
                    des données à caractère personnel peuvent être collectées. Yann Le Flohic est le responsable du
                    traitement de ces données.
                </p>
                <p>
                    <strong>Finalité :</strong> Ces données sont collectées dans le seul but de traiter vos demandes de
                    contact et d'échanger sur des projets (web ou photographie). Elles ne sont en aucun cas revendues,
                    cédées ou utilisées à des fins de prospection commerciale par des tiers.
                </p>
                <p>
                    <strong>Droits des utilisateurs :</strong> Conformément au Règlement Général sur la Protection des
                    Données (RGPD), vous disposez d'un droit d'accès, de rectification, d'opposition et de suppression
                    de vos données personnelles. Vous pouvez exercer ce droit en envoyant un message à l'adresse : <a
                            href="mailto:yann.lfhc@icloud.com"
                            class="text-foreground hover:text-accent transition-colors">yann.lfhc@icloud.com</a>.
                </p>
                <p>
                    <strong>Cookies :</strong> Ce site n'utilise actuellement aucun traceur ou cookie non essentiel à
                    son fonctionnement technique. Par conséquent, aucun bandeau de consentement préalable n'est requis.
                </p>
            </section>

        </div>
    </div>
</main>

<!-- Intégration du Footer -->
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>