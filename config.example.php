<?php
// Copier ce fichier en « config.php » (sur le serveur seulement) et le remplir.
// config.php ne doit JAMAIS être envoyé sur GitHub (il est dans .gitignore).
// Les informations MySQL se trouvent dans l'espace client IONOS : Hébergement → Bases de données.

return [
  'db_host' => 'dbXXXXXXXX.hosting-data.io',   // « Nom d'hôte »
  'db_name' => 'dbsXXXXXXXX',                  // « Nom de la base de données »
  'db_user' => 'dbuXXXXXXXX',                  // « Nom d'utilisateur »
  'db_pass' => 'mot-de-passe-de-la-base',

  // Mot de passe de la page admin.php (pour créer les codes de chacun). Choisissez-en un long.
  'admin_password' => 'changez-moi-avec-une-longue-phrase',
];
