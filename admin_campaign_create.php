<?php
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Middleware.php';

// Super Admin or NGO Admin
Middleware::role([1, 2]);

$pdo = getDatabase();
$error = '';
$success = '';

// Fetch categories
$categories = [];
try {
    $stmt = $pdo->query("SELECT id, name FROM campaign_categories WHERE status = 'active'");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $short_description = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $target_amount = (float)($_POST['target_amount'] ?? 0);
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    
    // Simple slug generator
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    
    if (empty($name) || empty($category_id) || empty($description) || empty($target_amount) || empty($start_date) || empty($end_date)) {
        $error = "All fields are required.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO campaigns (category_id, name, slug, short_description, description, target_amount, start_date, end_date, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)");
            $stmt->execute([$category_id, $name, $slug . '-' . time(), $short_description, $description, $target_amount, $start_date, $end_date, $_SESSION['user_id']]);
            $success = "Campaign created successfully!";
            
            // Redirect to dashboard after 2 seconds
            header("refresh:2;url=admin_dashboard.php");
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Campaign | <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: var(--text-dark); }
        .form-control { width: 100%; padding: 10px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; font-family: inherit; }
        .alert-success { padding: 15px; background: rgba(16,185,129,0.1); color: var(--success); border-radius: 8px; margin-bottom: 20px; }
        .alert-danger { padding: 15px; background: rgba(239,68,68,0.1); color: var(--danger); border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="dashboard-layout">
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>
        <div class="page-content">
            <div class="page-header">
                <div class="page-title">
                    <h1>Create Campaign</h1>
                </div>
                <a href="admin_dashboard.php" class="btn-primary" style="background: white; color: var(--text-dark); border: 1px solid rgba(0,0,0,0.1); text-decoration: none;">Back to Dashboard</a>
            </div>
            
            <div class="glass-card" style="max-width: 800px; margin: 0 auto;">
                <?php if ($error): ?><div class="alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert-success"><?php echo htmlspecialchars($success); ?></div><?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Campaign Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Short Description</label>
                        <input type="text" name="short_description" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Detailed Description</label>
                        <textarea name="description" class="form-control" rows="5" required></textarea>
                    </div>
                    <div style="display: flex; gap: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Target Amount ($)</label>
                            <input type="number" step="0.01" name="target_amount" class="form-control" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top: 20px;">
                        <button type="submit" class="btn-primary" style="width: 100%;">Create Campaign</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
<script src="assets/js/dashboard.js"></script>
</body>
</html>
