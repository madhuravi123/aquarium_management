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

$customers = $conn->query("SELECT * FROM customers ORDER BY id DESC");
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Customer Management</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo htmlspecialchars($_SESSION['msg_type']); ?> alert-dismissible fade show">
                <i class="fas fa-info-circle me-1"></i>
                <?php echo htmlspecialchars($_SESSION['message']);
                unset($_SESSION['message'], $_SESSION['msg_type']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
            <i class="fas fa-plus"></i> Add New Customer
        </button>

        <div class="card card-custom">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>City</th>
                                <th>Pincode</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; while ($row = $customers->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone'] ?? '—'); ?></td>
                                    <td><?php echo htmlspecialchars($row['email'] ?? '—'); ?></td>
                                    <td><?php echo htmlspecialchars($row['city'] ?? '—'); ?></td>
                                    <td><?php echo htmlspecialchars($row['pincode'] ?? '—'); ?></td>
                                    <td class="small text-muted"><?php echo htmlspecialchars($row['address'] ?? '—'); ?></td>
                                    <td>
                                        <a href="customers_action.php?delete=<?php echo $row['id']; ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Delete customer \'<?php echo htmlspecialchars(addslashes($row['name'])); ?>\'?\n\nNote: All linked sales and invoice records will also be removed.')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="customers_action.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Arjun Kumar" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" placeholder="e.g. 9876543210"
                                   pattern="[0-9]{10}" title="Enter a valid 10-digit phone number" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="e.g. name@email.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">City <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control" placeholder="e.g. Cuddalore" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Pincode <span class="text-danger">*</span></label>
                            <input type="text" name="pincode" class="form-control" placeholder="e.g. 607001"
                                   pattern="[0-9]{6}" title="Enter a valid 6-digit pincode" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Street / Area</label>
                            <input type="text" name="address" class="form-control" placeholder="Door no., Street name, Area">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
