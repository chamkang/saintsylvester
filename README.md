# SAINT SYLVESTER MEDICAL FOUNDATION — Website

Bilingual (FR default / EN) clinic website with online appointment booking and
account-less patient pre-registration, built per `SAINT-SYLVESTER-TRD.md`
(in the Desktop `novena` folder).

## Run it (XAMPP)

The site is already in `htdocs`, so with Apache running just open:

```
http://localhost/saintsylvester/
```

Or use the PHP built-in server (no Apache needed):

```
C:\xampp\php\php.exe -S 127.0.0.1:8123 -t C:\xampp\htdocs\saintsylvester
```

The SQLite database (`data/clinic.sqlite`) is already created and seeded.
To rebuild it from scratch: delete `data/clinic.sqlite` and run `php setup.php`.

## Admin panel

```
http://localhost/saintsylvester/admin/
login:    admin@saintsylvester.local
password: ChangeMe!2026   ← CHANGE THIS before going live
```

Roles: `admin`, `receptionist`. Features: dashboard, appointment
confirm/cancel/complete/no-show (with audit log), patient search + verify,
contact-message inbox.

## What's where

| Path | Purpose |
|---|---|
| `config.php` | Clinic identity (phone, WhatsApp, map), booking rules, DB driver |
| `setup.php` | Schema + seed (services, placeholder doctors/schedules, admin user) |
| `lang/fr.php`, `lang/en.php` | All UI strings (FR is default) |
| `includes/` | DB layer, helpers (i18n, CSRF, rate limit, slot engine), header/footer |
| `api/slots.php` | Available slots for doctor/service + date |
| `api/book.php` | Creates a pending appointment (atomic, double-booking impossible) |
| `appointment.php` + `assets/js/booking.js` | 4-step booking wizard |
| `register.php` | Account-less patient registration → MRN `SSMF-P-#####` |
| `manage.php` | Lookup/cancel by reference + phone (rate-limited) |
| `admin/` | Staff panel |
| `assets/css/main.css` | Design system (tokens in `:root`) |
| `assets/js/hero3d.js` | Three.js hero (auto-fallback to CSS poster) |

## Real data already in place

- Phone/WhatsApp: +237 675 97 13 96 · Email: fmsaintsylvestre@gmail.com · BP 9026 Bonabéri (`config.php`)
- Real medical team with ONMC numbers (`includes/seed-content.php` + DB): Dr. Akwa John (founder),
  Dr. Ayameria Assiene, Dr. Yemene Zangue, Dr. Engama Ebong, Dr. Tchatchouang Lowe
- Rich bilingual content on all 9 service pages (body + feature lists, stored in `services` table)

## STILL PLACEHOLDER — replace before launch

1. **Consultation days/hours per doctor** — invented; confirm with the clinic and edit the `schedules` table
2. **Doctor photos** — stock photos on 4 doctors, initials fallback on Dr. Tchatchouang;
   drop real files in `assets/img/team/` and update `doctors.photo`
3. **Exact map location / GPS** — `config.php` (`CLINIC_MAP_EMBED`, `CLINIC_MAP_LINK`); create a Google Business Profile
4. **Stats** (years, patients, births) — `settings` table
5. **Admin password + email** — `users` table
6. **Confirm +237 675 97 13 96 is on WhatsApp** (used for wa.me links)
7. **Email sending** — contact/booking confirmations currently store to DB only; wire PHPMailer + SMTP
8. **HTTPS + domain** — mandatory before collecting real patient data
9. **Go-live for online payment** — currently `PAYMENT_PROVIDER='sandbox'` (simulated). To take
   real MTN MoMo / Orange Money: open a **Fapshi** account (fapshi.com), put the keys in `config.php`
   (`FAPSHI_API_USER`, `FAPSHI_API_KEY`), set `PAYMENT_PROVIDER='fapshi'`, and deploy under HTTPS.
   Adjust the flat fee in admin/settings (`consultation_fee`, currently 5000 FCFA placeholder).

## Payment flow (how it works)

- Flat consultation fee, paid online before the visit. Booking → created `pending`/`unpaid`
  (holds the slot) → patient redirected to `pay.php` → pays via MoMo → `paid`.
- Provider is pluggable (`includes/payments.php`): `sandbox` (test, no real money) or `fapshi` (live).
- Unpaid bookings release their slot after `PAYMENT_HOLD_MINUTES` (20) via `sweep_expired_holds()`,
  called by the slot APIs and the booking endpoint.
- Receptionist can mark a booking paid at the desk (cash) from admin → Appointments → "Marquer payé".
- A patient who abandoned payment can resume it from "Manage my appointment".

## Production notes

- Switch to MySQL by editing `config.php` (`SSMF_DB_DRIVER`) — the schema in
  `setup.php` uses SQLite syntax; port the `CREATE TABLE` statements when migrating
  (keep the partial unique index on appointments → in MySQL 8 use a generated
  column or check-in-trigger).
- `data/` is blocked from HTTP by `.htaccess` (Apache). The PHP built-in server
  ignores `.htaccess` — fine for local dev only.
- The future HMS consumes `patients.mrn` and `appointments.reference` as stable
  IDs — never rename them.
