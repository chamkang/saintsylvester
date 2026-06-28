<?php
/**
 * Dynamic robots.txt — host-aware Sitemap line so it points at the live domain.
 * Reached via the front controller, which aliases /robots.txt to this file.
 */
require_once __DIR__ . '/includes/db.php';

$scheme = ((! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')) ? 'https' : 'http';
$host = SITE_HOST !== '' ? SITE_HOST : ($_SERVER['HTTP_HOST'] ?? 'saintsylvester.vercel.app');

header('Content-Type: text/plain; charset=UTF-8');
echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /admin/\n";
echo "Disallow: /api/\n";
echo "Disallow: /setup.php\n";
echo "Disallow: /pay.php\n";
echo "Disallow: /manage.php\n\n";
echo "Sitemap: {$scheme}://{$host}/sitemap.xml\n";
