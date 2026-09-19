<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

session_bootstrap();

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $error = "Sessiya eskirgan. Sahifani yangilab qayta urinib ko'ring.";
    } else {
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        $pdo = get_pdo();
        $stmt = $pdo->prepare('SELECT id, password_hash FROM admins WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $username;
            header('Location: index.php');
            exit;
        }

        sleep(1);
        $error = "Login yoki parol noto'g'ri.";
    }
}

$token = csrf_token();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kirish — Admin panel</title>
<link rel="icon" href="../images/favicon.png" type="image/png">
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="admin.css">
</head>
<body class="adm-body">

<div class="admin-login-wrap">
  <div class="admin-login-card">
    <div class="admin-login-card__brand">
      <img src="../images/logo.png" alt="">
      <div>
        <h1>Admin panel</h1>
      </div>
    </div>
    <p class="admin-login-card__sub">Zayavkalarni ko'rish uchun tizimga kiring.</p>

    <?php if ($error !== ''): ?>
      <div class="admin-error"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
      <div class="field">
        <label for="username">Login</label>
        <input type="text" id="username" name="username" required autofocus>
      </div>
      <div class="field">
        <label for="password">Parol</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn btn--primary btn--block">Kirish</button>
    </form>
  </div>
</div>

</body>
</html>
