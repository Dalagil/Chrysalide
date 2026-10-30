<?php
// API de Chrysalide : connexion par code personnel et synchronisation des fiches.
// Tout passe en POST, en JSON. Le code personnel est envoyé dans l'en-tête X-Chrysalide-Code.
require __DIR__ . '/lib.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function out($data, int $status = 200): void {
  http_response_code($status);
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(['error' => 'méthode'], 405);

$in = json_decode(file_get_contents('php://input') ?: '', true);
if (!is_array($in)) out(['error' => 'requête invalide'], 400);

try {
  $db = chry_db();
} catch (Throwable $e) {
  out(['error' => 'serveur indisponible'], 503);
}

// Retrouve la personne à partir de son code.
function current_user(PDO $db): ?array {
  $code = $_SERVER['HTTP_X_CHRYSALIDE_CODE'] ?? '';
  if ($code === '') return null;
  $st = $db->prepare('SELECT id, name, cats, cats_ts FROM chry_users WHERE code_hash = ?');
  $st->execute([chry_hash_code($code)]);
  return $st->fetch() ?: null;
}

function next_rev(PDO $db): int {
  $db->exec('UPDATE chry_counter SET v = LAST_INSERT_ID(v + 1) WHERE k = 1');
  return (int)$db->query('SELECT LAST_INSERT_ID()')->fetchColumn();
}

$user = current_user($db);
if (!$user) {
  usleep(800000); // ralentit les essais au hasard
  out(['error' => 'code inconnu'], 401);
}

$action = $in['a'] ?? '';

if ($action === 'login') {
  out(['name' => $user['name']]);
}

if ($action === 'sync') {
  $uid = (int)$user['id'];
  $since = max(0, (int)($in['since'] ?? 0));
  $changes = is_array($in['changes'] ?? null) ? $in['changes'] : [];
  if (count($changes) > 50) out(['error' => 'trop de fiches à la fois'], 413);

  $accepted = [];
  $db->beginTransaction();
  try {
    $sel = $db->prepare('SELECT ts FROM chry_notes WHERE user_id = ? AND id = ? FOR UPDATE');
    $ins = $db->prepare('INSERT INTO chry_notes (user_id, id, data, ts, deleted, rev) VALUES (?, ?, ?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE data = VALUES(data), ts = VALUES(ts), deleted = VALUES(deleted), rev = VALUES(rev)');
    foreach ($changes as $ch) {
      $id = (string)($ch['id'] ?? '');
      $ts = (int)($ch['ts'] ?? 0);
      if (!preg_match('/^[A-Za-z0-9_-]{1,40}$/', $id) || $ts <= 0) continue;
      $deleted = !empty($ch['deleted']) ? 1 : 0;
      $data = $deleted ? '{}' : json_encode($ch['data'] ?? new stdClass(), JSON_UNESCAPED_UNICODE);
      if ($data === false || strlen($data) > 6000000) continue;
      $sel->execute([$uid, $id]);
      $cur = $sel->fetchColumn();
      if ($cur !== false && (int)$cur >= $ts) { $accepted[] = $id; continue; } // la version du serveur est plus récente
      $ins->execute([$uid, $id, $data, $ts, $deleted, next_rev($db)]);
      $accepted[] = $id;
    }
    // Catégories ajoutées : la plus récente gagne.
    $cats = $in['cats'] ?? null;
    if (is_array($cats) && isset($cats['list'], $cats['ts']) && is_array($cats['list']) && (int)$cats['ts'] > (int)$user['cats_ts']) {
      $list = array_values(array_filter(array_map(fn($c) => mb_substr(trim((string)$c), 0, 60), $cats['list']), 'strlen'));
      $db->prepare('UPDATE chry_users SET cats = ?, cats_ts = ? WHERE id = ?')
         ->execute([json_encode($list, JSON_UNESCAPED_UNICODE), (int)$cats['ts'], $uid]);
      $user['cats'] = json_encode($list, JSON_UNESCAPED_UNICODE);
      $user['cats_ts'] = (int)$cats['ts'];
    }
    $db->commit();
  } catch (Throwable $e) {
    $db->rollBack();
    out(['error' => 'enregistrement impossible'], 500);
  }

  // Renvoie ce qui a changé depuis la dernière synchro, par paquets.
  $limit = 40;
  $st = $db->prepare('SELECT id, data, ts, deleted, rev FROM chry_notes WHERE user_id = ? AND rev > ? ORDER BY rev LIMIT ' . ($limit + 1));
  $st->execute([$uid, $since]);
  $rows = $st->fetchAll();
  $more = count($rows) > $limit;
  if ($more) array_pop($rows);
  $notes = [];
  $last = $since;
  foreach ($rows as $r) {
    $notes[] = ['id' => $r['id'], 'ts' => (int)$r['ts'], 'deleted' => (bool)$r['deleted'],
                'data' => $r['deleted'] ? null : json_decode($r['data'], true)];
    $last = (int)$r['rev'];
  }
  out([
    'accepted' => $accepted,
    'notes' => $notes,
    'since' => $last,
    'more' => $more,
    'cats' => ['list' => json_decode($user['cats'] ?: '[]', true) ?: [], 'ts' => (int)$user['cats_ts']],
  ]);
}

out(['error' => 'action inconnue'], 400);
