/* =====================================================
   School Uniform Ordering System
   Everything is saved in the browser using localStorage.
   ===================================================== */

var CURRENCY = "$";   // change to "₱" or any symbol you like

// ---------- 1. Starting data (used the first time the page opens) ----------
var DEFAULT_PRODUCTS = [
  { id: 1, name: "Polo Shirt",       price: 12, sizes: ["S", "M", "L", "XL"], stock: 30 },
  { id: 2, name: "School Pants",     price: 18, sizes: ["S", "M", "L", "XL"], stock: 25 },
  { id: 3, name: "School Skirt",     price: 16, sizes: ["S", "M", "L"],       stock: 20 },
  { id: 4, name: "PE T-Shirt",       price: 10, sizes: ["S", "M", "L", "XL"], stock: 40 },
  { id: 5, name: "PE Jogging Pants", price: 14, sizes: ["S", "M", "L", "XL"], stock: 35 },
  { id: 6, name: "School Necktie",   price: 5,  sizes: ["One Size"],          stock: 50 }
];

// ---------- 2. localStorage helpers ----------
function loadData(key, fallback) {
  try {
    var saved = JSON.parse(localStorage.getItem(key));
    return saved || fallback;
  } catch (e) {
    return fallback;
  }
}

function saveData() {
  try {
    localStorage.setItem("uniformProducts", JSON.stringify(products));
    localStorage.setItem("uniformOrders", JSON.stringify(orders));
  } catch (e) {
    showMessage("Could not save data. Your browser may be blocking storage.", "fail");
  }
}

var products = loadData("uniformProducts", JSON.parse(JSON.stringify(DEFAULT_PRODUCTS)));
var orders = loadData("uniformOrders", []);
var editingId = null;   // null = placing a new order, otherwise the id of the order being edited

// ---------- 3. Small helper functions ----------
function $(id) { return document.getElementById(id); }

function money(n) { return CURRENCY + Number(n).toFixed(2); }

function findProduct(id) {
  return products.find(function (p) { return p.id === id; });
}

// Stops names typed by users from being treated as HTML
function esc(text) {
  return String(text).replace(/[&<>"']/g, function (c) {
    return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
  });
}

var messageTimer;
function showMessage(text, type) {
  $("message").innerHTML = '<span class="' + type + '">' + esc(text) + "</span>";
  clearTimeout(messageTimer);
  messageTimer = setTimeout(function () { $("message").innerHTML = ""; }, 4000);
}

// ---------- 4. Page navigation ----------
function showPage(pageId) {
  document.querySelectorAll(".page").forEach(function (p) { p.classList.add("hidden"); });
  $(pageId).classList.remove("hidden");
  document.querySelectorAll("nav button").forEach(function (b) {
    b.classList.toggle("active", b.dataset.page === pageId);
  });
}

document.querySelectorAll("nav button").forEach(function (btn) {
  btn.addEventListener("click", function () {
    if (btn.dataset.page !== "order" && editingId !== null) { resetForm(); }  // leaving the form cancels editing
    showPage(btn.dataset.page);
  });
});

// ---------- 5. Dashboard ----------
function renderDashboard() {
  var sales = 0, stock = 0, pending = 0;
  orders.forEach(function (o) {
    sales += o.total;
    if (o.status === "Pending") pending++;
  });
  products.forEach(function (p) { stock += p.stock; });

  $("totalOrders").textContent = orders.length;
  $("totalSales").textContent = money(sales);
  $("totalStock").textContent = stock;
  $("pendingOrders").textContent = pending;
}

// ---------- 6. Product list ----------
function renderProducts() {
  var html = "";
  products.forEach(function (p) {
    var stockText = p.stock > 0 ? p.stock : '<span class="out">Out of stock</span>';
    html += "<tr><td>" + esc(p.name) + "</td><td>" + esc(p.sizes.join(", ")) +
            "</td><td>" + money(p.price) + "</td><td>" + stockText + "</td></tr>";
  });
  $("productTable").innerHTML = html;
}

// ---------- 7. Order form ----------
function fillProductSelect(selectedId) {
  var html = '<option value="">-- Select uniform --</option>';
  products.forEach(function (p) {
    html += '<option value="' + p.id + '">' + esc(p.name) + " (" + money(p.price) + ", stock: " + p.stock + ")</option>";
  });
  $("product").innerHTML = html;
  $("product").value = selectedId || "";
}

function fillSizeSelect(selectedSize) {
  var product = findProduct(Number($("product").value));
  var html = '<option value="">-- Select size --</option>';
  if (product) {
    product.sizes.forEach(function (s) { html += "<option>" + esc(s) + "</option>"; });
  }
  $("size").innerHTML = html;
  $("size").value = selectedSize || "";
}

function updateTotalPrice() {
  var product = findProduct(Number($("product").value));
  var qty = Number($("quantity").value);
  var total = (product && qty > 0) ? product.price * qty : 0;
  $("totalPrice").textContent = money(total);
}

$("product").addEventListener("change", function () { fillSizeSelect(""); updateTotalPrice(); });
$("quantity").addEventListener("input", updateTotalPrice);

function clearErrors() {
  document.querySelectorAll(".error").forEach(function (e) { e.textContent = ""; });
  document.querySelectorAll(".invalid").forEach(function (e) { e.classList.remove("invalid"); });
}

function setError(fieldId, text) {
  $(fieldId + "Error").textContent = text;
  $(fieldId).classList.add("invalid");
}

function resetForm() {
  editingId = null;
  $("orderForm").reset();
  clearErrors();
  fillProductSelect("");
  fillSizeSelect("");
  updateTotalPrice();
  $("formTitle").textContent = "Place an Order";
  $("submitBtn").textContent = "Place Order";
  $("cancelEdit").classList.add("hidden");
}

// ---------- 8. Validate + save an order (new or edited) ----------
$("orderForm").addEventListener("submit", function (e) {
  e.preventDefault();
  clearErrors();

  var name = $("studentName").value.trim();
  var studentId = $("studentId").value.trim();
  var productId = Number($("product").value);
  var size = $("size").value;
  var qtyText = $("quantity").value.trim();
  var qty = Number(qtyText);
  var status = $("status").value;
  var valid = true;

  // Validation checks
  if (!/^[A-Za-z][A-Za-z .'-]{1,49}$/.test(name)) {
    setError("studentName", "Enter a valid name (letters only, at least 2 characters).");
    valid = false;
  }
  if (!/^[A-Za-z0-9-]{3,20}$/.test(studentId)) {
    setError("studentId", "Student ID must be 3-20 letters, numbers or dashes.");
    valid = false;
  }
  var product = findProduct(productId);
  if (!product) { setError("product", "Please choose a uniform."); valid = false; }
  if (product && !size) { setError("size", "Please choose a size."); valid = false; }
  if (qtyText === "" || !Number.isInteger(qty) || qty < 1) {
    setError("quantity", "Quantity must be a whole number of 1 or more.");
    valid = false;
  }
  if (!valid) { showMessage("Please fix the errors in the form.", "fail"); return; }

  // Stock check. When editing, the old quantity is "given back" first,
  // so a student can keep or lower their quantity without a false error.
  var oldOrder = editingId !== null ? orders.find(function (o) { return o.id === editingId; }) : null;
  var available = product.stock + (oldOrder && oldOrder.productId === productId ? oldOrder.qty : 0);
  if (qty > available) {
    setError("quantity", "Only " + available + " in stock for " + product.name + ".");
    showMessage("Not enough stock. Please lower the quantity.", "fail");
    return;
  }

  if (oldOrder) {
    // EDIT: return old quantity to stock, then take the new quantity
    findProduct(oldOrder.productId).stock += oldOrder.qty;
    product.stock -= qty;
    oldOrder.name = name;
    oldOrder.studentId = studentId;
    oldOrder.productId = productId;
    oldOrder.productName = product.name;
    oldOrder.size = size;
    oldOrder.qty = qty;
    oldOrder.total = product.price * qty;
    oldOrder.status = status;
    showMessage("Order #" + oldOrder.id + " updated.", "success");
  } else {
    // NEW ORDER
    var nextId = orders.length ? Math.max.apply(null, orders.map(function (o) { return o.id; })) + 1 : 1;
    product.stock -= qty;
    orders.push({
      id: nextId, date: new Date().toLocaleDateString(),
      name: name, studentId: studentId,
      productId: productId, productName: product.name,
      size: size, qty: qty, total: product.price * qty, status: status
    });
    showMessage("Order #" + nextId + " placed. Total: " + money(product.price * qty), "success");
  }

  saveData();
  resetForm();
  refreshAll();
  showPage("orders");
});

$("cancelEdit").addEventListener("click", function () {
  resetForm();
  showPage("orders");
});

// ---------- 9. Order list, search, edit, delete ----------
function renderOrders() {
  var keyword = $("searchBox").value.trim().toLowerCase();
  var list = orders.filter(function (o) {
    return o.name.toLowerCase().includes(keyword) || o.studentId.toLowerCase().includes(keyword);
  });

  if (list.length === 0) {
    $("orderTable").innerHTML = '<tr><td colspan="10" class="empty">' +
      (orders.length ? "No orders match your search." : "No orders yet.") + "</td></tr>";
    return;
  }

  var html = "";
  list.forEach(function (o) {
    html += "<tr>" +
      "<td>" + o.id + "</td><td>" + esc(o.date) + "</td><td>" + esc(o.name) + "</td><td>" + esc(o.studentId) + "</td>" +
      "<td>" + esc(o.productName) + "</td><td>" + esc(o.size) + "</td><td>" + o.qty + "</td><td>" + money(o.total) + "</td>" +
      '<td><span class="badge ' + o.status + '">' + o.status + "</span></td>" +
      '<td><button class="btn small" data-edit="' + o.id + '">Edit</button>' +
      '<button class="btn small red" data-delete="' + o.id + '">Delete</button></td></tr>';
  });
  $("orderTable").innerHTML = html;
}

$("searchBox").addEventListener("input", renderOrders);

// One click listener for all Edit / Delete buttons in the table
$("orderTable").addEventListener("click", function (e) {
  var editId = e.target.dataset.edit;
  var deleteId = e.target.dataset.delete;

  if (editId) { startEdit(Number(editId)); }
  if (deleteId) { deleteOrder(Number(deleteId)); }
});

function startEdit(id) {
  var order = orders.find(function (o) { return o.id === id; });
  if (!order) return;

  resetForm();
  editingId = id;
  $("studentName").value = order.name;
  $("studentId").value = order.studentId;
  fillProductSelect(order.productId);
  fillSizeSelect(order.size);
  $("quantity").value = order.qty;
  $("status").value = order.status;
  updateTotalPrice();

  $("formTitle").textContent = "Edit Order #" + id;
  $("submitBtn").textContent = "Update Order";
  $("cancelEdit").classList.remove("hidden");
  showPage("order");
}

function deleteOrder(id) {
  var order = orders.find(function (o) { return o.id === id; });
  if (!order) return;

  if (!confirm("Delete order #" + id + " for " + order.name + "? This cannot be undone.")) return;

  findProduct(order.productId).stock += order.qty;   // put the uniforms back in stock
  orders = orders.filter(function (o) { return o.id !== id; });
  saveData();
  refreshAll();
  showMessage("Order #" + id + " deleted and stock restored.", "success");
}

// ---------- 10. Refresh everything on screen ----------
function refreshAll() {
  renderDashboard();
  renderProducts();
  renderOrders();
  if (editingId === null) { fillProductSelect($("product").value); fillSizeSelect($("size").value); }
}

// ---------- 11. Start the app ----------
resetForm();
refreshAll();
showPage("home");
