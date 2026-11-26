<?php
require 'includes/db.php';
require 'includes/functions.php';
$title = 'Overdue books';

$rows = $pdo->query('SELECT * FROM v_overdue_books ORDER BY days_overdue DESC, title')->fetchAll();

require 'includes/header.php';
?>
<div class="table-wrap">
<table>
  <tr><th>Book</th><th>Member</th><th>Contact</th><th>Due</th><th>Days overdue</th><th>Fine so far</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?= e($r['title']) ?></td>
    <td><?= e($r['member_name']) ?></td>
    <td class="muted"><?= e($r['email']) ?><?= $r['phone'] ? ' · ' . e($r['phone']) : '' ?></td>
    <td><?= e($r['due_date']) ?></td>
    <td class="late"><?= (int)$r['days_overdue'] ?></td>
    <td class="late"><?= money($r['days_overdue'] * FINE_PER_DAY) ?></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="6" class="empty">Nothing is overdue.</td></tr><?php endif; ?>
</table>
</div>
<?php require 'includes/footer.php'; ?>
