---
name: verifier-mvc
description: Vérifie que le code du portfolio respecte la stricte séparation Modèle/Vue/Contrôleur (M, V, C Front-End et Back-End PHP) et les standards définis dans AGENTS.md. À utiliser avant de conclure toute modification de script ou de contrôleur, ou quand on demande de contrôler, relire ou valider le code MVC.
---

# Vérifier la séparation MVC

## Étapes

1. Consulter l'aide de l'outil si besoin : `node .agents/verifier-mvc/scripts/check-mvc.js --help`.
2. Lancer la vérification automatique :
   - Sur l'ensemble du projet : `node .agents/verifier-mvc/scripts/check-mvc.js`
   - Uniquement sur le Front-End JS : `node .agents/verifier-mvc/scripts/check-mvc.js --js-only`
   - Sur un fichier précis : `node .agents/verifier-mvc/scripts/check-mvc.js <chemin/vers/fichier>`
3. Si le script signale une violation :
   - **M qui appelle V, ou V qui appelle M :** déplacer l'appel dans un gestionnaire de `C` (`C.handler_...`), qui interroge le Modèle puis demande à la Vue d'afficher ;
   - **M ou C qui accède au DOM (`document`, `window`) :** déplacer cet accès dans une fonction de `V`, appelée par `C` ;
   - **Boucle `foreach` détectée en PHP :** la remplacer impérativement par une fonction de tableau (`array_map`, `array_filter`, `array_reduce`) ou un itérateur, conformément à `AGENTS.md` ;
   - **Absence de typage strict PHP :** ajouter `declare(strict_types=1);` en début de fichier.
4. Relancer le script jusqu'à obtenir « ✅ SUCCÈS : Aucune violation détectée ».
5. Rendre un compte rendu : violations trouvées, corrections apportées et statut final.

## Contraintes

- Ne jamais modifier le script de vérification ni `resources/rules.json` pour faire passer artificiellement le test.
- Le script analyse la structure syntaxique directe (`M.` dans V, `V.` dans M, `document.` dans M ou C) : relire également le code à la main pour traquer toute dépendance indirecte (ex: variable globale partagée non déclarée).
