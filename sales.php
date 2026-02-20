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

// ── SORT ──────────────────────────────────────────────────────────────────────
$sort      = ($_GET['sort'] ?? 'date_desc');
$order_sql = ($sort === 'date_asc') ? 'ORDER BY s.sale_date ASC' : 'ORDER BY s.sale_date DESC';
$next_sort = ($sort === 'date_asc') ? 'date_desc' : 'date_asc';
$sort_icon = ($sort === 'date_asc')  ? '&#8593;' : '&#8595;'; // ↑ ↓

// ── DATE FILTER ───────────────────────────────────────────────────────────────
$date_from = trim($_GET['date_from'] ?? '');
$date_to   = trim($_GET['date_to']   ?? '');

$where  = '';
$params = [];
$types  = '';

if ($date_from !== '') {
    $where   .= " WHERE DATE(s.sale_date) >= ?";
    $params[] = $date_from;
    $types   .= 's';
}
if ($date_to !== '') {
    $where  .= ($where === '') ? " WHERE" : " AND";
    $where  .= " DATE(s.sale_date) <= ?";
    $params[] = $date_to;
    $types   .= 's';
}

$sql = "SELECT s.*, c.name as customer_name
        FROM sales s
        LEFT JOIN customers c ON s.customer_id = c.id
        $where
        $order_sql";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $sales = $stmt->get_result();
} else {
    $sales = $conn->query($sql);
}

// ── TODAY TOTAL (for quick stat) ──────────────────────────────────────────────
$today_total = $conn->query(
    "SELECT SUM(final_amount) as t FROM sales WHERE DATE(sale_date) = CURDATE()"
)->fetch_assoc()['t'] ?? 0;
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Sales Management</h2>
            <span class="badge bg-success fs-6">
                Today&rsquo;s Total: &#8377;<?php echo number_format($today_total, 2); ?>
            </span>
        </div>

        <div class="d-flex flex-wrap gap-2 align-items-end mb-3">
            <a href="sales_new.php" class="btn btn-success">
                <i class="fas fa-plus"></i> New Sale
            </a>

            <!-- Date filter form -->
            <form method="GET" class="d-flex flex-wrap gap-2 align-items-end ms-auto">
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                <div>
                    <label class="form-label small fw-semibold mb-1">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                <div>
                    <label class="form-label small fw-semibold mb-1">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
                <?php if ($date_from !== '' || $date_to !== ''): ?>
                    <a href="sales.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card card-custom">
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>
                                <!-- Clickable sort-by-date header -->
                                <a href="sales.php?sort=<?php echo $next_sort;
                                    echo $date_from !== '' ? '&date_from=' . urlencode($date_from) : '';
                                    echo $date_to   !== '' ? '&date_to='   . urlencode($date_to)   : '';
                                ?>" class="text-white text-decoration-none">
                                    Date <?php echo $sort_icon; ?>
                                </a>
                            </th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $row_count = 0;
                        while ($row = $sales->fetch_assoc()):
                            $row_count++;
                            $is_today = (substr($row['sale_date'], 0, 10) === date('Y-m-d'));
                        ?>
                            <tr <?php echo $is_today ? 'class="table-success"' : ''; ?>>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name'] ?? 'Walk-in Customer'); ?></td>
                                <td>
                                    <?php
                                    $ts   = strtotime($row['sale_date']);
                                    $time = date('g:i A', $ts);
                                    echo date('d M Y', $ts);
                                    if (date('H:i', $ts) !== '00:00') echo ' <span class="text-muted small">' . $time . '</span>';
                                    ?>
                                    <?php if ($is_today): ?>
                                        <span class="badge bg-success ms-1" style="font-size:.65rem;">Today</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-semibold">&#8377;<?php echo number_format($row['final_amount'], 2); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($row['payment_method'])); ?></td>
                                <td>
                                    <a href="invoice_print.php?id=<?php echo $row['id']; ?>" target="_blank"
                                       class="btn btn-sm btn-secondary">
                                        <i class="fas fa-print"></i> Invoice
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                        <?php if ($row_count === 0): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-search me-2"></i>No sales found for the selected date range.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($row_count > 0): ?>
            <div class="card-footer text-muted small">
                Showing <?php echo $row_count; ?> sale(s)
                <?php if ($date_from !== '' || $date_to !== ''): ?>
                    &nbsp;|&nbsp;
                    <?php if ($date_from !== '') echo 'From: ' . htmlspecialchars($date_from); ?>
                    <?php if ($date_to   !== '') echo ' &nbsp;To: ' . htmlspecialchars($date_to); ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>