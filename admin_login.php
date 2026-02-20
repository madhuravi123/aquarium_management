<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'customer') {
        header("Location: shop.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}
require_once 'config/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Harini Aquarium Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0a3d62 0%, #1e6091 50%, #0d4f7e 100%);
            min-height: 100vh;
        }
        .login-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }
        .login-header {
            background: linear-gradient(135deg, #0a3d62, #1e6091);
            border-radius: 16px 16px 0 0;
            padding: 2rem;
        }
        .credential-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 8px;
            border-left: 4px solid;
        }
        .admin-cred  { border-color: #dc3545; }
        .staff-cred  { border-color: #0d6efd; }
        .cust-cred   { border-color: #198754; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh;">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">

                <!-- Back to landing -->
                <div class="text-center mb-3">
                    <a href="index.php" class="text-white text-decoration-none small">
                        <i class="fas fa-arrow-left me-1"></i>Back to Home
                    </a>
                </div>

                <!-- Business Title -->
                <div class="text-center text-white mb-3">
                    <i class="fas fa-water" style="font-size:2.5rem;" class="mb-2 d-block"></i>
                    <h3 class="fw-bold mb-0">Harini Aquarium</h3>
                    <p class="text-light small mb-0">Management Portal</p>
                    <p class="text-light" style="font-size:0.78rem;">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        Manjakuppam, Cuddalore – 607001
                    </p>
                </div>

                <div class="card login-card">
                    <!-- Card Header -->
                    <div class="login-header text-white text-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In to Your Account
                        </h5>
                    </div>

                    <div class="card-body p-4">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                <?php echo htmlspecialchars($_GET['error']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                                <i class="fas fa-check-circle me-1"></i>
                                <?php echo htmlspecialchars($_GET['success']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['info'])): ?>
                            <div class="alert alert-info alert-dismissible fade show py-2" role="alert">
                                <i class="fas fa-info-circle me-1"></i>
                                <?php echo htmlspecialchars($_GET['info']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form action="auth_login.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold">
                                    <i class="fas fa-user me-1 text-primary"></i>Username
                                </label>
                                <input type="text" class="form-control" id="username" name="username"
                                       placeholder="Enter username" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">
                                    <i class="fas fa-lock me-1 text-primary"></i>Password
                                </label>
                                <input type="password" class="form-control" id="password" name="password"
                                       placeholder="Enter password" required>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Default Credentials -->
                    <div class="card-footer bg-light px-4 py-3">
                        <p class="text-muted text-center mb-2" style="font-size:0.8rem;">
                            <i class="fas fa-info-circle me-1"></i><strong>Demo Login Credentials</strong>
                        </p>
                        <div class="credential-box admin-cred">
                            <span class="badge bg-danger me-1" style="font-size:0.72rem;">Admin</span>
                            <small><strong>harini</strong> / harini@123</small>
                        </div>
                        <div class="credential-box staff-cred">
                            <span class="badge bg-primary me-1" style="font-size:0.72rem;">Staff</span>
                            <small><strong>rajan</strong> / staff@123</small>
                        </div>
                        <div class="credential-box cust-cred">
                            <span class="badge bg-success me-1" style="font-size:0.72rem;">Customer</span>
                            <small><strong>demo</strong> / demo@123</small>
                        </div>
                    </div>
                </div>

                <p class="text-center text-light mt-3" style="font-size:0.78rem;">
                    <a href="shop.php" class="text-warning text-decoration-none">
                        <i class="fas fa-fish me-1"></i>Browse shop without logging in
                    </a>
                </p>

                <p class="text-center text-light mt-1" style="font-size:0.78rem;">
                    &copy; <?php echo date('Y'); ?> Harini Aquarium &amp; Fish Shop, Cuddalore
                </p>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
