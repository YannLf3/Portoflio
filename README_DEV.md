# 🚀 Environnement de développement

Mémo de démarrage et d'arrêt de l'environnement local

**Stack :** PHP MVC · Docker · Tailwind CSS · npm

---

## 📋 Sommaire

- 🚀 Démarrage rapide
- 🐳 1. Docker
- 🎨 2. Tailwind CSS
- 🐘 3. Serveur PHP
- 🌐 Accès aux services
- 🛑 Arrêter l'environnement
- 🔍 Vérifications
- ⚡ Commandes essentielles

---

## 🚀 Démarrage rapide

Après avoir rallumé ton ordinateur et ouvert PhpStorm, suis simplement ces étapes :

```
🐳 Docker      →  démarre les conteneurs
🎨 Tailwind    →  compile automatiquement le CSS
🐘 PHP         →  lance le serveur web
🌐 Navigateur  →  ouvre ton application
```

### ⚡ Les 3 commandes à retenir

**Terminal 1 — Docker**

```bash
docker compose up -d
```

**Terminal 2 — Tailwind**

```bash
npm run dev
```

**Terminal 3 — PHP**

```bash
php -S localhost:8000 -t public
```

> 💡 Les terminaux 2 et 3 doivent rester ouverts pendant que tu développes.

---

## 🐳 1. Docker

Docker permet notamment de faire fonctionner les services nécessaires au projet, comme la base de données.

### ▶️ Démarrer les conteneurs

Ouvre Docker Desktop, puis dans le terminal PhpStorm, à la racine du projet :

```bash
docker compose up -d
```

### 🔍 Vérifier l'état des conteneurs

```bash
docker compose ps
```

Tu dois retrouver les différents conteneurs du projet avec un statut actif.

---

## 🎨 2. Tailwind CSS

Tailwind surveille tes fichiers et génère automatiquement le CSS utilisé par l'application.

Le fichier CSS généré se trouve ici :

```
public/css/style.css
```

### ▶️ Lancer Tailwind

Dans un premier terminal PhpStorm :

```bash
npm run dev
```

Laisse ce terminal ouvert pendant toute ta session.

> 💡 **Pourquoi ?**
>
> Lorsque tu modifies tes classes Tailwind ou tes fichiers sources, le watcher recompilera automatiquement le CSS.

---

## 🐘 3. Serveur PHP

Le serveur PHP permet d'accéder à ton application dans le navigateur.

### ▶️ Lancer le serveur

Ouvre un second terminal PhpStorm avec le bouton `+`.

Puis exécute :

```bash
php -S localhost:8000 -t public
```

Le dossier `public/` sert de point d'entrée à ton application.

> ⚠️ Ne ferme pas ce terminal pendant ton développement.

---

## 🌐 Accès aux services

Une fois Docker, Tailwind et PHP démarrés, voici les différentes adresses disponibles :

| Service | URL |
| --- | --- |
| 🏠 Site public | http://localhost:8000/ |
| 🔐 Connexion Back-Office | http://localhost:8000/login |
| ⚙️ Administration | http://localhost:8000/admin |
| 🗄️ phpMyAdmin | http://localhost:8080 |

### 🗄️ phpMyAdmin

Pour gérer la base de données, ouvre :

```
http://localhost:8080
```

**🔑 Identifiants**

- Utilisateur : `root`
- Mot de passe : `root`

---

## 🛑 Arrêter l'environnement

Lorsque tu as terminé ta session de développement, arrête proprement chaque service.

### 1️⃣ Arrêter Tailwind

Dans le terminal où `npm run dev` tourne :

```
Ctrl + C
```

### 2️⃣ Arrêter le serveur PHP

Dans le terminal où le serveur PHP tourne :

```
Ctrl + C
```

### 3️⃣ Arrêter Docker

Pour arrêter les conteneurs et libérer les ressources de ton ordinateur :

```bash
docker compose stop
```

> 💡 `stop` arrête les conteneurs sans les supprimer. Tu pourras donc les redémarrer avec `docker compose up -d`.

---

## 🔍 Vérifications

Si quelque chose ne fonctionne pas, vérifie dans cet ordre :

### 🐳 Docker

```bash
docker compose ps
```

Les conteneurs doivent être actifs.

### 🎨 Tailwind

Vérifie que ce processus tourne :

```bash
npm run dev
```

Puis regarde si :

```
public/css/style.css
```

est bien mis à jour.

### 🐘 PHP

Vérifie que le serveur tourne avec :

```bash
php -S localhost:8000 -t public
```

Puis ouvre :

```
http://localhost:8000/
```

### 🗄️ Base de données

Vérifie que le conteneur de base de données est actif avec :

```bash
docker compose ps
```

Puis accède à :

```
http://localhost:8080
```

---

## ⚡ Commandes essentielles

### ▶️ Démarrage

```bash
# Docker
docker compose up -d

# Tailwind
npm run dev

# Serveur PHP
php -S localhost:8000 -t public
```

### 🔍 Vérification Docker

```bash
docker compose ps
```

### 🛑 Arrêt

```
Ctrl + C
```

Puis :

```bash
docker compose stop
```

---

## ✅ Checklist quotidienne

Avant de commencer à coder :

- [ ] 🐳 Docker Desktop est lancé
- [ ] 📦 Les conteneurs Docker sont actifs
- [ ] 🎨 `npm run dev` tourne
- [ ] 🐘 Le serveur PHP tourne
- [ ] 🌐 `localhost:8000` fonctionne
- [ ] 🗄️ phpMyAdmin fonctionne si nécessaire

---

## 🎯 Architecture des terminaux

Pour garder ton environnement propre, tu peux utiliser 3 terminaux dans PhpStorm :

```
┌─────────────────────────────────────────────┐
│ TERMINAL 1                                  │
│ 🐳 Docker                                   │
│                                             │
│ $ docker compose up -d                      │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ TERMINAL 2                                  │
│ 🎨 Tailwind CSS                             │
│                                             │
│ $ npm run dev                               │
│                                             │
│ ⏳ Laisse tourner                           │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ TERMINAL 3                                  │
│ 🐘 Serveur PHP                              │
│                                             │
│ $ php -S localhost:8000 -t public           │
│                                             │
│ ⏳ Laisse tourner                           │
└─────────────────────────────────────────────┘
```

---

## 🚀 Prêt à coder !

Si les trois services sont démarrés :

```
🐳 Docker       ✅
🎨 Tailwind     ✅
🐘 PHP          ✅
🌐 Application  ✅
```

Tu peux commencer à développer. 🔥

---

> 💡 En cas de problème, commence toujours par vérifier `docker compose ps`, puis les terminaux Tailwind et PHP.
