<?php
// Admin dashboard: totals from the database
require 'config.php'; require_admin(); $title = 'Dashboard';
$orders  = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$sales   = $pdo->query("SELECT COALESCE(SUM(total_price),0) FROM orders")->fetchColumn();
$pending = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Pending'")->fetchColumn();
$stock   = (int)$pdo->query("SELECT COALESCE(SUM(stock_quantity),0) FROM uniforms")->fetchColumn();
$low = $pdo->query("SELECT uniform_name, size, stock_quantity FROM uniforms WHERE stock_quantity <= 5 ORDER BY stock_quantity")->fetchAll();
include 'includes/header.php';
?>
<div class="cards">
<div class="card"><span>Total Orders</span><b><?= $orders ?></b></div><div class="card"><span>Total Sales</span><b><?= money($sales) ?></b></div>
<div class="card"><span>Pending Orders</span><b><?= $pending ?></b></div><div class="card"><span>Uniforms in Stock</span><b><?= $stock ?></b></div></div>
<div class="panel"><h3>Low Stock (5 or fewer)</h3>
<?php if ($low): ?><ul><?php foreach ($low as $l): ?><li><?= e($l['uniform_name'] . ' - ' . $l['size']) ?>: <b><?= $l['stock_quantity'] ?></b> left</li><?php endforeach; ?></ul>
<?php else: ?><p>All items have enough stock.</p><?php endif; ?></div>
<?php include 'includes/footer.php'; ?>
