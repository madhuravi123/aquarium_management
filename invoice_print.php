<?php
require_once 'config/db_connect.php';

if (!isset($_GET['id'])) {
    die("Invalid Invoice ID");
}

$id = $_GET['id'];
$sale = $conn->query("SELECT s.*, c.name, c.address FROM sales s LEFT JOIN customers c ON s.customer_id = c.id WHERE s.id = $id")->fetch_assoc();
$items = $conn->query("SELECT si.*, f.name FROM sale_items si JOIN fish f ON si.fish_id = f.id WHERE si.sale_id = $id");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice #
        <?php echo $id; ?>
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #fff;
            padding: 20px;
        }

        .invoice-header {
            border-bottom: 2px solid #ddd;
            margin-bottom: 20px;
            pb-3;
        }
    </style>
</head>

<body onload="window.print()">
    <div class="container">
        <div class="row invoice-header">
            <div class="col-6">
                <h2>Aquarium Shop</h2>
                <p>123 Fish Street, Water City<br>Phone: 555-0199</p>
            </div>
            <div class="col-6 text-end">
                <h3>INVOICE</h3>
                <p><strong>Invoice #:</strong>
                    <?php echo $sale['id']; ?><br>
                    <strong>Date:</strong>
                    <?php echo date('d M Y', strtotime($sale['sale_date'])); ?>
                </p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <h5>Bill To:</h5>
                <p>
                    <?php echo $sale['name'] ?? 'Walk-in Customer'; ?><br>
                    <?php echo $sale['address'] ?? ''; ?>
                </p>
            </div>
        </div>

        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $items->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php echo $item['name']; ?>
                        </td>
                        <td>
                            <?php echo $item['quantity']; ?>
                        </td>
                        <td>$
                            <?php echo number_format($item['unit_price'], 2); ?>
                        </td>
                        <td>$
                            <?php echo number_format($item['total_price'], 2); ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total Amount</th>
                    <th>$
                        <?php echo number_format($sale['final_amount'], 2); ?>
                    </th>
                </tr>
            </tfoot>
        </table>

        <div class="mt-5 text-center text-muted">
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>

</html>