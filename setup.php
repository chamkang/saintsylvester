<?php
/**
 * One-time setup: creates the schema and seeds initial content.
 * Run: php setup.php   (or open /setup.php in the browser once)
 * Safe to re-run — tables are created IF NOT EXISTS and seeding only runs when empty.
 *
 * NOTE: doctor names, stats and phone numbers are PLACEHOLDERS pending
 * the real data from the clinic (TRD §11). Edit below or via the admin panel.
 */
require_once __DIR__ . '/includes/db.php';

$pdo = db();
$isCli = PHP_SAPI === 'cli';
function out(string $m) { global $isCli; echo $m . ($isCli ? PHP_EOL : '<br>'); }

if (db_driver() === 'pgsql') {
    /* ---- Postgres / Supabase schema (production on Vercel) ----
     * Same shape as the SQLite schema below. TEXT/INTEGER and CHECK constraints
     * carry over unchanged; AUTOINCREMENT becomes SERIAL; datetime('now') becomes
     * to_char(now(),...); the reserved word "key" is double-quoted; and the
     * partial unique index (no double-booking) is supported natively. */
    $pdo->exec("CREATE TABLE IF NOT EXISTS patients (
      id SERIAL PRIMARY KEY,
      mrn TEXT UNIQUE,
      first_name TEXT NOT NULL, last_name TEXT NOT NULL,
      dob TEXT, sex TEXT CHECK (sex IN ('F','M')),
      marital_status TEXT CHECK (marital_status IN ('single','married','divorced','widowed') OR marital_status IS NULL),
      phone TEXT UNIQUE NOT NULL, email TEXT, address TEXT,
      emergency_name TEXT, emergency_phone TEXT,
      blood_group TEXT, allergies TEXT, medications TEXT,
      consent_at TEXT, verified_at TEXT,
      created_at TEXT DEFAULT to_char(now(),'YYYY-MM-DD HH24:MI:SS'),
      updated_at TEXT, deleted_at TEXT
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS doctors (
      id SERIAL PRIMARY KEY,
      slug TEXT UNIQUE NOT NULL, full_name TEXT NOT NULL,
      onmc TEXT,
      specialty_fr TEXT, specialty_en TEXT,
      bio_fr TEXT, bio_en TEXT, photo TEXT,
      languages TEXT, is_active INTEGER DEFAULT 1, sort_order INTEGER DEFAULT 0
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS services (
      id SERIAL PRIMARY KEY,
      slug TEXT UNIQUE NOT NULL,
      name_fr TEXT NOT NULL, name_en TEXT NOT NULL,
      summary_fr TEXT, summary_en TEXT, body_fr TEXT, body_en TEXT,
      features_fr TEXT, features_en TEXT,
      icon TEXT, duration_min INTEGER DEFAULT 20,
      is_flagship INTEGER DEFAULT 0, is_active INTEGER DEFAULT 1, sort_order INTEGER DEFAULT 0
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS doctor_service (
      doctor_id INTEGER NOT NULL REFERENCES doctors(id),
      service_id INTEGER NOT NULL REFERENCES services(id),
      PRIMARY KEY (doctor_id, service_id)
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS schedules (
      id SERIAL PRIMARY KEY,
      doctor_id INTEGER NOT NULL REFERENCES doctors(id),
      weekday INTEGER NOT NULL CHECK (weekday BETWEEN 0 AND 6),
      start_time TEXT NOT NULL, end_time TEXT NOT NULL,
      slot_minutes INTEGER NOT NULL DEFAULT 20
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS schedule_exceptions (
      id SERIAL PRIMARY KEY,
      doctor_id INTEGER REFERENCES doctors(id),
      date TEXT NOT NULL, start_time TEXT, end_time TEXT, reason TEXT
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS appointments (
      id SERIAL PRIMARY KEY,
      reference TEXT UNIQUE,
      patient_id INTEGER REFERENCES patients(id),
      guest_name TEXT, guest_phone TEXT, guest_email TEXT,
      booking_for TEXT DEFAULT 'self' CHECK (booking_for IN ('self','other')),
      other_name TEXT,
      doctor_id INTEGER NOT NULL REFERENCES doctors(id),
      service_id INTEGER NOT NULL REFERENCES services(id),
      starts_at TEXT NOT NULL, ends_at TEXT NOT NULL,
      status TEXT NOT NULL DEFAULT 'pending'
        CHECK (status IN ('pending','confirmed','completed','cancelled','no_show')),
      cancel_reason TEXT, notes TEXT,
      payment_status TEXT DEFAULT 'unpaid', amount INTEGER, currency TEXT DEFAULT 'FCFA',
      pay_token TEXT, paid_at TEXT,
      created_at TEXT DEFAULT to_char(now(),'YYYY-MM-DD HH24:MI:SS'), updated_at TEXT
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
      id SERIAL PRIMARY KEY,
      appointment_id INTEGER REFERENCES appointments(id),
      provider TEXT NOT NULL, provider_ref TEXT,
      amount INTEGER NOT NULL, currency TEXT DEFAULT 'FCFA',
      status TEXT NOT NULL DEFAULT 'pending',
      payer_phone TEXT, method TEXT, raw TEXT,
      created_at TEXT DEFAULT to_char(now(),'YYYY-MM-DD HH24:MI:SS'), updated_at TEXT
    )");
    // FR-B5: a live (pending/confirmed) appointment locks its slot — Postgres
    // supports the partial unique index natively (unlike MySQL).
    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS no_double_booking
                ON appointments (doctor_id, starts_at)
                WHERE status IN ('pending','confirmed')");
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
      id SERIAL PRIMARY KEY,
      name TEXT NOT NULL, email TEXT UNIQUE NOT NULL, password_hash TEXT NOT NULL,
      role TEXT NOT NULL DEFAULT 'receptionist' CHECK (role IN ('admin','receptionist')),
      last_login_at TEXT, is_active INTEGER DEFAULT 1
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
      id SERIAL PRIMARY KEY,
      name TEXT NOT NULL, phone TEXT, email TEXT, body TEXT NOT NULL,
      is_read INTEGER DEFAULT 0, created_at TEXT DEFAULT to_char(now(),'YYYY-MM-DD HH24:MI:SS')
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS testimonials (
      id SERIAL PRIMARY KEY,
      initials TEXT NOT NULL, body_fr TEXT NOT NULL, body_en TEXT NOT NULL,
      is_published INTEGER DEFAULT 1, sort_order INTEGER DEFAULT 0
    )");
    $pdo->exec('CREATE TABLE IF NOT EXISTS settings (
      "key" TEXT PRIMARY KEY, value_fr TEXT, value_en TEXT
    )');
    $pdo->exec("CREATE TABLE IF NOT EXISTS rate_limits (
      id SERIAL PRIMARY KEY,
      ip TEXT NOT NULL, action TEXT NOT NULL, ts BIGINT NOT NULL
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS audit_log (
      id SERIAL PRIMARY KEY,
      user_id INTEGER, action TEXT NOT NULL, entity TEXT, entity_id INTEGER,
      changes TEXT, created_at TEXT DEFAULT to_char(now(),'YYYY-MM-DD HH24:MI:SS')
    )");

} else {

$pdo->exec("
CREATE TABLE IF NOT EXISTS patients (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  mrn TEXT UNIQUE,
  first_name TEXT NOT NULL, last_name TEXT NOT NULL,
  dob TEXT, sex TEXT CHECK (sex IN ('F','M')),
  marital_status TEXT CHECK (marital_status IN ('single','married','divorced','widowed') OR marital_status IS NULL),
  phone TEXT UNIQUE NOT NULL, email TEXT, address TEXT,
  emergency_name TEXT, emergency_phone TEXT,
  blood_group TEXT, allergies TEXT, medications TEXT,
  consent_at TEXT, verified_at TEXT,
  created_at TEXT DEFAULT (datetime('now','localtime')),
  updated_at TEXT, deleted_at TEXT
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS doctors (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  slug TEXT UNIQUE NOT NULL, full_name TEXT NOT NULL,
  onmc TEXT,
  specialty_fr TEXT, specialty_en TEXT,
  bio_fr TEXT, bio_en TEXT, photo TEXT,
  languages TEXT, is_active INTEGER DEFAULT 1, sort_order INTEGER DEFAULT 0
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS services (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  slug TEXT UNIQUE NOT NULL,
  name_fr TEXT NOT NULL, name_en TEXT NOT NULL,
  summary_fr TEXT, summary_en TEXT, body_fr TEXT, body_en TEXT,
  features_fr TEXT, features_en TEXT,
  icon TEXT, duration_min INTEGER DEFAULT 20,
  is_flagship INTEGER DEFAULT 0, is_active INTEGER DEFAULT 1, sort_order INTEGER DEFAULT 0
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS doctor_service (
  doctor_id INTEGER NOT NULL REFERENCES doctors(id),
  service_id INTEGER NOT NULL REFERENCES services(id),
  PRIMARY KEY (doctor_id, service_id)
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS schedules (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  doctor_id INTEGER NOT NULL REFERENCES doctors(id),
  weekday INTEGER NOT NULL CHECK (weekday BETWEEN 0 AND 6),
  start_time TEXT NOT NULL, end_time TEXT NOT NULL,
  slot_minutes INTEGER NOT NULL DEFAULT 20
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS schedule_exceptions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  doctor_id INTEGER REFERENCES doctors(id),
  date TEXT NOT NULL, start_time TEXT, end_time TEXT, reason TEXT
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS appointments (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  reference TEXT UNIQUE,
  patient_id INTEGER REFERENCES patients(id),
  guest_name TEXT, guest_phone TEXT, guest_email TEXT,
  booking_for TEXT DEFAULT 'self' CHECK (booking_for IN ('self','other')),
  other_name TEXT,
  doctor_id INTEGER NOT NULL REFERENCES doctors(id),
  service_id INTEGER NOT NULL REFERENCES services(id),
  starts_at TEXT NOT NULL, ends_at TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'pending'
    CHECK (status IN ('pending','confirmed','completed','cancelled','no_show')),
  cancel_reason TEXT, notes TEXT,
  payment_status TEXT DEFAULT 'unpaid', amount INTEGER, currency TEXT DEFAULT 'FCFA',
  pay_token TEXT, paid_at TEXT,
  created_at TEXT DEFAULT (datetime('now','localtime')), updated_at TEXT
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS payments (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  appointment_id INTEGER REFERENCES appointments(id),
  provider TEXT NOT NULL, provider_ref TEXT,
  amount INTEGER NOT NULL, currency TEXT DEFAULT 'FCFA',
  status TEXT NOT NULL DEFAULT 'pending',
  payer_phone TEXT, method TEXT, raw TEXT,
  created_at TEXT DEFAULT (datetime('now','localtime')), updated_at TEXT
)");
// FR-B5: a live (pending/confirmed) appointment locks its slot atomically
$pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS no_double_booking
            ON appointments (doctor_id, starts_at)
            WHERE status IN ('pending','confirmed')");

$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL, email TEXT UNIQUE NOT NULL, password_hash TEXT NOT NULL,
  role TEXT NOT NULL DEFAULT 'receptionist' CHECK (role IN ('admin','receptionist')),
  last_login_at TEXT, is_active INTEGER DEFAULT 1
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS messages (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL, phone TEXT, email TEXT, body TEXT NOT NULL,
  is_read INTEGER DEFAULT 0, created_at TEXT DEFAULT (datetime('now','localtime'))
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS testimonials (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  initials TEXT NOT NULL, body_fr TEXT NOT NULL, body_en TEXT NOT NULL,
  is_published INTEGER DEFAULT 1, sort_order INTEGER DEFAULT 0
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS settings (
  key TEXT PRIMARY KEY, value_fr TEXT, value_en TEXT
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS rate_limits (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  ip TEXT NOT NULL, action TEXT NOT NULL, ts INTEGER NOT NULL
)");

$pdo->exec("
CREATE TABLE IF NOT EXISTS audit_log (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER, action TEXT NOT NULL, entity TEXT, entity_id INTEGER,
  changes TEXT, created_at TEXT DEFAULT (datetime('now','localtime'))
)");

} // end SQLite schema

out('Schema OK.');

/* ---------- seed (only when empty) ---------- */

if ((int)$pdo->query("SELECT COUNT(*) c FROM services")->fetch()['c'] === 0) {

    $services = [
        // slug, fr, en, summary_fr, summary_en, icon, duration, flagship
        ['fertility', 'Fertilité & PMA', 'Fertility Center',
         'Bilan de fertilité du couple, suivi de l\'ovulation et accompagnement personnalisé vers la parentalité.',
         'Couple fertility assessment, ovulation monitoring and personalised support towards parenthood.',
         'fertility', 30, 1],
        ['gynecology', 'Obstétrique & Gynécologie', 'Obstetrics & Gynecology',
         'Santé de la femme à chaque étape : consultations gynécologiques, suivi de grossesse et accouchement.',
         'Women\'s health at every stage: gynecological consultations, pregnancy follow-up and delivery.',
         'gyneco', 20, 0],
        ['antenatal', 'Consultation prénatale (CPN)', 'Antenatal Clinic',
         'Suivi régulier de la grossesse pour la santé de la maman et du bébé, de la conception à la naissance.',
         'Regular pregnancy monitoring for mother and baby, from conception to birth.',
         'antenatal', 20, 0],
        ['general-medicine', 'Médecine générale', 'General Medicine',
         'Consultations pour toute la famille : diagnostic, traitement et orientation vers nos spécialistes.',
         'Consultations for the whole family: diagnosis, treatment and referral to our specialists.',
         'general', 20, 0],
        ['internal-medicine', 'Médecine interne & Cardiologie', 'Internal Medicine & Cardiology',
         'Prise en charge des maladies chroniques et du cœur : hypertension, diabète, suivi cardiologique.',
         'Care for chronic and heart conditions: hypertension, diabetes, cardiology follow-up.',
         'cardio', 30, 0],
        ['pediatrics', 'Pédiatrie', 'Pediatrics',
         'Soins attentifs pour nourrissons, enfants et adolescents : croissance, vaccins et consultations.',
         'Attentive care for infants, children and teens: growth, vaccines and consultations.',
         'pediatrics', 20, 0],
        ['surgery', 'Chirurgie', 'Surgery',
         'Interventions chirurgicales programmées dans un bloc opératoire équipé, avec suivi post-opératoire.',
         'Scheduled surgical procedures in an equipped operating theatre, with post-operative follow-up.',
         'surgery', 30, 0],
        ['imaging', 'Radiographie & Échographie', 'Radiography & Echography',
         'Imagerie médicale sur place : radiographies et échographies, dont l\'échographie obstétricale.',
         'On-site medical imaging: X-rays and ultrasound scans, including obstetric ultrasound.',
         'imaging', 20, 0],
        ['laboratory', 'Laboratoire d\'analyses', 'Laboratory',
         'Analyses médicales fiables et rapides : hématologie, biochimie, sérologie, bilans de fertilité.',
         'Reliable, fast medical tests: hematology, biochemistry, serology, fertility panels.',
         'lab', 15, 0],
    ];
    $st = $pdo->prepare("INSERT INTO services (slug,name_fr,name_en,summary_fr,summary_en,icon,duration_min,is_flagship,sort_order)
                         VALUES (?,?,?,?,?,?,?,?,?)");
    foreach ($services as $i => $s) $st->execute([...$s, $i]);
    $svcId = [];
    foreach ($pdo->query("SELECT id, slug FROM services")->fetchAll() as $r) $svcId[$r['slug']] = $r['id'];

    // real medical team + rich service content (includes/seed-content.php)
    $seed = require __DIR__ . '/includes/seed-content.php';
    $st = $pdo->prepare("INSERT INTO doctors (slug,full_name,onmc,specialty_fr,specialty_en,bio_fr,bio_en,languages,photo,sort_order)
                         VALUES (?,?,?,?,?,?,?,?,?,?)");
    $link = $pdo->prepare("INSERT INTO doctor_service (doctor_id,service_id) VALUES (?,?)");
    $sched = $pdo->prepare("INSERT INTO schedules (doctor_id,weekday,start_time,end_time,slot_minutes) VALUES (?,?,?,?,?)");
    foreach ($seed['doctors'] as $i => $d) {
        $st->execute([$d[0],$d[1],$d[2],$d[3],$d[4],$d[5],$d[6],$d[7],$d[8],$i]);
        $docId = (int)$pdo->lastInsertId();
        foreach ($d[9] as $slug) $link->execute([$docId, $svcId[$slug]]);
        foreach ($d[10] as $s) $sched->execute([$docId, $s[0], $s[1], $s[2], $s[3]]);
    }
    $up = $pdo->prepare("UPDATE services SET body_fr = ?, body_en = ?, features_fr = ?, features_en = ? WHERE slug = ?");
    foreach ($seed['service_content'] as $slug => $c) $up->execute([$c[0], $c[1], $c[2], $c[3], $slug]);

    // testimonials (anonymized — initials only, per TRD §8)
    $t = $pdo->prepare("INSERT INTO testimonials (initials,body_fr,body_en,sort_order) VALUES (?,?,?,?)");
    $t->execute(['M. & E. T.',
        'Après deux ans d\'attente, le Docteur nous a accompagnés avec une patience extraordinaire. Aujourd\'hui notre fille a 6 mois. Merci du fond du cœur.',
        'After two years of waiting, the Doctor supported us with extraordinary patience. Today our daughter is 6 months old. Thank you from the bottom of our hearts.', 0]);
    $t->execute(['S. N.',
        'Suivi de grossesse impeccable, équipe à l\'écoute et laboratoire sur place. Je recommande la fondation à toutes les futures mamans de Bonabéri.',
        'Flawless pregnancy follow-up, an attentive team and an on-site lab. I recommend the foundation to every expecting mother in Bonaberi.', 1]);
    $t->execute(['J.-P. K.',
        'Pris en charge pour mon hypertension par le cardiologue. Rendez-vous en ligne très pratique, zéro attente à la clinique.',
        'Treated for my hypertension by the cardiologist. Online booking is very practical — zero waiting at the clinic.', 2]);

    // settings
    $set = $pdo->prepare('INSERT INTO settings ("key",value_fr,value_en) VALUES (?,?,?)');
    $set->execute(['hours_weekday_val', '08h00 – 18h00', '8:00 AM – 6:00 PM']);
    $set->execute(['hours_saturday_val', '08h00 – 14h00', '8:00 AM – 2:00 PM']);
    // PLACEHOLDER stats — confirm real figures with the clinic
    $set->execute(['stat_years', '10', '10']);
    $set->execute(['stat_patients', '5000', '5000']);
    $set->execute(['stat_births', '1200', '1200']);
    $set->execute(['stat_services', '9', '9']);
    $set->execute(['consultation_fee', '5000', '5000']); // FCFA — adjust to the clinic's real fee

    out('Seed data OK (placeholder doctors/stats — replace with real clinic data).');
} else {
    out('Seed skipped (data already present).');
}

if ((int)$pdo->query("SELECT COUNT(*) c FROM users")->fetch()['c'] === 0) {
    $pdo->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)")
        ->execute(['Administrator', 'admin@saintsylvester.local', password_hash('ChangeMe!2026', PASSWORD_DEFAULT), 'admin']);
    out('Admin user created: admin@saintsylvester.local / ChangeMe!2026 — CHANGE THIS PASSWORD.');
}

out('Setup complete.');
