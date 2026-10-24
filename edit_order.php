<?php
// Admin: edit an order. Old quantity goes back to stock first, then the new quantity is taken.
// If the new quantity is too big the transaction is rolled back, so stock never goes wrong.
require 'config.php'; require_admin(); $title = 'Edit Order';
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$st = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?"); $st->execute([$id]); $old = $st->fetch();
if (!$old) { flash('error', 'Order not found.'); header('Location: orders.php'); exit; }
$d = ['student_id' => $old['student_id'], 'student_name' => $old['student_name'], 'uniform_id' => (int)$old['uniform_id'],
      'quantity' => $old['quantity'], 'order_status' => $old['order_status']];
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    [$d, $errors] = clean_order_input();
    $d['order_status'] = $_POST['order_status'] ?? '';
    if (!in_array($d['order_status'], ['Pending', 'Processing', 'Completed'], true)) $errors[] = 'Invalid order status.';
    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("SELECT order_id FROM orders WHERE order_id = ? FOR UPDATE")->execute([$id]);
            $pdo->prepare("UPDATE uniforms SET stock_quantity = stock_quantity + ? WHERE uniform_id = ?")->execute([$old['quantity'], $old['uniform_id']]);  // give old stock back
            $st = $pdo->prepare("SELECT price, stock_quantity FROM uniforms WHERE uniform_id = ? FOR UPDATE"); $st->execute([$d['uniform_id']]); $u = $st->fetch();
            if (!$u) $errors[] = 'That uniform does not exist.';
            elseif ($d['quantity'] > $u['stock_quantity']) $errors[] = "Not enough stock. Only {$u['stock_quantity']} available for this order.";
            else {
                $pdo->prepare("UPDATE uniforms SET stock_quantity = stock_quantity - ? WHERE uniform_id = ?")->execute([$d['quantity'], $d['uniform_id']]);
                $pdo->prepare("UPDATE orders SET student_id=?, student_name=?, uniform_id=?, quantity=?, total_price=?, order_status=? WHERE order_id=?")
                    ->execute([$d['student_id'], $d['student_name'], $d['uniform_id'], $d['quantity'], $u['price'] * $d['quantity'], $d['order_status'], $id]);
                $pdo->commit(); flash('ok', "Order #$id updated."); header('Location: orders.php'); exit;
            }
            $pdo->rollBack();
        } catch (PDOException $ex) { if ($pdo->inTransaction()) $pdo->rollBack(); $errors[] = 'Database error. Please try again.'; }
    }
}
$uniforms = $pdo->query("SELECT * FROM uniforms ORDER BY uniform_name, uniform_id")->fetchAll();
$isEdit = true; $oldId = $old['uniform_id']; $oldQty = $old['quantity']; $title = "Edit Order #$id";
include 'includes/header.php';
foreach ($errors as $m) echo '<div class="msg error">' . e($m) . '</div>';
include 'includes/order_form.php'; include 'includes/footer.php';
