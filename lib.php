<?php
// Fonctions communes à api.php et admin.php. N'affiche rien si on l'ouvre directement.
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'lib.php') { http_response_code(404); exit; }

function chry_config(): array {
  static $c = null;
  if ($c === null) {
    $f = __DIR__ . '/config.php';
    if (!is_file($f)) { throw new RuntimeException('config.php manquant'); }
    $c = require $f;
  }
  return $c;
}

function chry_db(): PDO {
  static $pdo = null;
  if ($pdo === null) {
    $c = chry_config();
    $pdo = new PDO(
      'mysql:host=' . $c['db_host'] . ';dbname=' . $c['db_name'] . ';charset=utf8mb4',
      $c['db_user'], $c['db_pass'],
      [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
       PDO::ATTR_EMULATE_PREPARES => false]
    );
  }
  return $pdo;
}

// Code lisible et sans caractères ambigus (pas de 0/O, 1/I/L) : ex. « K7MQ-R4TX ».
function chry_new_code(): string {
  $a = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
  $s = '';
  for ($i = 0; $i < 8; $i++) { $s .= $a[random_int(0, strlen($a) - 1)]; }
  return substr($s, 0, 4) . '-' . substr($s, 4);
}

// Normalise ce que la personne tape : majuscules, sans espaces ni tirets.
function chry_hash_code(string $code): string {
  $n = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));
  return hash('sha256', 'chrysalide|' . $n);
}
