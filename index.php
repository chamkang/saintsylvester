<?php
$page = 'home';
require __DIR__ . '/includes/header.php';

$services = get_services();
$flagship = null;
$others = [];
foreach ($services as $s) { if ($s['is_flagship']) { $flagship = $s; } else { $others[] = $s; } }
$doctors = db()->query("SELECT * FROM doctors WHERE is_active = 1 ORDER BY sort_order LIMIT 4")->fetchAll();
$testimonials = db()->query("SELECT * FROM testimonials WHERE is_published = 1 ORDER BY sort_order")->fetchAll();

/** "M. & E. T." -> "ME", "S. N." -> "SN" — letter avatar from stored initials */
function testi_letters(string $initials): string {
    preg_match_all('/[A-Za-zÀ-Ý]/u', $initials, $m);
    return strtoupper(implode('', array_slice($m[0], 0, 2)));
}
?>

<!-- ============ HERO — looping video + rotating banner texts ============ -->
<section class="hero-slider" id="heroSlider" aria-label="<?= CLINIC_NAME ?>">

  <div class="hero-video" aria-hidden="true">
    <video autoplay muted loop playsinline preload="auto" poster="assets/img/hero/slide-1.jpg" id="heroVideo">
      <source src="assets/video/dr.mp4" type="video/mp4">
    </video>
  </div>

  <div class="slide active">
    <div class="container slide-content">
      <span class="slide-eyebrow"><?= t('hero_eyebrow') ?></span>
      <h1><?= t('hero_title_a') ?> <span class="accent"><?= t('hero_title_accent') ?></span><br><?= t('hero_title_b') ?></h1>
      <p class="slide-sub"><?= t('hero_sub') ?></p>
      <div class="slide-ctas">
        <a class="btn btn-primary btn-lg" href="appointment.php"><?= icon('calendar') ?> <?= t('hero_cta_book') ?></a>
        <a class="btn btn-ghost-light btn-lg" href="service.php?slug=fertility"><?= t('hero_cta_fert') ?> <?= icon('arrow') ?></a>
      </div>
    </div>
  </div>

  <div class="slide">
    <div class="container slide-content">
      <span class="slide-eyebrow"><?= t('sl2_eyebrow') ?></span>
      <h2 class="slide-title"><?= t('sl2_title_a') ?> <span class="accent"><?= t('sl2_accent') ?></span><br><?= t('sl2_title_b') ?></h2>
      <p class="slide-sub"><?= t('sl2_sub') ?></p>
      <div class="slide-ctas">
        <a class="btn btn-primary btn-lg" href="doctors.php"><?= t('nav_doctors') ?> <?= icon('arrow') ?></a>
        <a class="btn btn-ghost-light btn-lg" href="appointment.php"><?= t('hero_cta_book') ?></a>
      </div>
    </div>
  </div>

  <div class="slide">
    <div class="container slide-content">
      <span class="slide-eyebrow"><?= t('sl3_eyebrow') ?></span>
      <h2 class="slide-title"><?= t('sl3_title_a') ?> <span class="accent"><?= t('sl3_accent') ?></span><br><?= t('sl3_title_b') ?></h2>
      <p class="slide-sub"><?= t('sl3_sub') ?></p>
      <div class="slide-ctas">
        <a class="btn btn-primary btn-lg" href="services.php"><?= t('nav_services') ?> <?= icon('arrow') ?></a>
        <a class="btn btn-ghost-light btn-lg" href="contact.php"><?= t('nav_contact') ?></a>
      </div>
    </div>
  </div>

  <!-- self-moving 3D ornaments -->
  <div class="hero-ornaments" aria-hidden="true">
    <span class="orn-cross"><i></i><i></i></span>
    <span class="orn-capsule"></span>
    <span class="orn-ring"></span>
    <span class="orn-pulse"></span>
  </div>

  <div class="slide-count" aria-hidden="true"><em id="slideNow">01</em> <small>/ 03</small></div>
  <div class="slider-dots" id="sliderDots" role="tablist" aria-label="Slides"></div>
  <div class="slider-arrows">
    <button type="button" id="slidePrev" aria-label="Previous slide"><?= icon('arrow-l') ?></button>
    <button type="button" id="slideNext" aria-label="Next slide"><?= icon('arrow') ?></button>
  </div>
</section>

<!-- ============ FERTILITY / IVF SPOTLIGHT — flagship service, placed first ============ -->
<section class="ivf-band" aria-label="<?= e(t('ivf_badge')) ?>">
  <div class="ivf-bg" aria-hidden="true"><img src="assets/img/home/incubator.jpg" alt=""></div>
  <div class="container ivf-inner">
    <span class="ivf-badge"><?= icon('heart') ?> <?= t('ivf_badge') ?></span>
    <h2><?= t('ivf_title') ?> <span class="ivf-accent"><?= t('ivf_accent') ?></span></h2>
    <p class="ivf-lead"><?= t('ivf_sub') ?></p>
    <div class="ivf-ctas">
      <a class="btn btn-primary btn-lg" href="appointment.php?service=fertility"><?= icon('calendar') ?> <?= t('ivf_cta_book') ?></a>
      <a class="btn btn-ghost-light btn-lg" href="service.php?slug=fertility"><?= t('ivf_cta_more') ?> <?= icon('arrow') ?></a>
    </div>
  </div>
</section>

<!-- ============ INFO TRIO ============ -->
<div class="info-trio container">
  <div class="info-trio-grid">
    <div class="trio-card trio-dark">
      <h3><?= icon('clock') ?> <?= t('trio_hours_t') ?></h3>
      <ul class="trio-hours">
        <li><span><?= t('hours_all') ?></span><strong><?= t('hours_all_val') ?></strong></li>
      </ul>
      <a class="trio-phone" href="tel:<?= CLINIC_PHONE_LINK ?>"><?= icon('phone') ?> <?= CLINIC_PHONE ?></a>
    </div>

    <div class="trio-card trio-blue">
      <h3><?= icon('calendar') ?> <?= t('qb_title') ?></h3>
      <form class="qb-form" id="quickbookForm">
        <div>
          <label for="qbService"><?= t('qb_service') ?></label>
          <select id="qbService" name="service">
            <?php foreach ($services as $s): ?>
              <option value="<?= e($s['slug']) ?>"><?= e(lcol($s, 'name')) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="qbDate"><?= t('qb_date') ?></label>
          <input id="qbDate" type="date" name="date" min="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d', strtotime('+' . BOOKING_DAYS_AHEAD . ' days')) ?>">
        </div>
        <button class="btn btn-light" type="submit"><?= t('qb_btn') ?> <?= icon('arrow') ?></button>
      </form>
    </div>

    <div class="trio-card trio-light">
      <h3><?= icon('heart') ?> <?= t('trio_help_t') ?></h3>
      <p><?= t('trio_help_p') ?></p>
      <p style="margin-bottom:10px"><a class="btn btn-whatsapp" href="https://wa.me/<?= CLINIC_WHATSAPP ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> <?= t('ct_wa_btn') ?></a></p>
      <a class="detail" href="<?= CLINIC_MAP_LINK ?>" target="_blank" rel="noopener" style="font-weight:600"><?= icon('pin') ?> <?= t('ct_directions') ?></a>
    </div>
  </div>
</div>

<!-- ============ ABOUT PREVIEW (collage + parallax badge) ============ -->
<section class="section">
  <div class="container about-grid">
    <div class="collage reveal" data-parallax-item>
      <span class="orn-cross" aria-hidden="true"><i></i><i></i></span>
      <span class="collage-dots" aria-hidden="true"></span>
      <figure class="collage-main" style="margin:0"><img src="assets/img/home/patient.jpg" alt="<?= e(t('ab_title')) ?>" loading="lazy"></figure>
      <figure class="collage-second" style="margin:0"><img src="assets/img/home/p2.jpg" alt="" loading="lazy"></figure>
      <div class="collage-badge">
        <strong><span data-count="<?= e(setting('stat_years')) ?>">0</span><em>+</em></strong>
        <span><?= t('ha_badge') ?></span>
      </div>
    </div>
    <div class="reveal">
      <span class="eyebrow"><?= t('nav_about') ?></span>
      <h2><?= t('ha_title') ?></h2>
      <p style="color:var(--n-600)"><?= t('ha_p') ?></p>
      <ul class="feature-list">
        <li><?= icon('check') ?> <?= t('ab_why_1') ?></li>
        <li><?= icon('check') ?> <?= t('ab_why_2') ?></li>
        <li><?= icon('check') ?> <?= t('ab_why_3') ?></li>
        <li><?= icon('check') ?> <?= t('ab_why_4') ?></li>
      </ul>
      <div class="about-cta-row">
        <a class="btn btn-navy" href="about.php"><?= t('ha_more') ?> <?= icon('arrow') ?></a>
        <span class="about-phone">
          <?= icon('phone') ?>
          <span><small><?= t('ha_call_label') ?></small><a href="tel:<?= CLINIC_PHONE_LINK ?>"><?= CLINIC_PHONE ?></a></span>
        </span>
      </div>
    </div>
  </div>
</section>

<!-- ============ SERVICES ============ -->
<section class="section section-cool" id="services">
  <div class="container">
    <div class="section-head center reveal">
      <span class="eyebrow center"><?= t('services_eyebrow') ?></span>
      <h2><?= t('services_title') ?></h2>
      <p><?= t('services_sub') ?></p>
      <hr class="head-rule">
    </div>
    <div class="services-grid reveal-stagger">
      <?php if ($flagship): ?>
      <article class="svc-card flagship">
        <div class="flagship-body">
          <span class="svc-flag-tag"><?= t('svc_flagship') ?></span>
          <h3><?= e(lcol($flagship, 'name')) ?></h3>
          <p><?= e(lcol($flagship, 'summary')) ?></p>
          <a class="btn btn-light" href="service.php?slug=<?= e($flagship['slug']) ?>"><?= t('svc_more') ?> <?= icon('arrow') ?></a>
        </div>
        <div class="flagship-media"><img src="assets/img/home/incubator.jpg" alt="" loading="lazy"></div>
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

<!-- ============ PARALLAX STATS ============ -->
<section class="stats-band" aria-label="Statistics">
  <div class="stats-bg" data-parallax="0.3" aria-hidden="true"><img src="assets/img/home/old.jpg" alt="" loading="lazy"></div>
  <div class="container stats-grid">
    <div class="stat reveal"><strong><span data-count="<?= e(setting('stat_years')) ?>">0</span><em>+</em></strong><span><?= t('stat_years') ?></span></div>
    <div class="stat reveal"><strong><span data-count="<?= e(setting('stat_patients')) ?>">0</span><em>+</em></strong><span><?= t('stat_patients') ?></span></div>
    <div class="stat reveal"><strong><span data-count="<?= e(setting('stat_births')) ?>">0</span><em>+</em></strong><span><?= t('stat_births') ?></span></div>
    <div class="stat reveal"><strong><span data-count="<?= e(setting('stat_services')) ?>">0</span></strong><span><?= t('stat_services') ?></span></div>
  </div>
</section>

<!-- ============ FERTILITY (photo + self-rotating DNA helix) ============ -->
<section class="section">
  <div class="container fertility-split">
    <div class="reveal">
      <span class="eyebrow"><?= t('fert_eyebrow') ?></span>
      <h2><?= t('fert_title') ?></h2>
      <p style="color:var(--n-600)"><?= t('fert_p') ?></p>
      <ul class="fert-checklist">
        <li><?= icon('check') ?> <?= t('fert_li1') ?></li>
        <li><?= icon('check') ?> <?= t('fert_li2') ?></li>
        <li><?= icon('check') ?> <?= t('fert_li3') ?></li>
        <li><?= icon('check') ?> <?= t('fert_li4') ?></li>
      </ul>
      <a class="btn btn-primary" href="appointment.php?service=fertility"><?= t('fert_cta') ?> <?= icon('arrow') ?></a>
    </div>
    <div class="fert-visual reveal">
      <div class="helix-stage" aria-hidden="true">
        <span class="helix-glow"></span>
        <div class="helix"></div>
      </div>
      <figure class="fert-photo" style="margin:0">
        <img src="assets/img/home/p2.jpg" alt="" loading="lazy">
        <figcaption><?= t('fert_quote') ?></figcaption>
      </figure>
    </div>
  </div>
</section>

<!-- ============ MARQUEE (self-moving ticker) ============ -->
<div class="marquee" aria-hidden="true">
  <div class="marquee-track">
    <?php for ($i = 0; $i < 2; $i++): foreach ($services as $s): ?>
      <span class="marquee-item"><?= e(lcol($s, 'name')) ?></span>
    <?php endforeach; endfor; ?>
  </div>
</div>

<!-- ============ DOCTORS (photo cards) ============ -->
<section class="section section-cool">
  <div class="container">
    <div class="section-head center reveal">
      <span class="eyebrow center"><?= t('docs_eyebrow') ?></span>
      <h2><?= t('docs_title') ?></h2>
      <p><?= t('docs_sub') ?></p>
      <hr class="head-rule">
    </div>
    <div class="doctors-grid reveal-stagger">
      <?php foreach ($doctors as $d): ?>
      <article class="doc-card">
        <div class="doc-photo">
          <?= doctor_photo($d) ?>
          <div class="doc-overlay">
            <a class="btn btn-primary" href="appointment.php"><?= t('doc_book') ?></a>
          </div>
        </div>
        <div class="doc-meta">
          <h3><?= e($d['full_name']) ?></h3>
          <span class="doc-spec"><?= e(lcol($d, 'specialty')) ?></span>
          <p class="doc-days"><?= icon('calendar') ?> <?= e(doctor_days((int)$d['id'])) ?></p>
          <?php if (!empty($d['onmc'])): ?><p class="doc-onmc"><?= icon('shield') ?> <?= t('onmc_label') ?> <?= e($d['onmc']) ?></p><?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ TESTIMONIALS (auto carousel) ============ -->
<?php if ($testimonials): ?>
<section class="section">
  <div class="container">
    <div class="section-head center reveal">
      <span class="eyebrow center"><?= t('testi_eyebrow') ?></span>
      <h2><?= t('testi_title') ?></h2>
      <hr class="head-rule">
    </div>
    <div class="testi-wrap reveal" id="testiCarousel">
      <span class="testi-quote-mark" aria-hidden="true">“</span>
      <?php foreach ($testimonials as $i => $tm): ?>
      <div class="testi-slide<?= $i === 0 ? ' active' : '' ?>">
        <blockquote><?= e(lcol($tm, 'body')) ?></blockquote>
        <div class="testi-who">
          <span class="t-avatar" aria-hidden="true"><?= e(testi_letters($tm['initials'])) ?></span>
          <span style="text-align:left">
            <span class="t-name"><?= e($tm['initials']) ?></span>
            <span class="t-tag"><?= t('testi_tag') ?></span>
          </span>
        </div>
      </div>
      <?php endforeach; ?>
      <div class="testi-dots" id="testiDots"></div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
