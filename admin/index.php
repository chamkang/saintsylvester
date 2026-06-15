<?php
/* Admin login — rate-limited, hashed passwords (FR-A1) */
require_once __DIR__ . '/_auth.php';

if (admin_user()) { header('Location: dashboard.php'); exit; }

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $error = 'Session expirée, réessayez.';
    } elseif (!rate_limit('admin_login', 6, 900)) {
        $error = 'Trop de tentatives. Réessayez dans 15 minutes.';
    } else {
        $st = db()->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $st->execute([trim($_POST['email'] ?? '')]);
        $u = $st->fetch();
        if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin'] = ['id' => (int)$u['id'], 'name' => $u['name'], 'role' => $u['role']];
            db()->prepare("UPDATE users SET last_login_at = ? WHERE id = ?")->execute([date('Y-m-d H:i:s'), $u['id']]);
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Identifiants incorrects.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Connexion · Admin — SSMF</title>
<link rel="icon" type="image/svg+xml" href="../assets/img/favicon.svg">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="login-wrap">
  <form class="login-card" method="post">
    <h1>SSMF <span style="color:var(--g600)">Admin</span></h1>
    <p>Saint Sylvester Medical Foundation — espace réservé au personnel.</p>
    <?php if ($error): ?><div class="err"><?= e($error) ?></div><?php endif; ?>
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <label for="aEmail">E-mail</label>
    <input id="aEmail" name="email" type="email" required autocomplete="username">
    <label for="aPass">Mot de passe</label>
    <input id="aPass" name="password" type="password" required autocomplete="current-password">
    <button class="btn btn-blue" type="submit">Se connecter</button>
  </form>
</div>
</body>
</html>
