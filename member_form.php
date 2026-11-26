<?php
// Add a member (no id) or edit one (?id=...)
require 'includes/db.php';
require 'includes/functions.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$member = ['name' => '', 'email' => '', 'phone' => '', 'member_type' => 'Student', 'status' => 'Active'];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM member WHERE member_id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) { flash('err', 'Member not found.'); redirect('members.php'); }
    $member = $found;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $member = array_merge($member, [
        'name'        => trim($_POST['name'] ?? ''),
        'email'       => trim($_POST['email'] ?? ''),
        'phone'       => trim($_POST['phone'] ?? ''),
        'member_type' => $_POST['member_type'] ?? '',
        'status'      => $_POST['status'] ?? 'Active',
    ]);

    if ($member['name'] === '') {
        $error = 'Name is required.';
    } elseif (!filter_var($member['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif ($member['phone'] !== '' && !preg_match('/^[0-9]{10}$/', $member['phone'])) {
        $error = 'Phone must be 10 digits.';
    } elseif (!in_array($member['member_type'], ['Student', 'Faculty'], true)) {
        $error = 'Choose Student or Faculty.';
    } elseif (!in_array($member['status'], ['Active', 'Suspended'], true)) {
        $error = 'Choose a valid status.';
    } else {
        try {
            if ($id) {
                $pdo->prepare('UPDATE member SET name=?, email=?, phone=?, member_type=?, status=? WHERE member_id=?')
                    ->execute([$member['name'], $member['email'], $member['phone'] ?: null,
                               $member['member_type'], $member['status'], $id]);
            } else {
                $pdo->prepare('INSERT INTO member (name, email, phone, member_type, join_date, status)
                               VALUES (?,?,?,?,CURDATE(),?)')
                    ->execute([$member['name'], $member['email'], $member['phone'] ?: null,
                               $member['member_type'], $member['status']]);
            }
            flash('ok', $id ? 'Member updated.' : 'Member added.');
            redirect('members.php');
        } catch (PDOException $e) {
            $error = is_duplicate($e) ? 'A member with this email already exists.' : 'Could not save the member.';
        }
    }
}

$title = $id ? 'Edit member' : 'Add member';
require 'includes/header.php';
if ($error) echo '<div class="msg err">' . e($error) . '</div>';
?>
<form method="post" class="box">
  <?= csrf_field() ?>
  <label for="name">Name</label>
  <input id="name" name="name" required maxlength="100" value="<?= e($member['name']) ?>">
  <label for="email">Email</label>
  <input id="email" name="email" type="email" required maxlength="100" value="<?= e($member['email']) ?>">
  <label for="phone">Phone (10 digits)</label>
  <input id="phone" name="phone" pattern="[0-9]{10}" maxlength="10" value="<?= e($member['phone']) ?>">
  <label for="member_type">Type</label>
  <select id="member_type" name="member_type" required>
    <?php foreach (['Student', 'Faculty'] as $t): ?>
      <option<?= $member['member_type'] === $t ? ' selected' : '' ?>><?= $t ?></option>
    <?php endforeach; ?>
  </select>
  <label for="status">Status</label>
  <select id="status" name="status">
    <?php foreach (['Active', 'Suspended'] as $s): ?>
      <option<?= $member['status'] === $s ? ' selected' : '' ?>><?= $s ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit">Save</button> <a href="members.php">Cancel</a>
</form>
<?php require 'includes/footer.php'; ?>
