<?php
/**
 * Dynamic sitemap — built from the request host (or SITE_HOST when set), so it is
 * always correct on whatever domain serves the site. Reached via the front
 * controller, which aliases /sitemap.xml to this file.
 */
require_once __DIR__ . '/includes/db.php';

$scheme = ((! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')) ? 'https' : 'http';
$host = SITE_HOST !== '' ? SITE_HOST : ($_SERVER['HTTP_HOST'] ?? 'saintsylvester.vercel.app');
$base = $scheme . '://' . $host;
$esc = fn ($s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

header('Content-Type: application/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

$pages = [
    ['/', 'weekly', '1.0'],
    ['/services.php', 'monthly', '0.8'],
    ['/appointment.php', 'monthly', '0.9'],
    ['/about.php', 'monthly', '0.7'],
    ['/doctors.php', 'monthly', '0.7'],
    ['/contact.php', 'monthly', '0.7'],
];
foreach ($pages as [$p, $cf, $pr]) {
    echo '  <url><loc>' . $esc($base . $p) . "</loc><changefreq>$cf</changefreq><priority>$pr</priority></url>\n";
}

try {
    $slugs = db()->query("SELECT slug FROM services WHERE is_active = 1 ORDER BY sort_order")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($slugs as $slug) {
        $pr = $slug === 'fertility' ? '0.9' : '0.6';
        echo '  <url><loc>' . $esc($base . '/service.php?slug=' . $slug) . "</loc><changefreq>monthly</changefreq><priority>$pr</priority></url>\n";
    }
} catch (Throwable $e) { /* service pages are optional in the sitemap */ }

echo '</urlset>';
