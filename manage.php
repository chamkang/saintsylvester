<?php
$page = 'manage';
require_once __DIR__ . '/includes/functions.php';

$error = null;
$cancelled = false;
$appt = null;

function find_appt(string $ref, string $phone): ?array {
    $st = db()->prepare(
        "SELECT a.*, d.full_name AS doctor_name,
                s.name_fr AS s_name_fr, s.name_en AS s_name_en
         FROM appointments a
         JOIN doctors d ON d.id = a.doctor_id
         JOIN services s ON s.id = a.service_id
         WHERE upper(a.reference) = upper(?) AND a.guest_phone = ?"
    );
    $st->execute([trim($ref), clean_phone($phone)]);
    return $st->fetch() ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $error = t('err_generic');
    } elseif (!rate_limit('manage', 8, 600)) { // anti-enumeration (TRD §7)
        $error = t('err_rate');
    } else {
        $appt = find_appt($_POST['reference'] ?? '', $_POST['phone'] ?? '');
        if (!$appt) {
            $error = t('mg_not_found');
        } elseif (($_POST['do'] ?? '') === 'cancel' && in_array($appt['status'], ['pending', 'confirmed'], true)) {
            db()->prepare("UPDATE appointments SET status = 'cancelled', cancel_reason = 'patient', updated_at = ? WHERE id = ?")
               ->execute([date('Y-m-d H:i:s'), $appt['id']]);
            $appt['status'] = 'cancelled';
            $cancelled = true;
        }
    }
}

require __DIR__ . '/includes/header.php';
$svcName = $appt ? ($appt['s_name_' . current_lang()] ?? $appt['s_name_fr']) : '';
?>

<?php page_banner(t('mg_title'), t('mg_sub'), t('nav_manage')); ?>

<section class="section" style="padding-top:42px">
  <div class="container" style="max-width:640px">

    <?php if ($cancelled): ?>
      <div class="alert alert-success"><?= icon('check') ?> <?= t('mg_cancelled_ok') ?></div>
    <?php elseif ($error): ?>
      <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($appt): ?>
      <div class="appt-card">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:18px">
          <strong style="font-family:var(--font-head);font-size:1.15rem;color:var(--primary-900)"><?= e($appt['reference']) ?></strong>
          <span class="status-badge status-<?= e($appt['status']) ?>"><?= t('status_' . $appt['status']) ?></span>
        </div>
        <div class="sum-row"><?= icon('heart') ?><span><small><?= t('bk_step1') ?></small><b><?= e($svcName) ?></b></span></div>
        <div class="sum-row"><?= icon('user') ?><span><small><?= t('bk_step2') ?></small><b><?= e($appt['doctor_name']) ?></b></span></div>
        <div class="sum-row"><?= icon('calendar') ?><span><small><?= t('bk_step3') ?></small>
          <b><?= e(format_date_local(substr($appt['starts_at'], 0, 10))) ?> · <?= substr($appt['starts_at'], 11, 5) ?></b></span></div>
        <?php if (($appt['payment_status'] ?? '') === 'paid'): ?>
          <div class="sum-row"><?= icon('check') ?><span><small><?= t('bk_fee_label') ?></small><b><?= t('pay_paid') ?> · <?= e(money((int)$appt['amount'])) ?></b></span></div>
        <?php elseif (($appt['payment_status'] ?? '') === 'unpaid' && $appt['status'] === 'pending' && !empty($appt['pay_token'])): ?>
          <div class="sum-row"><?= icon('shield') ?><span><small><?= t('bk_fee_label') ?></small><b style="color:var(--warning)"><?= e(money((int)$appt['amount'])) ?></b></span></div>
          <a class="btn btn-primary" style="margin-top:14px" href="pay.php?ref=<?= urlencode($appt['reference']) ?>&t=<?= urlencode($appt['pay_token']) ?>"><?= icon('shield') ?> <?= t('pay_btn', ['amount' => money((int)$appt['amount'])]) ?></a>
        <?php endif; ?>

        <?php if (in_array($appt['status'], ['pending', 'confirmed'], true)): ?>
          <form method="post" style="margin-top:22px" onsubmit="return confirm(<?= json_encode(t('mg_cancel_confirm')) ?>)">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="reference" value="<?= e($appt['reference']) ?>">
            <input type="hidden" name="phone" value="<?= e($appt['guest_phone']) ?>">
            <input type="hidden" name="do" value="cancel">
            <button class="btn btn-ghost-danger" type="submit"><?= t('mg_cancel') ?></button>
          </form>
        <?php endif; ?>
      </div>

    <?php else: ?>
      <form class="form-card" method="post">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <div class="form-grid">
          <div class="field full">
            <label for="mRef"><?= t('mg_ref') ?> <span class="req">*</span></label>
            <input id="mRef" name="reference" type="text" required placeholder="SSMF-2026-00000" value="<?= e($_POST['reference'] ?? '') ?>">
          </div>
          <div class="field full">
            <label for="mPhone"><?= t('mg_phone') ?> <span class="req">*</span></label>
            <input id="mPhone" name="phone" type="tel" required placeholder="+237 6XX XX XX XX" value="<?= e($_POST['phone'] ?? '') ?>">
          </div>
        </div>
        <div style="margin-top:22px;text-align:center">
          <button class="btn btn-primary" type="submit"><?= t('mg_find') ?> <?= icon('arrow') ?></button>
        </div>
      </form>
    <?php endif; ?>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
