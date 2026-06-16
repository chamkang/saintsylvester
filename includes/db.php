<?php
require_once __DIR__ . '/../config.php';

/**
 * Active DB engine this request:
 *   'pgsql'  → Supabase / Postgres (production on Vercel) — when DB env vars are set
 *   'mysql'  → when SSMF_DB_DRIVER = 'mysql'
 *   'sqlite' → local development default (no env vars) — unchanged behaviour
 *
 * Env vars win so the same code runs on Vercel (Postgres) and on local XAMPP
 * (SQLite) with no edits.
 */
function db_driver(): string {
    static $driver = null;
    if ($driver !== null) return $driver;
    $driver = (getenv('DATABASE_URL') || getenv('POSTGRES_URL') || getenv('DB_HOST')) ? 'pgsql' : SSMF_DB_DRIVER;
    return $driver;
}

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $opt = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    switch (db_driver()) {
        case 'pgsql':
            // Neon / Supabase / any Postgres. Accept a single connection URI
            // (DATABASE_URL, or POSTGRES_URL which Vercel's integration sets) or
            // discrete DB_HOST/DB_PORT/DB_NAME/DB_USER/DB_PASSWORD vars.
            // Default port is 5432 (standard Postgres / Neon); Supabase's pooler
            // URI carries its own :6543 explicitly, so it is unaffected.
            $url = getenv('DATABASE_URL') ?: getenv('POSTGRES_URL');
            if ($url) {
                $p    = parse_url($url) ?: [];
                $host = $p['host'] ?? '';
                $port = $p['port'] ?? 5432;
                $name = isset($p['path']) ? ltrim($p['path'], '/') : 'postgres';
                $user = isset($p['user']) ? rawurldecode($p['user']) : '';
                $pass = isset($p['pass']) ? rawurldecode($p['pass']) : '';
            } else {
                $host = getenv('DB_HOST') ?: '';
                $port = getenv('DB_PORT') ?: '5432';
                $name = getenv('DB_NAME') ?: 'postgres';
                $user = getenv('DB_USER') ?: '';
                $pass = getenv('DB_PASSWORD') ?: '';
            }
            // SSL is required; emulated prepares keep us compatible with poolers
            // (Supabase 6543 / Neon pooler) that have no server-side prepares.
            $opt[PDO::ATTR_EMULATE_PREPARES] = true;
            $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s;sslmode=require', $host, $port, $name);
            // Neon routes connections by SNI, but the vercel-php libpq is too old to
            // send it — so pass the endpoint id explicitly (the first hostname label,
            // minus any "-pooler" suffix). Harmless/skipped for non-Neon hosts.
            if (strpos($host, '.neon.tech') !== false) {
                $endpoint = preg_replace('/-pooler$/', '', explode('.', $host)[0]);
                $dsn .= ';options=endpoint=' . $endpoint;
            }
            $pdo = new PDO($dsn, $user, $pass, $opt);
            break;

        case 'mysql':
            $pdo = new PDO(
                'mysql:host=' . SSMF_MYSQL_HOST . ';dbname=' . SSMF_MYSQL_NAME . ';charset=utf8mb4',
                SSMF_MYSQL_USER, SSMF_MYSQL_PASS, $opt
            );
            break;

        default: // sqlite
            $dir = dirname(SSMF_SQLITE_PATH);
            if (!is_dir($dir)) mkdir($dir, 0775, true);
            $pdo = new PDO('sqlite:' . SSMF_SQLITE_PATH, null, null, $opt);
            $pdo->exec('PRAGMA foreign_keys = ON');
            $pdo->exec('PRAGMA busy_timeout = 5000');
            break;
    }
    return $pdo;
}

function db_ready(): bool {
    try {
        db()->query("SELECT 1 FROM services LIMIT 1");
        return true;
    } catch (Throwable $e) {
        return false;
    }
}
