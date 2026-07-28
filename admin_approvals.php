<?php
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Middleware.php';

// Super Admin only
Middleware::role([1]);

$pdo = getDatabase();

// Handle Approval Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'], $_POST['type'])) {
    $id = (int)$_POST['id'];
    $action = $_POST['action'] === 'approve' ? 'approved' : 'rejected';
    $type = $_POST['type'];

    try {
        if ($type === 'campaign') {
            $status = $_POST['action'] === 'approve' ? 'active' : 'cancelled';
            $stmt = $pdo->prepare("UPDATE campaigns SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
        } elseif ($type === 'user') {
            $status = $_POST['action'] === 'approve' ? 'active' : 'banned';
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
        }
        // Redirect to prevent form resubmission
        header("Location: admin_approvals.php?success=1");
        exit;
    } catch (PDOException $e) {
        $error = "Error updating status: " . $e->getMessage();
    }
}

// Fetch pending items
$pending_campaigns = [];
$pending_users = [];

try {
    $stmt = $pdo->query("SELECT id, name, created_at, 'campaign' as type FROM campaigns WHERE status = 'draft'");
    $pending_campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT id, full_name as name, created_at, 'user' as type FROM users WHERE status = 'inactive'");
    $pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

$all_pending = array_merge($pending_campaigns, $pending_users);
usort($all_pending, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Approvals | <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
<div class="dashboard-layout">
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>
        <div class="page-content">
            <div class="page-header">
                <div class="page-title">
                    <h1>Pending Approvals</h1>
                </div>
            </div>
            
            <div class="glass-card">
                <?php if (isset($_GET['success'])): ?>
                    <div style="padding: 15px; background: rgba(16,185,129,0.1); color: var(--success); border-radius: 8px; margin-bottom: 20px;">Status updated successfully.</div>
                <?php endif; ?>

                <?php if (empty($all_pending)): ?>
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--success); margin-bottom: 15px;"></i>
                        <h3>All caught up!</h3>
                        <p>There are no pending items requiring your approval.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Name</th>
                                    <th>Date Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($all_pending as $item): ?>
                                <tr>
                                    <td><span class="status-badge status-pending"><?php echo ucfirst($item['type']); ?></span></td>
                                    <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                                    <td><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="type" value="<?php echo $item['type']; ?>">
                                            <button type="submit" name="action" value="approve" class="btn-primary" style="padding: 5px 10px; font-size: 0.8rem; background: var(--success); border: none;"><i class="fas fa-check"></i> Approve</button>
                                            <button type="submit" name="action" value="reject" class="btn-primary" style="padding: 5px 10px; font-size: 0.8rem; background: var(--danger); border: none;"><i class="fas fa-times"></i> Reject</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
<script src="assets/js/dashboard.js"></script>
</body>
</html>
