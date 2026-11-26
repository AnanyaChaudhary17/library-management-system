<?php
require 'includes/db.php';
require 'includes/functions.php';
require_login();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $member_id = (int)($_POST['member_id'] ?? 0);
    $book_id   = (int)($_POST['book_id'] ?? 0);

    try {
        $pdo->beginTransaction();

        // Lock the member and book rows so two staff can't break a rule at the same moment
        $stmt = $pdo->prepare('SELECT * FROM member WHERE member_id = ? FOR UPDATE');
        $stmt->execute([$member_id]);
        $member = $stmt->fetch();
        if (!$member) throw new RuleException('Please choose a member.');

        // Rule: a suspended member cannot borrow
        if ($member['status'] !== 'Active') {
            throw new RuleException($member['name'] . ' is suspended and cannot borrow books.');
        }

        // Rule: student max 3, faculty max 5 books at a time
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM issue WHERE member_id = ? AND return_date IS NULL');
        $stmt->execute([$member_id]);
        $holding = (int)$stmt->fetchColumn();
        $limit = borrow_limit($member['member_type']);
        if ($holding >= $limit) {
            throw new RuleException($member['name'] . " already holds $holding books. "
                . $member['member_type'] . " limit is $limit.");
        }

        $stmt = $pdo->prepare('SELECT * FROM book WHERE book_id = ? FOR UPDATE');
        $stmt->execute([$book_id]);
        $book = $stmt->fetch();
        if (!$book) throw new RuleException('Please choose a book.');

        // Rule: no copies available => cannot issue
        if ($book['available_copies'] < 1) {
            throw new RuleException('"' . $book['title'] . '" has no copies available.');
        }

        // Due date is calculated in SQL: today + 14 days
        $pdo->prepare('INSERT INTO issue (book_id, member_id, staff_id, issue_date, due_date)
                       VALUES (?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL ? DAY))')
            ->execute([$book_id, $member_id, $_SESSION['staff_id'], LOAN_DAYS]);
        $issue_id = $pdo->lastInsertId();

        $pdo->prepare('UPDATE book SET available_copies = available_copies - 1 WHERE book_id = ?')
            ->execute([$book_id]);

        $stmt = $pdo->prepare('SELECT due_date FROM issue WHERE issue_id = ?');
        $stmt->execute([$issue_id]);
        $due = $stmt->fetchColumn();

        $pdo->commit();
        flash('ok', '"' . $book['title'] . '" issued to ' . $member['name'] . '. Due on ' . $due . '.');
        redirect('issue.php');
    } catch (RuleException $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = 'Could not issue the book. Please try again.';
    }
}

$members = $pdo->query('SELECT member_id, name, member_type, status FROM member ORDER BY name')->fetchAll();
$books   = $pdo->query('SELECT book_id, title, author, available_copies FROM book ORDER BY title')->fetchAll();

$title = 'Issue book';
require 'includes/header.php';
if ($error) echo '<div class="msg err">' . e($error) . '</div>';
?>
<form method="post" class="box">
  <?= csrf_field() ?>
  <label for="member_id">Member</label>
  <select id="member_id" name="member_id" required>
    <option value="">Select member…</option>
    <?php foreach ($members as $m): ?>
      <option value="<?= (int)$m['member_id'] ?>"<?= (int)($_POST['member_id'] ?? 0) === (int)$m['member_id'] ? ' selected' : '' ?>>
        <?= e($m['name']) ?> (<?= e($m['member_type']) ?><?= $m['status'] !== 'Active' ? ', ' . e($m['status']) : '' ?>)
      </option>
    <?php endforeach; ?>
  </select>
  <label for="book_id">Book</label>
  <select id="book_id" name="book_id" required>
    <option value="">Select book…</option>
    <?php foreach ($books as $b): ?>
      <option value="<?= (int)$b['book_id'] ?>"<?= (int)($_POST['book_id'] ?? 0) === (int)$b['book_id'] ? ' selected' : '' ?>>
        <?= e($b['title']) ?> — <?= e($b['author']) ?> (<?= (int)$b['available_copies'] ?> available)
      </option>
    <?php endforeach; ?>
  </select>
  <p class="muted">Due date is set automatically: <?= LOAN_DAYS ?> days from today.</p>
  <button type="submit">Issue book</button>
</form>
<?php require 'includes/footer.php'; ?>
