<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
if ($_SESSION['role'] === 'customer') {
    header("Location: shop.php");
    exit();
}
require_once 'config/db_connect.php';
include 'includes/header.php';

$suppliers = $conn->query("SELECT * FROM suppliers");
$fish = $conn->query("SELECT * FROM fish");
// Convert fish to JS array for easy access
$fish_options = [];
while ($f = $fish->fetch_assoc()) {
    $fish_options[] = $f;
}
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h2>New Purchase</h2>
        <form action="purchases_action.php" method="POST">
            <div class="card card-custom mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Supplier</label>
                            <select name="supplier_id" class="form-select" required>
                                <option value="">Select Supplier</option>
                                <?php while ($s = $suppliers->fetch_assoc()): ?>
                                    <option value="<?php echo $s['id']; ?>">
                                        <?php echo $s['name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Purchase Date</label>
                            <input type="date" name="purchase_date" class="form-control"
                                value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-custom mb-3">
                <div class="card-header bg-light">Items</div>
                <div class="card-body">
                    <table class="table" id="itemsTable">
                        <thead>
                            <tr>
                                <th>Item Type</th>
                                <th>Fish / Item Name</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <!-- Rows added by JS -->
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addItemRow()">Add Item</button>
                </div>
            </div>

            <div class="card card-custom mb-3">
                <div class="card-body text-end">
                    <h4>Total Amount: &#8377;<span id="grandTotal">0.00</span></h4>
                    <input type="hidden" name="total_amount" id="totalAmountInput">
                    <button type="submit" name="save_purchase" class="btn btn-success btn-lg">Complete Purchase</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const fishData = <?php echo json_encode($fish_options); ?>;

    function addItemRow() {
        const rowId = document.querySelectorAll('#itemsBody tr').length;
        const html = `
        <tr id="row_${rowId}">
            <td>
                <select class="form-select" name="items[${rowId}][type]" onchange="toggleItemType(${rowId}, this.value)">
                    <option value="fish">Fish</option>
                    <option value="supply">Supply</option>
                </select>
            </td>
            <td>
                <div id="fish_select_${rowId}">
                    <select class="form-select" name="items[${rowId}][fish_id]" onchange="updatePrice(${rowId}, this)">
                        <option value="">Select Fish</option>
                        ${fishData.map(f => `<option value="${f.id}" data-price="${f.purchase_price}">${f.name}</option>`).join('')}
                    </select>
                </div>
                <div id="supply_input_${rowId}" style="display:none;">
                    <input type="text" class="form-control" name="items[${rowId}][item_name]" placeholder="Item Name">
                </div>
            </td>
            <td><input type="number" class="form-control" name="items[${rowId}][quantity]" value="1" min="1" onchange="calcRow(${rowId})"></td>
            <td><input type="number" step="0.01" class="form-control" name="items[${rowId}][unit_price]" value="0" onchange="calcRow(${rowId})"></td>
            <td><input type="text" class="form-control" id="total_${rowId}" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(${rowId})"><i class="fas fa-trash"></i></button></td>
        </tr>
    `;
        document.getElementById('itemsBody').insertAdjacentHTML('beforeend', html);
    }

    function toggleItemType(id, type) {
        if (type === 'fish') {
            document.getElementById(`fish_select_${id}`).style.display = 'block';
            document.getElementById(`supply_input_${id}`).style.display = 'none';
        } else {
            document.getElementById(`fish_select_${id}`).style.display = 'none';
            document.getElementById(`supply_input_${id}`).style.display = 'block';
        }
    }

    function updatePrice(id, select) {
        const price = select.options[select.selectedIndex].getAttribute('data-price');
        if (price) {
            document.querySelector(`#row_${id} input[name="items[${id}][unit_price]"]`).value = price;
            calcRow(id);
        }
    }

    function calcRow(id) {
        const qty = document.querySelector(`#row_${id} input[name="items[${id}][quantity]"]`).value;
        const price = document.querySelector(`#row_${id} input[name="items[${id}][unit_price]"]`).value;
        const total = (qty * price).toFixed(2);
        document.getElementById(`total_${id}`).value = total;
        calcGrandTotal();
    }

    function calcGrandTotal() {
        let total = 0;
        document.querySelectorAll('[id^="total_"]').forEach(el => {
            total += parseFloat(el.value || 0);
        });
        document.getElementById('grandTotal').innerText = total.toFixed(2);
        document.getElementById('totalAmountInput').value = total.toFixed(2);
    }

    function removeRow(id) {
        document.getElementById(`row_${id}`).remove();
        calcGrandTotal();
    }

    // Add one row by default
    addItemRow();
</script>
<?php include 'includes/footer.php'; ?>