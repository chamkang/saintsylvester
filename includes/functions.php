<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

/* ---------- i18n ---------- */

function current_lang(): string {
    static $lang = null;
    if ($lang !== null) return $lang;
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'], true)) {
        $lang = $_GET['lang'];
        setcookie('ssmf_lang', $lang, time() + 31536000, '/');
    } elseif (isset($_COOKIE['ssmf_lang']) && in_array($_COOKIE['ssmf_lang'], ['fr', 'en'], true)) {
        $lang = $_COOKIE['ssmf_lang'];
    } else {
        $lang = DEFAULT_LANG;
    }
    return $lang;
}

function t(string $key, array $vars = []): string {
    static $strings = null;
    if ($strings === null) $strings = require __DIR__ . '/../lang/' . current_lang() . '.php';
    $s = $strings[$key] ?? $key;
    foreach ($vars as $k => $v) $s = str_replace('{' . $k . '}', $v, $s);
    return $s;
}

/** Pick the right language column from a DB row, e.g. lcol($svc, 'name') -> name_fr|name_en */
function lcol(array $row, string $base): string {
    return $row[$base . '_' . current_lang()] ?? $row[$base . '_fr'] ?? '';
}

function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

/** Current URL with lang param swapped — for the language switcher */
function lang_switch_url(string $to): string {
    $params = $_GET;
    $params['lang'] = $to;
    return strtok($_SERVER['REQUEST_URI'], '?') . '?' . http_build_query($params);
}

function format_date_local(string $ymd): string {
    $ts = strtotime($ymd);
    $days = ['fr' => ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi'],
             'en' => ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']];
    $months = ['fr' => ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'],
               'en' => ['January','February','March','April','May','June','July','August','September','October','November','December']];
    $l = current_lang();
    $d = $days[$l][(int)date('w', $ts)];
    $m = $months[$l][(int)date('n', $ts) - 1];
    return $l === 'fr'
        ? sprintf('%s %d %s %s', $d, (int)date('j', $ts), $m, date('Y', $ts))
        : sprintf('%s, %s %d, %s', $d, $m, (int)date('j', $ts), date('Y', $ts));
}

/* ---------- security ---------- */

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_check(?string $token): bool {
    return is_string($token) && hash_equals($_SESSION['csrf'] ?? '', $token);
}

/** Sliding-window rate limit per IP+action. Returns false when over the limit. */
function rate_limit(string $action, int $max, int $windowSeconds): bool {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $now = time();
    db()->prepare("DELETE FROM rate_limits WHERE ts < ?")->execute([$now - 86400]);
    $st = db()->prepare("SELECT COUNT(*) c FROM rate_limits WHERE ip = ? AND action = ? AND ts > ?");
    $st->execute([$ip, $action, $now - $windowSeconds]);
    if ((int)$st->fetch()['c'] >= $max) return false;
    db()->prepare("INSERT INTO rate_limits (ip, action, ts) VALUES (?,?,?)")->execute([$ip, $action, $now]);
    return true;
}

function clean_phone(string $raw): string {
    $p = preg_replace('/[^0-9+]/', '', $raw);
    if (preg_match('/^6\d{8}$/', $p)) $p = '+237' . $p;          // local mobile
    if (preg_match('/^237\d{9}$/', $p)) $p = '+' . $p;
    return $p;
}

function valid_phone(string $p): bool {
    return (bool)preg_match('/^\+\d{8,15}$/', $p);
}

function json_out(array $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/* ---------- booking slot engine (TRD §6.4) ---------- */

/**
 * All consultation slots for a doctor on a date, each flagged available or booked.
 * Booked = a pending/confirmed appointment holds it (shown red in the UI).
 * Past/cutoff times and full-day closures are excluded entirely.
 * Returns [ ['time' => 'HH:MM', 'available' => bool], ... ] ordered by time.
 */
function day_slots(int $doctorId, string $date): array {
    $weekday = (int)date('w', strtotime($date));
    $slots = []; // 'HH:MM' => available?

    $st = db()->prepare("SELECT start_time, end_time, slot_minutes FROM schedules WHERE doctor_id = ? AND weekday = ?");
    $st->execute([$doctorId, $weekday]);
    foreach ($st->fetchAll() as $block) {
        $t = strtotime("$date {$block['start_time']}");
        $end = strtotime("$date {$block['end_time']}");
        $step = max(5, (int)$block['slot_minutes']) * 60;
        for (; $t + $step <= $end + 1; $t += $step) $slots[date('H:i', $t)] = true;
    }
    if (!$slots) return [];

    // clinic-wide or doctor-specific exceptions (holidays, leave)
    $st = db()->prepare("SELECT start_time, end_time FROM schedule_exceptions WHERE date = ? AND (doctor_id IS NULL OR doctor_id = ?)");
    $st->execute([$date, $doctorId]);
    foreach ($st->fetchAll() as $ex) {
        if (empty($ex['start_time'])) return []; // full-day closure
        foreach (array_keys($slots) as $hm) {
            if ($hm >= substr($ex['start_time'], 0, 5) && $hm < substr($ex['end_time'], 0, 5)) unset($slots[$hm]);
        }
    }

    // mark booked (pending/confirmed hold the slot) instead of removing it
    // substr(starts_at,1,10) is the 'YYYY-MM-DD' date part, portable across SQLite + Postgres
    $st = db()->prepare("SELECT starts_at FROM appointments WHERE doctor_id = ? AND substr(starts_at,1,10) = ? AND status IN ('pending','confirmed')");
    $st->execute([$doctorId, $date]);
    foreach ($st->fetchAll() as $b) {
        $hm = date('H:i', strtotime($b['starts_at']));
        if (isset($slots[$hm])) $slots[$hm] = false;
    }

    // drop past times + same-day cutoff entirely
    $cutoff = time() + BOOKING_CUTOFF_HOURS * 3600;
    foreach (array_keys($slots) as $hm) {
        if (strtotime("$date $hm") < $cutoff) unset($slots[$hm]);
    }

    ksort($slots); // 'HH:MM' zero-padded sorts chronologically
    $out = [];
    foreach ($slots as $hm => $avail) $out[] = ['time' => $hm, 'available' => $avail];
    return $out;
}

/** Available start times for a doctor on a date (used to validate bookings). */
function available_slots(int $doctorId, string $date): array {
    $out = [];
    foreach (day_slots($doctorId, $date) as $s) if ($s['available']) $out[] = $s['time'];
    return $out;
}

/** Doctors offering a service */
function doctors_for_service(int $serviceId): array {
    $st = db()->prepare(
        "SELECT d.* FROM doctors d
         JOIN doctor_service ds ON ds.doctor_id = d.id
         WHERE ds.service_id = ? AND d.is_active = 1 ORDER BY d.sort_order"
    );
    $st->execute([$serviceId]);
    return $st->fetchAll();
}

function get_services(bool $activeOnly = true): array {
    $sql = "SELECT * FROM services" . ($activeOnly ? " WHERE is_active = 1" : "") . " ORDER BY sort_order";
    return db()->query($sql)->fetchAll();
}

/** "Lun · Mer · Ven" / "Mon · Wed · Fri" consultation-day summary for a doctor */
function doctor_days(int $doctorId): string {
    $st = db()->prepare("SELECT DISTINCT weekday FROM schedules WHERE doctor_id = ? ORDER BY weekday");
    $st->execute([$doctorId]);
    $names = current_lang() === 'fr'
        ? ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam']
        : ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    $days = array_map(fn($r) => $names[(int)$r['weekday']], $st->fetchAll());
    return implode(' · ', $days);
}

require_once __DIR__ . '/payments.php';

function setting(string $key): string {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (db()->query('SELECT "key", value_fr, value_en FROM settings')->fetchAll() as $r) $cache[$r['key']] = $r;
    }
    return isset($cache[$key]) ? lcol($cache[$key], 'value') : '';
}
