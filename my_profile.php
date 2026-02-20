<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: index.php");
    exit();
}
require_once 'config/db_connect.php';
$page_title = 'My Profile';

$user_id = (int)$_SESSION['user_id'];
$success = '';
$error   = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $full_name = trim($_POST['full_name'] ?? '');
        $phone     = trim($_POST['phone'] ?? '');
        $address   = trim($_POST['address'] ?? '');

        if (!$full_name || !$phone) {
            $error = 'Full name and phone are required.';
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->bind_param("sssi", $full_name, $phone, $address, $user_id);
            if ($stmt->execute()) {
                $_SESSION['full_name'] = $full_name;
                $success = 'Profile updated successfully!';
            } else {
                $error = 'Failed to update profile. Please try again.';
            }
        }
    } elseif ($action === 'change_password') {
        $current_pw  = $_POST['current_password'] ?? '';
        $new_pw      = $_POST['new_password'] ?? '';
        $confirm_pw  = $_POST['confirm_password'] ?? '';

        // Fetch current hash
        $pw_res = $conn->query("SELECT password FROM users WHERE id = $user_id");
        $pw_row = $pw_res->fetch_assoc();

        if (!password_verify($current_pw, $pw_row['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new_pw) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($new_pw !== $confirm_pw) {
            $error = 'New passwords do not match.';
        } else {
            $new_hash = password_hash($new_pw, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $new_hash, $user_id);
            if ($stmt->execute()) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password. Please try again.';
            }
        }
    }
}

// Fetch fresh user data
$user_res = $conn->query("SELECT username, full_name, phone, address, created_at FROM users WHERE id = $user_id");
$user = $user_res->fetch_assoc();

// Order stats
$stats = $conn->query("
    SELECT COUNT(*) as total_orders, COALESCE(SUM(final_amount),0) as total_spent
    FROM sales WHERE user_id = $user_id
")->fetch_assoc();

include 'includes/customer_header.php';
?>

<div class="container py-4">
    <h4 class="fw-bold mb-1"><i class="fas fa-user-circle me-2 text-primary"></i>My Profile</h4>
    <p class="text-muted mb-4">Manage your personal information and account settings.</p>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Profile Card -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body py-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                         style="width:80px;height:80px;font-size:2rem;">
                        <?php echo strtoupper(substr($user['full_name'] ?? $user['username'], 0, 1)); ?>
                    </div>
                    <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></h5>
                    <p class="text-muted small mb-3">@<?php echo htmlspecialchars($user['username']); ?></p>
                    <span class="badge bg-success mb-3">Customer</span>
                    <div class="row text-center border-top pt-3">
                        <div class="col-6 border-end">
                            <h5 class="fw-bold text-primary mb-0"><?php echo $stats['total_orders']; ?></h5>
                            <small class="text-muted">Orders</small>
                        </div>
                        <div class="col-6">
                            <h5 class="fw-bold text-success mb-0">&#8377;<?php echo number_format($stats['total_spent'], 0); ?></h5>
                            <small class="text-muted">Spent</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3 border-0 bg-light">
                <div class="card-body">
                    <small class="text-muted">
                        <i class="fas fa-calendar me-1"></i>
                        Member since: <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                    </small>
                </div>
            </div>
        </div>

        <!-- Edit Profile + Change Password -->
        <div class="col-md-8">
            <!-- Edit Profile -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fas fa-user-edit me-2"></i>Edit Profile
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" required
                                   value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Username</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            <small class="text-muted">Username cannot be changed.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" required
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="10-digit mobile number">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Delivery Address</label>
                            <textarea name="address" class="form-control" rows="3"
                                      placeholder="Your full delivery address..."><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary fw-semibold">
                            <i class="fas fa-save me-1"></i>Save Changes
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-secondary text-white fw-bold">
                    <i class="fas fa-lock me-2"></i>Change Password
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                            <small class="text-muted">Minimum 6 characters.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-secondary fw-semibold">
                            <i class="fas fa-key me-1"></i>Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="text-center text-muted py-4 mt-5 border-top bg-white">
    <small>&copy; <?php echo date('Y'); ?> Harini Aquarium &amp; Fish Shop, Cuddalore</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
