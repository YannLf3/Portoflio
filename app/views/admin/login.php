<!DOCTYPE html>
<html lang="fr" data-mode="dev" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | Espace Admin</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="font-mono bg-background flex items-center justify-center min-h-screen p-4">

<!-- Calque de fond discret -->
<div class="fixed inset-0 pointer-events-none z-0 bg-grid-pattern opacity-40"></div>

<!-- Carte de connexion -->
<div class="relative z-10 w-full max-w-md bg-background-2/80 backdrop-blur-md border border-border-strong rounded-2xl p-8 sm:p-12 shadow-2xl">

    <div class="flex flex-col gap-2 mb-8">
            <span class="text-xs font-mono tracking-widest text-accent uppercase">
                Espace Admin
            </span>
        <h1 class="text-3xl font-serif font-normal text-foreground">
            Connexion
        </h1>
    </div>

    <!-- Affichage du message d'erreur PHP si les identifiants sont faux -->
    <?php if (!empty($error)): ?>
        <div class="mb-6 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-xs font-mono">
            ⚠ <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="/login" method="POST" class="flex flex-col gap-6">

        <!-- Email / Identifiant -->
        <div class="flex flex-col gap-2">
            <label for="email" class="text-[10px] font-mono tracking-widest uppercase text-muted-foreground">Identifiant
                ou Email</label>
            <input type="text" id="email" name="email" placeholder="yann@example.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required
                   class="w-full bg-background/50 border border-border-strong rounded-lg px-4 py-3 text-sm text-foreground focus:outline-none focus:border-accent transition-colors">
        </div>

        <!-- Mot de passe -->
        <div class="flex flex-col gap-2">
            <label for="password" class="text-[10px] font-mono tracking-widest uppercase text-muted-foreground">Mot de
                passe</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required
                   class="w-full bg-background/50 border border-border-strong rounded-lg px-4 py-3 text-sm text-foreground focus:outline-none focus:border-accent transition-colors">
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between mt-4 pt-6 border-t border-border-strong">
            <a href="/" class="text-xs font-mono text-muted-foreground hover:text-foreground transition-colors">
                ← Retour au site
            </a>
            <button type="submit"
                    class="bg-accent text-accent-contrast font-mono text-xs uppercase tracking-wider px-6 py-2.5 rounded-full hover:scale-105 active:scale-95 transition-all">
                Entrer
            </button>
        </div>
    </form>
</div>

</body>
</html>