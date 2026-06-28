<?php
require_once __DIR__ . '/functions.php';
$page = $page ?? '';
$lang = current_lang();

// Per-page <title> (a page may set $pageTitle before including this file)
$ssmf_titles = [
    'about'       => t('nav_about'),
    'services'    => t('nav_services'),
    'doctors'     => t('nav_doctors'),
    'contact'     => t('nav_contact'),
    'appointment' => t('nav_book'),
    'register'    => t('nav_register'),
    'manage'      => t('nav_manage'),
];
if (!isset($pageTitle) && isset($ssmf_titles[$page])) {
    $pageTitle = $ssmf_titles[$page] . ' — ' . CLINIC_NAME;
}
$pageTitle = $pageTitle ?? t('meta_title');

// Per-page meta description (a page may set $pageDesc before including this file)
$ssmf_descs = [
    'about'       => t('seo_about_desc'),
    'services'    => t('seo_services_desc'),
    'doctors'     => t('seo_doctors_desc'),
    'contact'     => t('seo_contact_desc'),
    'appointment' => t('seo_book_desc'),
    'register'    => t('seo_register_desc'),
    'manage'      => t('seo_manage_desc'),
];
if (!isset($pageDesc) && isset($ssmf_descs[$page])) {
    $pageDesc = $ssmf_descs[$page];
}
$pageDesc = $pageDesc ?? t('meta_desc');

// SEO: absolute URLs for canonical / hreflang / Open Graph
$ssmf_scheme = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')) ? 'https' : 'http';
$ssmf_host  = SITE_HOST !== '' ? SITE_HOST : ($_SERVER['HTTP_HOST'] ?? 'saintsylvester.vercel.app');
$ssmf_base  = $ssmf_scheme . '://' . $ssmf_host;
$ssmf_path  = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$ssmf_canon = $ssmf_base . $ssmf_path . '?lang=' . $lang;
$ssmf_ogimg = $ssmf_base . '/assets/img/hero/slide-1.jpg';
$ssmf_ogloc = $lang === 'fr' ? 'fr_FR' : 'en_US';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($pageDesc) ?>">
<meta name="robots" content="index, follow">
<link rel="canonical" href="<?= e($ssmf_canon) ?>">
<link rel="alternate" hreflang="fr" href="<?= e($ssmf_base . $ssmf_path) ?>?lang=fr">
<link rel="alternate" hreflang="en" href="<?= e($ssmf_base . $ssmf_path) ?>?lang=en">
<link rel="alternate" hreflang="x-default" href="<?= e($ssmf_base . $ssmf_path) ?>">

<!-- Open Graph / Twitter -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e(CLINIC_NAME) ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($pageDesc) ?>">
<meta property="og:url" content="<?= e($ssmf_canon) ?>">
<meta property="og:image" content="<?= e($ssmf_ogimg) ?>">
<meta property="og:locale" content="<?= $ssmf_ogloc ?>">
<meta property="og:locale:alternate" content="<?= $lang === 'fr' ? 'en_US' : 'fr_FR' ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($pageTitle) ?>">
<meta name="twitter:description" content="<?= e($pageDesc) ?>">
<meta name="twitter:image" content="<?= e($ssmf_ogimg) ?>">
<meta name="theme-color" content="#051E33">
<link rel="icon" type="image/png" href="assets/img/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/main.css">
<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'MedicalClinic',
    'name' => CLINIC_NAME,
    'url' => $ssmf_base . '/',
    'image' => $ssmf_ogimg,
    'telephone' => CLINIC_PHONE,
    'email' => CLINIC_EMAIL,
    'priceRange' => '$$',
    'hasMap' => CLINIC_MAP_LINK,
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => 'BP 9026, Bonabéri',
        'addressLocality' => 'Douala',
        'addressRegion' => 'Littoral',
        'addressCountry' => 'CM',
    ],
    'areaServed' => [
        ['@type' => 'City', 'name' => 'Douala'],
        ['@type' => 'Country', 'name' => 'Cameroun'],
    ],
    'openingHoursSpecification' => [
        '@type' => 'OpeningHoursSpecification',
        'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
        'opens' => '00:00', 'closes' => '23:59',
    ],
    'medicalSpecialty' => ['Gynecologic', 'Obstetric', 'Pediatric', 'Cardiovascular', 'PrimaryCare'],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
</script>
</head>
<body data-page="<?= e($page) ?>">

<a class="skip-link" href="#main"><?= $lang === 'fr' ? 'Aller au contenu' : 'Skip to content' ?></a>

<div class="site-head">
<div class="topbar">
  <div class="container topbar-inner">
    <div class="topbar-left">
      <span class="topbar-item"><?= icon('clock') ?> <?= t('top_hours') ?></span>
      <span class="topbar-item"><?= icon('pin') ?> <?= e($lang === 'fr' ? CLINIC_ADDRESS_FR : CLINIC_ADDRESS_EN) ?></span>
    </div>
    <div class="topbar-right">
      <a class="topbar-item topbar-phone" href="tel:<?= CLINIC_PHONE_LINK ?>"><?= icon('phone') ?> <span><?= t('top_call') ?> :</span> <strong><?= CLINIC_PHONE ?></strong></a>
      <a class="lang-switch" href="<?= e(lang_switch_url(t('lang_other'))) ?>" aria-label="Switch language"><?= icon('globe') ?> <?= t('lang_label') ?></a>
    </div>
  </div>
</div>

<header class="navbar" id="navbar">
  <div class="container navbar-inner">
    <a class="brand" href="index.php" aria-label="<?= CLINIC_NAME ?>">
      <span class="brand-mark"><span class="logo-dark"><?= ssmf_logo() ?></span><span class="logo-light"><?= ssmf_logo_light() ?></span></span>
      <span class="brand-text">
        <span class="brand-name">Saint Sylvester</span>
        <span class="brand-tag">MEDICAL FOUNDATION</span>
      </span>
    </a>
    <nav class="nav-links" id="navLinks" aria-label="Main navigation">
      <a href="index.php" class="<?= $page === 'home' ? 'active' : '' ?>"><?= t('nav_home') ?></a>
      <a href="about.php" class="<?= $page === 'about' ? 'active' : '' ?>"><?= t('nav_about') ?></a>
      <a href="services.php" class="<?= in_array($page, ['services','service']) ? 'active' : '' ?>"><?= t('nav_services') ?></a>
      <a href="doctors.php" class="<?= $page === 'doctors' ? 'active' : '' ?>"><?= t('nav_doctors') ?></a>
      <a href="contact.php" class="<?= $page === 'contact' ? 'active' : '' ?>"><?= t('nav_contact') ?></a>
      <a href="manage.php" class="nav-secondary <?= $page === 'manage' ? 'active' : '' ?>"><?= t('nav_manage') ?></a>
      <a href="register.php" class="nav-secondary <?= $page === 'register' ? 'active' : '' ?>"><?= t('nav_register') ?></a>
      <a href="appointment.php" class="btn btn-primary nav-cta"><?= t('nav_book') ?></a>
    </nav>
    <button class="nav-toggle" id="navToggle" aria-label="Menu" aria-expanded="false" aria-controls="navLinks">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>
</div><!-- /.site-head -->
<div class="nav-scrim" id="navScrim" aria-hidden="true"></div>

<main id="main">
<?php
/* ---------- shared inline SVG helpers ---------- */
/* Official Saint Sylvester mark (green cross + blue fertility motif). The logo
   is full-colour, so the same image reads on both the transparent and scrolled
   navbar states — no separate dark/light variant needed. */
function ssmf_logo(): string {
    return '<img src="assets/img/logo.png" alt="Saint Sylvester Medical Foundation" style="display:block;width:100%;height:100%;object-fit:contain" loading="eager">';
}

function ssmf_logo_light(): string {
    return ssmf_logo();
}

function icon(string $name): string {
    $p = [
        'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'pin'     => '<path d="M12 21s-7-5.1-7-11a7 7 0 0 1 14 0c0 5.9-7 11-7 11z"/><circle cx="12" cy="10" r="2.6"/>',
        'phone'   => '<path d="M5 4h4l2 5-2.5 1.5a12 12 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
        'globe'   => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 4 5.7 4 9s-1.5 6.4-4 9c-2.5-2.6-4-5.7-4-9s1.5-6.4 4-9z"/>',
        'check'   => '<path d="M4 12.5 9.5 18 20 6.5"/>',
        'arrow'   => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'arrow-l' => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
        'whatsapp'=> '<path d="M12 3a9 9 0 0 0-7.8 13.5L3 21l4.7-1.2A9 9 0 1 0 12 3z"/><path d="M9 8.5c.5 2.5 4 6 6.5 6.5l1-2-2.2-1-1 .7c-1-.5-2-1.5-2.5-2.5l.7-1-1-2.2-1.5.5z" fill="currentColor" stroke="none"/>',
        'calendar'=> '<rect x="4" y="6" width="16" height="15" rx="2"/><path d="M4 10h16M8 3v5M16 3v5"/>',
        'user'    => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-3.5 4.5-5 8-5s6.5 1.5 8 5"/>',
        'mail'    => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'shield'  => '<path d="M12 3 5 6v5c0 5 3 8.5 7 10 4-1.5 7-5 7-10V6l-7-3z"/>',
        'heart'   => '<path d="M12 20s-7.5-4.7-9-9.3C2 7.6 4 5 6.8 5c2 0 3.7 1.2 4.4 2.9h1.6C13.5 6.2 15.2 5 17.2 5 20 5 22 7.6 21 10.7c-1.5 4.6-9 9.3-9 9.3z"/>',
        'award'   => '<circle cx="12" cy="9" r="6"/><path d="m8.5 14-2 7 5.5-3 5.5 3-2-7"/>',
    ];
    return '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($p[$name] ?? '') . '</svg>';
}

/** Service icons — distinct stroke illustrations per specialty */
function service_icon(string $key): string {
    $p = [
        'fertility'  => '<circle cx="11" cy="11" r="5.5"/><path d="M11 16.5V21M8.5 19h5"/><circle cx="17.5" cy="6.5" r="2.5"/>',
        'gyneco'     => '<circle cx="12" cy="8" r="5"/><path d="M12 13v8M8.8 18h6.4"/>',
        'antenatal'  => '<path d="M12 21c-4 0-7-3-7-7 0-5 4-9 7-11 3 2 7 6 7 11 0 4-3 7-7 7z"/><circle cx="12" cy="13" r="3"/>',
        'general'    => '<path d="M8 3v4a4 4 0 0 0 8 0V3"/><path d="M12 11v4a4 4 0 0 0 4 4h1"/><circle cx="19" cy="19" r="2.5"/>',
        'cardio'     => '<path d="M12 20s-7.5-4.7-9-9.3C2 7.6 4 5 6.8 5c2 0 3.7 1.2 4.4 2.9h1.6C13.5 6.2 15.2 5 17.2 5 20 5 22 7.6 21 10.7c-1.5 4.6-9 9.3-9 9.3z"/><path d="M5 12h4l1.5-3 2 6 1.5-3h4"/>',
        'pediatrics' => '<circle cx="12" cy="9" r="5"/><path d="M9.5 8.5h.01M14.5 8.5h.01M10 11.5c.6.6 1.3 1 2 1s1.4-.4 2-1"/><path d="M5 21c1.2-3 3.8-4.5 7-4.5s5.8 1.5 7 4.5"/>',
        'surgery'    => '<path d="M3 21l9.5-9.5"/><path d="M14 4l6 6-7.5 7.5-6-6L14 4z"/><path d="M16 8l-1.5 1.5"/>',
        'imaging'    => '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="M12 4v16M8 8h.01M8 12h.01M8 16h.01M16 8h.01M16 12h.01M16 16h.01"/>',
        'lab'        => '<path d="M9 3h6M10 3v6l-5 9a2.5 2.5 0 0 0 2.2 3.5h9.6A2.5 2.5 0 0 0 19 18l-5-9V3"/><path d="M8.5 15h7"/>',
    ];
    return '<svg class="svc-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($p[$key] ?? $p['general']) . '</svg>';
}

/** Classic inner-page banner: image + navy overlay + breadcrumb + 3D ornaments */
function page_banner(string $title, string $sub = '', string $crumb = '', string $img = 'assets/img/hero/page-banner.jpg'): void {
    ?>
<section class="page-hero">
  <div class="page-hero-bg" aria-hidden="true"><img src="<?= e($img) ?>" alt=""></div>
  <span class="orn-cross" aria-hidden="true"><i></i><i></i></span>
  <span class="orn-ring" aria-hidden="true"></span>
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="index.php"><?= t('nav_home') ?></a>
      <span class="sep">✚</span>
      <span><?= e($crumb !== '' ? $crumb : $title) ?></span>
    </nav>
    <h1><?= e($title) ?></h1>
    <?php if ($sub !== ''): ?><p><?= e($sub) ?></p><?php endif; ?>
  </div>
</section>
    <?php
}

/** Doctor initials, e.g. "Dr. Sylvester N." -> "SN" */
function doctor_initials(string $name): string {
    $parts = preg_split('/[\s.]+/', trim(str_replace('Dr', '', $name)));
    $ini = '';
    foreach ($parts as $w) { if ($w !== '' && ctype_alpha($w[0])) $ini .= strtoupper($w[0]); if (strlen($ini) >= 2) break; }
    return $ini;
}

/**
 * Doctor photo block for cards: real photo when set (doctors.photo), serif
 * initials monogram otherwise. Replace photos by dropping files in
 * assets/img/team/ and updating the doctors.photo column.
 */
function doctor_photo(array $d): string {
    // Clinic preference: show name initials for every doctor (no photos).
    return '<span class="doc-fallback"><span>' . e(doctor_initials($d['full_name'])) . '</span></span>';
}
?>
