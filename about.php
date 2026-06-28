<?php
$page = 'about';
require __DIR__ . '/includes/header.php';

$founder = db()->query("SELECT * FROM doctors WHERE slug = 'dr-akwa-john'")->fetch();
$founderPhoto = ($founder && $founder['photo'] && is_file(__DIR__ . '/' . $founder['photo']))
    ? $founder['photo'] : 'assets/img/team/doctor-1.jpg';

page_banner(t('ab_title'), t('ab_sub'), t('nav_about'), 'assets/img/hero/slide-3.jpg');
?>

<section class="section">
  <div class="container about-grid">
    <div class="collage reveal">
      <span class="orn-cross" aria-hidden="true"><i></i><i></i></span>
      <span class="collage-dots" aria-hidden="true"></span>
      <figure class="collage-main" style="margin:0"><img src="assets/img/about/about-2.jpg" alt="<?= e(t('ab_title')) ?>" loading="lazy"></figure>
      <figure class="collage-second" style="margin:0"><img src="assets/img/about/about-3.jpg" alt="" loading="lazy"></figure>
      <div class="collage-badge">
        <strong><span data-count="<?= e(setting('stat_years')) ?>">0</span><em>+</em></strong>
        <span><?= t('ha_badge') ?></span>
      </div>
    </div>
    <div class="reveal">
      <span class="eyebrow"><?= t('ab_story_t') ?></span>
      <h2><?= t('ab_mission_t') ?></h2>
      <p style="color:var(--n-600)"><?= t('ab_story_p') ?></p>
      <p style="color:var(--n-600)"><?= t('ab_mission_p') ?></p>
      <div class="about-cta-row" style="margin-top:26px">
        <a class="btn btn-primary" href="appointment.php"><?= t('nav_book') ?> <?= icon('arrow') ?></a>
        <span class="about-phone">
          <?= icon('phone') ?>
          <span><small><?= t('ha_call_label') ?></small><a href="tel:<?= CLINIC_PHONE_LINK ?>"><?= CLINIC_PHONE ?></a></span>
        </span>
      </div>
    </div>
  </div>
</section>

<!-- ============ THE FOUNDER ============ -->
<section class="section section-cool founder-section">
  <div class="container founder-grid">
    <div class="founder-visual reveal">
      <span class="founder-frame" aria-hidden="true"></span>
      <span class="collage-dots" aria-hidden="true"></span>
      <figure class="founder-photo" style="margin:0">
        <img src="<?= e($founderPhoto) ?>" alt="Dr. Akwa John — <?= e(t('fd_role')) ?>" loading="lazy">
        <span class="founder-onmc"><?= icon('shield') ?> <?= t('onmc_label') ?> 4529</span>
      </figure>
      <div class="founder-badge">
        <strong><span data-count="30">0</span><em>+</em></strong>
        <span><?= t('fd_badge') ?></span>
      </div>
    </div>

    <div class="reveal">
      <span class="eyebrow"><?= t('fd_eyebrow') ?></span>
      <h2 class="founder-name">Dr. Akwa John</h2>
      <span class="founder-role"><?= t('fd_role') ?></span>
      <p><?= t('fd_p1') ?></p>
      <p><?= t('fd_p2') ?></p>
      <blockquote class="founder-quote">
        <?= t('fd_quote') ?>
        <cite>— Dr. Akwa John</cite>
      </blockquote>
      <div class="founder-stats">
        <div class="fstat"><strong>30<em>+</em></strong><span><?= t('fd_stat_years') ?></span></div>
        <div class="fstat"><strong>2016</strong><span><?= t('fd_stat_founded') ?></span></div>
      </div>
      <a class="btn btn-primary" href="appointment.php?service=fertility"><?= icon('calendar') ?> <?= t('fd_cta') ?></a>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="values-grid reveal-stagger">
      <article class="value-card">
        <span class="svc-chip"><?= icon('award') ?></span>
        <h3><?= t('ab_v1_t') ?></h3>
        <p style="color:var(--n-600);font-size:.94rem;margin:0"><?= t('ab_v1_p') ?></p>
      </article>
      <article class="value-card">
        <span class="svc-chip"><?= icon('heart') ?></span>
        <h3><?= t('ab_v2_t') ?></h3>
        <p style="color:var(--n-600);font-size:.94rem;margin:0"><?= t('ab_v2_p') ?></p>
      </article>
      <article class="value-card">
        <span class="svc-chip"><?= icon('shield') ?></span>
        <h3><?= t('ab_v3_t') ?></h3>
        <p style="color:var(--n-600);font-size:.94rem;margin:0"><?= t('ab_v3_p') ?></p>
      </article>
    </div>
  </div>
</section>

<section class="section section-cool">
  <div class="container fertility-split">
    <div class="reveal">
      <div class="section-head" style="margin-bottom:0">
        <span class="eyebrow"><?= t('ab_why_t') ?></span>
        <h2><?= t('ab_why_t') ?></h2>
      </div>
      <ul class="why-list reveal-stagger" style="grid-template-columns:1fr">
        <li><?= icon('check') ?> <?= t('ab_why_1') ?></li>
        <li><?= icon('check') ?> <?= t('ab_why_2') ?></li>
        <li><?= icon('check') ?> <?= t('ab_why_3') ?></li>
        <li><?= icon('check') ?> <?= t('ab_why_4') ?></li>
      </ul>
    </div>
    <div class="helix-stage reveal" aria-hidden="true">
      <span class="helix-glow"></span>
      <div class="helix" style="height:380px"></div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
