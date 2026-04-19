<?php
declare(strict_types=1);

/**
 * @param array<string,mixed> $opts title, active (nav slug), user
 */
function render_head(string $title, array $opts = []): void
{
    $pageTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $active = $opts['active'] ?? '';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex,nofollow">
  <title><?= $pageTitle ?> | Genacom CRM</title>
  <link rel="icon" type="image/png" href="https://cdn.prod.website-files.com/698569e723cb64d9d28f0a78/698569e723cb64d9d28f0af8_genacom-fav.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="app-body">
  <a class="skip-link" href="#main">Skip to content</a>
  <header class="app-topbar">
    <div class="app-topbar-inner">
      <a class="brand" href="index.php">
        <img src="https://cdn.prod.website-files.com/698569e723cb64d9d28f0a78/69856a1b23cb64d9d28f1242_genacom-logo.svg" alt="Genacom" width="120" height="32" class="brand-logo">
        <span class="brand-text">CRM</span>
      </a>
      <?php if (!empty($opts['user'])): ?>
      <nav class="app-nav" aria-label="Main">
        <a href="index.php" class="nav-link<?= $active === 'dash' ? ' active' : '' ?>">Dashboard</a>
        <a href="contacts.php" class="nav-link<?= $active === 'contacts' ? ' active' : '' ?>">Contacts</a>
        <a href="companies.php" class="nav-link<?= $active === 'companies' ? ' active' : '' ?>">Companies</a>
        <a href="deals.php" class="nav-link<?= $active === 'deals' ? ' active' : '' ?>">Deals</a>
        <a href="activities.php" class="nav-link<?= $active === 'activities' ? ' active' : '' ?>">Activity</a>
      </nav>
      <div class="user-menu">
        <span class="user-name"><?= htmlspecialchars((string)$opts['user']['name'], ENT_QUOTES, 'UTF-8') ?></span>
        <a href="logout.php" class="btn btn-ghost btn-sm">Sign out</a>
      </div>
      <?php endif; ?>
    </div>
  </header>
  <main id="main" class="app-main">
<?php
}

function render_footer(): void
{
    ?>
  </main>
  <footer class="app-footer">
    <div class="app-footer-inner">
      <p>Genacom · <a href="mailto:david@genacom.com">david@genacom.com</a> · Bay Area website management</p>
    </div>
  </footer>
  <script src="assets/js/main.js"></script>
</body>
</html>
<?php
}

function h(?string $s): string
{
    if ($s === null) {
        return '';
    }
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
