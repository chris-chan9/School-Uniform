// 1) Automatic total price + basic form validation for the order form
var form = document.getElementById('orderForm');
if (form) {
  var sel = form.elements['uniform_id'], qty = form.elements['quantity'], totalEl = document.getElementById('total');

  function chosen() { return sel.options[sel.selectedIndex]; }
  function availableStock() {                       // when editing, the old quantity counts as available again
    var o = chosen(); if (!o || !o.value) return 0;
    var s = Number(o.dataset.stock);
    if (o.value === form.dataset.oldId) s += Number(form.dataset.oldQty);
    return s;
  }
  function updateTotal() {
    var o = chosen(), q = parseInt(qty.value, 10);
    var price = o && o.value ? Number(o.dataset.price) : 0;
    totalEl.textContent = (price && q > 0 ? price * q : 0).toFixed(2);
  }
  sel.addEventListener('change', updateTotal);
  qty.addEventListener('input', updateTotal);
  updateTotal();

  form.addEventListener('submit', function (e) {
    var errors = [], f = form.elements, q = Number(qty.value);
    if (!/^[A-Za-z0-9-]{3,20}$/.test(f['student_id'].value.trim())) errors.push('Student ID must be 3-20 letters, numbers or dashes.');
    if (!/^[A-Za-z][A-Za-z .'-]{1,99}$/.test(f['student_name'].value.trim())) errors.push('Please enter a valid student name (letters only).');
    if (!sel.value) errors.push('Please choose a uniform.');
    if (!Number.isInteger(q) || q < 1) errors.push('Quantity must be a whole number of 1 or more.');
    else if (sel.value && q > availableStock()) errors.push('Not enough stock. Only ' + availableStock() + ' available.');
    if (errors.length) {
      e.preventDefault();
      var box = document.getElementById('jsErrors');
      box.innerHTML = '';
      errors.forEach(function (m) { var d = document.createElement('div'); d.className = 'msg error'; d.textContent = m; box.appendChild(d); });
    }
  });
}

// 2) Ask for confirmation before deleting
document.querySelectorAll('.confirm-delete').forEach(function (f) {
  f.addEventListener('submit', function (e) { if (!confirm(f.dataset.msg || 'Delete this record?')) e.preventDefault(); });
});
