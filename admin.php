<?php
// Page d'administration : créer un carnet pour quelqu'un et lui donner son code personnel.
require __DIR__ . '/lib.php';
header('Cache-Control: no-store');
header('X-Frame-Options: DENY');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict', 'secure' => !empty($_SERVER['HTTPS'])]);
session_start();

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$msg = ''; $newCode = ''; $newFor = '';
try { $cfg = chry_config(); } catch (Throwable $e) { $cfg = null; $msg = 'Fichier config.php introuvable : copiez config.example.php en config.php et remplissez-le.'; }

if ($cfg && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $act = $_POST['act'] ?? '';
  if ($act === 'login') {
    if (hash_equals((string)$cfg['admin_password'], (string)($_POST['pw'] ?? '')) && $cfg['admin_password'] !== 'changez-moi-avec-une-longue-phrase') {
      session_regenerate_id(true);
      $_SESSION['admin'] = true;
      $_SESSION['csrf'] = bin2hex(random_bytes(16));
    } else { sleep(1); $msg = 'Mot de passe incorrect (ou pas encore changé dans config.php).'; }
  } elseif (!empty($_SESSION['admin']) && hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
    try {
      $db = chry_db();
      if ($act === 'create') {
        $name = trim((string)($_POST['name'] ?? ''));
        if ($name === '') { $msg = 'Indiquez un prénom.'; }
        else {
          $newCode = chry_new_code(); $newFor = $name;
          $db->prepare('INSERT INTO chry_users (name, code_hash) VALUES (?, ?)')->execute([mb_substr($name, 0, 80), chry_hash_code($newCode)]);
        }
      } elseif ($act === 'recode') {
        $id = (int)($_POST['id'] ?? 0);
        $st = $db->prepare('SELECT name FROM chry_users WHERE id = ?'); $st->execute([$id]);
        if ($n = $st->fetchColumn()) {
          $newCode = chry_new_code(); $newFor = $n;
          $db->prepare('UPDATE chry_users SET code_hash = ? WHERE id = ?')->execute([chry_hash_code($newCode), $id]);
        }
      } elseif ($act === 'logout') { $_SESSION = []; session_destroy(); }
    } catch (Throwable $e) { $msg = 'Erreur base de données : vérifiez config.php et que schema.sql a bien été exécuté.'; }
  }
}

$users = [];
if (!empty($_SESSION['admin']) && $cfg) {
  try {
    $users = chry_db()->query('SELECT u.id, u.name, u.created_at, COUNT(n.id) AS nb
      FROM chry_users u LEFT JOIN chry_notes n ON n.user_id = u.id AND n.deleted = 0 GROUP BY u.id ORDER BY u.id')->fetchAll();
  } catch (Throwable $e) { $msg = $msg ?: 'Impossible de lire la base : vérifiez config.php et que schema.sql a bien été exécuté.'; }
}
?><!DOCTYPE html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex"><title>Chrysalide · admin</title>
<style>
body{font:16px/1.5 system-ui,sans-serif;max-width:560px;margin:0 auto;padding:16px;background:#faf6ef;color:#2b2620}
input,button{font:inherit;padding:10px;border-radius:8px;border:1px solid #e5dccf}button{background:#5f6a47;color:#fff;border:0;cursor:pointer}
button.s{background:#e3e8d3;color:#2b2620}table{width:100%;border-collapse:collapse;margin:12px 0}td,th{padding:8px;border-bottom:1px solid #e5dccf;text-align:left}
.code{font:700 28px/1.2 ui-monospace,monospace;letter-spacing:3px;background:#fff;border:2px solid #d4a93a;border-radius:10px;padding:14px;text-align:center;margin:8px 0}
.msg{background:#f6e3c1;padding:10px;border-radius:8px}form.inline{display:inline}
</style></head><body>
<h1>Chrysalide · admin</h1>
<?php if ($msg): ?><p class="msg"><?= h($msg) ?></p><?php endif; ?>
<?php if (empty($_SESSION['admin'])): ?>
  <form method="post"><input type="hidden" name="act" value="login">
    <p><input type="password" name="pw" placeholder="Mot de passe admin" autofocus style="width:100%"></p>
    <button>Entrer</button></form>
<?php else: $csrf = h($_SESSION['csrf']); ?>
  <?php if ($newCode): ?>
    <p>Code personnel de <b><?= h($newFor) ?></b> :</p>
    <div class="code"><?= h($newCode) ?></div>
    <p>Notez-le maintenant : il ne sera plus affiché. Il se saisit une fois sur chaque appareil (majuscules et tiret facultatifs).</p>
  <?php endif; ?>
  <h2>Carnets</h2>
  <table><tr><th>Prénom</th><th>Fiches</th><th></th></tr>
  <?php foreach ($users as $u): ?>
    <tr><td><?= h($u['name']) ?></td><td><?= (int)$u['nb'] ?></td><td>
      <form method="post" class="inline" onsubmit="return confirm('Remplacer le code de <?= h($u['name']) ?> ? L’ancien ne marchera plus sur ses appareils.')">
      <input type="hidden" name="act" value="recode"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><input type="hidden" name="csrf" value="<?= $csrf ?>">
      <button class="s">Nouveau code</button></form></td></tr>
  <?php endforeach; if (!$users): ?><tr><td colspan="3">Aucun carnet pour l’instant.</td></tr><?php endif; ?>
  </table>
  <h2>Nouveau carnet</h2>
  <form method="post"><input type="hidden" name="act" value="create"><input type="hidden" name="csrf" value="<?= $csrf ?>">
    <input name="name" placeholder="Prénom (ex. Claudie)" required> <button>Créer et afficher le code</button></form>
  <form method="post" style="margin-top:24px"><input type="hidden" name="act" value="logout"><input type="hidden" name="csrf" value="<?= $csrf ?>"><button class="s">Se déconnecter</button></form>
<?php endif; ?>
</body></html>
