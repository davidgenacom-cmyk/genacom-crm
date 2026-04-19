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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'save') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = trim((string)($_POST['name'] ?? ''));
        $website = trim((string)($_POST['website'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $city = trim((string)($_POST['city'] ?? ''));
        $state = trim((string)($_POST['state'] ?? ''));
        $notes = trim((string)($_POST['notes'] ?? ''));
        if ($name === '') {
            flash_set('error', 'Company name is required.');
        } elseif ($id > 0) {
            $stmt = $pdo->prepare(
                'UPDATE companies SET name=?, website=?, phone=?, city=?, state=?, notes=? WHERE id=?'
            );
            $stmt->execute([$name, $website ?: null, $phone ?: null, $city ?: null, $state ?: null, $notes ?: null, $id]);
            flash_set('success', 'Company updated.');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO companies (name, website, phone, city, state, notes) VALUES (?,?,?,?,?,?)'
            );
            $stmt->execute([$name, $website ?: null, $phone ?: null, $city ?: null, $state ?: null, $notes ?: null]);
            flash_set('success', 'Company added.');
        }
        header('Location: companies.php');
        exit;
    }
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare('DELETE FROM companies WHERE id=?')->execute([$id]);
            flash_set('success', 'Company removed.');
        }
        header('Location: companies.php');
        exit;
    }
}

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editRow = null;
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM companies WHERE id=?');
    $stmt->execute([$editId]);
    $editRow = $stmt->fetch();
}

$rows = $pdo->query('SELECT * FROM companies ORDER BY name ASC')->fetchAll();

$flash = flash_get();
render_head('Companies', ['active' => 'companies', 'user' => $user]);
if ($flash) {
    echo '<div class="flash ' . h($flash['type']) . '">' . h($flash['message']) . '</div>';
}
?>
  <div class="page-head fade-up">
    <h1>Companies</h1>
  </div>
  <p class="lead fade-up">Accounts you serve — linked to contacts and deals.</p>

  <div class="split cols-2">
    <div class="panel fade-up">
      <h2>Directory</h2>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>City</th>
              <th>Phone</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><strong><?= h((string)$r['name']) ?></strong></td>
                <td><?php
                    $city = trim((string)($r['city'] ?? ''));
                    $state = trim((string)($r['state'] ?? ''));
                    echo h($city !== '' && $state !== '' ? $city . ', ' . $state : ($city !== '' ? $city : $state));
                    ?></td>
                <td><?= h((string)($r['phone'] ?? '')) ?></td>
                <td style="white-space:nowrap">
                  <a class="link-muted" href="companies.php?edit=<?= (int)$r['id'] ?>">Edit</a>
                  <form method="post" style="display:inline;margin-left:0.5rem" onsubmit="return confirm('Delete this company?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if ($rows === []): ?>
              <tr><td colspan="4">No companies yet. Add one on the right.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="panel fade-up">
      <h2><?= $editRow ? 'Edit company' : 'Add company' ?></h2>
      <form method="post" action="companies.php">
        <input type="hidden" name="action" value="save">
        <?php if ($editRow): ?>
          <input type="hidden" name="id" value="<?= (int)$editRow['id'] ?>">
        <?php endif; ?>
        <div class="form-field">
          <label for="name">Name *</label>
          <input id="name" name="name" required value="<?= h((string)($editRow['name'] ?? '')) ?>">
        </div>
        <div class="form-field" style="margin-top:0.75rem">
          <label for="website">Website</label>
          <input id="website" name="website" type="url" placeholder="https://" value="<?= h((string)($editRow['website'] ?? '')) ?>">
        </div>
        <div class="form-grid" style="margin-top:0.75rem">
          <div class="form-field">
            <label for="phone">Phone</label>
            <input id="phone" name="phone" value="<?= h((string)($editRow['phone'] ?? '')) ?>">
          </div>
          <div class="form-field">
            <label for="city">City</label>
            <input id="city" name="city" value="<?= h((string)($editRow['city'] ?? '')) ?>">
          </div>
          <div class="form-field">
            <label for="state">State</label>
            <input id="state" name="state" value="<?= h((string)($editRow['state'] ?? '')) ?>">
          </div>
        </div>
        <div class="form-field" style="margin-top:0.75rem">
          <label for="notes">Notes</label>
          <textarea id="notes" name="notes"><?= h((string)($editRow['notes'] ?? '')) ?></textarea>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary"><?= $editRow ? 'Save changes' : 'Create company' ?></button>
          <?php if ($editRow): ?>
            <a class="btn btn-ghost" href="companies.php">Cancel</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>
<?php
render_footer();
