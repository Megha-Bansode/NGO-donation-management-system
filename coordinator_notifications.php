<?php
// coordinator_notifications.php
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Middleware.php';
require_once __DIR__ . '/includes/dashboard/components.php';

// Protect this dashboard: Only Event Coordinator (Role ID 5) can access
Middleware::role([5]);

$pdo = getDatabase();

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success_msg = '';
$error_msg = '';

$coordinator_id = $_SESSION['user_id'];

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_msg = "Invalid security token.";
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'send_broadcast') {
            $title = htmlspecialchars(trim($_POST['title']));
            $message = htmlspecialchars(trim($_POST['message']));
            $target_audience = $_POST['target'];
            $type = 'system';
            
            if ($title && $message && $target_audience) {
                try {
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare("INSERT INTO notifications (recipient_id, title, message, notification_type) VALUES (?, ?, ?, ?)");
                    $count = 0;
                    
                    if ($target_audience === 'all_my_volunteers') {
                        // All approved volunteers across all assigned events
                        $q = $pdo->prepare("
                            SELECT DISTINCT vr.volunteer_id 
                            FROM volunteer_registrations vr 
                            JOIN events e ON vr.event_id = e.id 
                            WHERE e.coordinator_id = ? AND vr.approval_status = 'approved'
                        ");
                        $q->execute([$coordinator_id]);
                        $users = $q->fetchAll(PDO::FETCH_COLUMN);
                        
                        foreach ($users as $uid) {
                            $stmt->execute([$uid, $title, $message, $type]);
                            $count++;
                        }
                        $pdo->commit();
                        $success_msg = "Notification sent successfully to $count volunteers.";
                        
                    } elseif (strpos($target_audience, 'event_') === 0) {
                        $event_id = (int) str_replace('event_', '', $target_audience);
                        
                        // Verify ownership of this event
                        $verify = $pdo->prepare("SELECT coordinator_id FROM events WHERE id = ?");
                        $verify->execute([$event_id]);
                        $owner_id = $verify->fetchColumn();
                        
                        if ($owner_id != $coordinator_id) {
                            $error_msg = "You do not have permission to send notifications for this event.";
                            $pdo->rollBack();
                        } else {
                            $q = $pdo->prepare("SELECT DISTINCT volunteer_id FROM volunteer_registrations WHERE event_id = ? AND approval_status = 'approved'");
                            $q->execute([$event_id]);
                            $users = $q->fetchAll(PDO::FETCH_COLUMN);
                            
                            foreach ($users as $uid) {
                                $stmt->execute([$uid, $title, $message, $type]);
                                $count++;
                            }
                            $pdo->commit();
                            $success_msg = "Notification sent successfully to $count participants.";
                        }
                    } else {
                        $error_msg = "Invalid target audience selected.";
                    }
                } catch (Exception $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $error_msg = "Failed to send notification: " . $e->getMessage();
                }
            } else {
                $error_msg = "Please fill in all required fields.";
            }
        } elseif ($action === 'mark_read') {
            $notif_id = filter_var($_POST['notif_id'], FILTER_VALIDATE_INT);
            if ($notif_id) {
                try {
                    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND recipient_id = ?");
                    $stmt->execute([$notif_id, $coordinator_id]);
                    $success_msg = "Notification marked as read.";
                } catch (PDOException $e) {
                    $error_msg = "Database error: " . $e->getMessage();
                }
            }
        }
    }
}

// Fetch active events for the target dropdown
$eventsStmt = $pdo->prepare("SELECT id, title FROM events WHERE coordinator_id = ? AND status != 'completed' ORDER BY event_date ASC");
$eventsStmt->execute([$coordinator_id]);
$myEvents = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch my notifications
try {
    $notifStmt = $pdo->prepare("SELECT * FROM notifications WHERE recipient_id = ? ORDER BY created_at DESC LIMIT 50");
    $notifStmt->execute([$coordinator_id]);
    $my_notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $my_notifications = [];
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Notifications | <?php echo htmlspecialchars(APP_NAME); ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            text-align: center;
            background: rgba(0,0,0,0.02);
            border-radius: 12px;
            border: 1px dashed rgba(0,0,0,0.1);
        }
        .empty-state i {
            font-size: 3rem;
            color: var(--text-muted);
            margin-bottom: 15px;
            opacity: 0.5;
        }
        .empty-state h4 {
            margin-bottom: 5px;
            color: var(--text-dark);
        }
        .empty-state p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        .notification-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .notification-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
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
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 5px;">Communication</p>
                    <h1>Event Notifications</h1>
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
                <!-- Compose Notification Form -->
                <div class="glass-card" style="flex: 1; min-width: 400px; height: fit-content;">
                    <div class="card-header">
                        <h3 class="card-title">Compose Notification</h3>
                    </div>
                    
                    <form method="POST" action="coordinator_notifications.php">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="send_broadcast">
                        
                        <div class="form-group">
                            <label>Target Audience *</label>
                            <select name="target" required>
                                <option value="">Select Audience...</option>
                                <option value="all_my_volunteers">All My Volunteers (All Events)</option>
                                <optgroup label="Specific Events">
                                    <?php foreach ($myEvents as $event): ?>
                                        <option value="event_<?php echo $event['id']; ?>">
                                            Event: <?php echo htmlspecialchars($event['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                            <small style="color: var(--text-muted); display: block; margin-top: 5px;">Notifications will only be sent to volunteers with an 'approved' status.</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Subject / Title *</label>
                            <input type="text" name="title" required placeholder="e.g. Change in Venue Location">
                        </div>
                        
                        <div class="form-group">
                            <label>Message Content *</label>
                            <textarea name="message" rows="5" required placeholder="Type your message here..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">
                            <i class="fas fa-paper-plane"></i> Send Notification
                        </button>
                    </form>
                </div>
                
                <!-- Information Panel -->
                <div class="glass-card" style="flex: 1; min-width: 300px; height: fit-content; background: rgba(124,154,134,0.05); border: 1px solid rgba(124,154,134,0.2);">
                    <div class="card-header">
                        <h3 class="card-title">Notification Guidelines</h3>
                    </div>
                    <div style="color: var(--text-dark); line-height: 1.6; font-size: 0.9rem;">
                        <p style="margin-bottom: 15px;"><strong>Best Practices for Coordinators:</strong></p>
                        <ul style="padding-left: 20px; margin-bottom: 15px;">
                            <li style="margin-bottom: 10px;">Send reminders 24-48 hours before an event begins.</li>
                            <li style="margin-bottom: 10px;">Keep messages concise and focused on actionable information.</li>
                            <li style="margin-bottom: 10px;">Include critical details like time changes, attire requirements, or parking instructions.</li>
                            <li style="margin-bottom: 10px;">Only broadcast to "All My Volunteers" for general announcements that apply globally.</li>
                        </ul>
                        <div style="padding: 15px; background: white; border-radius: 8px; border-left: 4px solid var(--primary);">
                            <i class="fas fa-info-circle" style="color: var(--primary);"></i> Volunteers will see these notifications immediately in their personal dashboard when they log in.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Received Notifications Section -->
            <div class="glass-card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h3 class="card-title">My Inbox</h3>
                </div>
                
                <?php if (empty($my_notifications)): ?>
                    <div class="empty-state">
                        <i class="far fa-bell-slash"></i>
                        <h4>No Notifications</h4>
                        <p>You're all caught up! No new notifications.</p>
                    </div>
                <?php else: ?>
                    <div class="notification-list">
                        <?php foreach($my_notifications as $notif): ?>
                            <div class="notification-card <?php echo $notif['is_read'] ? 'read' : 'unread'; ?>" style="padding: 15px; border: 1px solid rgba(0,0,0,0.05); border-radius: 8px; margin-bottom: 10px; background: <?php echo $notif['is_read'] ? '#fff' : 'rgba(124, 154, 134, 0.05)'; ?>; display: flex; justify-content: space-between; align-items: flex-start;">
                                <div>
                                    <div style="font-weight: 600; color: var(--text-dark); margin-bottom: 5px;"><?php echo htmlspecialchars($notif['title']); ?></div>
                                    <div style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 8px;"><?php echo nl2br(htmlspecialchars($notif['message'])); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--primary);"><i class="far fa-clock"></i> <?php echo date('M d, Y h:i A', strtotime($notif['created_at'])); ?></div>
                                </div>
                                <?php if(!$notif['is_read']): ?>
                                    <form method="POST" action="coordinator_notifications.php" style="margin: 0;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="action" value="mark_read">
                                        <input type="hidden" name="notif_id" value="<?php echo $notif['id']; ?>">
                                        <button type="submit" class="badge" style="background: var(--primary); color: white; border: none; cursor: pointer;"><i class="fas fa-check"></i> Mark Read</button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge" style="background: rgba(0,0,0,0.05); color: var(--text-muted);"><i class="fas fa-check-double"></i> Read</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
        </div>
    </main>
</div>

<script src="assets/js/dashboard.js"></script>
</body>
</html>
