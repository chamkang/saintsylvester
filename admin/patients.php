<?php
require_once __DIR__ . '/_auth.php';
require_admin();

// mark verified at first physical visit (FR-R6)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['do'] ?? '') === 'verify' && $id) {
        db()->prepare("UPDATE patients SET verified_at = ?, updated_at = ? WHERE id = ? AND verified_at IS NULL")
           ->execute([date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $id]);
        audit('verify', 'patient', $id);
    }
    header('Location: patients.php?q=' . urlencode($_POST['q'] ?? ''));
    exit;
}

$q = trim($_GET['q'] ?? '');
$args = [];
$sql = "SELECT * FROM patients WHERE deleted_at IS NULL";
if ($q !== '') {
    $sql .= " AND (mrn LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR phone LIKE ?)";
    array_push($args, "%$q%", "%$q%", "%$q%", "%$q%");
}
$sql .= " ORDER BY id DESC LIMIT 100";
$st = db()->prepare($sql);
$st->execute($args);
$rows = $st->fetchAll();

admin_header('Patients', 'pat');
?>

<form class="filters" method="get">
  <input type="text" name="q" placeholder="MRN, nom, téléphone…" value="<?= e($q) ?>" style="min-width:280px">
  <button class="btn btn-blue" type="submit">Rechercher</button>
  <a class="btn btn-mut" href="patients.php">Tous</a>
</form>

<div class="panel">
  <?php if (!$rows): ?>
    <p class="muted">Aucun patient trouvé.</p>
  <?php else: ?>
  <table>
    <tr><th>Dossier</th><th>Nom</th><th>Naiss.</th><th>Sexe</th><th>Sit. mat.</th><th>Téléphone</th><th>Quartier</th><th>Groupe</th><th>Allergies</th><th>Inscrit le</th><th>Vérifié</th></tr>
    <?php $msLabels = ['single' => 'Célibataire', 'married' => 'Marié(e)', 'divorced' => 'Divorcé(e)', 'widowed' => 'Veuf/Veuve']; ?>
    <?php foreach ($rows as $p): ?>
    <tr>
      <td><strong><?= e($p['mrn']) ?></strong></td>
      <td><?= e($p['first_name'] . ' ' . $p['last_name']) ?></td>
      <td><?= e($p['dob']) ?></td>
      <td><?= e($p['sex']) ?></td>
      <td><?= e($msLabels[$p['marital_status'] ?? ''] ?? '—') ?></td>
      <td><a href="tel:<?= e($p['phone']) ?>"><?= e($p['phone']) ?></a></td>
      <td><?= e($p['address'] ?? '—') ?></td>
      <td><?= e($p['blood_group'] ?? '—') ?></td>
      <td><?= e($p['allergies'] ?? '—') ?></td>
      <td><?= e(substr((string)$p['created_at'], 0, 10)) ?></td>
      <td>
        <?php if ($p['verified_at']): ?>✔ <span class="muted"><?= e(substr($p['verified_at'], 0, 10)) ?></span>
        <?php else: ?>
        <form method="post" style="margin:0">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
          <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          <input type="hidden" name="do" value="verify">
          <input type="hidden" name="q" value="<?= e($q) ?>">
          <button class="btn btn-ok" type="submit">Vérifier</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>

<?php admin_footer(); ?>
