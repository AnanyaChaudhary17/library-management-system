<?php
// Add a book (no id) or edit one (?id=...)
require 'includes/db.php';
require 'includes/functions.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$book = ['isbn' => '', 'title' => '', 'author' => '', 'publisher' => '', 'pub_year' => '',
         'category_id' => '', 'total_copies' => 1, 'available_copies' => 1];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM book WHERE book_id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) { flash('err', 'Book not found.'); redirect('books.php'); }
    $book = $found;
}
$current = $book;   // row as stored in the database (empty defaults when adding)
$categories = $pdo->query('SELECT category_id, category_name FROM category ORDER BY category_name')->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $book = array_merge($book, [
        'isbn'         => trim($_POST['isbn'] ?? ''),
        'title'        => trim($_POST['title'] ?? ''),
        'author'       => trim($_POST['author'] ?? ''),
        'publisher'    => trim($_POST['publisher'] ?? ''),
        'pub_year'     => trim($_POST['pub_year'] ?? ''),
        'category_id'  => (int)($_POST['category_id'] ?? 0),
        'total_copies' => trim($_POST['total_copies'] ?? ''),
    ]);
    $copies = filter_var($book['total_copies'], FILTER_VALIDATE_INT);
    $year   = $book['pub_year'] === '' ? null : filter_var($book['pub_year'], FILTER_VALIDATE_INT);

    // available = available + (change in total); the book's current loans are untouched
    $new_available = $id ? $current['available_copies'] + ($copies - $current['total_copies']) : $copies;

    if ($book['isbn'] === '' || $book['title'] === '' || $book['author'] === '') {
        $error = 'ISBN, title and author are required.';
    } elseif ($copies === false || $copies < 0) {
        $error = 'Total copies must be 0 or more.';
    } elseif ($year === false || ($year !== null && ($year < 1000 || $year > 2100))) {
        $error = 'Publication year must be between 1000 and 2100.';
    } elseif ($id && $new_available < 0) {
        $error = 'Total copies cannot be less than the number of copies currently issued.';
    } else {
        try {
            if ($id) {
                $sql = 'UPDATE book SET isbn=?, title=?, author=?, publisher=?, pub_year=?, category_id=?,
                        total_copies=?, available_copies=? WHERE book_id=?';
                $args = [$book['isbn'], $book['title'], $book['author'], $book['publisher'] ?: null, $year,
                         $book['category_id'], $copies, $new_available, $id];
            } else {
                $sql = 'INSERT INTO book (isbn, title, author, publisher, pub_year, category_id, total_copies, available_copies)
                        VALUES (?,?,?,?,?,?,?,?)';
                $args = [$book['isbn'], $book['title'], $book['author'], $book['publisher'] ?: null, $year,
                         $book['category_id'], $copies, $copies];
            }
            $pdo->prepare($sql)->execute($args);
            flash('ok', $id ? 'Book updated.' : 'Book added.');
            redirect('books.php');
        } catch (PDOException $e) {
            $error = is_duplicate($e) ? 'A book with this ISBN already exists.'
                   : (is_fk_violation($e) ? 'Choose a valid category.' : 'Could not save the book.');
        }
    }
}

$title = $id ? 'Edit book' : 'Add book';
require 'includes/header.php';
if ($error) echo '<div class="msg err">' . e($error) . '</div>';
?>
<form method="post" class="box">
  <?= csrf_field() ?>
  <label for="isbn">ISBN</label>
  <input id="isbn" name="isbn" required maxlength="20" value="<?= e($book['isbn']) ?>">
  <label for="title">Title</label>
  <input id="title" name="title" required maxlength="150" value="<?= e($book['title']) ?>">
  <label for="author">Author</label>
  <input id="author" name="author" required maxlength="100" value="<?= e($book['author']) ?>">
  <label for="publisher">Publisher</label>
  <input id="publisher" name="publisher" maxlength="100" value="<?= e($book['publisher']) ?>">
  <label for="pub_year">Publication year</label>
  <input id="pub_year" name="pub_year" type="number" min="1000" max="2100" value="<?= e($book['pub_year']) ?>">
  <label for="category_id">Category</label>
  <select id="category_id" name="category_id" required>
    <option value="">Select…</option>
    <?php foreach ($categories as $c): ?>
      <option value="<?= (int)$c['category_id'] ?>"<?= (int)$book['category_id'] === (int)$c['category_id'] ? ' selected' : '' ?>><?= e($c['category_name']) ?></option>
    <?php endforeach; ?>
  </select>
  <label for="total_copies">Total copies</label>
  <input id="total_copies" name="total_copies" type="number" min="0" required value="<?= e($book['total_copies']) ?>">
  <button type="submit">Save</button> <a href="books.php">Cancel</a>
</form>
<?php require 'includes/footer.php'; ?>
