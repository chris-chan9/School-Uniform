<?php
// Uniform products page. Admin can also update stock here.
require 'config.php'; $title = 'Uniform Products';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin(); csrf_check();
    $stock = filter_var($_POST['stock'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100000]]);
    if ($stock === false) flash('error', 'Stock must be a whole number (0 or more).');
    else {
        $pdo->prepare("UPDATE uniforms SET stock_quantity = ? WHERE uniform_id = ?")->execute([$stock, (int)$_POST['id']]);
        flash('ok', 'Stock updated.');
    }
    header('Location: index.php'); exit;
}
$rows = $pdo->query("SELECT * FROM uniforms ORDER BY uniform_name, uniform_id")->fetchAll();
include 'includes/header.php';
?>
<div class="panel tw"><table><tr><th>Uniform</th><th>Size</th><th>Price</th><th>Stock</th><?= is_admin() ? '<th>Update Stock</th>' : '' ?></tr>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['uniform_name']) ?></td><td><?= e($r['size']) ?></td><td><?= money($r['price']) ?></td>
<td><?php if ($r['stock_quantity'] < 1): ?><span class="tag red">Out of stock</span>
<?php elseif ($r['stock_quantity'] <= 5): ?><?= $r['stock_quantity'] ?> <span class="tag orange">Low</span>
<?php else: ?><?= $r['stock_quantity'] ?><?php endif; ?></td>
<?php if (is_admin()): ?><td><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['uniform_id'] ?>">
<input type="number" name="stock" min="0" value="<?= $r['stock_quantity'] ?>" required><button class="btn sm">Save</button></form></td><?php endif; ?>
</tr><?php endforeach; ?></table></div>
<p><a class="btn" href="order.php">Place an Order</a></p>
<?php include 'includes/footer.php'; ?>
