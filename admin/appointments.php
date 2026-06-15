<?php
require_once __DIR__ . '/_auth.php';
require_admin();

// status transitions (FR-B6/FR-B9) with audit trail (FR-A7)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null) && ($_POST['do'] ?? '') === 'mark_paid') {
    $id = (int)($_POST['id'] ?? 0);
    payment_mark_paid($id, 'DESK', 'cash');
    audit('mark_paid', 'appointment', $id, ['method' => 'cash/desk']);
    header('Location: appointments.php?' . http_build_query(array_filter([
        'q' => $_POST['q'] ?? '', 'status' => $_POST['f_status'] ?? '', 'date' => $_POST['f_date'] ?? '',
    ])));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $id = (int)($_POST['id'] ?? 0);
    $to = $_POST['to'] ?? '';
    $allowed = [
        'pending'   => ['confirmed', 'cancelled'],
        'confirmed' => ['completed', 'cancelled', 'no_show'],
    ];
    $st = db()->prepare("SELECT status FROM appointments WHERE id = ?");
    $st->execute([$id]);
    $cur = $st->fetch()['status'] ?? null;
    if ($cur && in_array($to, $allowed[$cur] ?? [], true)) {
        db()->prepare("UPDATE appointments SET status = ?, updated_at = ?, cancel_reason = CASE WHEN ? = 'cancelled' THEN 'clinic' ELSE cancel_reason END WHERE id = ?")
           ->execute([$to, date('Y-m-d H:i:s'), $to, $id]);
        audit('status_change', 'appointment', $id, ['from' => $cur, 'to' => $to]);
    }
    header('Location: appointments.php?' . http_build_query(array_filter([
        'q' => $_POST['q'] ?? '', 'status' => $_POST['f_status'] ?? '', 'date' => $_POST['f_date'] ?? '',
    ])));
    exit;
}

$q = trim($_GET['q'] ?? '');
$fStatus = $_GET['status'] ?? '';
$fDate = $_GET['date'] ?? '';

$where = [];
$args = [];
if ($q !== '') { $where[] = "(a.reference LIKE ? OR a.guest_name LIKE ? OR a.guest_phone LIKE ?)"; array_push($args, "%$q%", "%$q%", "%$q%"); }
if (in_array($fStatus, ['pending','confirmed','completed','cancelled','no_show'], true)) { $where[] = "a.status = ?"; $args[] = $fStatus; }
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fDate)) { $where[] = "date(a.starts_at) = ?"; $args[] = $fDate; }

$sql = "SELECT a.*, d.full_name doc, s.name_fr svc, p.mrn patient_mrn, p.verified_at patient_verified
        FROM appointments a
        JOIN doctors d ON d.id = a.doctor_id
        JOIN services s ON s.id = a.service_id
        LEFT JOIN patients p ON p.id = a.patient_id"
     . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
     . " ORDER BY a.starts_at DESC LIMIT 200";
$st = db()->prepare($sql);
$st->execute($args);
$rows = $st->fetchAll();

admin_header('Rendez-vous', 'appt');
?>

<form class="filters" method="get">
  <input type="text" name="q" placeholder="Référence, nom, téléphone…" value="<?= e($q) ?>">
  <select name="status">
    <option value="">— Tous statuts —</option>
    <?php foreach (['pending','confirmed','completed','cancelled','no_show'] as $s): ?>
      <option value="<?= $s ?>" <?= $fStatus === $s ? 'selected' : '' ?>><?= status_label($s) ?></option>
    <?php endforeach; ?>
  </select>
  <input type="date" name="date" value="<?= e($fDate) ?>">
  <button class="btn btn-blue" type="submit">Filtrer</button>
  <a class="btn btn-mut" href="appointments.php">Réinitialiser</a>
</form>

<div class="panel">
  <?php if (!$rows): ?>
    <p class="muted">Aucun rendez-vous trouvé.</p>
  <?php else: ?>
  <table>
    <tr><th>Date & heure</th><th>Référence</th><th>Patient</th><th>Téléphone</th><th>Service</th><th>Médecin</th><th>Statut</th><th>Paiement</th><th>Actions</th></tr>
    <?php foreach ($rows as $a): ?>
    <tr>
      <td><strong><?= e(substr($a['starts_at'], 0, 16)) ?></strong></td>
      <td><?= e($a['reference']) ?><?php if ($a['notes']): ?><br><span class="muted" title="<?= e($a['notes']) ?>">📝 note</span><?php endif; ?></td>
      <td>
        <?= e($a['booking_for'] === 'other' && $a['other_name'] ? $a['other_name'] . ' (via ' . $a['guest_name'] . ')' : $a['guest_name']) ?>
        <?php if (!empty($a['patient_mrn'])): ?>
          <br><a href="patients.php?q=<?= e($a['patient_mrn']) ?>" class="mrn-tag" title="<?= $a['patient_verified'] ? 'Dossier vérifié' : 'Dossier à vérifier à l\'accueil' ?>"><?= e($a['patient_mrn']) ?><?= $a['patient_verified'] ? ' ✔' : '' ?></a>
        <?php endif; ?>
      </td>
      <td><a href="tel:<?= e($a['guest_phone']) ?>"><?= e($a['guest_phone']) ?></a></td>
      <td><?= e($a['svc']) ?></td>
      <td><?= e($a['doc']) ?></td>
      <td><span class="badge b-<?= e($a['status']) ?>"><?= status_label($a['status']) ?></span></td>
      <td>
        <?php $ps = $a['payment_status'] ?? 'waived'; ?>
        <?php if ($ps === 'paid'): ?>
          <span class="badge b-confirmed">Payé</span><?php if ($a['amount']): ?><br><span class="muted"><?= number_format((int)$a['amount'], 0, ',', ' ') ?> <?= e($a['currency'] ?: 'FCFA') ?></span><?php endif; ?>
        <?php elseif ($ps === 'unpaid'): ?>
          <span class="badge b-pending">Non payé</span>
          <form method="post" style="margin:6px 0 0">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
            <input type="hidden" name="do" value="mark_paid">
            <input type="hidden" name="q" value="<?= e($q) ?>">
            <input type="hidden" name="f_status" value="<?= e($fStatus) ?>">
            <input type="hidden" name="f_date" value="<?= e($fDate) ?>">
            <button class="btn btn-ok" type="submit">Marquer payé</button>
          </form>
        <?php else: ?>
          <span class="muted">—</span>
        <?php endif; ?>
      </td>
      <td>
        <div class="actions">
        <?php
        $buttons = [
            'pending'   => [['confirmed', 'Confirmer', 'btn-ok'], ['cancelled', 'Annuler', 'btn-danger']],
            'confirmed' => [['completed', 'Terminé', 'btn-blue'], ['no_show', 'Absent', 'btn-mut'], ['cancelled', 'Annuler', 'btn-danger']],
        ][$a['status']] ?? [];
        foreach ($buttons as [$to, $label, $cls]): ?>
          <form method="post">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
            <input type="hidden" name="to" value="<?= $to ?>">
            <input type="hidden" name="q" value="<?= e($q) ?>">
            <input type="hidden" name="f_status" value="<?= e($fStatus) ?>">
            <input type="hidden" name="f_date" value="<?= e($fDate) ?>">
            <button class="btn <?= $cls ?>" type="submit"><?= $label ?></button>
          </form>
        <?php endforeach; ?>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>

<?php admin_footer(); ?>
