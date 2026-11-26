<?php
require 'includes/db.php';
require 'includes/functions.php';
$title = 'Dashboard';

$stats = $pdo->query(
    "SELECT
       (SELECT COUNT(*) FROM book)                                           AS titles,
       (SELECT COALESCE(SUM(total_copies), 0) FROM book)                     AS copies,
       (SELECT COUNT(*) FROM member)                                         AS members,
       (SELECT COUNT(*) FROM v_issued_books)                                 AS issued,
       (SELECT COUNT(*) FROM v_overdue_books)                                AS overdue,
       (SELECT COUNT(*) FROM fine WHERE paid_status = 'Unpaid')              AS unpaid_count,
       (SELECT COALESCE(SUM(amount), 0) FROM fine WHERE paid_status = 'Unpaid') AS unpaid_amount"
)->fetch();

require 'includes/header.php';
?>
<div class="cards">
  <div class="stat"><b><?= (int)$stats['titles'] ?></b><span>Book titles (<?= (int)$stats['copies'] ?> copies)</span></div>
  <div class="stat"><b><?= (int)$stats['members'] ?></b><span>Members</span></div>
  <div class="stat"><b><?= (int)$stats['issued'] ?></b><span>Books issued now</span></div>
  <div class="stat <?= $stats['overdue'] ? 'alert' : '' ?>"><b><?= (int)$stats['overdue'] ?></b><span>Overdue books</span></div>
  <div class="stat <?= $stats['unpaid_count'] ? 'alert' : '' ?>"><b><?= (int)$stats['unpaid_count'] ?></b>
    <span>Unpaid fines (<?= money($stats['unpaid_amount']) ?>)</span></div>
</div>
<?php require 'includes/footer.php'; ?>
