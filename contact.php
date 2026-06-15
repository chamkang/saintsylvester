<?php
$page = 'contact';
require_once __DIR__ . '/includes/functions.php';

$sent = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null) || !empty($_POST['website'])) {
        $errors[] = t('err_generic');
    } elseif (!rate_limit('contact', 5, 3600)) {
        $errors[] = t('err_rate');
    } else {
        $name = trim($_POST['name'] ?? '');
        $phone = clean_phone($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $body = trim($_POST['message'] ?? '');
        if ($name === '' || $body === '') $errors[] = t('err_required');
        if (!valid_phone($phone)) $errors[] = t('err_phone');
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $email = '';
        if (!$errors) {
            db()->prepare("INSERT INTO messages (name, phone, email, body) VALUES (?,?,?,?)")
               ->execute([$name, $phone, $email ?: null, mb_substr($body, 0, 2000)]);
            $sent = true;
            // production: also send via SMTP (PHPMailer) — see TRD §6.1
        }
    }
}

require __DIR__ . '/includes/header.php';
$lang = current_lang();
?>

<?php page_banner(t('ct_title'), t('ct_sub'), t('nav_contact')); ?>

<section class="section" style="padding-top:42px">
  <div class="container contact-layout">

    <div class="contact-cards reveal-stagger">
      <div class="info-card">
        <span class="svc-chip"><?= icon('pin') ?></span>
        <div>
          <h3><?= t('ct_address_t') ?></h3>
          <p><?= e($lang === 'fr' ? CLINIC_ADDRESS_FR : CLINIC_ADDRESS_EN) ?></p>
          <a class="detail" href="<?= CLINIC_MAP_LINK ?>" target="_blank" rel="noopener"><?= t('ct_directions') ?> →</a>
        </div>
      </div>
      <div class="info-card">
        <span class="svc-chip"><?= icon('phone') ?></span>
        <div>
          <h3><?= t('ct_call_t') ?></h3>
          <a class="detail" href="tel:<?= CLINIC_PHONE_LINK ?>"><?= CLINIC_PHONE ?></a>
        </div>
      </div>
      <div class="info-card">
        <span class="svc-chip"><?= icon('whatsapp') ?></span>
        <div>
          <h3><?= t('ct_wa_t') ?></h3>
          <a class="detail" href="https://wa.me/<?= CLINIC_WHATSAPP ?>" target="_blank" rel="noopener"><?= t('ct_wa_btn') ?> →</a>
        </div>
      </div>
      <div class="info-card">
        <span class="svc-chip"><?= icon('clock') ?></span>
        <div>
          <h3><?= t('ct_hours_t') ?></h3>
          <p><?= t('hours_weekday') ?> : <?= e(setting('hours_weekday_val')) ?><br>
             <?= t('hours_saturday') ?> : <?= e(setting('hours_saturday_val')) ?><br>
             <?= t('hours_sunday') ?> : <?= t('hours_sunday_val') ?></p>
        </div>
      </div>
    </div>

    <div>
      <div class="contact-map reveal" data-tilt>
        <iframe src="<?= CLINIC_MAP_EMBED ?>" title="Google Maps — <?= CLINIC_NAME ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
      </div>

      <div class="form-card reveal">
        <h2 style="font-size:1.3rem"><?= t('ct_form_title') ?></h2>

        <?php if ($sent): ?>
          <div class="alert alert-success"><?= icon('check') ?> <?= t('ct_sent') ?></div>
        <?php endif; ?>
        <?php foreach (array_unique($errors) as $err): ?>
          <div class="alert alert-error"><?= e($err) ?></div>
        <?php endforeach; ?>

        <?php if (!$sent): ?>
        <form method="post" novalidate>
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
          <input type="text" name="website" value="" style="position:absolute;left:-200vw" tabindex="-1" aria-hidden="true">
          <div class="form-grid">
            <div class="field">
              <label for="cName"><?= t('ct_name') ?> <span class="req">*</span></label>
              <input id="cName" name="name" type="text" required maxlength="120" value="<?= e($_POST['name'] ?? '') ?>">
            </div>
            <div class="field">
              <label for="cPhone"><?= t('ct_phone') ?> <span class="req">*</span></label>
              <input id="cPhone" name="phone" type="tel" required placeholder="+237 6XX XX XX XX" value="<?= e($_POST['phone'] ?? '') ?>">
            </div>
            <div class="field full">
              <label for="cEmail"><?= t('ct_email') ?></label>
              <input id="cEmail" name="email" type="email" maxlength="160" value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="field full">
              <label for="cMsg"><?= t('ct_msg') ?> <span class="req">*</span></label>
              <textarea id="cMsg" name="message" required maxlength="2000"><?= e($_POST['message'] ?? '') ?></textarea>
            </div>
          </div>
          <div style="margin-top:20px">
            <button class="btn btn-primary" type="submit"><?= icon('mail') ?> <?= t('ct_send') ?></button>
          </div>
        </form>
        <?php endif; ?>
      </div>
    </div>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
