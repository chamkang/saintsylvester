<?php
require_once __DIR__ . '/includes/functions.php';

$st = db()->prepare("SELECT * FROM services WHERE slug = ? AND is_active = 1");
$st->execute([$_GET['slug'] ?? '']);
$svc = $st->fetch();
if (!$svc) { header('Location: services.php'); exit; }

$page = 'service';
$pageTitle = lcol($svc, 'name') . ' — ' . CLINIC_NAME;
$pageDesc = lcol($svc, 'summary');
require __DIR__ . '/includes/header.php';

$docs = doctors_for_service((int)$svc['id']);
// Fertility keeps its own photo; every other service uses the Services-page hero
// image for both its banner and the in-page photo.
if ($svc['slug'] === 'fertility') {
    $photo = 'assets/img/services/fertility.jpg';
    if (!is_file(__DIR__ . '/' . $photo)) $photo = 'assets/img/hero/page-banner.jpg';
} else {
    $photo = 'assets/img/hero/slide-3.jpg';
}

$paragraphs = array_filter(array_map('trim', explode("\n\n", (string)lcol($svc, 'body'))));
if (!$paragraphs) $paragraphs = [lcol($svc, 'summary')];
$features = array_filter(array_map('trim', explode('|', (string)lcol($svc, 'features'))));

page_banner(lcol($svc, 'name'), lcol($svc, 'summary'), t('nav_services'), $photo);
?>

<section class="section">
  <div class="container svc-detail-grid">

    <div class="reveal">
      <figure class="svc-hero-photo" style="margin:0 0 26px"><img src="<?= e($photo) ?>" alt="<?= e(lcol($svc, 'name')) ?>" loading="lazy"></figure>

      <div class="svc-body">
        <?php foreach ($paragraphs as $i => $p): ?>
          <p class="<?= $i === 0 ? 'svc-lead' : '' ?>"><?= e($p) ?></p>
        <?php endforeach; ?>
        <p class="svc-duration-note"><?= icon('clock') ?> <?= t('svc_duration', ['min' => (int)$svc['duration_min']]) ?></p>
      </div>

      <?php if ($features): ?>
      <div class="svc-features">
        <h2><?= t('svc_features_t') ?></h2>
        <hr class="head-rule" style="margin-bottom:22px">
        <ul class="feature-list">
          <?php foreach ($features as $f): ?>
            <li><?= icon('check') ?> <?= e($f) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
    </div>

    <aside class="svc-side">
      <div class="summary-card" style="position:static">
        <h3><?= icon('user') ?> <?= t('svc_doctors') ?></h3>
        <?php foreach ($docs as $d): ?>
          <div class="sum-row">
            <span class="doc-avatar"><span><?= e(doctor_initials($d['full_name'])) ?></span></span>
            <span style="margin-left:6px">
              <b><?= e($d['full_name']) ?></b>
              <small><?= e(lcol($d, 'specialty')) ?></small>
              <small><?= t('onmc_label') ?> <?= e($d['onmc']) ?> · <?= e(doctor_days((int)$d['id'])) ?></small>
            </span>
          </div>
        <?php endforeach; ?>
        <?php if (PAYMENT_ENABLED && consultation_fee() > 0): ?>
        <div class="svc-fee-row"><span><?= t('bk_fee_label') ?></span><strong><?= e(money(consultation_fee())) ?></strong></div>
        <?php endif; ?>
        <a class="btn btn-primary" style="width:100%;margin-top:14px" href="appointment.php?service=<?= e($svc['slug']) ?>">
          <?= icon('calendar') ?> <?= t('svc_book') ?>
        </a>
      </div>

      <div class="form-card">
        <h3 style="font-size:1.05rem;display:flex;align-items:center;gap:9px"><?= icon('clock') ?> <?= t('trio_hours_t') ?></h3>
        <ul class="hours-list hours-light">
          <li><span><?= t('hours_all') ?></span><strong><?= t('hours_all_val') ?></strong></li>
        </ul>
      </div>

      <div class="form-card">
        <h3 style="font-size:1.05rem;display:flex;align-items:center;gap:9px"><?= icon('heart') ?> <?= t('trio_help_t') ?></h3>
        <p style="font-size:.9rem;color:var(--n-600)"><?= t('trio_help_p') ?></p>
        <a class="btn btn-outline" style="width:100%;margin-bottom:10px" href="tel:<?= CLINIC_PHONE_LINK ?>"><?= icon('phone') ?> <?= CLINIC_PHONE ?></a>
        <a class="btn btn-whatsapp" style="width:100%" href="https://wa.me/<?= CLINIC_WHATSAPP ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> <?= t('ct_wa_btn') ?></a>
      </div>
    </aside>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
