<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Administration</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div>
    <form action="/admin/login" method="post">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" required>
        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Se connecter</button>
    </form>
</div>
</body>
</html>