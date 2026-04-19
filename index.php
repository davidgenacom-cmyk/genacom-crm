<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/flash.php';
require __DIR__ . '/includes/layout.php';

require_login();
$user = current_user();
if (!$user) {
    header('Location: login.php');
    exit;
}

$pdo = db_connect($CONFIG);

$counts = [
    'contacts' => (int)$pdo->query('SELECT COUNT(*) FROM contacts')->fetchColumn(),
    'companies' => (int)$pdo->query('SELECT COUNT(*) FROM companies')->fetchColumn(),
    'deals_open' => (int)$pdo->query("SELECT COUNT(*) FROM deals WHERE stage NOT IN ('won','lost')")->fetchColumn(),
    'pipeline' => (float)$pdo->query("SELECT COALESCE(SUM(value),0) FROM deals WHERE stage NOT IN ('won','lost')")->fetchColumn(),
];

$recent = $pdo->query(
    'SELECT a.id, a.type, a.subject, a.created_at, c.first_name, c.last_name, d.title AS deal_title
     FROM activities a
     LEFT JOIN contacts c ON c.id = a.contact_id
     LEFT JOIN deals d ON d.id = a.deal_id
     ORDER BY a.created_at DESC
     LIMIT 8'
)->fetchAll();

$flash = flash_get();

render_head('Dashboard', ['active' => 'dash', 'user' => $user]);
if ($flash) {
    echo '<div class="flash ' . h($flash['type']) . '">' . h($flash['message']) . '</div>';
}
?>
  <div class="page-head fade-up">
    <h1>Dashboard</h1>
  </div>
  <p class="lead fade-up">Overview of relationships, pipeline value, and latest touchpoints.</p>

  <div class="grid-stats fade-up">
    <div class="stat-card">
      <div class="label">Contacts</div>
      <div class="value"><?= (int)$counts['contacts'] ?></div>
    </div>
    <div class="stat-card">
      <div class="label">Companies</div>
      <div class="value"><?= (int)$counts['companies'] ?></div>
    </div>
    <div class="stat-card">
      <div class="label">Open deals</div>
      <div class="value"><?= (int)$counts['deals_open'] ?></div>
    </div>
    <div class="stat-card">
      <div class="label">Pipeline (open)</div>
      <div class="value">$<?= number_format($counts['pipeline'], 0) ?></div>
    </div>
  </div>

  <div class="panel fade-up">
    <h2>Recent activity</h2>
    <?php if ($recent === []): ?>
      <p class="lead" style="margin:0">No activity yet. Log a call or note from the Activity page.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>When</th>
              <th>Type</th>
              <th>Subject</th>
              <th>Contact</th>
              <th>Deal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent as $r): ?>
              <tr>
                <td class="mono"><?= h(substr((string)$r['created_at'], 0, 16)) ?></td>
                <td><span class="badge"><?= h((string)$r['type']) ?></span></td>
                <td><?= h((string)($r['subject'] ?? '')) ?></td>
                <td><?= h(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''))) ?></td>
                <td><?= h((string)($r['deal_title'] ?? '')) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
<?php
render_footer();
