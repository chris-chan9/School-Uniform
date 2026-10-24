<?php
// Place a new order (anyone can use this page). Stock is checked and reduced inside one database transaction.
require 'config.php'; $title = 'Place an Order';
$d = ['student_id' => '', 'student_name' => '', 'uniform_id' => 0, 'quantity' => 1]; $errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    [$d, $errors] = clean_order_input();
    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $st = $pdo->prepare("SELECT price, stock_quantity FROM uniforms WHERE uniform_id = ? FOR UPDATE");  // lock the row
            $st->execute([$d['uniform_id']]); $u = $st->fetch();
            if (!$u) $errors[] = 'That uniform does not exist.';
            elseif ($d['quantity'] > $u['stock_quantity']) $errors[] = "Not enough stock. Only {$u['stock_quantity']} left.";
            else {
                $pdo->prepare("UPDATE uniforms SET stock_quantity = stock_quantity - ? WHERE uniform_id = ?")->execute([$d['quantity'], $d['uniform_id']]);
                $pdo->prepare("INSERT INTO orders (student_id, student_name, uniform_id, quantity, total_price) VALUES (?,?,?,?,?)")
                    ->execute([$d['student_id'], $d['student_name'], $d['uniform_id'], $d['quantity'], $u['price'] * $d['quantity']]);
                $id = $pdo->lastInsertId(); $pdo->commit();
                flash('ok', "Order #$id placed! Total: " . money($u['price'] * $d['quantity']));
                header('Location: order.php'); exit;
            }
            $pdo->rollBack();
        } catch (PDOException $ex) { if ($pdo->inTransaction()) $pdo->rollBack(); $errors[] = 'Database error. Please try again.'; }
    }
}
$uniforms = $pdo->query("SELECT * FROM uniforms ORDER BY uniform_name, uniform_id")->fetchAll();
$isEdit = false; $oldId = 0; $oldQty = 0;
include 'includes/header.php';
foreach ($errors as $m) echo '<div class="msg error">' . e($m) . '</div>';
include 'includes/order_form.php'; include 'includes/footer.php';
