<?php
require 'includes/db.php';
require 'includes/functions.php';
require_login();

// Mark a fine as paid
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $stmt = $pdo->prepare("UPDATE fine SET paid_status = 'Paid', paid_date = CURDATE()
                           WHERE fine_id = ? AND paid_status = 'Unpaid'");
    $stmt->execute([(int)($_POST['fine_id'] ?? 0)]);
    flash($stmt->rowCount() ? 'ok' : 'err', $stmt->rowCount() ? 'Fine marked as paid.' : 'Fine not found or already paid.');
    redirect('fines.php');
}

$rows = $pdo->query(
    "SELECT f.fine_id, f.amount, f.paid_status, f.paid_date, b.title, m.name AS member_name, i.due_date, i.return_date
     FROM fine f
     JOIN issue i  ON i.issue_id  = f.issue_id
     JOIN book b   ON b.book_id   = i.book_id
     JOIN member m ON m.member_id = i.member_id
     ORDER BY f.paid_status DESC, f.fine_id DESC"      // 'Unpaid' sorts after 'Paid' in the ENUM, so DESC puts it first
)->fetchAll();

$title = 'Fines';
require 'includes/header.php';
?>
<div class="table-wrap">
<table>
  <tr><th>Member</th><th>Book</th><th>Due</th><th>Returned</th><th>Amount</th><th>Status</th><th></th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?= e($r['member_name']) ?></td>
    <td><?= e($r['title']) ?></td>
    <td class="muted"><?= e($r['due_date']) ?></td>
    <td class="muted"><?= e($r['return_date']) ?></td>
    <td><?= money($r['amount']) ?></td>
    <td><?= $r['paid_status'] === 'Paid'
          ? '<span class="tag ok">Paid ' . e($r['paid_date']) . '</span>'
          : '<span class="tag bad">Unpaid</span>' ?></td>
    <td>
      <?php if ($r['paid_status'] === 'Unpaid'): ?>
      <form class="inline" method="post">
        <?= csrf_field() ?><input type="hidden" name="fine_id" value="<?= (int)$r['fine_id'] ?>">
        <button class="small">Mark paid</button>
      </form>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="7" class="empty">No fines recorded.</td></tr><?php endif; ?>
</table>
</div>
<?php require 'includes/footer.php'; ?>
