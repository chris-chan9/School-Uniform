<?php
// Shared settings and helper functions. Every page loads this file first.
session_start();

define('CURRENCY', '$');
define('ADMIN_USERNAME', 'admin');      // simple admin login for a school project:
define('ADMIN_PASSWORD', 'admin123');   // change these before real use

// Database connection (XAMPP defaults: user "root", empty password)
$pdo = new PDO('mysql:host=localhost;dbname=uniform_db;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }   // safe output (prevents XSS)
function money($n) { return CURRENCY . number_format((float)$n, 2); }
function is_admin() { return !empty($_SESSION['admin']); }
function require_admin() { if (!is_admin()) { flash('error', 'Please log in as admin.'); header('Location: login.php'); exit; } }
function flash($type, $msg) { $_SESSION['flash'] = [$type, $msg]; }              // message shown once on the next page
function csrf_field() {                                                          // hidden token in every POST form
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}
function csrf_check() { if (!hash_equals($_SESSION['csrf'] ?? 'x', $_POST['csrf'] ?? '')) die('Invalid request.'); }

// Reads and validates the order form fields. Returns [cleanData, listOfErrors].
function clean_order_input() {
    $d = [
        'student_id'   => trim($_POST['student_id'] ?? ''),
        'student_name' => trim($_POST['student_name'] ?? ''),
        'uniform_id'   => (int)($_POST['uniform_id'] ?? 0),
        'quantity'     => trim($_POST['quantity'] ?? ''),
    ];
    $err = [];
    if (!preg_match('/^[A-Za-z0-9-]{3,20}$/', $d['student_id'])) $err[] = 'Student ID must be 3-20 letters, numbers or dashes.';
    if (!preg_match("/^[A-Za-z][A-Za-z .'-]{1,99}$/", $d['student_name'])) $err[] = 'Please enter a valid student name (letters only).';
    if ($d['uniform_id'] < 1) $err[] = 'Please choose a uniform.';
    $q = filter_var($d['quantity'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 500]]);
    if ($q === false) $err[] = 'Quantity must be a whole number from 1 to 500.'; else $d['quantity'] = $q;
    return [$d, $err];
}
