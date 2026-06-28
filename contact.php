<?php
$page = 'contact';
require_once __DIR__ . '/includes/functions.php';

require __DIR__ . '/includes/header.php';
$lang = current_lang();
?>

<?php page_banner(t('ct_title'), t('ct_sub'), t('nav_contact')); ?>

<section class="section" style="padding-top:42px">
  <div class="container contact-layout">

    <div class="contact-cards reveal-stagger">
      <div class="info-card">
        <span class="svc-chip"><?= icon('pin') ?></span>
        <div>
          <h3><?= t('ct_address_t') ?></h3>
          <p><?= e($lang === 'fr' ? CLINIC_ADDRESS_FR : CLINIC_ADDRESS_EN) ?></p>
          <a class="detail" href="<?= CLINIC_MAP_LINK ?>" target="_blank" rel="noopener"><?= t('ct_directions') ?> →</a>
        </div>
      </div>
      <div class="info-card">
        <span class="svc-chip"><?= icon('phone') ?></span>
        <div>
          <h3><?= t('ct_call_t') ?></h3>
          <a class="detail" href="tel:<?= CLINIC_PHONE_LINK ?>"><?= CLINIC_PHONE ?></a>
        </div>
      </div>
      <div class="info-card">
        <span class="svc-chip"><?= icon('whatsapp') ?></span>
        <div>
          <h3><?= t('ct_wa_t') ?></h3>
          <a class="detail" href="https://wa.me/<?= CLINIC_WHATSAPP ?>" target="_blank" rel="noopener"><?= t('ct_wa_btn') ?> →</a>
        </div>
      </div>
      <div class="info-card">
        <span class="svc-chip"><?= icon('clock') ?></span>
        <div>
          <h3><?= t('ct_hours_t') ?></h3>
          <p><?= t('hours_all') ?> : <strong><?= t('hours_all_val') ?></strong></p>
        </div>
      </div>
    </div>

    <div>
      <div class="form-card reveal" style="text-align:center">
        <h2 style="font-size:1.3rem"><?= t('ct_reach_t') ?></h2>
        <p style="color:var(--n-600);margin:8px 0 22px"><?= t('ct_reach_p') ?></p>
        <div style="display:flex;flex-direction:column;gap:12px">
          <a class="btn btn-navy btn-lg" style="width:100%" href="tel:<?= CLINIC_PHONE_LINK ?>"><?= icon('phone') ?> <?= t('ct_call_btn') ?> · <?= CLINIC_PHONE ?></a>
          <a class="btn btn-primary btn-lg" style="width:100%" href="https://wa.me/<?= CLINIC_WHATSAPP ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> <?= t('ct_wa_btn') ?></a>
        </div>
      </div>

      <div class="contact-map reveal" data-tilt style="margin-top:22px">
        <iframe src="<?= CLINIC_MAP_EMBED ?>" title="Google Maps — <?= CLINIC_NAME ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
      </div>
    </div>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
