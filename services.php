<?php
$page = 'services';
require __DIR__ . '/includes/header.php';

$services = get_services();
$flagship = null;
$others = [];
foreach ($services as $s) { if ($s['is_flagship']) { $flagship = $s; } else { $others[] = $s; } }

page_banner(t('services_title'), t('services_sub'), t('nav_services'), 'assets/img/hero/slide-3.jpg');
?>

<section class="section">
  <div class="container">
    <div class="services-grid reveal-stagger">
      <?php if ($flagship): ?>
      <article class="svc-card flagship">
        <div class="flagship-body">
          <span class="svc-flag-tag"><?= t('svc_flagship') ?></span>
          <h3><?= e(lcol($flagship, 'name')) ?></h3>
          <p><?= e(lcol($flagship, 'summary')) ?></p>
          <a class="btn btn-light" href="service.php?slug=<?= e($flagship['slug']) ?>"><?= t('svc_more') ?> <?= icon('arrow') ?></a>
        </div>
        <div class="flagship-media"><img src="assets/img/services/fertility.jpg" alt="" loading="lazy"></div>
      </article>
      <?php endif; ?>
      <?php foreach ($others as $s): ?>
      <article class="svc-card">
        <span class="svc-chip"><?= service_icon($s['icon']) ?></span>
        <h3><?= e(lcol($s, 'name')) ?></h3>
        <p><?= e(lcol($s, 'summary')) ?></p>
        <a class="svc-link" href="service.php?slug=<?= e($s['slug']) ?>"><?= t('svc_more') ?> <?= icon('arrow') ?></a>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
