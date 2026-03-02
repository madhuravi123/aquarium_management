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
    <title>Login – Aquamart Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            min-height: 100vh;
            background: linear-gradient(-45deg, #0f0c29, #302b63, #24243e, #0f0c29);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            position: relative;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(0, 255, 255, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(138, 43, 226, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(0, 191, 255, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 90% 90%, rgba(72, 61, 139, 0.1) 0%, transparent 40%);
            pointer-events: none;
            z-index: 0;
        }
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        .floating-bubbles {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }
        .bubble {
            position: absolute;
            bottom: -100px;
            background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.9) 0%, rgba(255,255,255,0.4) 50%, rgba(255,255,255,0.1) 100%);
            border-radius: 50%;
            animation: rise 15s infinite ease-in;
            border: 1px solid rgba(255,255,255,0.6);
            box-shadow: 
                inset 0 -5px 15px rgba(255,255,255,0.3),
                inset 5px 5px 15px rgba(255,255,255,0.4),
                0 0 10px rgba(255,255,255,0.2);
        }
        .bubble:nth-child(1) { width: 25px; height: 25px; left: 5%; animation-duration: 12s; animation-delay: 0s; }
        .bubble:nth-child(2) { width: 15px; height: 15px; left: 10%; animation-duration: 14s; animation-delay: 1s; }
        .bubble:nth-child(3) { width: 35px; height: 35px; left: 15%; animation-duration: 16s; animation-delay: 2s; }
        .bubble:nth-child(4) { width: 20px; height: 20px; left: 22%; animation-duration: 11s; animation-delay: 0.5s; }
        .bubble:nth-child(5) { width: 45px; height: 45px; left: 30%; animation-duration: 18s; animation-delay: 3s; }
        .bubble:nth-child(6) { width: 18px; height: 18px; left: 38%; animation-duration: 13s; animation-delay: 1.5s; }
        .bubble:nth-child(7) { width: 30px; height: 30px; left: 45%; animation-duration: 15s; animation-delay: 4s; }
        .bubble:nth-child(8) { width: 12px; height: 12px; left: 52%; animation-duration: 10s; animation-delay: 0s; }
        .bubble:nth-child(9) { width: 40px; height: 40px; left: 58%; animation-duration: 17s; animation-delay: 2.5s; }
        .bubble:nth-child(10) { width: 22px; height: 22px; left: 65%; animation-duration: 12s; animation-delay: 1s; }
        .bubble:nth-child(11) { width: 28px; height: 28px; left: 72%; animation-duration: 14s; animation-delay: 3.5s; }
        .bubble:nth-child(12) { width: 16px; height: 16px; left: 78%; animation-duration: 11s; animation-delay: 0.5s; }
        .bubble:nth-child(13) { width: 50px; height: 50px; left: 85%; animation-duration: 19s; animation-delay: 2s; }
        .bubble:nth-child(14) { width: 14px; height: 14px; left: 92%; animation-duration: 13s; animation-delay: 4s; }
        .bubble:nth-child(15) { width: 32px; height: 32px; left: 3%; animation-duration: 16s; animation-delay: 5s; }
        .bubble:nth-child(16) { width: 20px; height: 20px; left: 25%; animation-duration: 12s; animation-delay: 6s; }
        .bubble:nth-child(17) { width: 38px; height: 38px; left: 48%; animation-duration: 15s; animation-delay: 7s; }
        .bubble:nth-child(18) { width: 24px; height: 24px; left: 68%; animation-duration: 14s; animation-delay: 5.5s; }
        .bubble:nth-child(19) { width: 18px; height: 18px; left: 88%; animation-duration: 11s; animation-delay: 6.5s; }
        .bubble:nth-child(20) { width: 42px; height: 42px; left: 35%; animation-duration: 17s; animation-delay: 8s; }
        @keyframes rise {
            0% { bottom: -100px; opacity: 0; transform: translateX(0) scale(1); }
            10% { opacity: 0.8; }
            50% { opacity: 0.5; }
            100% { bottom: 110%; opacity: 0; transform: translateX(50px) scale(0.6); }
        }
        /* Floating Fish */
        .floating-fish {
            position: fixed;
            font-size: 24px;
            color: rgba(255, 255, 255, 0.7);
            animation: swimAcross 20s linear infinite;
            z-index: 0;
            pointer-events: none;
            filter: drop-shadow(0 0 8px rgba(255,255,255,0.3));
        }
        .floating-fish::before {
            content: '\f578';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }
        .fish-1 { top: 15%; font-size: 28px; animation-duration: 25s; animation-delay: 0s; color: rgba(255, 180, 100, 0.8); }
        .fish-2 { top: 35%; font-size: 20px; animation-duration: 18s; animation-delay: 5s; color: rgba(100, 200, 255, 0.8); transform: scaleX(-1); animation-name: swimAcrossReverse; }
        .fish-3 { top: 55%; font-size: 32px; animation-duration: 30s; animation-delay: 2s; color: rgba(255, 100, 150, 0.8); }
        .fish-4 { top: 75%; font-size: 18px; animation-duration: 15s; animation-delay: 8s; color: rgba(150, 255, 150, 0.8); transform: scaleX(-1); animation-name: swimAcrossReverse; }
        .fish-5 { top: 25%; font-size: 22px; animation-duration: 22s; animation-delay: 12s; color: rgba(255, 220, 100, 0.8); }
        .fish-6 { top: 65%; font-size: 26px; animation-duration: 28s; animation-delay: 6s; color: rgba(100, 255, 220, 0.8); transform: scaleX(-1); animation-name: swimAcrossReverse; }
        .fish-7 { top: 45%; font-size: 16px; animation-duration: 16s; animation-delay: 10s; color: rgba(220, 150, 255, 0.8); }
        .fish-8 { top: 85%; font-size: 24px; animation-duration: 20s; animation-delay: 4s; color: rgba(255, 150, 100, 0.8); transform: scaleX(-1); animation-name: swimAcrossReverse; }
        @keyframes swimAcross {
            0% { left: -50px; transform: scaleX(1) translateY(0); }
            25% { transform: scaleX(1) translateY(-15px); }
            50% { transform: scaleX(1) translateY(10px); }
            75% { transform: scaleX(1) translateY(-10px); }
            100% { left: calc(100% + 50px); transform: scaleX(1) translateY(0); }
        }
        @keyframes swimAcrossReverse {
            0% { right: -50px; left: auto; transform: scaleX(-1) translateY(0); }
            25% { transform: scaleX(-1) translateY(-15px); }
            50% { transform: scaleX(-1) translateY(10px); }
            75% { transform: scaleX(-1) translateY(-10px); }
            100% { right: calc(100% + 50px); left: auto; transform: scaleX(-1) translateY(0); }
        }
        .container {
            position: relative;
            z-index: 1;
        }
        .login-card {
            border: none;
            border-radius: 24px;
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(255, 255, 255, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            overflow: hidden;
            transform: translateY(0);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 
                0 35px 60px -15px rgba(0, 0, 0, 0.6),
                0 0 0 1px rgba(255, 255, 255, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.15);
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            border-radius: 24px 24px 0 0;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            animation: shimmer 8s linear infinite;
        }
        @keyframes shimmer {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .login-header h5, .login-header .nav-link {
            position: relative;
            z-index: 1;
        }
        .nav-pills .nav-link {
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }
        .nav-pills .nav-link:hover {
            background: rgba(255,255,255,0.2) !important;
            transform: scale(1.05);
        }
        .nav-pills .nav-link.active {
            background: rgba(255,255,255,0.25) !important;
            border: 1px solid rgba(255,255,255,0.3);
        }
        .card-body {
            padding: 2rem !important;
        }
        .form-control {
            border-radius: 12px;
            padding: 14px 18px;
            border: 2px solid #e9ecef;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
            background: #fff;
            transform: translateY(-2px);
        }
        .form-label {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            color: #495057;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 14px 28px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
            background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%);
        }
        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            border-radius: 12px;
            padding: 14px 28px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4);
        }
        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(17, 153, 142, 0.5);
            background: linear-gradient(135deg, #0e8a80 0%, #2ed970 100%);
        }
        .credential-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 10px;
            border-left: 4px solid;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
        }
        .credential-box:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .admin-cred  { border-color: #ef4444; background: linear-gradient(135deg, #fef2f2 0%, #fff 100%); }
        .staff-cred  { border-color: #667eea; background: linear-gradient(135deg, #eef2ff 0%, #fff 100%); }
        .cust-cred   { border-color: #10b981; background: linear-gradient(135deg, #ecfdf5 0%, #fff 100%); }
        .card-footer {
            background: linear-gradient(180deg, #f8f9fa 0%, #fff 100%) !important;
            border-top: 1px solid rgba(0,0,0,0.05);
            border-radius: 0 0 24px 24px !important;
            padding: 1.5rem !important;
        }
        .alert {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .brand-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.1) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            border: 2px solid rgba(255,255,255,0.3);
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        }
        .brand-icon i {
            font-size: 2rem;
        }
        .preview-box {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 1px solid #a7f3d0;
            border-radius: 12px;
            padding: 1rem;
        }
        /* Fish icon animation */
        @keyframes swim {
            0%, 100% { transform: translateX(0) rotate(0deg); }
            25% { transform: translateX(3px) rotate(2deg); }
            75% { transform: translateX(-3px) rotate(-2deg); }
        }
        .fa-fish {
            animation: swim 3s ease-in-out infinite;
            display: inline-block;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-4">

    <!-- Floating Bubbles Animation -->
    <div class="floating-bubbles">
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
    </div>

    <!-- Floating Fish Animation -->
    <div class="floating-fish fish-1"></div>
    <div class="floating-fish fish-2"></div>
    <div class="floating-fish fish-3"></div>
    <div class="floating-fish fish-4"></div>
    <div class="floating-fish fish-5"></div>
    <div class="floating-fish fish-6"></div>
    <div class="floating-fish fish-7"></div>
    <div class="floating-fish fish-8"></div>

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
                    <h3 class="fw-bold mb-0">Aquamart</h3>
                    <p class="text-light small mb-0">Management Portal</p>
                    <p class="text-light" style="font-size:0.78rem;">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        Manjakuppam, Cuddalore – 607001
                    </p>
                </div>

                <div class="card login-card">
                    <!-- Card Header with Tabs -->
                    <div class="login-header text-white text-center">
                        <div class="brand-icon">
                            <i class="fas fa-fish"></i>
                        </div>
                        <h5 class="mb-1 fw-bold">Welcome to Aquamart</h5>
                        <p class="mb-3 opacity-75" style="font-size: 0.85rem;">Your Premium Fish & Aquarium Store</p>
                        <ul class="nav nav-pills justify-content-center gap-2">
                            <li class="nav-item">
                                <button class="nav-link active text-white px-4"
                                        data-bs-toggle="pill" data-bs-target="#loginTab"
                                        id="loginTabBtn">
                                    <i class="fas fa-sign-in-alt me-1"></i>Login
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link text-white px-4"
                                        data-bs-toggle="pill" data-bs-target="#registerTab"
                                        id="registerTabBtn">
                                    <i class="fas fa-user-plus me-1"></i>Register
                                </button>
                            </li>
                        </ul>
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

                        <div class="tab-content">
                            <!-- LOGIN TAB -->
                            <div class="tab-pane fade show active" id="loginTab">
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

                            <!-- REGISTER TAB -->
                            <div class="tab-pane fade" id="registerTab">
                                <form action="register.php" method="POST">
                                    <div class="mb-3">
                                        <label for="reg_name" class="form-label fw-semibold">
                                            <i class="fas fa-user me-1 text-success"></i>Your Full Name
                                        </label>
                                        <input type="text" class="form-control" id="reg_name" name="full_name"
                                               placeholder="e.g. Ravi Kumar" required>
                                    </div>
                                    <div class="mb-3 preview-box">
                                        <p class="mb-1 text-muted" style="font-size:0.8rem;"><i class="fas fa-key me-1"></i>Your auto-generated login:</p>
                                        <div><small class="text-muted">Username:</small> <strong id="preview_user" class="text-primary">—</strong></div>
                                        <div><small class="text-muted">Password:</small> <strong id="preview_pass" class="text-success">—</strong></div>
                                    </div>
                                    <div class="d-grid mt-3">
                                        <button type="submit" class="btn btn-success btn-lg fw-semibold">
                                            <i class="fas fa-user-plus me-2"></i>Create Account &amp; Login
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Credential Hints -->
                    <div class="card-footer bg-light px-4 py-3">
                        <p class="text-muted text-center mb-2" style="font-size:0.8rem;">
                            <i class="fas fa-info-circle me-1"></i><strong>Staff Login Credentials</strong>
                        </p>
                        <div class="credential-box admin-cred">
                            <span class="badge bg-danger me-1" style="font-size:0.72rem;">Admin</span>
                            <small><strong>harini</strong> / harini@123</small>
                        </div>
                        <div class="credential-box staff-cred">
                            <span class="badge bg-primary me-1" style="font-size:0.72rem;">Staff</span>
                            <small><strong>kalpana</strong> / kalpana@123</small>
                        </div>
                        <div class="credential-box cust-cred">
                            <span class="badge bg-success me-1" style="font-size:0.72rem;">Customer</span>
                            <small>Use <em>New Customer</em> tab &rarr; password is <em>yourname@123</em></small>
                        </div>
                    </div>
                </div>

                <p class="text-center text-light mt-3" style="font-size:0.78rem;">
                    <a href="shop.php" class="text-warning text-decoration-none">
                        <i class="fas fa-fish me-1"></i>Browse shop without logging in
                    </a>
                </p>

                <p class="text-center text-light mt-1" style="font-size:0.78rem;">
                    &copy; <?php echo date('Y'); ?> Aquamart &amp; Fish Shop, Cuddalore
                </p>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-preview username/password as user types their name
        document.getElementById('reg_name').addEventListener('input', function () {
            var name = this.value.trim().toLowerCase().replace(/\s+/g, '');
            document.getElementById('preview_user').textContent = name || '—';
            document.getElementById('preview_pass').textContent = name ? name + '@123' : '—';
        });

        // Auto-switch to register tab if URL has ?tab=register
        if (new URLSearchParams(window.location.search).get('tab') === 'register') {
            document.getElementById('registerTabBtn').click();
        }
    </script>
</body>
</html>
