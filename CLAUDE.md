# Chrysalide — consignes pour Claude Code

## ⚠️ Ce dossier EST le site en ligne
`/var/www/chrysalide` est servi directement par Apache : toute modification enregistrée est visible immédiatement par les utilisatrices.
- Faire un `git commit` après chaque modification qui fonctionne (points de retour). Annuler : `git checkout -- <fichier>`.
- **Ne jamais modifier `config.php`** (accès MySQL + mot de passe admin, hors git), sauf demande explicite.
- **Ne jamais écrire, vider ou supprimer dans la base `chrysalide`** (tables `chry_users`, `chry_notes`, `chry_counter`) : ce sont les vraies fiches de Claudie. Lecture seule pour diagnostiquer, et seulement si on le demande.
- **Après toute modification de `index.html` (ou d'un fichier mis en cache), augmenter `VERSION` dans `sw.js`** (`"v4"` → `"v5"`…). Sinon les téléphones gardent l'ancienne version.
- Ne pas relancer `/root/installer.sh` : il ferait un `git pull` par-dessus les modifications locales et régénérerait le mot de passe admin.

## À qui c'est destiné
Carnet familial, **jamais commercialisé** (« c'est pour nous »). Utilisatrice principale : **Claudie**, la mère de Blaise, retraitée, pas à l'aise avec l'informatique, téléphone **Huawei + Chrome**. Elle dicte souvent ses messages (fautes de transcription possibles).
- Textes de l'interface en **français**, **vouvoiement**, phrases courtes et concrètes.
- Gros boutons, peu d'options, pas de jargon. Toute nouveauté doit rester simple pour elle.
- Être honnête sur ce qui n'a pas été testé sur un vrai téléphone (rien n'a jamais été testé sur son Huawei).

## Ce que fait le carnet
Fiches avec titre, catégorie, texte, origine (« recette de Martine »), remarques personnelles, photo facultative (réduite à 700 px, JPEG 0,6). Catégories par défaut : Recettes, Bricolage, Couture, Tapisserie, Jardin, et d'autres ajoutables. Recherche, partage (partage du téléphone ou copie), sauvegarde/import en JSON. Aucune IA, aucune dépendance externe.

## Architecture
- **Hébergement** : VPS IONOS (Debian 12, IP 87.106.40.192), Apache + PHP 8.2 + MariaDB, HTTPS Let's Encrypt (renouvellement automatique).
- **Adresse** : https://chrysalide.87-106-40-192.sslip.io/ (sslip.io : nom gratuit dérivé de l'IP ; si un vrai domaine est acheté plus tard, il faudra réinstaller l'appli sur les téléphones, les fiches restent en base).
- **Dépôt GitHub** : `Dalagil/Chrysalide`, branche **`ionos`** (cette version). La branche `main` est l'ancienne version GitHub Pages (localStorage seul), servie sur dalagil.github.io/Chrysalide/.
- Pare-feu : IONOS (Réseau → Stratégies de pare-feu) **et** ufw sur le serveur ; ports 22, 80, 443 ouverts. Le 3306 (MySQL) est encore ouvert à tous : **à fermer** (passer Workbench par un tunnel SSH).

| Fichier | Rôle |
|---|---|
| `index.html` | Toute l'appli (HTML/CSS/JS sans bibliothèque) |
| `sw.js` | Service worker : hors ligne, cache versionné (`VERSION`) ; ne met jamais en cache les `.php` |
| `manifest.webmanifest`, `*.png` | Appli installable, icônes (logo chrysalide : rond lin, C brun, cocon vert sauge, point doré) |
| `api.php` | API JSON (POST) : `login`, `sync` |
| `lib.php` | Connexion PDO, génération et hachage des codes |
| `admin.php` | Création des carnets et des codes personnels (protégée par `admin_password`) |
| `.htaccess` | HTTPS forcé (sauf `/.well-known/`), blocage de config/lib/sql/md/.git |
| `serveur/schema.sql` | Création des tables |

## Connexion et synchronisation
- Chaque personne a un **code personnel** (8 caractères, ex. `K7MQ-R4TX`), créé dans `admin.php`. Seul son SHA-256 est stocké (`chry_users.code_hash`). Le code est envoyé dans l'en-tête `X-Chrysalide-Code` ; il est saisi une fois par appareil.
- Côté appareil : tout l'état (`S`) est gardé dans **IndexedDB** (repli localStorage) : copie des fiches, `dirty` (modifs à envoyer), `gone` (suppressions), `since` (dernière révision reçue).
- `sync` : envoie les modifs par paquets de 8, reçoit ce qui a changé depuis `since` par paquets de 40. **La modification la plus récente (`ts`) gagne.** Les suppressions sont propagées. Le compteur `chry_counter` donne les numéros de révision.
- Synchro au démarrage, après chaque modification, au retour du réseau, au retour sur l'appli et toutes les minutes. Statut affiché : « ✓ Carnet à jour et sauvegardé » / « ⏳ N modifications en attente ».

## Historique et décisions
- Première version : page publiée sur le compte Claude de Claudie → ne s'ouvrait pas hors de la conversation. Remplacée par une PWA sur GitHub Pages, puis par cette version serveur pour que les fiches suivent sur tous les appareils et navigateurs.
- Le bandeau « Ouvrez dans Chrome » de la version GitHub Pages a été retiré ici (inutile puisque les fiches sont sur le serveur).
- Transfert des anciennes fiches : 💾 Sauvegarde → « Préparer et copier » dans l'ancienne version, puis « Importer le texte collé » dans la nouvelle.

## Idées en attente (à faire seulement si demandé)
- **Bouton micro** (Web Speech API) pour dicter ; le micro du clavier fonctionne déjà. Ne pas le promettre avant de l'avoir testé sur son Huawei.
- **« Coller une recette et la ranger »** : répartir un texte collé entre titre, ingrédients, étapes. Pas d'import automatique depuis un lien (impossible sans serveur dédié). Toujours citer la source, ne pas recopier telles quelles des recettes publiées.
- **Partage avec une amie** (bouton grisé « bientôt » sur l'accueil).
- Fermer le port 3306.
