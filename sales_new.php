<?php
session_start();
require_once 'config/db_connect.php';
include 'includes/header.php';

$customers = $conn->query("SELECT * FROM customers");
$fish = $conn->query("SELECT * FROM fish WHERE stock_quantity > 0");

$fish_options = [];
while ($f = $fish->fetch_assoc()) {
    $fish_options[] = $f;
}
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h2>New Sale</h2>
        <form action="sales_action.php" method="POST">
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-custom mb-3">
                        <div class="card-header">Customer Details</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label>Customer</label>
                                <select name="customer_id" class="form-select">
                                    <option value="">Walk-in Customer</option>
                                    <?php while ($c = $customers->fetch_assoc()): ?>
                                        <option value="<?php echo $c['id']; ?>">
                                            <?php echo $c['name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Sale Date</label>
                                <input type="date" name="sale_date" class="form-control"
                                    value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card card-custom mb-3">
                        <div class="card-header">Items</div>
                        <div class="card-body">
                            <table class="table" id="saleTable">
                                <thead>
                                    <tr>
                                        <th>Fish</th>
                                        <th>Stock</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="saleBody"></tbody>
                            </table>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addSaleRow()">Add
                                Item</button>
                        </div>
                    </div>

                    <div class="card card-custom">
                        <div class="card-body text-end">
                            <h4>Total: $<span id="grandTotal">0.00</span></h4>
                            <input type="hidden" name="total_amount" id="totalInput">
                            <button type="submit" name="save_sale" class="btn btn-success btn-lg">Complete Sale</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const fishList = <?php echo json_encode($fish_options); ?>;

    function addSaleRow() {
        const rowId = document.querySelectorAll('#saleBody tr').length;
        let options = `<option value="">Select Fish</option>`;
        fishList.forEach(f => {
            options += `<option value="${f.id}" data-price="${f.selling_price}" data-stock="${f.stock_quantity}">${f.name} (Stock: ${f.stock_quantity})</option>`;
        });

        const html = `
        <tr id="row_${rowId}">
            <td>
                <select class="form-select" name="items[${rowId}][fish_id]" onchange="updateSalePrice(${rowId}, this)" required>
                    ${options}
                </select>
            </td>
            <td><span id="stock_${rowId}" class="badge bg-info">0</span></td>
            <td><input type="number" class="form-control" name="items[${rowId}][quantity]" value="1" min="1" onchange="calcSaleRow(${rowId})"></td>
            <td><input type="number" class="form-control" name="items[${rowId}][unit_price]" readonly></td>
            <td><input type="text" class="form-control" id="total_${rowId}" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(${rowId})"><i class="fas fa-trash"></i></button></td>
        </tr>
    `;
        document.getElementById('saleBody').insertAdjacentHTML('beforeend', html);
    }

    function updateSalePrice(id, select) {
        const option = select.options[select.selectedIndex];
        const price = option.getAttribute('data-price');
        const stock = option.getAttribute('data-stock');

        if (price) {
            document.querySelector(`#row_${id} input[name="items[${id}][unit_price]"]`).value = price;
            document.getElementById(`stock_${id}`).innerText = stock;
            document.querySelector(`#row_${id} input[name="items[${id}][quantity]"]`).setAttribute('max', stock);
            calcSaleRow(id);
        }
    }

    function calcSaleRow(id) {
        const qty = document.querySelector(`#row_${id} input[name="items[${id}][quantity]"]`).value;
        const price = document.querySelector(`#row_${id} input[name="items[${id}][unit_price]"]`).value;
        const total = (qty * price).toFixed(2);
        document.getElementById(`total_${id}`).value = total;
        calcSaleTotal();
    }

    function calcSaleTotal() {
        let total = 0;
        document.querySelectorAll('[id^="total_"]').forEach(el => {
            total += parseFloat(el.value || 0);
        });
        document.getElementById('grandTotal').innerText = total.toFixed(2);
        document.getElementById('totalInput').value = total.toFixed(2);
    }

    function removeRow(id) {
        document.getElementById(`row_${id}`).remove();
        calcSaleTotal();
    }

    addSaleRow();
</script>
<?php include 'includes/footer.php'; ?>