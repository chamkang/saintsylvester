<?php
$page = 'register';
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$success = null;
$duplicate = false;

// prefill from the post-booking prompt (?name=&phone=) — GET only, never overrides a submission
$prefill = [];
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (!empty($_GET['name'])) {
        $parts = preg_split('/\s+/', trim($_GET['name']), 2);
        $prefill['first_name'] = $parts[0] ?? '';
        $prefill['last_name'] = $parts[1] ?? '';
    }
    if (!empty($_GET['phone'])) $prefill['phone'] = trim($_GET['phone']);
}
$old = fn(string $k) => e($_POST[$k] ?? $prefill[$k] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $errors[] = t('err_generic');
    } elseif (!empty($_POST['website'])) {
        $errors[] = t('err_generic'); // honeypot
    } elseif (!rate_limit('register', 5, 3600)) {
        $errors[] = t('err_rate');
    } else {
        $first = trim($_POST['first_name'] ?? '');
        $last  = trim($_POST['last_name'] ?? '');
        $dob   = trim($_POST['dob'] ?? '');
        $sex   = in_array($_POST['sex'] ?? '', ['F', 'M'], true) ? $_POST['sex'] : '';
        $phone = clean_phone($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($first === '' || $last === '' || $dob === '' || $sex === '') $errors[] = t('err_required');
        if (!valid_phone($phone)) $errors[] = t('err_phone');
        if (empty($_POST['consent'])) $errors[] = t('err_consent');
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $email = '';
        if ($dob !== '' && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob) || $dob > date('Y-m-d'))) $errors[] = t('err_required');

        if (!$errors) {
            // FR-R3 duplicate guard — never reveal the existing file's contents
            $st = db()->prepare("SELECT id FROM patients WHERE phone = ? AND deleted_at IS NULL");
            $st->execute([$phone]);
            if ($st->fetch()) {
                $duplicate = true;
            } else {
                $marital = in_array($_POST['marital_status'] ?? '', ['single','married','divorced','widowed'], true)
                    ? $_POST['marital_status'] : null;
                $pdo = db();
                $pdo->beginTransaction();
                $pdo->prepare(
                    "INSERT INTO patients (first_name,last_name,dob,sex,marital_status,phone,email,address,
                                           emergency_name,emergency_phone,blood_group,allergies,medications,consent_at)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
                )->execute([
                    $first, $last, $dob, $sex, $marital, $phone, $email ?: null,
                    trim($_POST['address'] ?? '') ?: null,
                    trim($_POST['emergency_name'] ?? '') ?: null,
                    clean_phone($_POST['emergency_phone'] ?? '') ?: null,
                    trim($_POST['blood_group'] ?? '') ?: null,
                    trim($_POST['allergies'] ?? '') ?: null,
                    trim($_POST['medications'] ?? '') ?: null,
                    date('Y-m-d H:i:s'),
                ]);
                $id = (int)$pdo->lastInsertId();
                $mrn = sprintf('SSMF-P-%05d', $id);
                $pdo->prepare("UPDATE patients SET mrn = ? WHERE id = ?")->execute([$mrn, $id]);
                $pdo->commit();
                $success = $mrn;
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<?php page_banner(t('reg_title'), t('reg_sub'), t('nav_register')); ?>

<section class="section" style="padding-top:42px">
  <div class="container" style="max-width:780px">

  <?php if ($success): ?>
    <div class="form-card success-stage">
      <div class="success-check"><svg viewBox="0 0 24 24"><path d="M4 12.5 9.5 18 20 6.5"/></svg></div>
      <h2><?= t('reg_success_title') ?></h2>
      <p style="color:var(--n-600);max-width:460px;margin:0 auto 6px"><?= t('reg_success_sub') ?></p>
      <div class="ref-code"><?= e($success) ?></div>
      <p style="font-size:.9rem;color:var(--n-600)"><?= t('reg_bring') ?></p>
      <div class="success-actions">
        <a class="btn btn-primary" href="appointment.php"><?= icon('calendar') ?> <?= t('reg_book_now') ?></a>
        <a class="btn btn-outline" href="index.php"><?= t('back_home') ?></a>
      </div>
    </div>

  <?php else: ?>

    <?php if ($duplicate): ?>
      <div class="alert alert-info"><?= icon('shield') ?> <?= t('reg_exists') ?></div>
    <?php endif; ?>
    <?php foreach (array_unique($errors) as $err): ?>
      <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form class="form-card" method="post" novalidate>
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="text" name="website" value="" style="position:absolute;left:-200vw" tabindex="-1" aria-hidden="true">

      <div class="form-section-title"><span class="num">1</span> <?= t('reg_sec_identity') ?></div>
      <div class="form-grid">
        <div class="field">
          <label for="rFirst"><?= t('reg_first') ?> <span class="req">*</span></label>
          <input id="rFirst" name="first_name" type="text" required maxlength="80" value="<?= $old('first_name') ?>" autocomplete="given-name">
        </div>
        <div class="field">
          <label for="rLast"><?= t('reg_last') ?> <span class="req">*</span></label>
          <input id="rLast" name="last_name" type="text" required maxlength="80" value="<?= $old('last_name') ?>" autocomplete="family-name">
        </div>
        <div class="field">
          <label for="rDob"><?= t('reg_dob') ?> <span class="req">*</span></label>
          <input id="rDob" name="dob" type="date" required max="<?= date('Y-m-d') ?>" value="<?= $old('dob') ?>" autocomplete="bday">
        </div>
        <div class="field">
          <label for="rSex"><?= t('reg_sex') ?> <span class="req">*</span></label>
          <select id="rSex" name="sex" required>
            <option value=""></option>
            <option value="F" <?= ($_POST['sex'] ?? '') === 'F' ? 'selected' : '' ?>><?= t('sex_f') ?></option>
            <option value="M" <?= ($_POST['sex'] ?? '') === 'M' ? 'selected' : '' ?>><?= t('sex_m') ?></option>
          </select>
        </div>
        <div class="field">
          <label for="rMarital"><?= t('reg_marital') ?> <span class="opt">(<?= t('reg_optional') ?>)</span></label>
          <select id="rMarital" name="marital_status">
            <option value=""></option>
            <?php foreach (['single','married','divorced','widowed'] as $ms): ?>
              <option value="<?= $ms ?>" <?= ($_POST['marital_status'] ?? '') === $ms ? 'selected' : '' ?>><?= t('ms_' . $ms) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-section-title"><span class="num">2</span> <?= t('reg_sec_contact') ?></div>
      <div class="form-grid">
        <div class="field">
          <label for="rPhone"><?= t('reg_phone') ?> <span class="req">*</span></label>
          <input id="rPhone" name="phone" type="tel" required placeholder="+237 6XX XX XX XX" value="<?= $old('phone') ?>" autocomplete="tel">
          <div class="hint"><?= t('reg_phone_hint') ?></div>
        </div>
        <div class="field">
          <label for="rEmail"><?= t('bk_email') ?></label>
          <input id="rEmail" name="email" type="email" maxlength="160" value="<?= $old('email') ?>" autocomplete="email">
        </div>
        <div class="field full">
          <label for="rAddr"><?= t('reg_address') ?> <span class="opt">(<?= t('reg_optional') ?>)</span></label>
          <input id="rAddr" name="address" type="text" maxlength="200" value="<?= $old('address') ?>" autocomplete="street-address">
        </div>
      </div>

      <div class="form-section-title"><span class="num">3</span> <?= t('reg_sec_emergency') ?> <span class="opt">(<?= t('reg_optional') ?>)</span></div>
      <div class="form-grid">
        <div class="field">
          <label for="rEmName"><?= t('reg_em_name') ?></label>
          <input id="rEmName" name="emergency_name" type="text" maxlength="120" value="<?= $old('emergency_name') ?>">
        </div>
        <div class="field">
          <label for="rEmPhone"><?= t('reg_em_phone') ?></label>
          <input id="rEmPhone" name="emergency_phone" type="tel" placeholder="+237 6XX XX XX XX" value="<?= $old('emergency_phone') ?>">
        </div>
      </div>

      <div class="form-section-title"><span class="num">4</span> <?= t('reg_sec_medical') ?> <span class="opt">(<?= t('reg_optional') ?>)</span></div>
      <div class="form-grid">
        <div class="field">
          <label for="rBlood"><?= t('reg_blood') ?></label>
          <select id="rBlood" name="blood_group">
            <option value=""></option>
            <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
              <option <?= ($_POST['blood_group'] ?? '') === $bg ? 'selected' : '' ?>><?= $bg ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="rAllergies"><?= t('reg_allergies') ?></label>
          <input id="rAllergies" name="allergies" type="text" maxlength="300" value="<?= $old('allergies') ?>">
        </div>
        <div class="field full">
          <label for="rMeds"><?= t('reg_meds') ?></label>
          <input id="rMeds" name="medications" type="text" maxlength="300" value="<?= $old('medications') ?>">
        </div>
      </div>

      <div class="form-section-title" style="border-top:0;padding-top:8px"></div>
      <label class="check-row">
        <input type="checkbox" name="consent" value="1" required <?= !empty($_POST['consent']) ? 'checked' : '' ?>>
        <span><?= t('reg_consent') ?></span>
      </label>

      <div style="margin-top:26px;text-align:center">
        <button class="btn btn-primary btn-lg" type="submit"><?= icon('user') ?> <?= t('reg_btn') ?></button>
      </div>
    </form>
  <?php endif; ?>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
