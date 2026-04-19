<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/flash.php';
require __DIR__ . '/includes/layout.php';

if (current_user()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if ($email === '' || $password === '') {
        $error = 'Enter email and password.';
    } else {
        try {
            $pdo = db_connect($CONFIG);
            if (login_user($pdo, $email, $password)) {
                header('Location: index.php');
                exit;
            }
            $error = 'Invalid credentials.';
        } catch (Throwable $e) {
            $error = 'Cannot connect to database. Check config.php and MySQL.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign in | Genacom CRM</title>
  <link rel="icon" type="image/png" href="https://cdn.prod.website-files.com/698569e723cb64d9d28f0a78/698569e723cb64d9d28f0af8_genacom-fav.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="login-wrap">
    <div class="login-card fade-up">
      <a class="brand" href="https://www.genacom.com/" target="_blank" rel="noopener">
        <img src="https://cdn.prod.website-files.com/698569e723cb64d9d28f0a78/69856a1b23cb64d9d28f1242_genacom-logo.svg" alt="Genacom" width="140" height="36" class="brand-logo">
      </a>
      <h1>Sign in</h1>
      <p class="sub">Genacom CRM — secure access for your pipeline.</p>
      <?php if ($error !== ''): ?>
        <div class="flash error"><?= h($error) ?></div>
      <?php endif; ?>
      <form method="post" action="login.php" autocomplete="on">
        <div class="form-field">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-field" style="margin-top:0.75rem">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Continue</button>
        </div>
      </form>
      <p class="sub" style="margin-top:1.25rem;font-size:0.82rem">First-time setup? Copy <span class="mono">config.sample.php</span> to <span class="mono">config.php</span>, run <span class="mono">php install/setup-database.php</span>, then sign in with the default admin from the README.</p>
    </div>
  </div>
  <script src="assets/js/main.js"></script>
</body>
</html>
