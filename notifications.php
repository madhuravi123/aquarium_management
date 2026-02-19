<?php
session_start();
require_once 'config/db_connect.php';
include 'includes/header.php';

// Marking all as read when visiting? Or individual. Let's list them.
$notifs = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 50");
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="flex-grow-1 p-4">
        <h2>Notifications</h2>
        <div class="card card-custom mt-3">
            <div class="card-body">
                <ul class="list-group">
                    <?php while ($row = $notifs->fetch_assoc()): ?>
                        <li
                            class="list-group-item d-flex justify-content-between align-items-center <?php echo $row['is_read'] ? 'bg-light' : ''; ?>">
                            <div>
                                <span class="badge bg-primary rounded-pill">
                                    <?php echo ucfirst($row['type']); ?>
                                </span>
                                <?php echo $row['message']; ?>
                            </div>
                            <small class="text-muted">
                                <?php echo $row['created_at']; ?>
                            </small>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php
// Mark seen
$conn->query("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
include 'includes/footer.php';
?>