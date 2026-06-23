<?php
/**
 * Idempotent content sync.
 *
 * Re-applies the canonical content from seed-content.php — doctor profiles and
 * their weekly schedules — into the live database whenever the code's
 * SSMF_CONTENT_VERSION is newer than the version the database last applied.
 *
 * This lets content edits (names, schedules, …) go live on deploy without
 * re-running setup or hand-writing SQL: the PHP runs on Vercel and updates
 * Postgres itself. Only canonical content is touched — patients, bookings and
 * users are never altered. Matching is by doctor slug, so existing rows are
 * updated in place.
 */

function ssmf_sync_content(PDO $pdo): void
{
    $seed = require __DIR__ . '/seed-content.php';
    if (empty($seed['doctors'])) {
        return;
    }

    $pdo->beginTransaction();
    try {
        $find = $pdo->prepare('SELECT id FROM doctors WHERE slug = ?');
        $upd  = $pdo->prepare(
            'UPDATE doctors SET full_name=?, onmc=?, specialty_fr=?, specialty_en=?,
             bio_fr=?, bio_en=?, languages=?, photo=?, sort_order=?, is_active=1
             WHERE slug=?'
        );
        $ins = $pdo->prepare(
            'INSERT INTO doctors (slug, full_name, onmc, specialty_fr, specialty_en,
             bio_fr, bio_en, languages, photo, sort_order, is_active)
             VALUES (?,?,?,?,?,?,?,?,?,?,1)'
        );
        $delSched = $pdo->prepare('DELETE FROM schedules WHERE doctor_id = ?');
        $insSched = $pdo->prepare(
            'INSERT INTO schedules (doctor_id, weekday, start_time, end_time, slot_minutes)
             VALUES (?,?,?,?,?)'
        );

        foreach ($seed['doctors'] as $i => $d) {
            $find->execute([$d[0]]);
            $id = $find->fetchColumn();
            if ($id) {
                $upd->execute([$d[1], $d[2], $d[3], $d[4], $d[5], $d[6], $d[7], $d[8], $i, $d[0]]);
            } else {
                $ins->execute([$d[0], $d[1], $d[2], $d[3], $d[4], $d[5], $d[6], $d[7], $d[8], $i]);
                $find->execute([$d[0]]);
                $id = $find->fetchColumn();
            }
            // Rebuild this doctor's schedule from scratch (weekday/start/end/slot).
            $delSched->execute([$id]);
            foreach (($d[10] ?? []) as $s) {
                $insSched->execute([$id, $s[0], $s[1], $s[2], $s[3]]);
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/** Run the sync once per request, only when the DB hasn't applied the current version. */
function ssmf_ensure_content(): void
{
    static $done = false;
    if ($done || ! defined('SSMF_CONTENT_VERSION')) {
        return;
    }
    $done = true;

    try {
        $pdo = db();
        $cur = $pdo->query('SELECT value_fr FROM settings WHERE "key" = \'content_version\'')->fetchColumn();
        if ($cur === SSMF_CONTENT_VERSION) {
            return; // already up to date — this is the cheap common path
        }

        ssmf_sync_content($pdo);

        $up = $pdo->prepare('UPDATE settings SET value_fr = ? WHERE "key" = \'content_version\'');
        $up->execute([SSMF_CONTENT_VERSION]);
        if ($up->rowCount() === 0) {
            $pdo->prepare('INSERT INTO settings ("key", value_fr, value_en) VALUES (\'content_version\', ?, ?)')
                ->execute([SSMF_CONTENT_VERSION, SSMF_CONTENT_VERSION]);
        }
    } catch (Throwable $e) {
        // A sync failure must never take the site down; it simply retries next request.
    }
}
