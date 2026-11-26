<?php
require 'includes/db.php';
require 'includes/functions.php';
require_login();

// Delete (POST only, CSRF-protected)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        $pdo->prepare('DELETE FROM book WHERE book_id = ?')->execute([(int)($_POST['book_id'] ?? 0)]);
        flash('ok', 'Book deleted.');
    } catch (PDOException $e) {
        flash('err', is_fk_violation($e)
            ? 'Cannot delete: this book has issue records.'
            : 'Could not delete the book.');
    }
    redirect('books.php');
}

$q = trim($_GET['q'] ?? '');
$sql = 'SELECT b.*, c.category_name
        FROM book b JOIN category c ON c.category_id = b.category_id';
$params = [];
if ($q !== '') {
    $sql .= ' WHERE b.title LIKE :q1 OR b.author LIKE :q2 OR c.category_name LIKE :q3';
    $params = [':q1' => "%$q%", ':q2' => "%$q%", ':q3' => "%$q%"];
}
$sql .= ' ORDER BY b.title';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

$title = 'Books';
require 'includes/header.php';
?>
<form class="toolbar" method="get">
  <input type="search" name="q" placeholder="Search title, author or category" value="<?= e($q) ?>">
  <button type="submit">Search</button>
  <?php if ($q !== ''): ?><a href="books.php">Clear</a><?php endif; ?>
  <span class="spacer"></span>
  <a class="btn" href="book_form.php">+ Add book</a>
</form>

<div class="table-wrap">
<table>
  <tr><th>Title</th><th>Author</th><th>Category</th><th>ISBN</th><th>Available</th><th></th></tr>
  <?php foreach ($books as $b): ?>
  <tr>
    <td><?= e($b['title']) ?></td>
    <td><?= e($b['author']) ?></td>
    <td><?= e($b['category_name']) ?></td>
    <td class="muted"><?= e($b['isbn']) ?></td>
    <td><span class="tag <?= $b['available_copies'] > 0 ? 'ok' : 'bad' ?>"><?= (int)$b['available_copies'] ?> / <?= (int)$b['total_copies'] ?></span></td>
    <td>
      <a class="btn small" href="book_form.php?id=<?= (int)$b['book_id'] ?>">Edit</a>
      <form class="inline" method="post" onsubmit="return confirm('Delete this book?')">
        <?= csrf_field() ?><input type="hidden" name="book_id" value="<?= (int)$b['book_id'] ?>">
        <button class="small danger">Delete</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$books): ?><tr><td colspan="6" class="empty">No books found.</td></tr><?php endif; ?>
</table>
</div>
<?php require 'includes/footer.php'; ?>
