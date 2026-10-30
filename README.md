# Chrysalide

Carnet familial de fiches (recettes, bricolage, couture, jardin…), installable comme une application. Les fiches sont enregistrées sur le serveur (MySQL) et retrouvées sur n'importe quel appareil ou navigateur. Le carnet garde aussi une copie sur l'appareil : il fonctionne sans réseau et se synchronise au retour de la connexion.

## Fichiers

| Fichier | Rôle | À déposer sur IONOS ? |
|---|---|---|
| `index.html` | Le carnet | oui |
| `sw.js`, `manifest.webmanifest`, `*.png` | Appli installable, hors ligne, icônes | oui |
| `api.php`, `lib.php` | API de synchronisation | oui |
| `admin.php` | Créer les carnets et les codes personnels | oui |
| `.htaccess` | HTTPS forcé, fichiers sensibles bloqués | oui |
| `config.example.php` | Modèle → à copier en `config.php` **sur le serveur** | `config.php` seulement |
| `serveur/schema.sql` | Création des tables (une fois, dans phpMyAdmin) | non |

## Installation sur IONOS (hébergement mutualisé)

### 1. Base de données
1. Espace client IONOS → **Hébergement** → **Bases de données** → créer une base MySQL (ou réutiliser une existante : toutes les tables commencent par `chry_`).
2. Ouvrir **phpMyAdmin** sur cette base → onglet **SQL** → coller le contenu de `serveur/schema.sql` → **Exécuter**.
3. Noter le nom d'hôte, le nom de la base, l'utilisateur et le mot de passe.

### 2. Sous-domaine et HTTPS
1. **Domaines & SSL** → ajouter un sous-domaine, par ex. `chrysalide.mondomaine.fr`, pointant vers un **nouveau dossier** de l'hébergement (ex. `/chrysalide`).
2. Activer le **certificat SSL** sur ce sous-domaine (le certificat gratuit suffit). Sans HTTPS, l'appli ne s'installe pas et ne marche pas hors ligne.

### 3. Fichiers
1. Déposer dans le dossier (SFTP / VS Code) tous les fichiers marqués « oui », **`.htaccess` compris** (fichier caché).
2. Sur le serveur, copier `config.example.php` en `config.php` et le remplir : infos MySQL + un mot de passe admin long.

### 4. Créer les carnets
1. Ouvrir `https://chrysalide.mondomaine.fr/admin.php`, puis entrer le mot de passe admin.
2. **Nouveau carnet** → « Claudie » → le code personnel s'affiche (ex. `K7MQ-R4TX`). **Le noter** : il ne sera plus affiché. S'il est perdu, « Nouveau code » en crée un autre (l'ancien cesse de marcher).
3. Un carnet par personne : chacun ne voit que ses fiches.

### 5. Vérifier
- `https://…/config.php` doit renvoyer **403** (interdit) et `https://…/serveur/schema.sql` **404**.
- Ouvrir `https://…/`, taper le code : « ✓ Carnet à jour et sauvegardé » doit s'afficher.

## Pour ta mère (une seule fois)
1. Dans l'ancien carnet (dalagil.github.io/Chrysalide) : 💾 Sauvegarde → **Préparer et copier** → coller dans un e-mail à elle-même.
2. Ouvrir la nouvelle adresse dans Chrome → taper son code → menu ⋮ → **Installer l'application**.
3. Dans la nouvelle appli : 💾 Sauvegarde → coller → **Importer le texte collé**. Les fiches partent sur le serveur.
4. Supprimer l'ancienne icône Chrysalide de l'écran d'accueil pour ne pas confondre.

## Mettre à jour plus tard
1. Remplacer les fichiers modifiés sur le serveur.
2. **Dans `sw.js`, augmenter `VERSION`** (`"v4"` → `"v5"`…), sinon les téléphones gardent l'ancienne version.

## Comment marche la synchro
- Chaque fiche a un identifiant et une date de modification. Si la même fiche est modifiée sur deux appareils, **la modification la plus récente l'emporte**.
- Les changements faits hors ligne sont mis en attente (« ⏳ N modifications en attente ») et partent automatiquement au retour du réseau, à l'ouverture de l'appli et toutes les minutes.
- Les fiches supprimées sont aussi supprimées sur les autres appareils.
- « Fermer le carnet sur cet appareil » (en bas de 💾 Sauvegarde) efface la copie locale ; tout reste sur le serveur.

## Sauvegarde du serveur
IONOS sauvegarde les bases, mais un export de temps en temps ne fait pas de mal : phpMyAdmin → **Exporter** sur les tables `chry_*`.
