<?php
/* POST JSON — create a pending appointment (FR-B). Atomic slot lock via unique index. */
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'method'], 405);
$in = json_decode(file_get_contents('php://input'), true) ?: [];

if (!csrf_check($in['csrf'] ?? null)) json_out(['ok' => false, 'error' => t('err_generic')], 403);
if (!empty($in['website'])) json_out(['ok' => true, 'reference' => 'SSMF-0000-00000']); // honeypot: swallow bots
if (!rate_limit('book', 10, 3600)) json_out(['ok' => false, 'error' => t('err_rate')], 429);

sweep_expired_holds(); // free slots from bookings that were never paid in time

$serviceId = (int)($in['service_id'] ?? 0);
$doctorId  = (int)($in['doctor_id'] ?? 0);
$date      = $in['date'] ?? '';
$time      = $in['time'] ?? '';
$name      = trim($in['name'] ?? '');
$phone     = clean_phone($in['phone'] ?? '');
$email     = trim($in['email'] ?? '');
$bookingFor = ($in['booking_for'] ?? 'self') === 'other' ? 'other' : 'self';
$otherName = trim($in['other_name'] ?? '');
$notes     = mb_substr(trim($in['notes'] ?? ''), 0, 600);

if (!$serviceId || !$doctorId || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
    || !preg_match('/^\d{2}:\d{2}$/', $time) || $name === '') {
    json_out(['ok' => false, 'error' => t('err_required')], 422);
}
if (!valid_phone($phone)) json_out(['ok' => false, 'error' => t('err_phone')], 422);
if ($bookingFor === 'other' && $otherName === '') json_out(['ok' => false, 'error' => t('err_required')], 422);
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $email = '';

// the doctor must actually offer this service, and the slot must still be open
$st = db()->prepare("SELECT 1 FROM doctor_service WHERE doctor_id = ? AND service_id = ?");
$st->execute([$doctorId, $serviceId]);
if (!$st->fetch()) json_out(['ok' => false, 'error' => t('err_generic')], 422);
if (!in_array($time, available_slots($doctorId, $date), true)) {
    json_out(['ok' => false, 'code' => 'slot_taken', 'error' => t('err_slot_taken')], 409);
}

$svc = db()->prepare("SELECT slug, duration_min FROM services WHERE id = ?");
$svc->execute([$serviceId]);
$svcRow = $svc->fetch();
$duration = (int)($svcRow['duration_min'] ?? 20);
$svcSlug = $svcRow['slug'] ?? null;

// Link the booking to an existing patient file when possible:
//   1) an explicit MRN entered (strongest signal), otherwise
//   2) a self-booking whose phone matches a registered patient (phone is unique per file).
// Only self-bookings link by phone — for "someone else" the phone is the booker's, not the patient's.
// The front desk still verifies identity at the first visit (patients.verified_at), so a wrong
// match (e.g. a shared family phone) is caught there rather than silently trusted.
$patientId = null;
if (!empty($in['mrn']) && preg_match('/^SSMF-P-\d{3,}$/i', trim($in['mrn']))) {
    $st = db()->prepare("SELECT id FROM patients WHERE upper(mrn) = upper(?) AND deleted_at IS NULL");
    $st->execute([trim($in['mrn'])]);
    if ($p = $st->fetch()) $patientId = (int)$p['id'];
}
if ($patientId === null && $bookingFor === 'self') {
    $st = db()->prepare("SELECT id FROM patients WHERE phone = ? AND deleted_at IS NULL");
    $st->execute([$phone]);
    if ($p = $st->fetch()) $patientId = (int)$p['id'];
}

$startsAt = "$date $time:00";
$endsAt = date('Y-m-d H:i:s', strtotime($startsAt) + $duration * 60);

$needPay = PAYMENT_ENABLED && consultation_fee_for($svcSlug) > 0;
$fee = $needPay ? consultation_fee_for($svcSlug) : null;
$payToken = $needPay ? bin2hex(random_bytes(16)) : null;

$pdo = db();
try {
    $pdo->beginTransaction();
    $pdo->prepare(
        "INSERT INTO appointments (patient_id, guest_name, guest_phone, guest_email, booking_for, other_name,
                                   doctor_id, service_id, starts_at, ends_at, status, notes,
                                   payment_status, amount, currency, pay_token)
         VALUES (?,?,?,?,?,?,?,?,?,?, 'pending', ?, ?, ?, ?, ?)"
    )->execute([$patientId, $name, $phone, $email ?: null, $bookingFor, $otherName ?: null,
                $doctorId, $serviceId, $startsAt, $endsAt, $notes ?: null,
                $needPay ? 'unpaid' : 'waived', $fee, CONSULTATION_CURRENCY, $payToken]);
    $id = (int)$pdo->lastInsertId();
    $reference = sprintf('SSMF-%s-%05d', date('Y'), $id);
    $pdo->prepare("UPDATE appointments SET reference = ? WHERE id = ?")->execute([$reference, $id]);
    $pdo->commit();
} catch (PDOException $ex) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    // unique index hit = someone took the same slot a moment earlier (FR-B5)
    if (str_contains($ex->getMessage(), 'no_double_booking') || (string)$ex->getCode() === '23000') {
        json_out(['ok' => false, 'code' => 'slot_taken', 'error' => t('err_slot_taken')], 409);
    }
    json_out(['ok' => false, 'error' => t('err_generic')], 500);
}

if ($needPay) {
    json_out(['ok' => true, 'reference' => $reference,
              'pay_url' => 'pay.php?ref=' . urlencode($reference) . '&t=' . $payToken]);
}
json_out(['ok' => true, 'reference' => $reference]);
