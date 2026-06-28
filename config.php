<?php
/**
 * SAINT SYLVESTER MEDICAL FOUNDATION — site configuration
 * Driver: 'sqlite' (zero-setup dev) or 'mysql' (production).
 */

date_default_timezone_set('Africa/Douala');

define('SSMF_DB_DRIVER', 'sqlite');

// Canonical content (doctors + schedules) is re-synced from includes/seed-content.php
// whenever this version string changes. Bump it after editing seed content so the
// change applies on deploy without re-running setup or writing SQL.
define('SSMF_CONTENT_VERSION', '2026-06-23.schedules+ayamena');

// SQLite (dev)
define('SSMF_SQLITE_PATH', __DIR__ . '/data/clinic.sqlite');

// MySQL (production) — fill in before switching the driver
define('SSMF_MYSQL_HOST', 'localhost');
define('SSMF_MYSQL_NAME', 'saintsylvester');
define('SSMF_MYSQL_USER', 'root');
define('SSMF_MYSQL_PASS', '');

// Clinic identity
define('CLINIC_NAME', 'Saint Sylvester Medical Foundation');
define('CLINIC_PHONE', '+237 675 97 13 96');
define('CLINIC_PHONE_LINK', '+237675971396');
define('CLINIC_WHATSAPP', '237675971396');           // confirm this number is on WhatsApp
define('CLINIC_EMAIL', 'fmsaintsylvestre@gmail.com');
define('CLINIC_ADDRESS_FR', 'BP 9026, Bonabéri, Douala, Cameroun');
define('CLINIC_ADDRESS_EN', 'P.O. Box 9026, Bonaberi, Douala, Cameroon');
// TODO: replace with exact GPS coordinates / Plus Code from the clinic
define('CLINIC_MAP_EMBED', 'https://maps.google.com/maps?q=Bonaberi%2C+Douala%2C+Cameroon&t=&z=14&ie=UTF8&iwloc=&output=embed');
define('CLINIC_MAP_LINK', 'https://maps.google.com/?q=Bonaberi,+Douala,+Cameroon');

// Booking rules
define('BOOKING_DAYS_AHEAD', 21);     // how far in the future patients can book
define('BOOKING_CUTOFF_HOURS', 2);    // same-day cutoff before slot time

// ---- Payments (consultation fee, paid online before the visit) ----
define('PAYMENT_ENABLED', true);
// 'sandbox' = simulated payments for testing (no real money, works now).
// 'fapshi'  = live MTN MoMo / Orange Money via Fapshi (needs the keys below + HTTPS).
define('PAYMENT_PROVIDER', 'sandbox');
define('CONSULTATION_CURRENCY', 'FCFA');
define('PAYMENT_HOLD_MINUTES', 20);   // unpaid bookings release their slot after this

// Consultation fee in FCFA: a fixed default, with per-service overrides by slug.
define('CONSULTATION_FEE', 10000);
define('CONSULTATION_FEES', ['internal-medicine' => 15000]); // internist

// ---- Email notifications (new bookings) ----
define('BOOKING_NOTIFY_EMAIL', 'fmsaintsylvestre@gmail.com'); // where new bookings are sent
define('MAIL_FROM', 'Saint Sylvester <onboarding@resend.dev>'); // change to a verified domain once set up
define('RESEND_API_KEY', ''); // free key from resend.com — paste it here to switch emails on (see guide)

// Fapshi (https://fapshi.com) — get these from your Fapshi dashboard before going live
define('FAPSHI_BASE', 'https://live.fapshi.com'); // sandbox: https://sandbox.fapshi.com
define('FAPSHI_API_USER', '');        // TODO
define('FAPSHI_API_KEY', '');         // TODO

define('DEFAULT_LANG', 'fr');
