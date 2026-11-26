<?php
require 'includes/db.php';
require 'includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        $pdo->prepare('DELETE FROM member WHERE member_id = ?')->execute([(int)($_POST['member_id'] ?? 0)]);
        flash('ok', 'Member deleted.');
    } catch (PDOException $e) {
        flash('err', is_fk_violation($e)
            ? 'Cannot delete: this member has issue records. Suspend the member instead.'
            : 'Could not delete the member.');
    }
    redirect('members.php');
}

// Open loans per member (for the "holding" column)
$members = $pdo->query(
    'SELECT m.*, COUNT(i.issue_id) AS holding
     FROM member m
     LEFT JOIN issue i ON i.member_id = m.member_id AND i.return_date IS NULL
     GROUP BY m.member_id
     ORDER BY m.name'
)->fetchAll();

$title = 'Members';
require 'includes/header.php';
?>
<div class="toolbar"><span class="spacer"></span><a class="btn" href="member_form.php">+ Add member</a></div>
<div class="table-wrap">
<table>
  <tr><th>Name</th><th>Email</th><th>Phone</th><th>Type</th><th>Joined</th><th>Status</th><th>Holding</th><th></th></tr>
  <?php foreach ($members as $m): ?>
  <tr>
    <td><?= e($m['name']) ?></td>
    <td><?= e($m['email']) ?></td>
    <td class="muted"><?= e($m['phone']) ?></td>
    <td><?= e($m['member_type']) ?></td>
    <td class="muted"><?= e($m['join_date']) ?></td>
    <td><span class="tag <?= $m['status'] === 'Active' ? 'ok' : 'bad' ?>"><?= e($m['status']) ?></span></td>
    <td><?= (int)$m['holding'] ?> / <?= borrow_limit($m['member_type']) ?></td>
    <td>
      <a class="btn small" href="member_form.php?id=<?= (int)$m['member_id'] ?>">Edit</a>
      <form class="inline" method="post" onsubmit="return confirm('Delete this member?')">
        <?= csrf_field() ?><input type="hidden" name="member_id" value="<?= (int)$m['member_id'] ?>">
        <button class="small danger">Delete</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$members): ?><tr><td colspan="8" class="empty">No members yet.</td></tr><?php endif; ?>
</table>
</div>
<?php require 'includes/footer.php'; ?>
