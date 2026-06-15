<?php
require_once __DIR__ . '/_auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['do'] ?? '') === 'read' && $id) {
        db()->prepare("UPDATE messages SET is_read = 1 WHERE id = ?")->execute([$id]);
    }
    header('Location: messages.php');
    exit;
}

$rows = db()->query("SELECT * FROM messages ORDER BY is_read ASC, id DESC LIMIT 100")->fetchAll();

admin_header('Messages', 'msg');
?>

<div class="panel">
  <?php if (!$rows): ?>
    <p class="muted">Aucun message.</p>
  <?php else: ?>
  <table>
    <tr><th></th><th>Reçu le</th><th>Nom</th><th>Contact</th><th>Message</th><th></th></tr>
    <?php foreach ($rows as $m): ?>
    <tr style="<?= $m['is_read'] ? '' : 'font-weight:600;background:#FBFDFF' ?>">
      <td><?= $m['is_read'] ? '' : '●' ?></td>
      <td style="white-space:nowrap"><?= e(substr((string)$m['created_at'], 0, 16)) ?></td>
      <td><?= e($m['name']) ?></td>
      <td>
        <a href="tel:<?= e($m['phone']) ?>"><?= e($m['phone']) ?></a>
        <?php if ($m['email']): ?><br><a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a><?php endif; ?>
      </td>
      <td style="max-width:420px;font-weight:400"><?= nl2br(e($m['body'])) ?></td>
      <td>
        <?php if (!$m['is_read']): ?>
        <form method="post" style="margin:0">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
          <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
          <input type="hidden" name="do" value="read">
          <button class="btn btn-mut" type="submit">Marquer lu</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>

<?php admin_footer(); ?>
