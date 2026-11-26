<?php
require 'includes/db.php';
require 'includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $issue_id = (int)($_POST['issue_id'] ?? 0);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'SELECT i.issue_id, i.book_id, b.title, m.name AS member_name,
                    DATEDIFF(CURDATE(), i.due_date) AS diff
             FROM issue i
             JOIN book b   ON b.book_id   = i.book_id
             JOIN member m ON m.member_id = i.member_id
             WHERE i.issue_id = ? AND i.return_date IS NULL
             FOR UPDATE'
        );
        $stmt->execute([$issue_id]);
        $row = $stmt->fetch();
        if (!$row) throw new RuleException('This book is not currently issued (already returned?).');

        $days_late = max(0, (int)$row['diff']);
        $fine = $days_late * FINE_PER_DAY;

        $pdo->prepare('UPDATE issue SET return_date = CURDATE() WHERE issue_id = ?')->execute([$issue_id]);
        $pdo->prepare('UPDATE book SET available_copies = available_copies + 1 WHERE book_id = ?')
            ->execute([$row['book_id']]);

        // Fine row only when the book is late
        if ($days_late > 0) {
            $pdo->prepare('INSERT INTO fine (issue_id, amount) VALUES (?, ?)')->execute([$issue_id, $fine]);
        }
        $pdo->commit();

        $msg = '"' . $row['title'] . '" returned by ' . $row['member_name'] . '. ';
        $msg .= $days_late > 0
            ? "$days_late day(s) late. Fine: " . money($fine) . '.'
            : 'Returned on time. No fine.';
        flash($days_late > 0 ? 'err' : 'ok', $msg);
    } catch (RuleException $e) {
        $pdo->rollBack();
        flash('err', $e->getMessage());
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash('err', 'Could not return the book. Please try again.');
    }
    redirect('return.php');
}

$rows = $pdo->query('SELECT * FROM v_issued_books ORDER BY due_date')->fetchAll();

$title = 'Return book';
require 'includes/header.php';
?>
<div class="table-wrap">
<table>
  <tr><th>Book</th><th>Member</th><th>Issued</th><th>Due</th><th>Days late</th><th>Fine</th><th></th></tr>
  <?php foreach ($rows as $r): $late = (int)$r['days_overdue']; ?>
  <tr>
    <td><?= e($r['title']) ?></td>
    <td><?= e($r['member_name']) ?></td>
    <td class="muted"><?= e($r['issue_date']) ?></td>
    <td><?= e($r['due_date']) ?></td>
    <td class="<?= $late ? 'late' : 'muted' ?>"><?= $late ?></td>
    <td class="<?= $late ? 'late' : 'muted' ?>"><?= money($late * FINE_PER_DAY) ?></td>
    <td>
      <form class="inline" method="post">
        <?= csrf_field() ?><input type="hidden" name="issue_id" value="<?= (int)$r['issue_id'] ?>">
        <button class="small">Return</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="7" class="empty">No books are currently issued.</td></tr><?php endif; ?>
</table>
</div>
<?php require 'includes/footer.php'; ?>
