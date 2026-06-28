<?php
$page = 'doctors';
require __DIR__ . '/includes/header.php';

$doctors = db()->query("SELECT * FROM doctors WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
$svcByDoc = [];
$q = db()->query(
    "SELECT ds.doctor_id, s.name_fr, s.name_en FROM doctor_service ds
     JOIN services s ON s.id = ds.service_id WHERE s.is_active = 1 ORDER BY s.sort_order"
);
foreach ($q->fetchAll() as $r) $svcByDoc[$r['doctor_id']][] = lcol($r, 'name');

page_banner(t('docs_title'), t('docs_sub'), t('nav_doctors'), 'assets/img/hero/slide-2.jpg');
?>

<section class="section">
  <div class="container">
    <div class="doctors-grid doctors-2col reveal-stagger">
      <?php foreach ($doctors as $d): ?>
      <article class="doc-card doc-wide">
        <div class="doc-photo">
          <?= doctor_photo($d) ?>
          <div class="doc-overlay">
            <a class="btn btn-primary" href="appointment.php?doctor=<?= (int)$d['id'] ?>"><?= t('doc_book') ?></a>
          </div>
        </div>
        <div class="doc-meta">
          <h3><?= e($d['full_name']) ?></h3>
          <span class="doc-spec"><?= e(lcol($d, 'specialty')) ?></span>
          <p class="doc-bio"><?= e(lcol($d, 'bio')) ?></p>
          <ul class="doc-facts">
            <?php if (!empty($d['onmc'])): ?><li><?= icon('shield') ?> <span><?= t('onmc_label') ?> <strong><?= e($d['onmc']) ?></strong></span></li><?php endif; ?>
            <li><?= icon('calendar') ?> <span><?= e(doctor_days((int)$d['id'])) ?></span></li>
            <li><?= icon('globe') ?> <span><?= e($d['languages']) ?></span></li>
          </ul>
          <?php if (!empty($svcByDoc[$d['id']])): ?>
            <p class="doc-svcs"><?= e(implode(' · ', $svcByDoc[$d['id']])) ?></p>
          <?php endif; ?>
          <a class="btn btn-primary doc-book-btn" href="appointment.php?doctor=<?= (int)$d['id'] ?>"><?= t('doc_book') ?> <?= icon('arrow') ?></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
