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
        $contactId = (int)($_POST['contact_id'] ?? 0);
        $title = trim((string)($_POST['title'] ?? ''));
        $stage = (string)($_POST['stage'] ?? 'qualification');
        $value = (float)str_replace([',', '$'], '', (string)($_POST['value'] ?? '0'));
        $expected = trim((string)($_POST['expected_close'] ?? ''));
        $notes = trim((string)($_POST['notes'] ?? ''));
        $allowed = ['qualification', 'proposal', 'negotiation', 'won', 'lost'];
        if (!in_array($stage, $allowed, true)) {
            $stage = 'qualification';
        }
        if ($title === '') {
            flash_set('error', 'Deal title is required.');
        } else {
            $cid = $companyId > 0 ? $companyId : null;
            $ctid = $contactId > 0 ? $contactId : null;
            $exp = $expected !== '' ? $expected : null;
            if ($id > 0) {
                $stmt = $pdo->prepare(
                    'UPDATE deals SET company_id=?, contact_id=?, title=?, stage=?, value=?, expected_close=?, notes=? WHERE id=?'
                );
                $stmt->execute([$cid, $ctid, $title, $stage, $value, $exp, $notes ?: null, $id]);
                flash_set('success', 'Deal updated.');
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO deals (company_id, contact_id, title, stage, value, expected_close, notes) VALUES (?,?,?,?,?,?,?)'
                );
                $stmt->execute([$cid, $ctid, $title, $stage, $value, $exp, $notes ?: null]);
                flash_set('success', 'Deal created.');
            }
        }
        header('Location: deals.php');
        exit;
    }
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare('DELETE FROM deals WHERE id=?')->execute([$id]);
            flash_set('success', 'Deal removed.');
        }
        header('Location: deals.php');
        exit;
    }
}

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editRow = null;
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM deals WHERE id=?');
    $stmt->execute([$editId]);
    $editRow = $stmt->fetch();
}

$companies = $pdo->query('SELECT id, name FROM companies ORDER BY name')->fetchAll();
$contacts = $pdo->query('SELECT id, first_name, last_name FROM contacts ORDER BY last_name, first_name')->fetchAll();

$rows = $pdo->query(
    'SELECT d.*, co.name AS company_name, c.first_name, c.last_name
     FROM deals d
     LEFT JOIN companies co ON co.id = d.company_id
     LEFT JOIN contacts c ON c.id = d.contact_id
     ORDER BY d.updated_at DESC'
)->fetchAll();

function stage_badge_class(string $stage): string
{
    if ($stage === 'won') {
        return 'badge badge-won';
    }
    if ($stage === 'lost') {
        return 'badge badge-lost';
    }
    return 'badge';
}

$flash = flash_get();
render_head('Deals', ['active' => 'deals', 'user' => $user]);
if ($flash) {
    echo '<div class="flash ' . h($flash['type']) . '">' . h($flash['message']) . '</div>';
}
?>
  <div class="page-head fade-up">
    <h1>Deals</h1>
  </div>
  <p class="lead fade-up">Opportunities with stage, value, and expected close.</p>

  <div class="split cols-2">
    <div class="panel fade-up">
      <h2>Pipeline</h2>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Title</th>
              <th>Stage</th>
              <th>Value</th>
              <th>Account</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><strong><?= h((string)$r['title']) ?></strong></td>
                <td><span class="<?= h(stage_badge_class((string)$r['stage'])) ?>"><?= h((string)$r['stage']) ?></span></td>
                <td class="mono">$<?= number_format((float)$r['value'], 0) ?></td>
                <td><?= h((string)($r['company_name'] ?? '')) ?></td>
                <td style="white-space:nowrap">
                  <a class="link-muted" href="deals.php?edit=<?= (int)$r['id'] ?>">Edit</a>
                  <form method="post" style="display:inline;margin-left:0.5rem" onsubmit="return confirm('Delete this deal?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if ($rows === []): ?>
              <tr><td colspan="5">No deals yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="panel fade-up">
      <h2><?= $editRow ? 'Edit deal' : 'New deal' ?></h2>
      <form method="post" action="deals.php">
        <input type="hidden" name="action" value="save">
        <?php if ($editRow): ?>
          <input type="hidden" name="id" value="<?= (int)$editRow['id'] ?>">
        <?php endif; ?>
        <div class="form-field">
          <label for="title">Title *</label>
          <input id="title" name="title" required value="<?= h((string)($editRow['title'] ?? '')) ?>">
        </div>
        <div class="form-field" style="margin-top:0.75rem">
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
        <div class="form-field" style="margin-top:0.75rem">
          <label for="contact_id">Contact</label>
          <select id="contact_id" name="contact_id">
            <option value="0">— None —</option>
            <?php foreach ($contacts as $ct): ?>
              <option value="<?= (int)$ct['id'] ?>" <?= $editRow && (int)$editRow['contact_id'] === (int)$ct['id'] ? 'selected' : '' ?>>
                <?= h((string)$ct['first_name'] . ' ' . (string)$ct['last_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-grid" style="margin-top:0.75rem">
          <div class="form-field">
            <label for="stage">Stage</label>
            <?php $sg = (string)($editRow['stage'] ?? 'qualification'); ?>
            <select id="stage" name="stage">
              <?php foreach (['qualification', 'proposal', 'negotiation', 'won', 'lost'] as $opt): ?>
                <option value="<?= h($opt) ?>" <?= $sg === $opt ? 'selected' : '' ?>><?= h(ucfirst($opt)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field">
            <label for="value">Value (USD)</label>
            <input id="value" name="value" inputmode="decimal" value="<?= h((string)($editRow['value'] ?? '0')) ?>">
          </div>
          <div class="form-field">
            <label for="expected_close">Expected close</label>
            <input id="expected_close" name="expected_close" type="date" value="<?= h((string)($editRow['expected_close'] ?? '')) ?>">
          </div>
        </div>
        <div class="form-field" style="margin-top:0.75rem">
          <label for="notes">Notes</label>
          <textarea id="notes" name="notes"><?= h((string)($editRow['notes'] ?? '')) ?></textarea>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary"><?= $editRow ? 'Save changes' : 'Create deal' ?></button>
          <?php if ($editRow): ?>
            <a class="btn btn-ghost" href="deals.php">Cancel</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>
<?php
render_footer();
