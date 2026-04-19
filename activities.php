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
$uid = (int)$user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'save') {
        $contactId = (int)($_POST['contact_id'] ?? 0);
        $dealId = (int)($_POST['deal_id'] ?? 0);
        $type = (string)($_POST['type'] ?? 'note');
        $subject = trim((string)($_POST['subject'] ?? ''));
        $body = trim((string)($_POST['body'] ?? ''));
        $due = trim((string)($_POST['due_at'] ?? ''));
        $allowed = ['call', 'email', 'meeting', 'note', 'task'];
        if (!in_array($type, $allowed, true)) {
            $type = 'note';
        }
        $cid = $contactId > 0 ? $contactId : null;
        $did = $dealId > 0 ? $dealId : null;
        $dueAt = $due !== '' ? $due : null;
        $stmt = $pdo->prepare(
            'INSERT INTO activities (contact_id, deal_id, user_id, type, subject, body, due_at) VALUES (?,?,?,?,?,?,?)'
        );
        $stmt->execute([$cid, $did, $uid, $type, $subject ?: null, $body ?: null, $dueAt]);
        flash_set('success', 'Activity logged.');
        header('Location: activities.php');
        exit;
    }
    if ($action === 'complete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare('UPDATE activities SET completed_at = NOW() WHERE id=?')->execute([$id]);
            flash_set('success', 'Marked complete.');
        }
        header('Location: activities.php');
        exit;
    }
}

$contacts = $pdo->query('SELECT id, first_name, last_name FROM contacts ORDER BY last_name, first_name')->fetchAll();
$deals = $pdo->query('SELECT id, title FROM deals ORDER BY title')->fetchAll();

$rows = $pdo->query(
    'SELECT a.*, c.first_name, c.last_name, d.title AS deal_title, u.name AS user_name
     FROM activities a
     LEFT JOIN contacts c ON c.id = a.contact_id
     LEFT JOIN deals d ON d.id = a.deal_id
     JOIN users u ON u.id = a.user_id
     ORDER BY a.created_at DESC
     LIMIT 100'
)->fetchAll();

$flash = flash_get();
render_head('Activity', ['active' => 'activities', 'user' => $user]);
if ($flash) {
    echo '<div class="flash ' . h($flash['type']) . '">' . h($flash['message']) . '</div>';
}
?>
  <div class="page-head fade-up">
    <h1>Activity</h1>
  </div>
  <p class="lead fade-up">Log calls, emails, meetings, and notes with optional due dates.</p>

  <div class="split cols-2">
    <div class="panel fade-up">
      <h2>Timeline</h2>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>When</th>
              <th>Type</th>
              <th>Subject</th>
              <th>Contact</th>
              <th>Deal</th>
              <th>Owner</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td class="mono"><?= h(substr((string)$r['created_at'], 0, 16)) ?></td>
                <td><span class="badge"><?= h((string)$r['type']) ?></span></td>
                <td><?= h((string)($r['subject'] ?? '')) ?></td>
                <td><?= h(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''))) ?></td>
                <td><?= h((string)($r['deal_title'] ?? '')) ?></td>
                <td><?= h((string)$r['user_name']) ?></td>
                <td>
                  <?php if (empty($r['completed_at']) && (string)$r['type'] === 'task'): ?>
                    <form method="post" style="display:inline">
                      <input type="hidden" name="action" value="complete">
                      <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                      <button type="submit" class="btn btn-ghost btn-sm">Done</button>
                    </form>
                  <?php elseif (!empty($r['completed_at'])): ?>
                    <span class="badge badge-won" style="font-size:0.72rem">Complete</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if ($rows === []): ?>
              <tr><td colspan="7">No activity logged yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="panel fade-up">
      <h2>Log activity</h2>
      <form method="post" action="activities.php">
        <input type="hidden" name="action" value="save">
        <div class="form-field">
          <label for="type">Type</label>
          <select id="type" name="type">
            <?php foreach (['call', 'email', 'meeting', 'note', 'task'] as $opt): ?>
              <option value="<?= h($opt) ?>"><?= h(ucfirst($opt)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-field" style="margin-top:0.75rem">
          <label for="contact_id">Contact</label>
          <select id="contact_id" name="contact_id">
            <option value="0">— Optional —</option>
            <?php foreach ($contacts as $ct): ?>
              <option value="<?= (int)$ct['id'] ?>"><?= h((string)$ct['first_name'] . ' ' . (string)$ct['last_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-field" style="margin-top:0.75rem">
          <label for="deal_id">Deal</label>
          <select id="deal_id" name="deal_id">
            <option value="0">— Optional —</option>
            <?php foreach ($deals as $d): ?>
              <option value="<?= (int)$d['id'] ?>"><?= h((string)$d['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-field" style="margin-top:0.75rem">
          <label for="subject">Subject</label>
          <input id="subject" name="subject" placeholder="Short label">
        </div>
        <div class="form-field" style="margin-top:0.75rem">
          <label for="body">Details</label>
          <textarea id="body" name="body" placeholder="What happened?"></textarea>
        </div>
        <div class="form-field" style="margin-top:0.75rem">
          <label for="due_at">Due (optional, for tasks)</label>
          <input id="due_at" name="due_at" type="datetime-local">
        </div>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Log activity</button>
        </div>
      </form>
    </div>
  </div>
<?php
render_footer();
