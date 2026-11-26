<?php
require 'includes/db.php';
require 'includes/functions.php';
$title = 'Currently issued books';

$rows = $pdo->query('SELECT * FROM v_issued_books ORDER BY due_date, title')->fetchAll();

require 'includes/header.php';
?>
<div class="table-wrap">
<table>
  <tr><th>Book</th><th>Member</th><th>Type</th><th>Issued</th><th>Due</th><th>Status</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?= e($r['title']) ?></td>
    <td><?= e($r['member_name']) ?></td>
    <td class="muted"><?= e($r['member_type']) ?></td>
    <td class="muted"><?= e($r['issue_date']) ?></td>
    <td><?= e($r['due_date']) ?></td>
    <td><?= $r['days_overdue'] > 0
          ? '<span class="tag bad">' . (int)$r['days_overdue'] . ' day(s) overdue</span>'
          : '<span class="tag ok">On time</span>' ?></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="6" class="empty">No books are currently issued.</td></tr><?php endif; ?>
</table>
</div>
<?php require 'includes/footer.php'; ?>
