<?php
// coordinator_profile.php
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Middleware.php';
require_once __DIR__ . '/core/Security.php';
require_once __DIR__ . '/includes/dashboard/components.php';

// Protect this dashboard: Only Event Coordinator (Role ID 5) can access
Middleware::role([5]);

$pdo = getDatabase();
$user_id = $_SESSION['user_id'];

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success_msg = '';
$error_msg = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_msg = "Invalid security token.";
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            $full_name = htmlspecialchars(trim($_POST['full_name']));
            $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
            $phone = htmlspecialchars(trim($_POST['phone']));
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error_msg = "Please provide a valid email address.";
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
                    $stmt->execute([$full_name, $email, $phone, $user_id]);
                    $_SESSION['full_name'] = $full_name; // Update session
                    $_SESSION['email'] = $email;
                    $success_msg = "Coordinator Profile updated successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Error updating profile. Email might be already in use.";
                }
            }
        } elseif ($action === 'update_password') {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            if ($new_password !== $confirm_password) {
                $error_msg = "New passwords do not match.";
            } elseif (strlen($new_password) < 8) {
                $error_msg = "New password must be at least 8 characters long.";
            } else {
                // Verify current password
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($current_password, $user['password'])) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $updateStmt->execute([$hashed_password, $user_id]);
                    $success_msg = "Password updated successfully.";
                } else {
                    $error_msg = "Current password is incorrect.";
                }
            }
        }
    }
}

// Fetch current user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Coordinator Profile | <?php echo htmlspecialchars(APP_NAME); ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem; color: var(--text-dark); }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 8px;
            background: white;
            font-family: var(--font-body);
            transition: all 0.3s ease;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124, 154, 134, 0.2);
        }
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
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 5px;">Manage Your Account</p>
                    <h1>Event Coordinator Profile</h1>
                </div>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success" style="padding: 15px; background: rgba(39, 174, 96, 0.1); color: #27ae60; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_msg): ?>
                <div class="alert alert-danger" style="padding: 15px; background: rgba(231, 76, 60, 0.1); color: #e74c3c; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                
                <div class="glass-card" style="flex: 1; min-width: 300px;">
                    <div class="card-header">
                        <h3 class="card-title">Personal Information</h3>
                    </div>
                    <form method="POST" action="coordinator_profile.php">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-group">
                            <label>Assigned Role</label>
                            <input type="text" value="Event Coordinator" disabled style="background: rgba(0,0,0,0.02); color: var(--text-muted);">
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 5px;">Role change requires Super Admin intervention.</p>
                        </div>
                        
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        
                        <button type="submit" class="btn-primary" style="margin-top: 15px; width: 100%; justify-content: center;"><i class="fas fa-save"></i> Update Profile</button>
                    </form>
                </div>
                
                <div class="glass-card" style="flex: 1; min-width: 300px;">
                    <div class="card-header">
                        <h3 class="card-title">Change Password</h3>
                    </div>
                    <form method="POST" action="coordinator_profile.php">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="update_password">
                        
                        <div class="form-group">
                            <label>Current Password *</label>
                            <input type="password" name="current_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label>New Password *</label>
                            <input type="password" name="new_password" required minlength="8">
                        </div>
                        
                        <div class="form-group">
                            <label>Confirm New Password *</label>
                            <input type="password" name="confirm_password" required minlength="8">
                        </div>
                        
                        <button type="submit" class="btn-primary" style="margin-top: 15px; width: 100%; justify-content: center;"><i class="fas fa-key"></i> Change Password</button>
                    </form>
                </div>
            </div>
            
        </div>
    </main>
</div>

<script src="assets/js/dashboard.js"></script>
</body>
</html>
