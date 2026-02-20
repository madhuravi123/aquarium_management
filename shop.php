<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Admins/staff who land here get sent to their dashboard
if (isset($_SESSION['user_id']) && in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: dashboard.php");
    exit();
}

require_once 'config/db_connect.php';
require_once 'includes/error_logger.php';
$page_title = 'Browse Fish';

$is_logged_in  = isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer';
$display_name  = $is_logged_in ? ($_SESSION['full_name'] ?? $_SESSION['username']) : '';

// Filters
$search    = isset($_GET['search'])     ? trim($_GET['search'])    : '';
$water_type= isset($_GET['water_type']) ? $_GET['water_type']      : '';
$sort      = isset($_GET['sort'])       ? $_GET['sort']            : 'name';

// Build query
$where  = "WHERE stock_quantity > 0";
$params = [];
$types  = '';

if ($search !== '') {
    $where .= " AND (name LIKE ? OR species LIKE ? OR color LIKE ?)";
    $like   = "%$search%";
    $params = [$like, $like, $like];
    $types  = 'sss';
}
if (in_array($water_type, ['freshwater','saltwater','brackish'])) {
    $where .= " AND water_type = ?";
    $params[] = $water_type;
    $types   .= 's';
}

$order = match($sort) {
    'price_asc'  => 'selling_price ASC',
    'price_desc' => 'selling_price DESC',
    'stock'      => 'stock_quantity DESC',
    default      => 'name ASC'
};

$sql = "SELECT * FROM fish $where ORDER BY $order";
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $fish_result = $stmt->get_result();
} else {
    $fish_result = $conn->query($sql);
}

// Current cart quantities — used to pre-fill stepper state on page load
$cart_qtys = [];
if ($is_logged_in) {
    $cq = $conn->query("SELECT fish_id, quantity FROM cart WHERE user_id = " . (int)$_SESSION['user_id']);
    while ($cq && $r = $cq->fetch_assoc()) $cart_qtys[$r['fish_id']] = (int)$r['quantity'];
} else {
    $cart_qtys = array_map('intval', $_SESSION['guest_cart'] ?? []);
}

include 'includes/customer_header.php';
?>
<script>window._CSRF = <?= json_encode(csrf_token()) ?>;</script>

<div class="container py-4">
    <!-- Welcome Banner -->
    <div class="bg-ocean-gradient rounded-3 p-4 mb-4 shadow-premium">
        <div class="row align-items-center">
            <div class="col">
                <?php if ($is_logged_in): ?>
                    <h4 class="mb-1">
                        <i class="fas fa-hand-wave me-2"></i>
                        Welcome back, <?php echo htmlspecialchars($display_name); ?>!
                    </h4>
                    <p class="mb-0 opacity-75">Explore our fresh fish collection – direct from local farms.</p>
                <?php else: ?>
                    <h4 class="mb-1">
                        <i class="fas fa-fish me-2"></i>
                        Welcome to Harini Aquarium Shop!
                    </h4>
                    <p class="mb-0 opacity-75">
                        Browse freely — no login required.
                        <a href="admin_login.php" class="text-warning fw-semibold ms-2">
                            <i class="fas fa-sign-in-alt me-1"></i>Login for order history &amp; saved profile
                        </a>
                    </p>
                <?php endif; ?>
            </div>
            <div class="col-auto">
                <i class="fas fa-fish" style="font-size:3rem;opacity:0.3;"></i>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($_GET['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['err'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo htmlspecialchars($_GET['err']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Search & Filter Bar -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold small mb-1">
                        <i class="fas fa-search me-1"></i>Search Fish
                    </label>
                    <input type="text" name="search" class="form-control" placeholder="Name, species, color..."
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small mb-1">
                        <i class="fas fa-water me-1"></i>Water Type
                    </label>
                    <select name="water_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="freshwater" <?php echo $water_type=='freshwater' ? 'selected':''; ?>>Freshwater</option>
                        <option value="saltwater"  <?php echo $water_type=='saltwater'  ? 'selected':''; ?>>Saltwater</option>
                        <option value="brackish"   <?php echo $water_type=='brackish'   ? 'selected':''; ?>>Brackish</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small mb-1">
                        <i class="fas fa-sort me-1"></i>Sort By
                    </label>
                    <select name="sort" class="form-select">
                        <option value="name"       <?php echo $sort=='name'       ? 'selected':''; ?>>Name A–Z</option>
                        <option value="price_asc"  <?php echo $sort=='price_asc'  ? 'selected':''; ?>>Price: Low–High</option>
                        <option value="price_desc" <?php echo $sort=='price_desc' ? 'selected':''; ?>>Price: High–Low</option>
                        <option value="stock"      <?php echo $sort=='stock'      ? 'selected':''; ?>>In Stock</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <a href="shop.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Fish Grid -->
    <?php if ($fish_result && $fish_result->num_rows > 0): ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php while ($fish = $fish_result->fetch_assoc()): ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0" style="border-radius:12px;overflow:hidden;">
                    <!-- Fish Image -->
                    <div class="text-center bg-light" style="height:160px;display:flex;align-items:center;justify-content:center;">
                        <?php if (!empty($fish['image']) && file_exists('uploads/' . $fish['image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($fish['image']); ?>"
                                 alt="<?php echo htmlspecialchars($fish['name']); ?>"
                                 style="max-height:155px;max-width:100%;object-fit:contain;">
                        <?php else: ?>
                            <i class="fas fa-fish text-info" style="font-size:4rem;opacity:0.5;"></i>
                        <?php endif; ?>
                    </div>

                    <div class="card-body d-flex flex-column p-3">
                        <div class="mb-1">
                            <?php
                            $badge_class = match($fish['water_type']) {
                                'saltwater' => 'bg-info',
                                'brackish'  => 'bg-warning text-dark',
                                default     => 'bg-success'
                            };
                            ?>
                            <span class="badge <?php echo $badge_class; ?> small">
                                <?php echo ucfirst($fish['water_type']); ?>
                            </span>
                            <?php if ($fish['stock_quantity'] <= 5): ?>
                                <span class="badge bg-danger small">Only <?php echo $fish['stock_quantity']; ?> left!</span>
                            <?php endif; ?>
                        </div>

                        <h6 class="card-title fw-bold mb-0"><?php echo htmlspecialchars($fish['name']); ?></h6>
                        <small class="text-muted fst-italic mb-1"><?php echo htmlspecialchars($fish['species'] ?? ''); ?></small>

                        <div class="small text-muted mb-2">
                            <?php if ($fish['size']): ?>
                                <i class="fas fa-ruler-horizontal me-1"></i><?php echo htmlspecialchars($fish['size']); ?><br>
                            <?php endif; ?>
                            <?php if ($fish['color']): ?>
                                <i class="fas fa-palette me-1"></i><?php echo htmlspecialchars($fish['color']); ?>
                            <?php endif; ?>
                        </div>

                        <div class="mt-auto">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold text-success fs-5">&#8377;<?php echo number_format($fish['selling_price'], 2); ?></span>
                                <small class="text-muted">Stock: <?php echo $fish['stock_quantity']; ?></small>
                            </div>

                            <!-- Cart Stepper (Swiggy-style) -->
                            <?php $in_cart = $cart_qtys[$fish['id']] ?? 0; ?>
                            <div class="fish-cart-ctrl"
                                 data-id="<?php echo $fish['id']; ?>"
                                 data-max="<?php echo $fish['stock_quantity']; ?>">
                                <button type="button"
                                        class="btn btn-success btn-sm w-100 btn-fc-add<?php echo $in_cart > 0 ? ' d-none' : ''; ?>"
                                        style="border-radius:50px;font-weight:600;">
                                    <i class="fas fa-plus me-1"></i>Add
                                </button>
                                <div class="fc-stepper<?php echo $in_cart > 0 ? '' : ' d-none'; ?>">
                                    <div class="d-flex align-items-center justify-content-between"
                                         style="background:#198754;border-radius:50px;padding:5px 10px;">
                                        <button type="button" class="fc-btn fc-minus">&#8722;</button>
                                        <span class="fc-qty text-white fw-bold fs-6"><?php echo $in_cart; ?></span>
                                        <button type="button" class="fc-btn fc-plus">&#43;</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-fish text-muted" style="font-size:4rem;opacity:0.3;"></i>
            <h5 class="mt-3 text-muted">No fish found matching your criteria.</h5>
            <a href="shop.php" class="btn btn-outline-primary mt-2">
                <i class="fas fa-redo me-1"></i>Clear Filters
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Footer -->
<footer class="text-center text-muted py-4 mt-5 border-top bg-white">
    <small>
        &copy; <?php echo date('Y'); ?>
        <strong>Harini Aquarium &amp; Fish Shop</strong> &nbsp;|&nbsp;
        Manjakuppam, Cuddalore – 607001, Tamil Nadu &nbsp;|&nbsp;
        Ph: 9876543210
    </small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
/* Swiggy-style +/- stepper buttons */
.fc-btn {
    background: rgba(255,255,255,0.25);
    border: none;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    font-size: 1.15rem;
    font-weight: bold;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    padding: 0;
    transition: background 0.15s;
}
.fc-btn:hover  { background: rgba(255,255,255,0.42); }
.fc-btn:active { background: rgba(255,255,255,0.60); transform: scale(0.88); }
</style>

<script>
(function () {
    const CSRF = window._CSRF || '';

    function postCart(data) {
        var fd = new FormData();
        fd.append('csrf_token', CSRF);
        Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });
        fetch('cart_action.php', { method: 'POST', body: fd });
    }

    function updateBadge(delta) {
        var badge = document.getElementById('cart-count-badge');
        if (!badge) return;
        var count = Math.max(0, (parseInt(badge.textContent) || 0) + delta);
        badge.textContent = count;
        badge.classList.toggle('d-none', count === 0);
    }

    document.querySelectorAll('.fish-cart-ctrl').forEach(function (ctrl) {
        var fishId   = ctrl.dataset.id;
        var maxQty   = parseInt(ctrl.dataset.max) || 0;
        var addBtn   = ctrl.querySelector('.btn-fc-add');
        var stepper  = ctrl.querySelector('.fc-stepper');
        var minusBtn = ctrl.querySelector('.fc-minus');
        var plusBtn  = ctrl.querySelector('.fc-plus');
        var qtyEl    = ctrl.querySelector('.fc-qty');

        var qty = parseInt(qtyEl.textContent) || 0;

        addBtn.addEventListener('click', function () {
            if (maxQty < 1) return;
            qty = 1;
            qtyEl.textContent = qty;
            addBtn.classList.add('d-none');
            stepper.classList.remove('d-none');
            postCart({ action: 'add', fish_id: fishId, quantity: 1 });
            updateBadge(1);
        });

        plusBtn.addEventListener('click', function () {
            if (qty >= maxQty) return;
            qty++;
            qtyEl.textContent = qty;
            postCart({ action: 'update', fish_id: fishId, quantity: qty });
            updateBadge(1);
        });

        minusBtn.addEventListener('click', function () {
            if (qty <= 0) return;
            qty--;
            if (qty === 0) {
                stepper.classList.add('d-none');
                addBtn.classList.remove('d-none');
                postCart({ action: 'remove', fish_id: fishId });
            } else {
                qtyEl.textContent = qty;
                postCart({ action: 'update', fish_id: fishId, quantity: qty });
            }
            updateBadge(-1);
        });

        // Clicking anywhere on the card (outside the stepper) also triggers Add
        var card = ctrl.closest('.card');
        if (card) {
            card.style.cursor = 'pointer';
            card.addEventListener('click', function (e) {
                if (ctrl.contains(e.target)) return; // ignore clicks inside stepper area
                if (!addBtn.classList.contains('d-none')) {
                    addBtn.click(); // first click: add
                } else {
                    plusBtn.click(); // subsequent clicks: increment
                }
            });
        }
    });
})();
</script>
</body>
</html>
