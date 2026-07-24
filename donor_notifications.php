<?php
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Middleware.php';
require_once __DIR__ . '/includes/dashboard/components.php';
require_once __DIR__ . '/includes/dashboard/donor_queries.php';

Middleware::role([3]);

$pdo = getDatabase();
$donor_id = $_SESSION['user_id'];

// Handle Mark as Read Actions
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'mark_all_read') {
        $stmt = $pdo->prepare("UPDATE notifications SET read_status = 1 WHERE recipient_id = ?");
        $stmt->execute([$donor_id]);
    } elseif ($_GET['action'] == 'mark_read' && isset($_GET['id'])) {
        $notif_id = (int)$_GET['id'];
        $stmt = $pdo->prepare("UPDATE notifications SET read_status = 1 WHERE id = ? AND recipient_id = ?");
        $stmt->execute([$notif_id, $donor_id]);
    }
    // Redirect to clean URL
    header("Location: donor_notifications.php");
    exit;
}

$notifications = getDonorAllNotifications($pdo, $donor_id);
$unreadCount = 0;
foreach ($notifications as $n) {
    if ($n['read_status'] == 0) $unreadCount++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Notifications | <?php echo APP_NAME; ?></title>
    <meta name="description" content="Stay updated on your supported campaigns, donation confirmations, and account activity.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/donor.css">
</head>
<body data-donor-page="notifications">
<div class="dashboard-layout">
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>
        <div class="page-content">

            <!-- Page Header -->
            <div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
                <div class="page-title">
                    <h1>
                        Notifications
                        <?php if ($unreadCount > 0): ?>
                            <span class="notif-badge" style="font-size:0.7rem; vertical-align:middle;"><?php echo $unreadCount; ?></span>
                        <?php endif; ?>
                    </h1>
                    <p style="color:var(--text-muted); margin-top:6px;">Stay updated on your campaigns and account activity.</p>
                </div>
                <?php if ($unreadCount > 0): ?>
                <div>
                    <a href="donor_notifications.php?action=mark_all_read" class="btn-secondary" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                        <i class="fas fa-check-double"></i> Mark All as Read
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <div class="glass-card" style="padding:0; overflow:hidden;">

                <?php if (empty($notifications)): ?>
                    <div class="donor-empty-state" style="padding:64px 24px;">
                        <div class="donor-empty-icon"><i class="far fa-bell-slash"></i></div>
                        <div class="donor-empty-title">No Notifications</div>
                        <div class="donor-empty-text">You're all caught up! We'll notify you about important campaign updates and donation confirmations.</div>
                    </div>
                <?php else: ?>

                    <!-- Filter Tabs -->
                    <div class="notif-tab-bar" role="tablist" aria-label="Notification filters">
                        <button class="notif-tab active" data-filter="all" role="tab" aria-selected="true">
                            All <span style="font-size:0.78rem; color:var(--text-muted); margin-left:4px;">(<?php echo count($notifications); ?>)</span>
                        </button>
                        <button class="notif-tab" data-filter="unread" role="tab" aria-selected="false">
                            Unread
                            <?php if ($unreadCount > 0): ?>
                                <span class="notif-badge"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </button>
                        <button class="notif-tab" data-filter="read" role="tab" aria-selected="false">
                            Read <span style="font-size:0.78rem; color:var(--text-muted); margin-left:4px;">(<?php echo count($notifications) - $unreadCount; ?>)</span>
                        </button>
                    </div>

                    <div id="notifList">
                        <?php foreach($notifications as $notif):
                            $isUnread = ($notif['read_status'] == 0);
                        ?>
                        <div class="notif-item <?php echo $isUnread ? 'unread' : 'read'; ?>" role="article"
                             aria-label="<?php echo $isUnread ? 'Unread notification' : 'Read notification'; ?>: <?php echo htmlspecialchars($notif['title']); ?>">
                            <div style="display:flex; gap:16px; flex:1; min-width:0;">
                                <div style="width:48px; height:48px; border-radius:50%; background:<?php echo $isUnread ? 'var(--primary)' : '#e2e8f0'; ?>; color:<?php echo $isUnread ? 'white' : 'var(--text-muted)'; ?>; display:flex; align-items:center; justify-content:center; font-size:1.15rem; flex-shrink:0; transition:all 0.2s;">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <div style="min-width:0;">
                                    <h4 style="margin:0 0 4px; color:var(--text-dark); font-size:0.95rem; font-weight:<?php echo $isUnread ? '700' : '600'; ?>; line-height:1.4;">
                                        <?php echo htmlspecialchars($notif['title']); ?>
                                    </h4>
                                    <p style="margin:0 0 8px; color:var(--text-body); font-size:0.88rem; line-height:1.55;">
                                        <?php echo htmlspecialchars($notif['message']); ?>
                                    </p>
                                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                        <span class="notif-relative-time" data-notif-date="<?php echo htmlspecialchars($notif['created_at']); ?>">
                                            <i class="far fa-clock"></i>
                                            <?php echo date('M d, Y g:i A', strtotime($notif['created_at'])); ?>
                                        </span>
                                        <span style="font-size:0.75rem; background:#f1f5f9; padding:2px 8px; border-radius:12px; color:var(--text-muted); font-weight:600;">
                                            <?php echo htmlspecialchars($notif['notification_type']); ?>
                                        </span>
                                        <?php if ($isUnread): ?>
                                            <span style="width:8px; height:8px; background:var(--primary); border-radius:50%; display:inline-block;" title="Unread"></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php if ($isUnread): ?>
                                <div style="flex-shrink:0; margin-left:12px; align-self:center;">
                                    <a href="donor_notifications.php?action=mark_read&id=<?php echo $notif['id']; ?>"
                                       class="notif-mark-read-btn"
                                       title="Mark as Read"
                                       style="width:38px; height:38px; display:flex; align-items:center; justify-content:center; border-radius:50%; background:white; box-shadow:0 2px 8px rgba(0,0,0,0.08); color:var(--primary); text-decoration:none; transition:all 0.2s;"
                                       onmouseover="this.style.background='var(--primary)';this.style.color='white';"
                                       onmouseout="this.style.background='white';this.style.color='var(--primary)';">
                                        <i class="fas fa-check"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>

                        <!-- No results for tab -->
                        <div id="notifNoResults" class="donor-no-results" style="display:none;">
                            <i class="far fa-bell-slash" style="font-size:2rem; opacity:0.25; display:block; margin-bottom:10px;"></i>
                            No notifications in this category.
                        </div>
                    </div>

                <?php endif; ?>
            </div>

        </div>
    </main>
</div>
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/donor.js"></script>
</body>
</html>
