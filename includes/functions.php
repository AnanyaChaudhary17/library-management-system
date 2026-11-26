<?php
session_start();

// ---- Business rules (one place to change them) ----
const LOAN_DAYS     = 14;
const FINE_PER_DAY  = 2;                 // Rs per day after the due date
const LIMIT_STUDENT = 3;
const LIMIT_FACULTY = 5;

/** Thrown when a business rule blocks an action; message is safe to show. */
class RuleException extends Exception {}

function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function money($v): string { return '₹' . number_format((float)$v, 2); }

function redirect(string $url) { header('Location: ' . $url); exit; }

function require_login(): void {
    if (empty($_SESSION['staff_id'])) redirect('login.php');
}

// ---- flash messages (shown once after a redirect) ----
function flash(string $type, string $msg): void { $_SESSION['flash'] = [$type, $msg]; }
function show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        [$type, $msg] = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="msg ' . e($type) . '">' . e($msg) . '</div>';
    }
}

// ---- CSRF protection for every POST form ----
function csrf_field(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}
function csrf_check(): void {
    $ok = isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
    if (!$ok) { http_response_code(400); exit('Invalid form token. Go back and try again.'); }
}

function borrow_limit(string $member_type): int {
    return $member_type === 'Faculty' ? LIMIT_FACULTY : LIMIT_STUDENT;
}

/** True when a PDOException is a duplicate-key (UNIQUE) violation. */
function is_duplicate(PDOException $e): bool { return ($e->errorInfo[1] ?? 0) == 1062; }
/** True when a PDOException is a foreign-key violation (row still referenced). */
function is_fk_violation(PDOException $e): bool { return in_array($e->errorInfo[1] ?? 0, [1451, 1452]); }
