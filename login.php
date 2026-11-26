<?php
require 'includes/db.php';
require 'includes/functions.php';

if (!empty($_SESSION['staff_id'])) redirect('index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT staff_id, name, password_hash FROM staff WHERE username = ?');
    $stmt->execute([$username]);
    $staff = $stmt->fetch();

    if ($staff && password_verify($password, $staff['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['staff_id']   = (int)$staff['staff_id'];
        $_SESSION['staff_name'] = $staff['name'];
        redirect('index.php');
    }
    $error = 'Invalid username or password.';   // same message for both cases
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login · Library</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="login">
  <h1>Library Login</h1>
  <?php if ($error): ?><div class="msg err"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="box">
    <?= csrf_field() ?>
    <label for="username">Username</label>
    <input id="username" name="username" required autofocus maxlength="50" value="<?= e($_POST['username'] ?? '') ?>">
    <label for="password">Password</label>
    <input id="password" name="password" type="password" required>
    <button type="submit">Log in</button>
  </form>
</div>
</body>
</html>
