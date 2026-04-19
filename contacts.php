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
        $companyId = (int)($_POST['company_id'] ?? 0);
        $first = trim((string)($_POST['first_name'] ?? ''));
        $last = trim((string)($_POST['last_name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $title = trim((string)($_POST['title'] ?? ''));
        $status = (string)($_POST['status'] ?? 'lead');
        $notes = trim((string)($_POST['notes'] ?? ''));
        $allowed = ['lead', 'prospect', 'customer', 'inactive'];
        if (!in_array($status, $allowed, true)) {
            $status = 'lead';
        }
        if ($first === '' || $last === '') {
            flash_set('error', 'First and last name are required.');
        } else {
            $cid = $companyId > 0 ? $companyId : null;
            if ($id > 0) {
                $stmt = $pdo->prepare(
                    'UPDATE contacts SET company_id=?, first_name=?, last_name=?, email=?, phone=?, title=?, status=?, notes=? WHERE id=?'
                );
                $stmt->execute([$cid, $first, $last, $email ?: null, $phone ?: null, $title ?: null, $status, $notes ?: null, $id]);
                flash_set('success', 'Contact updated.');
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO contacts (company_id, first_name, last_name, email, phone, title, status, notes) VALUES (?,?,?,?,?,?,?,?)'
                );
                $stmt->execute([$cid, $first, $last, $email ?: null, $phone ?: null, $title ?: null, $status, $notes ?: null]);
                flash_set('success', 'Contact added.');
            }
        }
        header('Location: contacts.php');
        exit;
    }
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare('DELETE FROM contacts WHERE id=?')->execute([$id]);
            flash_set('success', 'Contact removed.');
        }
        header('Location: contacts.php');
        exit;
    }
}

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editRow = null;
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM contacts WHERE id=?');
    $stmt->execute([$editId]);
    $editRow = $stmt->fetch();
}

$companies = $pdo->query('SELECT id, name FROM companies ORDER BY name')->fetchAll();

$rows = $pdo->query(
    'SELECT c.*, co.name AS company_name
     FROM contacts c
     LEFT JOIN companies co ON co.id = c.company_id
     ORDER BY c.last_name, c.first_name'
)->fetchAll();

$flash = flash_get();
render_head('Contacts', ['active' => 'contacts', 'user' => $user]);
if ($flash) {
    echo '<div class="flash ' . h($flash['type']) . '">' . h($flash['message']) . '</div>';
}
?>
  <div class="page-head fade-up">
    <h1>Contacts</h1>
  </div>
  <p class="lead fade-up">People in your pipeline with status and account linkage.</p>

  <div class="split cols-2">
    <div class="panel fade-up">
      <h2>People</h2>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Company</th>
              <th>Status</th>
              <th>Email</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><strong><?= h((string)$r['first_name'] . ' ' . (string)$r['last_name']) ?></strong></td>
                <td><?= h((string)($r['company_name'] ?? '')) ?></td>
                <td><span class="badge"><?= h((string)$r['status']) ?></span></td>
                <td><?= h((string)($r['email'] ?? '')) ?></td>
                <td style="white-space:nowrap">
                  <a class="link-muted" href="contacts.php?edit=<?= (int)$r['id'] ?>">Edit</a>
                  <form method="post" style="display:inline;margin-left:0.5rem" onsubmit="return confirm('Delete this contact?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if ($rows === []): ?>
              <tr><td colspan="5">No contacts yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="panel fade-up">
      <h2><?= $editRow ? 'Edit contact' : 'Add contact' ?></h2>
      <form method="post" action="contacts.php">
        <input type="hidden" name="action" value="save">
        <?php if ($editRow): ?>
          <input type="hidden" name="id" value="<?= (int)$editRow['id'] ?>">
        <?php endif; ?>
        <div class="form-field">
          <label for="company_id">Company</label>
          <select id="company_id" name="company_id">
            <option value="0">— None —</option>
            <?php foreach ($companies as $co): ?>
              <option value="<?= (int)$co['id'] ?>" <?= $editRow && (int)$editRow['company_id'] === (int)$co['id'] ? 'selected' : '' ?>>
                <?= h((string)$co['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grid" style="margin-top:0.75rem">
          <div class="form-field">
            <label for="first_name">First name *</label>
            <input id="first_name" name="first_name" required value="<?= h((string)($editRow['first_name'] ?? '')) ?>">
          </div>
          <div class="form-field">
            <label for="last_name">Last name *</label>
            <input id="last_name" name="last_name" required value="<?= h((string)($editRow['last_name'] ?? '')) ?>">
          </div>
        </div>
        <div class="form-grid" style="margin-top:0.75rem">
          <div class="form-field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?= h((string)($editRow['email'] ?? '')) ?>">
          </div>
          <div class="form-field">
            <label for="phone">Phone</label>
            <input id="phone" name="phone" value="<?= h((string)($editRow['phone'] ?? '')) ?>">
          </div>
        </div>
        <div class="form-grid" style="margin-top:0.75rem">
          <div class="form-field">
            <label for="title">Title</label>
            <input id="title" name="title" value="<?= h((string)($editRow['title'] ?? '')) ?>">
          </div>
          <div class="form-field">
            <label for="status">Status</label>
            <?php $st = (string)($editRow['status'] ?? 'lead'); ?>
            <select id="status" name="status">
              <?php foreach (['lead', 'prospect', 'customer', 'inactive'] as $opt): ?>
                <option value="<?= h($opt) ?>" <?= $st === $opt ? 'selected' : '' ?>><?= h(ucfirst($opt)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-field" style="margin-top:0.75rem">
          <label for="notes">Notes</label>
          <textarea id="notes" name="notes"><?= h((string)($editRow['notes'] ?? '')) ?></textarea>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary"><?= $editRow ? 'Save changes' : 'Create contact' ?></button>
          <?php if ($editRow): ?>
            <a class="btn btn-ghost" href="contacts.php">Cancel</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>
<?php
render_footer();
