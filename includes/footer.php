<?php $lang = current_lang(); ?>
</main>

<section class="cta-band" aria-label="Call to action">
  <div class="cta-bg" data-parallax="0.25" aria-hidden="true"><img src="assets/img/hero/cta.jpg" alt=""></div>
  <div class="cta-orbs" aria-hidden="true"><i></i><i></i><i></i></div>
  <div class="container cta-inner reveal">
    <span class="orn-cross" aria-hidden="true"><i></i><i></i></span>
    <h2><?= t('cta_title') ?></h2>
    <p><?= t('cta_sub') ?></p>
    <a class="btn btn-light btn-lg" href="appointment.php"><?= t('cta_btn') ?> <?= icon('arrow') ?></a>
  </div>
</section>

<footer class="footer">
  <div class="container footer-grid">
    <div class="footer-col footer-about">
      <a class="brand brand-invert" href="index.php">
        <span class="brand-mark"><?= ssmf_logo_light() ?></span>
        <span class="brand-text">
          <span class="brand-name">Saint Sylvester</span>
          <span class="brand-tag">MEDICAL FOUNDATION</span>
        </span>
      </a>
      <p><?= t('foot_desc') ?></p>
      <a class="btn btn-whatsapp" href="https://wa.me/<?= CLINIC_WHATSAPP ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> WhatsApp</a>
    </div>
    <div class="footer-col">
      <h3><?= t('foot_links') ?></h3>
      <ul>
        <li><a href="about.php"><?= t('nav_about') ?></a></li>
        <li><a href="services.php"><?= t('nav_services') ?></a></li>
        <li><a href="doctors.php"><?= t('nav_doctors') ?></a></li>
        <li><a href="appointment.php"><?= t('nav_book') ?></a></li>
        <li><a href="register.php"><?= t('nav_register') ?></a></li>
        <li><a href="manage.php"><?= t('nav_manage') ?></a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h3><?= t('foot_hours') ?></h3>
      <ul class="hours-list">
        <li><span><?= t('hours_all') ?></span><strong><?= t('hours_all_val') ?></strong></li>
      </ul>
      <a class="footer-phone" href="tel:<?= CLINIC_PHONE_LINK ?>"><?= icon('phone') ?> <?= CLINIC_PHONE ?></a>
    </div>
    <div class="footer-col">
      <h3><?= t('foot_find') ?></h3>
      <div class="footer-map">
        <iframe src="<?= CLINIC_MAP_EMBED ?>" title="Google Maps — <?= CLINIC_NAME ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
      </div>
      <a class="footer-directions" href="<?= CLINIC_MAP_LINK ?>" target="_blank" rel="noopener"><?= icon('pin') ?> <?= t('foot_directions') ?></a>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="container">
      <span>© <?= date('Y') ?> <?= CLINIC_NAME ?>. <?= t('foot_rights') ?></span>
    </div>
  </div>
</footer>

<div class="mobile-bar" role="navigation" aria-label="Quick actions">
  <a href="tel:<?= CLINIC_PHONE_LINK ?>"><?= icon('phone') ?> <?= t('mb_call') ?></a>
  <a class="mobile-bar-book" href="appointment.php"><?= icon('calendar') ?> <?= t('mb_book') ?></a>
</div>

<script src="assets/js/main.js" defer></script>
<?php if (!empty($extraScripts)) foreach ($extraScripts as $src) echo '<script src="' . e($src) . '" defer></script>' . PHP_EOL; ?>
</body>
</html>
