<?php
$page = 'appointment';
$extraScripts = ['assets/js/booking.js'];
require __DIR__ . '/includes/header.php';

// Services not offered for online booking (handled in-clinic / via a consultation).
$hideFromBooking = ['surgery', 'imaging', 'laboratory'];
$services = array_values(array_filter(get_services(), fn($s) => !in_array($s['slug'], $hideFromBooking, true)));
$doctors = db()->query("SELECT * FROM doctors WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
$links = db()->query("SELECT doctor_id, service_id FROM doctor_service")->fetchAll(PDO::FETCH_NUM);

$jsServices = array_map(fn($s) => [
    'id' => (int)$s['id'], 'slug' => $s['slug'], 'name' => lcol($s, 'name'),
    'duration' => (int)$s['duration_min'], 'iconHtml' => service_icon($s['icon']),
    'feeLabel' => money(consultation_fee_for($s['slug'])),
], $services);

$jsDoctors = array_map(function ($d) {
    $parts = preg_split('/[\s.]+/', trim(str_replace('Dr', '', $d['full_name'])));
    $ini = '';
    foreach ($parts as $w) { if ($w !== '' && ctype_alpha($w[0])) $ini .= strtoupper($w[0]); if (strlen($ini) >= 2) break; }
    return ['id' => (int)$d['id'], 'name' => $d['full_name'], 'specialty' => lcol($d, 'specialty'), 'initials' => $ini];
}, $doctors);

$waTemplate = current_lang() === 'fr'
    ? 'Bonjour, je confirme ma demande de rendez-vous {ref} du {date} à {time}. Merci.'
    : 'Hello, I am confirming my appointment request {ref} on {date} at {time}. Thank you.';

$bookingData = [
    'lang' => current_lang(),
    'csrf' => csrf_token(),
    'whatsapp' => CLINIC_WHATSAPP,
    'daysAhead' => BOOKING_DAYS_AHEAD,
    'services' => $jsServices,
    'doctors' => $jsDoctors,
    'links' => array_map(fn($l) => [(int)$l[0], (int)$l[1]], $links),
    'i18n' => [
        'step1' => t('bk_step1'), 'step2' => t('bk_step2'), 'step3' => t('bk_step3'),
        'next' => t('bk_next'), 'confirm' => (PAYMENT_ENABLED && consultation_fee() > 0) ? t('bk_confirm_pay') : t('bk_confirm'),
        'anyDoctor' => t('bk_any_doctor'), 'anyDoctorSub' => t('bk_any_doctor_sub'),
        'pickDate' => t('bk_pick_date'), 'pickSlot' => t('bk_pick_slot'),
        'noSlots' => t('bk_no_slots'), 'loading' => t('bk_loading'),
        'booked' => t('bk_booked'), 'dayFull' => t('bk_day_full'),
        'legendOpen' => t('bk_legend_open'), 'legendFull' => t('bk_legend_full'),
        'duration' => t('svc_duration', ['min' => '{min}']),
        'summaryEmpty' => t('bk_sub'),
        'errRequired' => t('err_required'), 'errGeneric' => t('err_generic'),
        'errPickSlot' => t('bk_pick_time_err'),
        'successTitle' => t('bk_success_title'), 'successSub' => t('bk_success_sub'),
        'pendingNote' => t('bk_pending_note'), 'manageNote' => t('bk_manage_note'),
        'waConfirm' => t('bk_whatsapp'), 'bookNew' => t('bk_new'),
        'waTemplate' => $waTemplate,
        'regTitle' => t('bk_reg_title'), 'regP' => t('bk_reg_p'), 'regBtn' => t('bk_reg_btn'),
    ],
];
?>

<?php page_banner(t('bk_title'), t('bk_sub'), t('nav_book'), 'assets/img/hero/slide-3.jpg'); ?>

<section class="section" id="wizardTop" style="padding-top:42px">
  <div class="container wizard-layout">
    <div id="wizardWrap">
      <div class="wizard-steps" aria-hidden="true">
        <div class="wstep active"><span><?= t('bk_step1') ?></span></div>
        <div class="wstep"><span><?= t('bk_step2') ?></span></div>
        <div class="wstep"><span><?= t('bk_step3') ?></span></div>
        <div class="wstep"><span><?= t('bk_step4') ?></span></div>
      </div>

      <div id="wzError" aria-live="polite"></div>

      <div class="wizard-panel" id="panel1"></div>
      <div class="wizard-panel" id="panel2" hidden></div>
      <div class="wizard-panel" id="panel3" hidden></div>
      <div class="wizard-panel" id="panel4" hidden>
        <form id="bookForm" autocomplete="on">
          <div class="form-grid">
            <div class="field full">
              <label><?= t('bk_for') ?></label>
              <div style="display:flex;gap:22px;padding-top:4px">
                <label class="check-row" style="font-weight:500"><input type="radio" name="booking_for" value="self" checked> <?= t('bk_for_self') ?></label>
                <label class="check-row" style="font-weight:500"><input type="radio" name="booking_for" value="other"> <?= t('bk_for_other') ?></label>
              </div>
            </div>
            <div class="field full" id="otherNameField" hidden>
              <label for="bfOther"><?= t('bk_other_name') ?> <span class="req">*</span></label>
              <input id="bfOther" name="other_name" type="text" maxlength="120">
            </div>
            <div class="field">
              <label for="bfName"><?= t('bk_name') ?> <span class="req">*</span></label>
              <input id="bfName" name="name" type="text" required maxlength="120" autocomplete="name">
            </div>
            <div class="field">
              <label for="bfPhone"><?= t('bk_phone') ?> <span class="req">*</span></label>
              <input id="bfPhone" name="phone" type="tel" required placeholder="+237 6XX XX XX XX" autocomplete="tel">
            </div>
            <div class="field">
              <label for="bfEmail"><?= t('bk_email') ?></label>
              <input id="bfEmail" name="email" type="email" maxlength="160" autocomplete="email">
            </div>
            <div class="field">
              <label for="bfMrn"><?= t('bk_mrn') ?></label>
              <input id="bfMrn" name="mrn" type="text" maxlength="20" placeholder="SSMF-P-00000">
              <div class="hint"><?= t('bk_mrn_hint') ?></div>
            </div>
            <div class="field full">
              <label for="bfNotes"><?= t('bk_notes') ?></label>
              <textarea id="bfNotes" name="notes" maxlength="600"></textarea>
            </div>
            <input type="text" name="website" value="" style="position:absolute;left:-200vw" tabindex="-1" aria-hidden="true">
          </div>
        </form>
      </div>

      <div class="wizard-nav">
        <button class="btn btn-outline" type="button" id="wzBack" style="visibility:hidden"><?= t('bk_back') ?></button>
        <button class="btn btn-primary" type="button" id="wzNext" hidden><?= t('bk_next') ?></button>
      </div>
    </div>

    <aside class="summary-card" aria-live="polite">
      <h3><?= icon('calendar') ?> <?= t('bk_summary') ?></h3>
      <div id="sumRows"><p class="sum-placeholder"><?= t('bk_sub') ?></p></div>
      <?php if (PAYMENT_ENABLED && consultation_fee() > 0): ?>
      <div class="sum-fee">
        <span><?= t('bk_fee_label') ?></span>
        <strong id="sumFee"><?= e(money(consultation_fee())) ?></strong>
      </div>
      <p class="sum-fee-note"><?= icon('shield') ?> <?= t('bk_fee_note') ?></p>
      <?php endif; ?>
    </aside>
  </div>
</section>

<script>window.SSMF_BOOKING = <?= json_encode($bookingData, JSON_UNESCAPED_UNICODE) ?>;</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
