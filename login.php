<?php
// Admin login
require 'config.php'; $title = 'Admin Login';
if (is_admin()) { header('Location: dashboard.php'); exit; }
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (hash_equals(ADMIN_USERNAME, trim($_POST['username'] ?? '')) && hash_equals(ADMIN_PASSWORD, $_POST['password'] ?? '')) {
        session_regenerate_id(true); $_SESSION['admin'] = true; header('Location: dashboard.php'); exit;
    }
    $err = 'Wrong username or password.';
}
include 'includes/header.php';
if ($err) echo '<div class="msg error">' . e($err) . '</div>';
?>
<form method="post" class="panel narrow"><?= csrf_field() ?>
<label>Username<input name="username" required autofocus></label><label>Password<input type="password" name="password" required></label>
<button class="btn">Login</button></form>
<?php include 'includes/footer.php'; ?>
