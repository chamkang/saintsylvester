<?php
require_once __DIR__ . '/_auth.php';
require_admin();

$today = date('Y-m-d');
$stats = [
    'today' => (int)db()->query("SELECT COUNT(*) c FROM appointments WHERE date(starts_at) = '$today' AND status IN ('pending','confirmed')")->fetch()['c'],
    'pending' => (int)db()->query("SELECT COUNT(*) c FROM appointments WHERE status = 'pending'")->fetch()['c'],
    'patients' => (int)db()->query("SELECT COUNT(*) c FROM patients WHERE deleted_at IS NULL")->fetch()['c'],
    'unread' => (int)db()->query("SELECT COUNT(*) c FROM messages WHERE is_read = 0")->fetch()['c'],
];

$todays = db()->query(
    "SELECT a.*, d.full_name doc, s.name_fr svc FROM appointments a
     JOIN doctors d ON d.id = a.doctor_id JOIN services s ON s.id = a.service_id
     WHERE date(a.starts_at) = '$today' AND a.status IN ('pending','confirmed')
     ORDER BY a.starts_at"
)->fetchAll();

$recentPatients = db()->query(
    "SELECT * FROM patients WHERE deleted_at IS NULL ORDER BY id DESC LIMIT 5"
)->fetchAll();

admin_header('Tableau de bord', 'dash');
?>

<div class="cards">
  <div class="card"><span class="big"><?= $stats['today'] ?></span><span class="lbl">RDV aujourd'hui</span></div>
  <div class="card"><span class="big" style="color:#9C6F0E"><?= $stats['pending'] ?></span><span class="lbl">En attente de confirmation</span></div>
  <div class="card"><span class="big"><?= $stats['patients'] ?></span><span class="lbl">Patients enregistrés</span></div>
  <div class="card"><span class="big" style="color:#0B8A55"><?= $stats['unread'] ?></span><span class="lbl">Messages non lus</span></div>
</div>

<div class="panel">
  <h2>Rendez-vous du jour — <?= date('d/m/Y') ?></h2>
  <?php if (!$todays): ?>
    <p class="muted">Aucun rendez-vous aujourd'hui.</p>
  <?php else: ?>
  <table>
    <tr><th>Heure</th><th>Référence</th><th>Patient</th><th>Téléphone</th><th>Service</th><th>Médecin</th><th>Statut</th></tr>
    <?php foreach ($todays as $a): ?>
    <tr>
      <td><strong><?= substr($a['starts_at'], 11, 5) ?></strong></td>
      <td><a href="appointments.php?q=<?= e($a['reference']) ?>"><?= e($a['reference']) ?></a></td>
      <td><?= e($a['booking_for'] === 'other' && $a['other_name'] ? $a['other_name'] . ' (via ' . $a['guest_name'] . ')' : $a['guest_name']) ?></td>
      <td><a href="tel:<?= e($a['guest_phone']) ?>"><?= e($a['guest_phone']) ?></a></td>
      <td><?= e($a['svc']) ?></td>
      <td><?= e($a['doc']) ?></td>
      <td><span class="badge b-<?= e($a['status']) ?>"><?= status_label($a['status']) ?></span></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>

<div class="panel">
  <h2>Dernières inscriptions patients</h2>
  <?php if (!$recentPatients): ?>
    <p class="muted">Aucune inscription pour l'instant.</p>
  <?php else: ?>
  <table>
    <tr><th>Dossier</th><th>Nom</th><th>Téléphone</th><th>Inscrit le</th><th>Vérifié</th></tr>
    <?php foreach ($recentPatients as $p): ?>
    <tr>
      <td><a href="patients.php?q=<?= e($p['mrn']) ?>"><?= e($p['mrn']) ?></a></td>
      <td><?= e($p['first_name'] . ' ' . $p['last_name']) ?></td>
      <td><?= e($p['phone']) ?></td>
      <td><?= e(substr((string)$p['created_at'], 0, 16)) ?></td>
      <td><?= $p['verified_at'] ? '✔' : '—' ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>

<?php admin_footer(); ?>
