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

// Canonical site host (no scheme), e.g. www.saintsylvester.cm. Once your paid
// domain is live, set the SITE_HOST env var in Vercel so every SEO URL (canonical,
// Open Graph, sitemap, robots) uses it and ranking consolidates on one domain.
// Empty = use whatever host served the request.
define('SITE_HOST', getenv('SITE_HOST') ?: '');

// Booking rules
define('BOOKING_DAYS_AHEAD', 21);     // how far in the future patients can book
define('BOOKING_CUTOFF_HOURS', 2);    // same-day cutoff before slot time

// ---- Payments ----
// false = consultation fee is paid at the clinic (no online payment step).
// true  = collect the fee online at booking via PAYMENT_PROVIDER below.
define('PAYMENT_ENABLED', false);
// 'sandbox' = simulated payments for testing (no real money, works now).
// 'fapshi'  = live MTN MoMo / Orange Money via Fapshi (set the env vars below).
// Set PAYMENT_PROVIDER=fapshi in the Vercel environment variables to go live.
define('PAYMENT_PROVIDER', getenv('PAYMENT_PROVIDER') ?: 'sandbox');
define('CONSULTATION_CURRENCY', 'FCFA');
define('PAYMENT_HOLD_MINUTES', 20);   // unpaid bookings release their slot after this

// Consultation fee in FCFA: a fixed default, with per-service overrides by slug.
define('CONSULTATION_FEE', 10000);
define('CONSULTATION_FEES', ['internal-medicine' => 15000]); // internist

// ---- Email notifications (new bookings) ----
define('BOOKING_NOTIFY_EMAIL', 'fmsaintsylvestre@gmail.com'); // where new bookings are sent
define('MAIL_FROM', 'Saint Sylvester <onboarding@resend.dev>'); // change to a verified domain once set up
// SECRET — set in Vercel env vars, NOT here (this repo is public).
define('RESEND_API_KEY', getenv('RESEND_API_KEY') ?: '');

// Fapshi (https://fapshi.com) — MTN MoMo / Orange Money. Keys are SECRETS:
// set them as Vercel environment variables, never in this (public) file.
define('FAPSHI_BASE', getenv('FAPSHI_BASE') ?: 'https://live.fapshi.com'); // sandbox: https://sandbox.fapshi.com
define('FAPSHI_API_USER', getenv('FAPSHI_API_USER') ?: '');
define('FAPSHI_API_KEY', getenv('FAPSHI_API_KEY') ?: '');

define('DEFAULT_LANG', 'fr');
