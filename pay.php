<?php
$page = 'pay';
require_once __DIR__ . '/includes/functions.php';

sweep_expired_holds();

$ref = trim($_GET['ref'] ?? '');
$token = trim($_GET['t'] ?? '');

$st = db()->prepare(
    "SELECT a.*, d.full_name AS doctor_name, s.name_fr AS s_name_fr, s.name_en AS s_name_en
     FROM appointments a
     JOIN doctors d ON d.id = a.doctor_id
     JOIN services s ON s.id = a.service_id
     WHERE a.reference = ? AND a.pay_token = ?"
);
$st->execute([$ref, $token]);
$appt = $st->fetch();

$failed = !empty($_GET['failed']);

// handle a payment attempt
if ($appt && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)
    && $appt['status'] === 'pending' && $appt['payment_status'] === 'unpaid') {
    $method = in_array($_POST['method'] ?? '', ['mtn', 'orange'], true) ? $_POST['method'] : 'mtn';
    $action = $_POST['do'] ?? 'pay';
    $payId = payment_open((int)$appt['id'], (int)$appt['amount'], $method);

    if (PAYMENT_PROVIDER === 'sandbox') {
        if ($action === 'fail') {
            db()->prepare("UPDATE payments SET status = 'failed', updated_at = ? WHERE id = ?")
               ->execute([date('Y-m-d H:i:s'), $payId]);
            header('Location: pay.php?ref=' . urlencode($ref) . '&t=' . urlencode($token) . '&failed=1');
            exit;
        }
        payment_mark_paid((int)$appt['id'], 'SANDBOX-' . $payId, $method);
        header('Location: pay.php?ref=' . urlencode($ref) . '&t=' . urlencode($token));
        exit;
    }

    // live provider (Fapshi): start the charge and follow its instructions
    try {
        $r = payment_provider()->initiate($appt, $method);
        if (($r['mode'] ?? '') === 'redirect' && !empty($r['url'])) { header('Location: ' . $r['url']); exit; }
    } catch (Throwable $e) {
        header('Location: pay.php?ref=' . urlencode($ref) . '&t=' . urlencode($token) . '&failed=1');
        exit;
    }
}

require __DIR__ . '/includes/header.php';
$lang = current_lang();
$svcName = $appt ? ($appt['s_name_' . $lang] ?? $appt['s_name_fr']) : '';
page_banner(t('pay_title'), t('pay_sub'), t('nav_book'), 'assets/img/hero/slide-1.jpg');
?>

<section class="section" style="padding-top:42px">
  <div class="container" style="max-width:640px">

  <?php if (!$appt): ?>
    <div class="alert alert-error"><?= icon('shield') ?> <?= t('pay_notfound') ?></div>
    <p style="text-align:center"><a class="btn btn-primary" href="appointment.php"><?= t('pay_rebook') ?></a></p>

  <?php elseif ($appt['payment_status'] === 'paid'): ?>
    <?php
      $waText = rawurlencode(($lang === 'fr'
        ? "Bonjour, je confirme mon rendez-vous {$appt['reference']} (consultation réglée). Merci."
        : "Hello, confirming my appointment {$appt['reference']} (consultation paid). Thank you."));
      $showReg = empty($appt['patient_id']);
    ?>
    <div class="form-card success-stage">
      <div class="success-check"><svg viewBox="0 0 24 24"><path d="M4 12.5 9.5 18 20 6.5"/></svg></div>
      <h2><?= t('pay_done_title') ?></h2>
      <p style="color:var(--n-600);max-width:460px;margin:0 auto 6px"><?= t('pay_done_sub') ?></p>
      <div class="ref-code"><?= e($appt['reference']) ?></div>
      <p class="pay-receipt"><?= icon('check') ?> <?= t('pay_paid') ?> — <?= e(money((int)$appt['amount'])) ?></p>
      <p style="font-size:.9rem;color:var(--n-600)"><?= t('bk_pending_note') ?><br><?= t('bk_manage_note') ?></p>
      <div class="success-actions">
        <a class="btn btn-primary" target="_blank" rel="noopener" href="https://wa.me/<?= CLINIC_WHATSAPP ?>?text=<?= $waText ?>"><?= t('bk_whatsapp') ?></a>
        <a class="btn btn-outline" href="index.php"><?= t('back_home') ?></a>
      </div>
      <?php if ($showReg): ?>
      <div class="success-reg">
        <h3><?= t('bk_reg_title') ?></h3>
        <p><?= t('bk_reg_p') ?></p>
        <a class="btn btn-navy" href="register.php?name=<?= urlencode($appt['booking_for'] === 'other' && $appt['other_name'] ? $appt['other_name'] : $appt['guest_name']) ?>&phone=<?= urlencode($appt['guest_phone']) ?>"><?= t('bk_reg_btn') ?></a>
      </div>
      <?php endif; ?>
    </div>

  <?php elseif ($appt['status'] !== 'pending'): ?>
    <div class="alert alert-error"><?= icon('clock') ?> <strong><?= t('pay_expired_t') ?></strong></div>
    <p style="color:var(--n-600)"><?= t('pay_expired_p') ?></p>
    <p style="text-align:center"><a class="btn btn-primary" href="appointment.php"><?= t('pay_rebook') ?></a></p>

  <?php else: ?>
    <?php if ($failed): ?><div class="alert alert-error"><?= icon('shield') ?> <?= t('pay_failed') ?></div><?php endif; ?>

    <div class="pay-card">
      <div class="pay-summary">
        <h3><?= icon('calendar') ?> <?= t('pay_appt') ?></h3>
        <div class="sum-row"><?= icon('heart') ?><span><small><?= t('bk_step1') ?></small><b><?= e($svcName) ?></b></span></div>
        <div class="sum-row"><?= icon('user') ?><span><small><?= t('bk_step2') ?></small><b><?= e($appt['doctor_name']) ?></b></span></div>
        <div class="sum-row"><?= icon('calendar') ?><span><small><?= t('bk_step3') ?></small><b><?= e(format_date_local(substr($appt['starts_at'], 0, 10))) ?> · <?= substr($appt['starts_at'], 11, 5) ?></b></span></div>
        <div class="sum-row"><?= icon('shield') ?><span><small><?= t('mg_status') ?></small><b><?= e($appt['reference']) ?></b></span></div>
      </div>

      <div class="pay-amount-box">
        <span><?= t('pay_amount') ?></span>
        <strong><?= e(money((int)$appt['amount'])) ?></strong>
      </div>

      <form method="post" class="pay-form">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <p class="pay-method-label"><?= t('pay_method') ?></p>
        <div class="pay-methods">
          <label class="pay-method">
            <input type="radio" name="method" value="mtn" checked>
            <span class="pm-card pm-mtn"><b>MTN</b> <?= t('pay_mtn') ?></span>
          </label>
          <label class="pay-method">
            <input type="radio" name="method" value="orange">
            <span class="pm-card pm-orange"><b>Orange</b> <?= t('pay_orange') ?></span>
          </label>
        </div>

        <button class="btn btn-primary btn-lg pay-submit" type="submit" name="do" value="pay">
          <?= icon('shield') ?> <?= t('pay_btn', ['amount' => money((int)$appt['amount'])]) ?>
        </button>
        <p class="pay-secure"><?= icon('shield') ?> <?= t('pay_secure') ?></p>

        <?php if (PAYMENT_PROVIDER === 'sandbox'): ?>
        <div class="pay-sandbox">
          <span class="pay-sandbox-tag"><?= t('pay_sandbox') ?></span>
          <button class="btn btn-outline" type="submit" name="do" value="fail"><?= t('pay_sim_fail') ?></button>
        </div>
        <?php endif; ?>
      </form>
    </div>
  <?php endif; ?>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
