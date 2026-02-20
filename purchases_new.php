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
// Get pre-selected fish from URL parameter
$preselected_fish_id = isset($_GET['fish_id']) ? intval($_GET['fish_id']) : null;
$preselected_fish_price = 0;
if ($preselected_fish_id) {
    foreach($fish_options as $f) {
        if($f['id'] == $preselected_fish_id) {
            $preselected_fish_price = $f['purchase_price'];
            break;
        }
    }
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

        // Init Tom Select on the new row's selects.
        // The fish_id select benefits most (32 fish options → typable filter).
        // The type select (Fish / Supply) is also wrapped for visual consistency.
        const typeSel = document.querySelector(`#row_${rowId} select[name="items[${rowId}][type]"]`);
        const fishSel = document.querySelector(`#row_${rowId} select[name="items[${rowId}][fish_id]"]`);
        if (typeSel && !typeSel.tomselect) {
            new TomSelect(typeSel, { allowEmptyOption: false, create: false, dropdownParent: 'body' });
        }
        if (fishSel && !fishSel.tomselect) {
            new TomSelect(fishSel, { allowEmptyOption: true, create: false, dropdownParent: 'body' });
        }
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

    // Auto-select fish if passed via URL parameter
    <?php if ($preselected_fish_id): ?>
    (function() {
        const preselectedId = "<?php echo $preselected_fish_id; ?>";
        const preselectedPrice = "<?php echo $preselected_fish_price; ?>";
        const preselectedName = "<?php 
            $name = '';
            foreach($fish_options as $f) {
                if($f['id'] == $preselected_fish_id) {
                    $name = $f['name'];
                    break;
                }
            }
            echo addslashes($name);
        ?>";
        
        // Function to set the fish selection
        function setPreselectedFish() {
            const fishSelect = document.querySelector('select[name="items[0][fish_id]"]');
            const priceInput = document.querySelector('input[name="items[0][unit_price]"]');
            const qtyInput = document.querySelector('input[name="items[0][quantity]"]');
            
            if (!fishSelect) {
                console.log('Fish select not found, retrying...');
                setTimeout(setPreselectedFish, 100);
                return;
            }
            
            console.log('Found fish select, tomselect:', fishSelect.tomselect ? 'yes' : 'no');
            
            // Set the underlying select value first
            fishSelect.value = preselectedId;
            
            // Update TomSelect if initialized
            if (fishSelect.tomselect) {
                fishSelect.tomselect.setValue(preselectedId);
                fishSelect.tomselect.refreshOptions();
            }
            
            // Update price and calculate total
            if (priceInput && preselectedPrice > 0) {
                priceInput.value = preselectedPrice;
                const qty = qtyInput ? parseInt(qtyInput.value) || 1 : 1;
                const price = parseFloat(preselectedPrice) || 0;
                const total = (qty * price).toFixed(2);
                const totalInput = document.getElementById('total_0');
                if (totalInput) totalInput.value = total;
                calcGrandTotal();
            }
            
            console.log('Set fish to:', preselectedName, 'ID:', preselectedId, 'Price:', preselectedPrice);
        }
        
        // Start trying after a short delay
        setTimeout(setPreselectedFish, 500);
    })();
    <?php endif; ?>
</script>
<?php include 'includes/footer.php'; ?>