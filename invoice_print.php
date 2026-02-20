<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once 'config/db_connect.php';

if (!isset($_GET['id'])) {
    die("Invalid Invoice ID");
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT s.*, c.name as customer_name, c.address as customer_address, c.phone as customer_phone FROM sales s LEFT JOIN customers c ON s.customer_id = c.id WHERE s.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$sale = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$sale) {
    die("Invoice not found");
}

$stmt = $conn->prepare("SELECT si.*, f.name as fish_name, f.species FROM sale_items si JOIN fish f ON si.fish_id = f.id WHERE si.sale_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$items = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?php echo $id; ?> – Harini Aquarium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #fff; padding: 30px; font-family: Arial, sans-serif; }
        .invoice-header { border-bottom: 2px solid #0a3d62; margin-bottom: 20px; padding-bottom: 15px; }
        .brand-color { color: #0a3d62; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 10px; }
        }
    </style>
</head>
<body>
    <div class="container" style="max-width:800px;">
        <!-- Print Button -->
        <div class="no-print mb-3 d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-1"></i> Print Invoice
            </button>
            <a href="sales.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Sales
            </a>
        </div>

        <!-- Invoice Header -->
        <div class="row invoice-header align-items-start">
            <div class="col-7">
                <h3 class="fw-bold brand-color mb-0">
                    <i class="fas fa-fish me-2"></i>Harini Aquarium
                </h3>
                <p class="mb-0 text-muted" style="font-size:0.9rem;">Fish &amp; Aquarium Shop</p>
                <p class="mb-0" style="font-size:0.85rem;">
                    St. Joseph's College of Arts and Science,<br>
                    Manjakuppam, Cuddalore – 607001,<br>
                    Tamil Nadu, India.<br>
                    Phone: 9876543210 &nbsp;|&nbsp; GSTIN: 33XXXXX
                </p>
            </div>
            <div class="col-5 text-end">
                <h3 class="fw-bold text-uppercase brand-color">TAX INVOICE</h3>
                <table class="ms-auto" style="font-size:0.88rem;">
                    <tr>
                        <td class="pe-2"><strong>Invoice No.:</strong></td>
                        <td>#<?php echo str_pad($sale['id'], 5, '0', STR_PAD_LEFT); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Date:</strong></td>
                        <td><?php echo date('d M Y', strtotime($sale['sale_date'])); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Payment:</strong></td>
                        <td><?php echo strtoupper($sale['payment_method']); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Bill To -->
        <div class="row mb-4">
            <div class="col-6">
                <div class="border rounded p-3" style="background:#f8f9fa;">
                    <h6 class="fw-bold text-uppercase text-muted mb-2">Bill To:</h6>
                    <p class="mb-0 fw-semibold"><?php echo htmlspecialchars($sale['customer_name'] ?? 'Walk-in Customer'); ?></p>
                    <?php if ($sale['customer_phone']): ?>
                        <p class="mb-0 small">Ph: <?php echo htmlspecialchars($sale['customer_phone']); ?></p>
                    <?php endif; ?>
                    <?php if ($sale['customer_address']): ?>
                        <p class="mb-0 small text-muted"><?php echo htmlspecialchars($sale['customer_address']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table table-bordered" style="font-size:0.9rem;">
            <thead style="background:#0a3d62;color:#fff;">
                <tr>
                    <th>#</th>
                    <th>Item (Fish)</th>
                    <th>Species</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; while ($item = $items->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td class="fw-semibold"><?php echo htmlspecialchars($item['fish_name']); ?></td>
                        <td class="fst-italic text-muted small"><?php echo htmlspecialchars($item['species'] ?? ''); ?></td>
                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                        <td class="text-end">&#8377;<?php echo number_format($item['unit_price'], 2); ?></td>
                        <td class="text-end">&#8377;<?php echo number_format($item['total_price'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end fw-bold">Sub-Total</td>
                    <td class="text-end">&#8377;<?php echo number_format($sale['total_amount'], 2); ?></td>
                </tr>
                <tr>
                    <td colspan="5" class="text-end fw-bold">Tax (GST)</td>
                    <td class="text-end">&#8377;<?php echo number_format($sale['tax_amount'], 2); ?></td>
                </tr>
                <tr style="background:#0a3d62;color:#fff;">
                    <th colspan="5" class="text-end">Grand Total</th>
                    <th class="text-end">&#8377;<?php echo number_format($sale['final_amount'], 2); ?></th>
                </tr>
            </tfoot>
        </table>

        <?php if (!empty($sale['notes'])): ?>
        <div class="mb-3">
            <strong>Notes:</strong> <?php echo htmlspecialchars($sale['notes']); ?>
        </div>
        <?php endif; ?>

        <div class="row mt-5">
            <div class="col-8">
                <p class="text-muted small mb-1">Terms &amp; Conditions:</p>
                <p class="text-muted" style="font-size:0.78rem;">
                    1. All live fish are non-refundable once purchased.<br>
                    2. Exchange within 24 hours if fish arrives dead (with photo proof).<br>
                    3. This is a computer generated invoice.
                </p>
            </div>
            <div class="col-4 text-center">
                <div class="border-top pt-2 mt-5">
                    <small class="text-muted">Authorised Signatory</small><br>
                    <small class="fw-bold">Harini Aquarium &amp; Fish Shop</small>
                </div>
            </div>
        </div>

        <div class="text-center mt-4 pt-3 border-top text-muted">
            <small>Thank you for shopping at Harini Aquarium! &nbsp;|&nbsp; Cuddalore, Tamil Nadu</small>
        </div>
    </div>
</body>
</html>
