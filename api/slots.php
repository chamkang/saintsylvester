<?php
/* GET all slots for a day, each flagged available/booked.
   ?date=YYYY-MM-DD & (doctor_id=N | service_id=N for "no preference") */
require_once __DIR__ . '/../includes/functions.php';

sweep_expired_holds();

$date = $_GET['date'] ?? '';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) json_out(['error' => 'bad date'], 400);
$today = date('Y-m-d');
$max = date('Y-m-d', strtotime('+' . BOOKING_DAYS_AHEAD . ' days'));
if ($date < $today || $date > $max) json_out([]);

$doctors = [];
if (!empty($_GET['doctor_id'])) {
    $st = db()->prepare("SELECT * FROM doctors WHERE id = ? AND is_active = 1");
    $st->execute([(int)$_GET['doctor_id']]);
    if ($d = $st->fetch()) $doctors[] = $d;
} elseif (!empty($_GET['service_id'])) {
    $doctors = doctors_for_service((int)$_GET['service_id']);
}
if (!$doctors) json_out([]);

// Aggregate every doctor's slots by time. A time is available if ANY doctor is
// free; it stays in the list (flagged booked = red) if it exists in a schedule
// but no doctor is free at that moment.
$byTime = [];
foreach ($doctors as $d) {
    $short = preg_replace('/^Dr\.?\s*/', 'Dr ', $d['full_name']);
    foreach (day_slots((int)$d['id'], $date) as $s) {
        $hm = $s['time'];
        if (!isset($byTime[$hm])) {
            $byTime[$hm] = ['time' => $hm, 'available' => false, 'doctor_id' => null, 'doctor_name' => null, 'doctor_short' => null];
        }
        if ($s['available'] && !$byTime[$hm]['available']) {
            $byTime[$hm]['available']    = true;
            $byTime[$hm]['doctor_id']    = (int)$d['id'];
            $byTime[$hm]['doctor_name']  = $d['full_name'];
            $byTime[$hm]['doctor_short'] = $short;
        }
    }
}
ksort($byTime);
json_out(array_values($byTime));
