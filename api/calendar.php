<?php
/* GET availability status per date for the booking date strip.
   ?doctor_id=N | service_id=N  ->  { "2026-06-22": "open|full|off", ... }
     open = at least one slot free
     full = doctor works that day but every slot is booked (shown red)
     off  = doctor does not consult that day / no slots remain */
require_once __DIR__ . '/../includes/functions.php';

sweep_expired_holds();

$ids = [];
if (!empty($_GET['doctor_id'])) {
    $st = db()->prepare("SELECT id FROM doctors WHERE id = ? AND is_active = 1");
    $st->execute([(int)$_GET['doctor_id']]);
    if ($d = $st->fetch()) $ids[] = (int)$d['id'];
} elseif (!empty($_GET['service_id'])) {
    foreach (doctors_for_service((int)$_GET['service_id']) as $d) $ids[] = (int)$d['id'];
}
if (!$ids) json_out([]);

$out = [];
for ($i = 0; $i < BOOKING_DAYS_AHEAD; $i++) {
    $date = date('Y-m-d', strtotime("+$i days"));
    $hasSchedule = false;
    $hasOpen = false;
    foreach ($ids as $id) {
        $slots = day_slots($id, $date);
        if ($slots) $hasSchedule = true;
        foreach ($slots as $s) {
            if ($s['available']) { $hasOpen = true; break 2; }
        }
    }
    $out[$date] = !$hasSchedule ? 'off' : ($hasOpen ? 'open' : 'full');
}
json_out($out);
