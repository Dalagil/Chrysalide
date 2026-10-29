# Chrysalide

Carnet personnel (recettes, bricolage, couture, jardin…), installable comme une application sur le téléphone. Aucune dépendance, aucun serveur, aucune IA : tout se passe dans le navigateur.

## Contenu

| Fichier | Rôle |
|---|---|
| `index.html` | Le carnet (fiches stockées dans le `localStorage` du navigateur) |
| `manifest.webmanifest` | Nom, icônes, affichage en appli |
| `sw.js` | Service worker : ouverture sans réseau, mises à jour |
| `icon-192.png`, `icon-512.png` | Icônes de l'appli |
| `icon-maskable-512.png` | Icône pour Android (forme adaptée par le système) |
| `apple-touch-icon.png`, `favicon-32.png` | Icône iPhone et onglet |

## Mise en ligne sur GitHub Pages

> ⚠️ L'adresse finale sera `https://NOMDUCOMPTE.github.io/chrysalide/`. Les fiches sont liées à cette adresse : **ne plus renommer ni le compte ni le dépôt** une fois que ta mère a commencé à s'en servir, sinon le carnet apparaîtra vide (les fiches resteraient récupérables via la Sauvegarde, mais autant l'éviter).

1. Sur github.com, connecté au compte : bouton **New** (nouveau dépôt).
2. Nom : `chrysalide`. Visibilité : **Public** (obligatoire pour Pages en gratuit ; seul le code est public, pas les fiches). Ne rien cocher d'autre. **Create repository**.
3. Sur la page du dépôt vide : lien **uploading an existing file**. Glisser **tous les fichiers** du dossier (pas le dossier lui-même : `index.html` doit être à la racine). **Commit changes**.
4. **Settings** → **Pages** (menu de gauche) → *Source* : **Deploy from a branch** → Branch : **main**, dossier **/ (root)** → **Save**.
5. Attendre 1 à 2 minutes, recharger la page Settings → Pages : l'adresse s'affiche en haut. L'ouvrir pour vérifier.

## Mettre à jour plus tard

1. Remplacer le fichier modifié dans le dépôt (Add file → Upload files).
2. **Dans `sw.js`, augmenter `VERSION`** (`"v1"` → `"v2"`…) et l'envoyer aussi.
3. Sur le téléphone, la nouvelle version arrive à la prochaine ouverture avec du réseau (au pire à la deuxième). Les fiches ne sont pas touchées.

## Transférer les fiches de l'ancienne page Claude

L'ancienne page a déjà le menu 💾 **Sauvegarde** :

1. Ouvrir l'ancienne page (depuis la conversation Claude, là où elle fonctionne) → 💾 Sauvegarde → **Préparer et copier**.
2. Coller ce texte quelque part de sûr (note, e-mail à soi-même) : c'est aussi une sauvegarde.
3. Ouvrir la nouvelle appli → 💾 Sauvegarde → coller le texte dans le cadre → **Importer le texte collé**.

Une fiche déjà présente (même titre, même date) n'est pas importée deux fois.

## Limites connues

- Les fiches restent **sur ce téléphone, dans ce navigateur**. Désinstaller l'appli ou « effacer les données du site » dans Chrome les supprime : faire une Sauvegarde régulièrement.
- Le `localStorage` est limité à environ 5 Mo. Une photo réduite pèse environ 50 à 100 Ko : on peut compter quelques dizaines de fiches avec photo avant le message « Mémoire pleine ». Si ça devient gênant : passer le stockage en IndexedDB (beaucoup plus de place), à faire dans une prochaine version.
- Testé dans Chromium (ordinateur) : installation, enregistrement, import, ouverture hors ligne. **Pas testé sur un Huawei.**
