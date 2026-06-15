<?php
require_once __DIR__ . '/../includes/functions.php';

function admin_user(): ?array {
    return $_SESSION['admin'] ?? null;
}

function require_admin(): array {
    $u = admin_user();
    if (!$u) { header('Location: index.php'); exit; }
    return $u;
}

function audit(string $action, string $entity, int $entityId, array $changes = []): void {
    db()->prepare("INSERT INTO audit_log (user_id, action, entity, entity_id, changes) VALUES (?,?,?,?,?)")
       ->execute([admin_user()['id'] ?? null, $action, $entity, $entityId, json_encode($changes, JSON_UNESCAPED_UNICODE)]);
}

function admin_header(string $title, string $active): void {
    $u = require_admin();
    $unread = (int)db()->query("SELECT COUNT(*) c FROM messages WHERE is_read = 0")->fetch()['c'];
    $pending = (int)db()->query("SELECT COUNT(*) c FROM appointments WHERE status = 'pending'")->fetch()['c'];
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= e($title) ?> · Admin — SSMF</title>
<link rel="icon" type="image/svg+xml" href="../assets/img/favicon.svg">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="adm-shell">
  <aside class="adm-side">
    <div class="adm-brand">SSMF <span>Admin</span></div>
    <nav>
      <a href="dashboard.php" class="<?= $active === 'dash' ? 'on' : '' ?>">Tableau de bord</a>
      <a href="appointments.php" class="<?= $active === 'appt' ? 'on' : '' ?>">Rendez-vous <?= $pending ? "<b class='pill'>$pending</b>" : '' ?></a>
      <a href="patients.php" class="<?= $active === 'pat' ? 'on' : '' ?>">Patients</a>
      <a href="messages.php" class="<?= $active === 'msg' ? 'on' : '' ?>">Messages <?= $unread ? "<b class='pill'>$unread</b>" : '' ?></a>
    </nav>
    <div class="adm-user">
      <span><?= e($u['name']) ?> · <?= e($u['role']) ?></span>
      <a href="logout.php">Déconnexion</a>
    </div>
  </aside>
  <main class="adm-main">
    <h1><?= e($title) ?></h1>
<?php }

function admin_footer(): void {
    echo '</main></div></body></html>';
}

function status_label(string $s): string {
    return ['pending' => 'En attente', 'confirmed' => 'Confirmé', 'completed' => 'Terminé',
            'cancelled' => 'Annulé', 'no_show' => 'Non honoré'][$s] ?? $s;
}
