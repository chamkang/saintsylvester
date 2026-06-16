<?php
/**
 * Vercel serverless front controller (vercel-php runtime).
 *
 * vercel-php runs functions from /api, but Saint Sylvester is a classic
 * multi-page PHP app whose pages live at the project root (index.php, about.php,
 * service.php, doctors.php, api/book.php, ...). This shim maps the request path
 * to the matching file and executes it, so the existing code runs unchanged.
 * Local XAMPP serves those same files directly and ignores this router.
 *
 * Static files (assets/) are served by Vercel via vercel.json and never reach here.
 */
$root     = dirname(__DIR__);
$rootReal = realpath($root);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$rel = ltrim(rawurldecode($uri ?? '/'), '/');
if ($rel === '') $rel = 'index.php';

$candidate = $root . '/' . $rel;
// "/about" -> "/about.php",  "/admin/" -> "/admin/index.php"
if (is_dir($candidate)) {
    $candidate = rtrim($candidate, '/') . '/index.php';
} elseif (substr($rel, -4) !== '.php' && is_file($candidate . '.php')) {
    $candidate .= '.php';
}

$real = realpath($candidate);

$ok = $real !== false
    && strpos($real, $rootReal) === 0
    && strtolower(pathinfo($real, PATHINFO_EXTENSION)) === 'php';

if ($ok) {
    $relReal = str_replace('\\', '/', substr($real, strlen($rootReal) + 1));
    // never serve internal includes, raw config, the DB folder, or this router itself.
    // (api/book.php, api/slots.php, api/calendar.php stay reachable — only the router is blocked.)
    $forbidden = ['includes/', 'data/', 'config.php', 'api/index.php'];
    foreach ($forbidden as $f) {
        if (stripos($relReal, $f) === 0) { $ok = false; break; }
    }
}

if (!$ok) {
    http_response_code(404);
    echo 'Page not found.';
    return;
}

chdir(dirname($real));
$_SERVER['SCRIPT_FILENAME'] = $real;
$_SERVER['SCRIPT_NAME']     = '/' . $relReal;
$_SERVER['PHP_SELF']        = '/' . $relReal;

require $real;
