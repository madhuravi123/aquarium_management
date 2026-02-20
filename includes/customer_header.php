<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) session_start();

$is_logged_in = isset($_SESSION['user_id']) && ($_SESSION['role'] === 'customer');
$current_page = basename($_SERVER['PHP_SELF']);

// Pages that REQUIRE a logged-in customer session
$protected_pages = ['my_orders.php', 'my_profile.php'];
if (in_array($current_page, $protected_pages) && !$is_logged_in) {
    header("Location: admin_login.php?info=" . urlencode("Please login to access this page."));
    exit();
}

// Cart item count: DB cart for logged-in, session for guests
$cart_count = 0;
if ($is_logged_in && isset($conn)) {
    $uid = (int)$_SESSION['user_id'];
    $cc  = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id = $uid");
    if ($cc) $cart_count = (int)($cc->fetch_assoc()['total'] ?? 0);
} elseif (!empty($_SESSION['guest_cart'])) {
    $cart_count = array_sum($_SESSION['guest_cart']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' – ' : ''; ?>Harini Aquarium Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Tom Select (searchable/typable dropdowns) -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        .navbar-brand .brand-name { font-weight: 700; font-size: 1.2rem; }
        .navbar-brand small { font-size: 0.7rem; opacity: 0.85; }
        .cart-badge { font-size: 0.65rem; }
        .hero-bg { background: linear-gradient(135deg, #0a3d62 0%, #1e6091 100%); }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark hero-bg shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="shop.php">
            <i class="fas fa-fish me-2 fs-4"></i>
            <div>
                <div class="brand-name">Harini Aquarium</div>
                <small class="d-block">Fish &amp; Aquarium Shop, Cuddalore</small>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#customerNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="customerNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">

                <!-- Shop — always visible -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page=='shop.php' ? 'active fw-semibold' : ''; ?>" href="shop.php">
                        <i class="fas fa-store me-1"></i>Shop
                    </a>
                </li>

                <?php if ($is_logged_in): ?>
                <!-- Logged-in customer: Orders & Profile -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page=='my_orders.php' ? 'active fw-semibold' : ''; ?>" href="my_orders.php">
                        <i class="fas fa-box-open me-1"></i>My Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page=='my_profile.php' ? 'active fw-semibold' : ''; ?>" href="my_profile.php">
                        <i class="fas fa-user me-1"></i>Profile
                    </a>
                </li>
                <?php endif; ?>

                <!-- Cart — always visible -->
                <li class="nav-item">
                    <a class="nav-link position-relative <?php echo $current_page=='cart.php' ? 'active fw-semibold' : ''; ?>" href="cart.php">
                        <i class="fas fa-shopping-cart me-1"></i>Cart
                        <?php if ($cart_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-badge">
                                <?php echo $cart_count; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- Auth action -->
                <li class="nav-item ms-lg-2">
                    <?php if ($is_logged_in): ?>
                        <a class="btn btn-outline-light btn-sm" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    <?php else: ?>
                        <a class="btn btn-warning btn-sm fw-semibold" href="admin_login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    <?php endif; ?>
                </li>

            </ul>
        </div>
    </div>
</nav>
