<?php
/**
 * export-static.php — render the public site to a static ./dist folder (FR + EN).
 * Dev tool only (never deployed). Run from CLI with Apache serving the site:
 *     C:\xampp\php\php.exe export-static.php
 *
 * Booking / manage / register pages need the live PHP backend, so their links
 * are repointed to contact.html — the static build is a brochure snapshot.
 */
$BASE = 'http://localhost/saintsylvester';
$OUT  = __DIR__ . '/dist';

require_once __DIR__ . '/includes/db.php';

// brochure pages:  output filename => source path
$pages = [
    'index.html'    => 'index.php',
    'about.html'    => 'about.php',
    'services.html' => 'services.php',
    'doctors.html'  => 'doctors.php',
    'contact.html'  => 'contact.php',
];
foreach (db()->query("SELECT slug FROM services WHERE is_active = 1")->fetchAll() as $r) {
    $pages["service-{$r['slug']}.html"] = "service.php?slug={$r['slug']}";
}

function fetch_page(string $url): string {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 30]);
        $html = curl_exec($ch);
        if ($html === false) fwrite(STDERR, "FETCH FAIL $url: " . curl_error($ch) . "\n");
        curl_close($ch);
        return (string)$html;
    }
    return (string)@file_get_contents($url);
}

function rewrite_links(string $html, string $langTarget): string {
    // service detail:  service.php?slug=x  ->  service-x.html
    $html = preg_replace('#href=(["\'])service\.php\?slug=([a-z0-9\-]+)[^"\']*\1#i', 'href=$1service-$2.html$1', $html);
    // dynamic pages (need backend) -> contact
    $html = preg_replace('#href=(["\'])(appointment|manage|register|pay)\.php[^"\']*\1#i', 'href=$1contact.html$1', $html);
    // services list + bare service.php fallback
    $html = preg_replace('#href=(["\'])services\.php[^"\']*\1#i', 'href=$1services.html$1', $html);
    $html = preg_replace('#href=(["\'])service\.php[^"\']*\1#i', 'href=$1services.html$1', $html);
    // remaining core pages
    foreach (['index', 'about', 'doctors', 'contact'] as $p) {
        $html = preg_replace('#href=(["\'])' . $p . '\.php[^"\']*\1#i', 'href=$1' . $p . '.html$1', $html);
    }
    // assets -> absolute so root (FR) and /en/ (EN) pages share /assets
    $html = preg_replace('#(href|src)=(["\'])assets/#i', '$1=$2/assets/', $html);
    // language switcher -> the matching page in the other language (run last, wins)
    $html = preg_replace('#(class="lang-switch"[^>]*href=")[^"]*(")#i', '$1' . $langTarget . '$2', $html);
    return $html;
}

@mkdir($OUT, 0777, true);
@mkdir("$OUT/en", 0777, true);

$n = 0;
foreach (['fr', 'en'] as $lang) {
    foreach ($pages as $base => $path) {
        $sep  = strpos($path, '?') === false ? '?' : '&';
        $html = fetch_page("$BASE/$path{$sep}lang=$lang");
        if ($html === '') { echo "SKIP (empty): $lang/$base\n"; continue; }
        $langTarget = $lang === 'fr' ? "en/$base" : "../$base";
        $html = rewrite_links($html, $langTarget);
        $dest = $lang === 'fr' ? "$OUT/$base" : "$OUT/en/$base";
        file_put_contents($dest, $html);
        $n++;
    }
}
echo "Done — wrote $n HTML files to $OUT (copy ./assets next).\n";
