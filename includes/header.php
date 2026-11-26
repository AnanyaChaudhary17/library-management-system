<?php
// Every page: require db.php + functions.php, set $title, then include this file.
require_login();
$page = basename($_SERVER['SCRIPT_NAME']);
$nav = [
    'index.php'   => 'Dashboard',
    'books.php'   => 'Books',
    'members.php' => 'Members',
    'issue.php'   => 'Issue',
    'return.php'  => 'Return',
    'issued.php'  => 'Issued',
    'overdue.php' => 'Overdue',
    'fines.php'   => 'Fines',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Library') ?> · Library</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="top">
  <span class="brand">Library</span>
  <nav>
    <?php foreach ($nav as $file => $label): ?>
      <a href="<?= $file ?>"<?= $page === $file ? ' class="on"' : '' ?>><?= $label ?></a>
    <?php endforeach; ?>
  </nav>
  <span class="who"><?= e($_SESSION['staff_name']) ?> · <a href="logout.php">Logout</a></span>
</header>
<main>
<h1><?= e($title ?? '') ?></h1>
<?php show_flash(); ?>
