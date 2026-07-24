<?php
// admin_notifications.php
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

// Helper function for relative time
function get_relative_time($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$string) return 'just now';
    return array_slice($string, 0, 1)[0] . ' ago';
}

// Handle GET actions
if (isset($_GET['action'])) {
    $get_action = $_GET['action'];
    if ($get_action === 'mark_all_read') {
        try {
            $stmt = $pdo->prepare("UPDATE notifications SET read_status = 1 WHERE recipient_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $success_msg = "All notifications marked as read.";
        } catch (PDOException $e) {
            $error_msg = "Database error: " . $e->getMessage();
        }
    } elseif ($get_action === 'mark_read') {
        $notif_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if ($notif_id) {
            try {
                $stmt = $pdo->prepare("UPDATE notifications SET read_status = 1 WHERE id = ? AND recipient_id = ?");
                $stmt->execute([$notif_id, $_SESSION['user_id']]);
                $success_msg = "Notification marked as read.";
            } catch (PDOException $e) {
                $error_msg = "Database error: " . $e->getMessage();
            }
        }
    } elseif ($get_action === 'delete_notif') {
        $notif_id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
        if ($notif_id) {
            try {
                $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND recipient_id = ?");
                $stmt->execute([$notif_id, $_SESSION['user_id']]);
                $success_msg = "Notification deleted.";
            } catch (PDOException $e) {
                $error_msg = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_msg = "Invalid security token.";
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'send_broadcast') {
            $title = htmlspecialchars(trim($_POST['title']));
            $message = htmlspecialchars(trim($_POST['message']));
            $target = $_POST['target'];
            $type = 'system';
            
            if ($title && $message && $target) {
                try {
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare("INSERT INTO notifications (recipient_id, title, message, notification_type) VALUES (?, ?, ?, ?)");
                    $count = 0;
                    
                    if ($target === 'all') {
                        $users = $pdo->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
                    } else if (strpos($target, 'role_') === 0) {
                        $role_id = (int) str_replace('role_', '', $target);
                        $users = $pdo->prepare("SELECT id FROM users WHERE role_id = ?");
                        $users->execute([$role_id]);
                        $users = $users->fetchAll(PDO::FETCH_COLUMN);
                    } else if (strpos($target, 'user_') === 0) {
                        $users = [(int) str_replace('user_', '', $target)];
                    } else {
                        $users = [];
                    }
                    
                    foreach ($users as $uid) {
                        $stmt->execute([$uid, $title, $message, $type]);
                        $count++;
                    }
                    
                    $pdo->commit();
                    $success_msg = "Broadcast sent successfully to $count users.";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error_msg = "Failed to send broadcast: " . $e->getMessage();
                }
            } else {
                $error_msg = "Please fill in all required fields.";
            }
        }
    }
}

// Active Tab
$tab = $_GET['tab'] ?? 'inbox';

// Fetch Roles for dropdown
$roles = $pdo->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);

// Fetch Unread Received Count for Badge
try {
    $unreadCountStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE recipient_id = ? AND read_status = 0");
    $unreadCountStmt->execute([$_SESSION['user_id']]);
    $totalUnreadInbox = $unreadCountStmt->fetchColumn();
} catch (PDOException $e) {
    $totalUnreadInbox = 0;
}

// Tab 1: Fetch received notifications for inbox
$inbox_page = isset($_GET['inbox_page']) ? max(1, filter_var($_GET['inbox_page'], FILTER_VALIDATE_INT)) : 1;
$inbox_limit = 8;
$inbox_offset = ($inbox_page - 1) * $inbox_limit;
try {
    $inboxCountStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE recipient_id = ?");
    $inboxCountStmt->execute([$_SESSION['user_id']]);
    $totalInbox = $inboxCountStmt->fetchColumn();
    $totalInboxPages = ceil($totalInbox / $inbox_limit);

    $inboxStmt = $pdo->prepare("SELECT * FROM notifications WHERE recipient_id = ? ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $inboxStmt->bindValue(':limit', $inbox_limit, PDO::PARAM_INT);
    $inboxStmt->bindValue(':offset', $inbox_offset, PDO::PARAM_INT);
    $inboxStmt->execute();
    $inboxNotifications = $inboxStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $inboxNotifications = [];
    $totalInboxPages = 1;
}

// Tab 2: Fetch recent broadcasts sent (grouped for display in admin history)
$page = isset($_GET['page']) ? max(1, filter_var($_GET['page'], FILTER_VALIDATE_INT)) : 1;
$limit = 8;
$offset = ($page - 1) * $limit;
try {
    $countStmt = $pdo->query("SELECT COUNT(DISTINCT title, message, created_at) FROM notifications WHERE notification_type = 'system'");
    $totalGroups = $countStmt->fetchColumn();
    $totalPages = ceil($totalGroups / $limit);

    $query = "SELECT title, message, notification_type, created_at, COUNT(id) as recipient_count, SUM(read_status) as read_count 
              FROM notifications 
              WHERE notification_type = 'system' 
              GROUP BY title, message, created_at 
              ORDER BY created_at DESC 
              LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $broadcasts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $broadcasts = [];
    $totalPages = 1;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications & Communications | <?php echo htmlspecialchars(APP_NAME); ?></title>
    
    <!-- Premium Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Dashboard Core CSS -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
    
    <style>
        .tabs-container {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding-bottom: 10px;
        }
        .tab-item {
            padding: 10px 24px;
            border-radius: 30px;
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 600;
            transition: all var(--transition-fast);
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .tab-item.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 10px rgba(124, 154, 134, 0.2);
        }
        .tab-item:hover:not(.active) {
            background: rgba(0,0,0,0.04);
            color: var(--text-dark);
        }
        .tab-badge {
            background: var(--danger);
            color: white;
            font-size: 0.7rem;
            padding: 2px 7px;
            border-radius: 20px;
            font-weight: 700;
        }
        
        .layout-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 25px;
        }
        @media (max-width: 992px) {
            .layout-grid { grid-template-columns: 1fr; }
        }
        
        .form-card {
            background: white;
            padding: 25px;
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.04);
            box-shadow: var(--shadow-sm);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.9rem;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 8px;
            font-family: var(--font-body);
            transition: all 0.2s;
            background: #f9f9f9;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124, 154, 134, 0.2);
            background: white;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        /* Notifications List styling */
        .notif-card {
            background: white;
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.04);
            box-shadow: var(--shadow-sm);
            margin-bottom: 15px;
            padding: 20px;
            display: flex;
            gap: 15px;
            align-items: flex-start;
            position: relative;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .notif-card.unread {
            border-left: 4px solid var(--primary);
            background: rgba(124, 154, 134, 0.02);
        }
        .notif-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .notif-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .notif-icon.system { background: rgba(124, 154, 134, 0.1); color: var(--primary); }
        .notif-icon.alert { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
        
        .notif-content {
            flex: 1;
        }
        .notif-title {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 1rem;
            margin-bottom: 4px;
        }
        .notif-msg {
            color: var(--text-body);
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 8px;
        }
        .notif-meta {
            font-size: 0.75rem;
            color: var(--text-muted);
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .notif-actions {
            display: flex;
            gap: 8px;
        }
        .notif-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 0.9rem;
            transition: color 0.2s;
            padding: 4px;
        }
        .notif-btn:hover {
            color: var(--primary);
        }
        .notif-btn.delete-btn:hover {
            color: var(--danger);
        }
        
        .broadcast-item {
            padding: 20px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }
        .broadcast-item:last-child {
            border-bottom: none;
        }
        .broadcast-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: rgba(124, 154, 134, 0.1);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .broadcast-content {
            flex: 1;
        }
        .broadcast-title {
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 5px;
        }
        .broadcast-meta {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 10px;
            display: flex;
            gap: 15px;
        }
        .broadcast-text {
            color: var(--text-dark);
            font-size: 0.9rem;
            line-height: 1.5;
            background: rgba(0,0,0,0.02);
            padding: 12px;
            border-radius: 8px;
            border-left: 3px solid var(--primary);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            padding: 20px;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        .page-btn {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid rgba(0,0,0,0.1);
            background: white;
            color: var(--text-dark);
            text-decoration: none;
            transition: all 0.2s;
        }
        .page-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        .page-btn:hover:not(.active) {
            background: rgba(0,0,0,0.02);
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
            
            <!-- Navigation Tabs -->
            <div class="tabs-container">
                <a href="admin_notifications.php?tab=inbox" class="tab-item <?php echo $tab === 'inbox' ? 'active' : ''; ?>">
                    <i class="fas fa-inbox"></i> Notification Inbox
                    <?php if ($totalUnreadInbox > 0): ?>
                        <span class="tab-badge"><?php echo $totalUnreadInbox; ?></span>
                    <?php endif; ?>
                </a>
                <a href="admin_notifications.php?tab=broadcast" class="tab-item <?php echo $tab === 'broadcast' ? 'active' : ''; ?>">
                    <i class="fas fa-bullhorn"></i> Broadcast Centre
                </a>
            </div>

            <!-- Page Header -->
            <div class="page-header" style="margin-bottom: 20px;">
                <div class="page-title">
                    <h1>Communications</h1>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">
                        <?php echo $tab === 'inbox' ? 'View alerts and log reports sent to your administration account.' : 'Send global announcements and site-wide broadcast messages.'; ?>
                    </p>
                </div>
                <?php if ($tab === 'inbox' && $totalUnreadInbox > 0): ?>
                    <div class="header-actions">
                        <a href="admin_notifications.php?tab=inbox&action=mark_all_read" class="btn-primary" style="text-decoration: none;">
                            <i class="fas fa-check-double"></i> Mark All as Read
                        </a>
                    </div>
                <?php endif; ?>
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

            <?php if ($tab === 'inbox'): ?>
                <!-- INBOX VIEW -->
                <div>
                    <?php if (empty($inboxNotifications)): ?>
                        <?php render_empty_state('Inbox Empty', 'You have no notifications in your inbox.', 'far fa-bell-slash'); ?>
                    <?php else: ?>
                        <div>
                            <?php foreach ($inboxNotifications as $notif): ?>
                                <div class="notif-card <?php echo $notif['read_status'] == 0 ? 'unread' : ''; ?>">
                                    <div class="notif-icon <?php echo strpos(strtolower($notif['title']), 'alert') !== false || strpos(strtolower($notif['title']), 'fail') !== false ? 'alert' : 'system'; ?>">
                                        <i class="fas fa-bell"></i>
                                    </div>
                                    <div class="notif-content">
                                        <div class="notif-title"><?php echo htmlspecialchars($notif['title']); ?></div>
                                        <div class="notif-msg"><?php echo htmlspecialchars($notif['message']); ?></div>
                                        <div class="notif-meta">
                                            <span><i class="far fa-clock"></i> <?php echo get_relative_time($notif['created_at']); ?> (<?php echo date('M d, Y H:i', strtotime($notif['created_at'])); ?>)</span>
                                            <span style="margin: 0 5px;">•</span>
                                            <span>Type: <?php echo ucfirst($notif['notification_type']); ?></span>
                                        </div>
                                    </div>
                                    <div class="notif-actions">
                                        <?php if ($notif['read_status'] == 0): ?>
                                            <a href="admin_notifications.php?tab=inbox&action=mark_read&id=<?php echo $notif['id']; ?>&inbox_page=<?php echo $inbox_page; ?>" class="notif-btn" title="Mark as Read">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="admin_notifications.php?tab=inbox&action=delete_notif&id=<?php echo $notif['id']; ?>&inbox_page=<?php echo $inbox_page; ?>" class="notif-btn delete-btn" title="Delete Notification" onclick="return confirm('Delete this notification?');">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Inbox Pagination -->
                        <?php if ($totalInboxPages > 1): ?>
                            <div class="pagination" style="border:none; margin-top:20px;">
                                <?php for($i=1; $i<=$totalInboxPages; $i++): ?>
                                    <a href="?tab=inbox&inbox_page=<?php echo $i; ?>" class="page-btn <?php echo $i == $inbox_page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- BROADCAST CENTRE VIEW -->
                <div class="layout-grid">
                    <!-- Send Form -->
                    <div>
                        <div class="form-card">
                            <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--text-dark); display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-paper-plane" style="color: var(--primary);"></i> Send Broadcast
                            </h3>
                            <form method="POST" action="admin_notifications.php?tab=broadcast" onsubmit="return confirm('Are you sure you want to send this broadcast?');">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="action" value="send_broadcast">
                                
                                <div class="form-group">
                                    <label>Target Audience *</label>
                                    <select name="target" required>
                                        <option value="">Select Audience...</option>
                                        <option value="all" style="font-weight: bold;">All Users</option>
                                        <optgroup label="By Role">
                                            <?php foreach($roles as $role): ?>
                                                <option value="role_<?php echo $role['id']; ?>">All <?php echo htmlspecialchars($role['name']); ?>s</option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>Notification Title *</label>
                                    <input type="text" name="title" required placeholder="e.g., Important Platform Update">
                                </div>
                                
                                <div class="form-group">
                                    <label>Message *</label>
                                    <textarea name="message" required placeholder="Write your message here..."></textarea>
                                </div>
                                
                                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">
                                    <i class="fas fa-paper-plane"></i> Send Notification
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- History -->
                    <div>
                        <div class="form-card" style="padding: 0;">
                            <div style="padding: 20px 25px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                                <h3 style="margin: 0; color: var(--text-dark);">Broadcast History</h3>
                            </div>
                            
                            <?php if(empty($broadcasts)): ?>
                                <div style="padding: 40px; text-align: center; color: var(--text-muted);">
                                    <i class="fas fa-history" style="font-size: 3rem; opacity: 0.5; margin-bottom: 15px;"></i>
                                    <p>No broadcasts have been sent yet.</p>
                                </div>
                            <?php else: ?>
                                <div>
                                    <?php foreach($broadcasts as $bc): ?>
                                        <div class="broadcast-item">
                                            <div class="broadcast-icon">
                                                <i class="fas fa-bullhorn"></i>
                                            </div>
                                            <div class="broadcast-content">
                                                <div class="broadcast-title"><?php echo htmlspecialchars($bc['title']); ?></div>
                                                <div class="broadcast-meta">
                                                    <span><i class="far fa-clock"></i> <?php echo date('M d, Y H:i', strtotime($bc['created_at'])); ?></span>
                                                    <span><i class="fas fa-users"></i> Sent to <?php echo $bc['recipient_count']; ?> users</span>
                                                    <span style="color: var(--success);"><i class="fas fa-check-double"></i> <?php echo $bc['read_count']; ?> read</span>
                                                </div>
                                                <div class="broadcast-text">
                                                    <?php echo nl2br(htmlspecialchars($bc['message'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- Pagination -->
                                <?php if ($totalPages > 1): ?>
                                <div class="pagination">
                                    <?php for($i=1; $i<=$totalPages; $i++): ?>
                                        <a href="?tab=broadcast&page=<?php echo $i; ?>" class="page-btn <?php echo $i == $page ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
        </div>
    </main>
</div>

<script src="assets/js/dashboard.js"></script>
</body>
</html>
