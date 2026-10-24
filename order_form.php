<?php // Form used by both order.php (new order) and edit_order.php (edit). Needs: $d, $uniforms, $isEdit, $oldId, $oldQty ?>
<form method="post" id="orderForm" class="panel" data-old-id="<?= (int)$oldId ?>" data-old-qty="<?= (int)$oldQty ?>" novalidate>
<?= csrf_field() ?><div id="jsErrors"></div>
<label>Student ID *<input name="student_id" value="<?= e($d['student_id']) ?>" maxlength="20" placeholder="e.g. 2026-001" required></label>
<label>Student Name *<input name="student_name" value="<?= e($d['student_name']) ?>" maxlength="100" required></label>
<label>Uniform &amp; Size *<select name="uniform_id" required><option value="">-- Select uniform --</option>
<?php foreach ($uniforms as $u): $off = $u['stock_quantity'] < 1 && $u['uniform_id'] != $oldId; ?>
<option value="<?= $u['uniform_id'] ?>" data-price="<?= $u['price'] ?>" data-stock="<?= $u['stock_quantity'] ?>"
 <?= $d['uniform_id'] == $u['uniform_id'] ? 'selected' : '' ?> <?= $off ? 'disabled' : '' ?>>
 <?= e($u['uniform_name'] . ' - ' . $u['size']) ?> (<?= money($u['price']) ?>, <?= $off ? 'out of stock' : 'stock: ' . $u['stock_quantity'] ?>)</option>
<?php endforeach; ?></select></label>
<label>Quantity *<input type="number" name="quantity" min="1" step="1" value="<?= e($d['quantity']) ?>" required></label>
<?php if ($isEdit): ?><label>Order Status<select name="order_status">
<?php foreach (['Pending', 'Processing', 'Completed'] as $s): ?><option <?= $d['order_status'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
</select></label><?php endif; ?>
<p class="total">Total Price: <b><?= CURRENCY ?><span id="total">0.00</span></b></p>
<button class="btn"><?= $isEdit ? 'Update Order' : 'Place Order' ?></button>
<?php if ($isEdit): ?><a class="btn gray" href="orders.php">Cancel</a><?php endif; ?>
</form>
