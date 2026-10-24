<?php // Page layout top: navigation + message. Set $title before including this file.
$cur = basename($_SERVER['PHP_SELF']);
$links = ['index.php' => 'Uniform Products', 'order.php' => 'Place an Order'];
if (is_admin()) { $links['dashboard.php'] = 'Dashboard'; $links['orders.php'] = 'Orders'; }
?><!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> - Uniform Ordering</title><link rel="stylesheet" href="assets/style.css"></head><body>
<header><h1>🎽 School Uniform Ordering</h1><nav>
<?php foreach ($links as $f => $n): ?><a href="<?= $f ?>" class="<?= $cur === $f ? 'on' : '' ?>"><?= $n ?></a><?php endforeach; ?>
<?= is_admin() ? '<a href="logout.php">Logout</a>' : '<a href="login.php" class="' . ($cur === 'login.php' ? 'on' : '') . '">Admin Login</a>' ?>
</nav></header><main><h2><?= e($title) ?></h2>
<?php if (!empty($_SESSION['flash'])): [$t, $m] = $_SESSION['flash']; unset($_SESSION['flash']); ?><div class="msg <?= $t ?>"><?= e($m) ?></div><?php endif; ?>
