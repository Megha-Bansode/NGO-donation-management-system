<?php
// admin_settings.php
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Middleware.php';
require_once __DIR__ . '/includes/dashboard/components.php';

// Protect this dashboard: Only Super Admin (Role ID 1) can access
Middleware::role([1]);

$pdo = getDatabase();

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success_msg = '';
$error_msg = '';

// Check if settings row exists, if not, create it
$settingsCount = $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
if ($settingsCount == 0) {
    $pdo->query("INSERT INTO settings (ngo_name) VALUES ('Arohan Foundation')");
}

// Active tab
$tab = $_GET['tab'] ?? 'org';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_msg = "Invalid security token.";
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            $ngo_name = htmlspecialchars(trim($_POST['ngo_name']));
            $email = htmlspecialchars(trim($_POST['email']));
            $phone = htmlspecialchars(trim($_POST['phone']));
            $website = htmlspecialchars(trim($_POST['website']));
            $address = htmlspecialchars(trim($_POST['address']));
            $mission = htmlspecialchars(trim($_POST['mission']));
            $vision = htmlspecialchars(trim($_POST['vision']));
            $social_media_links = htmlspecialchars(trim($_POST['social_media_links'])); // Can be JSON string or simple text
            
            if ($ngo_name && $email) {
                try {
                    $stmt = $pdo->prepare("UPDATE settings SET 
                        ngo_name = ?, email = ?, phone = ?, website = ?, 
                        address = ?, mission = ?, vision = ?, social_media_links = ? 
                        WHERE id = 1"); // Assuming id 1 is the main settings row
                    
                    $stmt->execute([$ngo_name, $email, $phone, $website, $address, $mission, $vision, $social_media_links]);
                    $success_msg = "Organization profile updated successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Database error: " . $e->getMessage();
                }
            } else {
                $error_msg = "Organization Name and Email are required.";
            }
        } elseif ($action === 'update_personal_profile') {
            $full_name = htmlspecialchars(trim($_POST['full_name']));
            $email = htmlspecialchars(trim($_POST['email']));
            $phone = htmlspecialchars(trim($_POST['phone']));
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if ($full_name && $email) {
                try {
                    // Check if email already taken
                    $dupCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
                    $dupCheck->execute([$email, $_SESSION['user_id']]);
                    if ($dupCheck->fetchColumn() > 0) {
                        $error_msg = "This email address is already registered to another account.";
                    } else {
                        if ($password !== '') {
                            if (strlen($password) < 6) {
                                $error_msg = "New password must be at least 6 characters long.";
                            } elseif ($password !== $confirm_password) {
                                $error_msg = "Confirm password does not match the new password.";
                            } else {
                                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, password = ? WHERE id = ?");
                                $stmt->execute([$full_name, $email, $phone, $hashed_password, $_SESSION['user_id']]);
                                $success_msg = "Personal profile and password updated successfully.";
                                $_SESSION['full_name'] = $full_name;
                                $_SESSION['email'] = $email;
                            }
                        } else {
                            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
                            $stmt->execute([$full_name, $email, $phone, $_SESSION['user_id']]);
                            $success_msg = "Personal profile updated successfully.";
                            $_SESSION['full_name'] = $full_name;
                            $_SESSION['email'] = $email;
                        }
                    }
                } catch (PDOException $e) {
                    $error_msg = "Database error: " . $e->getMessage();
                }
            } else {
                $error_msg = "Full Name and Email are required fields.";
            }
        }
    }
}

// Fetch Current Settings
try {
    $settings = $pdo->query("SELECT * FROM settings ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $settings = [];
}

// Fetch Personal Profile Details
try {
    $userStmt = $pdo->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
    $userStmt->execute([$_SESSION['user_id']]);
    $currentUser = $userStmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $currentUser = [];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings | <?php echo htmlspecialchars(APP_NAME); ?></title>
    
    <!-- Premium Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Dashboard Core CSS -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
    
    <style>
        .settings-layout {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
        }
        @media (max-width: 992px) {
            .settings-layout {
                grid-template-columns: 1fr;
            }
        }
        
        .settings-nav {
            background: white;
            border-radius: 16px;
            padding: 20px 0;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
            align-self: start;
        }
        .settings-nav-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 25px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .settings-nav-item:hover {
            background: rgba(0,0,0,0.02);
            color: var(--text-dark);
        }
        .settings-nav-item.active {
            color: var(--primary);
            background: rgba(124, 154, 134, 0.05);
            border-left-color: var(--primary);
        }
        
        .settings-card {
            background: white;
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
            overflow: hidden;
        }
        .settings-header {
            padding: 25px 30px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .settings-header h3 {
            margin: 0;
            font-size: 1.2rem;
            color: var(--text-dark);
        }
        .settings-header p {
            margin: 5px 0 0 0;
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        .settings-body {
            padding: 30px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
        }
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.9rem;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 8px;
            font-family: var(--font-body);
            background: #f9f9f9;
            transition: all 0.2s;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124, 154, 134, 0.2);
            background: white;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .settings-footer {
            padding: 20px 30px;
            background: #fafafa;
            border-top: 1px solid rgba(0,0,0,0.05);
            display: flex;
            justify-content: flex-end;
        }
    </style>
</head>
<body>

<div class="dashboard-layout">
    <!-- Sidebar -->
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>

    <main class="main-content">
        <!-- Topbar -->
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>

        <div class="page-content">
            <!-- Header Section -->
            <div class="page-header">
                <div class="page-title">
                    <h1>System Settings</h1>
                    <div class="breadcrumb">
                        <span>Dashboard</span>
                        <span style="margin: 0 8px;">/</span>
                        <span style="color: var(--primary);">Settings</span>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <?php if ($success_msg): ?>
                <div style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.2);">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_msg); ?>
                </div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div style="background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.2);">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>

            <div class="settings-layout">
                <!-- Navigation -->
                <div class="settings-nav">
                    <a href="admin_settings.php?tab=org" class="settings-nav-item <?php echo $tab === 'org' ? 'active' : ''; ?>">
                        <i class="fas fa-building"></i> Organization Profile
                    </a>
                    <a href="admin_settings.php?tab=profile" class="settings-nav-item <?php echo $tab === 'profile' ? 'active' : ''; ?>">
                        <i class="fas fa-user-circle"></i> Personal Profile
                    </a>
                    <a href="#" class="settings-nav-item" onclick="alert('Coming soon: Payment Gateway configurations.')">
                        <i class="fas fa-credit-card"></i> Payment Settings
                    </a>
                    <a href="#" class="settings-nav-item" onclick="alert('Coming soon: Email SMTP configurations.')">
                        <i class="fas fa-envelope"></i> Email Integrations
                    </a>
                    <a href="#" class="settings-nav-item" onclick="alert('Coming soon: Platform configuration.')">
                        <i class="fas fa-sliders-h"></i> Platform Preferences
                    </a>
                </div>

                <!-- Content -->
                <div class="settings-card">
                    <?php if ($tab === 'profile'): ?>
                        <div class="settings-header">
                            <h3>Personal Profile</h3>
                            <p>Manage your account name, contact details, and password settings.</p>
                        </div>
                        
                        <form method="POST" action="admin_settings.php?tab=profile">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="action" value="update_personal_profile">
                            
                            <div class="settings-body">
                                <!-- Initials Avatar Circle -->
                                <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 25px; background: rgba(124,154,134,0.05); padding: 15px 20px; border-radius: 12px; border: 1px dashed rgba(124,154,134,0.2);">
                                    <div style="width: 60px; height: 60px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 800; border: 3px solid white; box-shadow: var(--shadow-sm); flex-shrink: 0;">
                                        <?php echo strtoupper(substr($currentUser['full_name'] ?? 'U', 0, 1)); ?>
                                    </div>
                                    <div>
                                        <h4 style="margin: 0 0 4px 0; color: var(--text-dark); font-size: 1.05rem; font-weight: 700;"><?php echo htmlspecialchars($currentUser['full_name'] ?? ''); ?></h4>
                                        <p style="margin: 0; color: var(--text-muted); font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;"><?php echo htmlspecialchars($currentUser['role_name'] ?? 'Super Administrator'); ?></p>
                                    </div>
                                </div>
                                
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label>Full Name *</label>
                                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($currentUser['full_name'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Email Address *</label>
                                        <input type="email" name="email" value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="form-group full-width">
                                        <label>Phone Number</label>
                                        <input type="text" name="phone" value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>New Password (Leave blank to keep current)</label>
                                        <input type="password" name="password" placeholder="Min. 6 characters">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Confirm New Password</label>
                                        <input type="password" name="confirm_password" placeholder="Confirm password">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="settings-footer">
                                <button type="submit" class="btn-primary" style="padding: 12px 25px;">
                                    <i class="fas fa-save"></i> Save Profile Details
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="settings-header">
                            <h3>Organization Profile</h3>
                            <p>Manage the public details of your NGO shown across the platform.</p>
                        </div>
                        
                        <form method="POST" action="admin_settings.php?tab=org">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="settings-body">
                                <div class="form-grid">
                                    <div class="form-group full-width">
                                        <label>Organization Name *</label>
                                        <input type="text" name="ngo_name" value="<?php echo htmlspecialchars($settings['ngo_name'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Contact Email *</label>
                                        <input type="email" name="email" value="<?php echo htmlspecialchars($settings['email'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Phone Number</label>
                                        <input type="text" name="phone" value="<?php echo htmlspecialchars($settings['phone'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="form-group full-width">
                                        <label>Website URL</label>
                                        <input type="url" name="website" value="<?php echo htmlspecialchars($settings['website'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="form-group full-width">
                                        <label>Office Address</label>
                                        <textarea name="address"><?php echo htmlspecialchars($settings['address'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group full-width">
                                        <label>Mission Statement</label>
                                        <textarea name="mission"><?php echo htmlspecialchars($settings['mission'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group full-width">
                                        <label>Vision Statement</label>
                                        <textarea name="vision"><?php echo htmlspecialchars($settings['vision'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="form-group full-width">
                                        <label>Social Media Links</label>
                                        <textarea name="social_media_links" placeholder="E.g., Facebook: https://..., Twitter: https://..."><?php echo htmlspecialchars($settings['social_media_links'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="settings-footer">
                                <button type="submit" class="btn-primary" style="padding: 12px 25px;">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
    </main>
</div>

<script src="assets/js/dashboard.js"></script>
</body>
</html>
