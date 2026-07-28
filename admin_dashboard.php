<?php
// admin_dashboard.php
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Middleware.php';
require_once __DIR__ . '/includes/dashboard/admin_queries.php';
require_once __DIR__ . '/includes/dashboard/components.php';

// Protect this dashboard: Only Super Admin (Role ID 1) can access
Middleware::role([1]);

// Initialize Database
$pdo = getDatabase();

// AJAX Backend Router
if (isset($_GET['ajax_action'])) {
    header('Content-Type: application/json');
    $action = $_GET['ajax_action'];

    if ($action === 'get_chart_data') {
        $range = $_GET['range'] ?? '6m';
        $data = [
            'labels' => [],
            'amounts' => []
        ];
        try {
            if ($range === '30d') {
                $stmt = $pdo->query("
                    SELECT DATE_FORMAT(donation_date, '%d %b') as label, 
                           SUM(amount) as total
                    FROM donations
                    WHERE payment_status = 'completed'
                      AND donation_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    GROUP BY DATE(donation_date)
                    ORDER BY DATE(donation_date) ASC
                ");
            } elseif ($range === '12m') {
                $stmt = $pdo->query("
                    SELECT DATE_FORMAT(donation_date, '%b %Y') as label, 
                           SUM(amount) as total
                    FROM donations
                    WHERE payment_status = 'completed'
                      AND donation_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                    GROUP BY YEAR(donation_date), MONTH(donation_date)
                    ORDER BY YEAR(donation_date) ASC, MONTH(donation_date) ASC
                ");
            } else { // 6m
                $stmt = $pdo->query("
                    SELECT DATE_FORMAT(donation_date, '%b %Y') as label, 
                           SUM(amount) as total
                    FROM donations
                    WHERE payment_status = 'completed'
                      AND donation_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                    GROUP BY YEAR(donation_date), MONTH(donation_date)
                    ORDER BY YEAR(donation_date) ASC, MONTH(donation_date) ASC
                ");
            }
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data['labels'][] = $row['label'];
                $data['amounts'][] = (float)$row['total'];
            }
        } catch (Exception $e) {
            error_log("AJAX Chart Data Error: " . $e->getMessage());
        }
        echo json_encode($data);
        exit;
    }

    if ($action === 'get_recent_activity') {
        $activities = [];
        try {
            $stmt = $pdo->query("
                SELECT a.action, a.module, a.created_at, u.full_name
                FROM activity_logs a
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY a.created_at DESC
                LIMIT 8
            ");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['formatted_time'] = date('M d, g:i A', strtotime($row['created_at']));
                $row['full_name'] = htmlspecialchars($row['full_name'] ?? 'System');
                $row['module'] = htmlspecialchars(ucfirst($row['module']));
                $row['action'] = htmlspecialchars(ucfirst($row['action']));
                $activities[] = $row;
            }
        } catch (Exception $e) {
            error_log("AJAX Activity Logs Error: " . $e->getMessage());
        }
        echo json_encode($activities);
        exit;
    }

    if ($action === 'toggle_user_status') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            exit;
        }
        $email = $_POST['email'] ?? '';
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'User email is required.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT status FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'User not found.']);
                exit;
            }

            $newStatus = ($user['status'] === 'active') ? 'suspended' : 'active';
            $updateStmt = $pdo->prepare("UPDATE users SET status = :status WHERE email = :email");
            $updateStmt->execute([':status' => $newStatus, ':email' => $email]);

            require_once __DIR__ . '/core/Logger.php';
            Logger::logActivity($pdo, $_SESSION['user_id'] ?? 1, 1, 'Users', 'Status Toggle', "Toggled status for $email to $newStatus.");

            echo json_encode(['success' => true, 'new_status' => $newStatus]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'export_kpis') {
        ob_clean();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=arohan_kpi_report_' . date('Ymd_His') . '.csv');
        $output = fopen('php://output', 'w');
        
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, ['Arohan Foundation - Super Admin KPI Statistics Report']);
        fputcsv($output, ['Generated On', date('Y-m-d H:i:s')]);
        fputcsv($output, []);
        
        fputcsv($output, ['KPI Metric Name', 'Value', 'Details']);
        
        $kpis = get_dashboard_kpis($pdo);
        fputcsv($output, ['Total Registered NGOs', $kpis['total_ngos'], 'NGO and Organization role users']);
        fputcsv($output, ['Total System Users', $kpis['total_users'], 'All user accounts combined']);
        fputcsv($output, ['Total Donation count', $kpis['total_donations'], 'Completed transactions count']);
        fputcsv($output, ['Amount Raised (INR)', '₹' . number_format($kpis['total_amount'], 2), 'Total funds raised through completed payments']);
        fputcsv($output, ['Active Campaigns', $kpis['active_campaigns'], 'Campaigns currently accepting donations']);
        fputcsv($output, ['Total Volunteers', $kpis['total_volunteers'], 'Registered volunteers count']);
        fputcsv($output, ['Upcoming & Ongoing Events', $kpis['total_events'], 'Events currently scheduled or ongoing']);
        fputcsv($output, ['Pending Approvals', $kpis['pending_approvals'], 'Draft campaigns + Inactive users awaiting review']);
        
        $totalActivities = 0;
        try {
            $totalActivities = (int)$pdo->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
        } catch (Exception $e) {}
        fputcsv($output, ['System Logged Activities', $totalActivities, 'Total entry count in activity_logs']);
        
        fclose($output);
        exit;
    }
}

// Fetch Real Data with proper IDs and Columns
$kpis = get_dashboard_kpis($pdo);
$upcomingEvents = get_upcoming_events($pdo, 5);
$latestCampaigns = get_latest_campaigns($pdo, 5);

$recentDonations = [];
try {
    $stmt = $pdo->prepare("
        SELECT d.id, d.amount, d.donation_date, d.payment_status, 
               u.full_name as donor_name, 
               c.name as campaign_name
        FROM donations d
        LEFT JOIN users u ON d.donor_id = u.id
        LEFT JOIN campaigns c ON d.campaign_id = c.id
        ORDER BY d.donation_date DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recentDonations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$recentUsers = [];
try {
    $stmt = $pdo->prepare("
        SELECT u.id, u.full_name, u.email, u.created_at, u.status, r.name as role_name
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        ORDER BY u.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$recentActivity = [];
try {
    $stmt = $pdo->query("
        SELECT a.action, a.module, a.created_at, u.full_name
        FROM activity_logs a
        LEFT JOIN users u ON a.user_id = u.id
        ORDER BY a.created_at DESC
        LIMIT 8
    ");
    $recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$chartData = get_chart_data($pdo);

$totalActivities = 0;
try {
    $totalActivities = (int)$pdo->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
} catch (Exception $e) {
    // fallback
}

// Encode chart data for JS
$chartLabelsJSON = json_encode($chartData['labels']);
$chartAmountsJSON = json_encode($chartData['amounts']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard | <?php echo htmlspecialchars(APP_NAME); ?></title>
    
    <!-- Premium Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Dashboard Core CSS -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="dashboard-layout">
    
    <!-- Premium Sidebar -->
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>

    <main class="main-content">
        <!-- Sticky Topbar -->
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>

        <div class="page-content">
            <!-- Header Section -->
            <div class="page-header">
                <div class="page-title">
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 5px;">Welcome Back,</p>
                    <h1><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Chief Administrator'); ?></h1>
                    <div class="breadcrumb" style="margin-top: 5px;">
                        <i class="far fa-calendar-alt"></i> 
                        <span><?php echo date('l, F j, Y'); ?></span>
                        <span style="margin: 0 8px;">•</span>
                        <i class="far fa-clock"></i>
                        <span><?php echo date('h:i A'); ?></span>
                    </div>
                </div>
                <div class="page-actions" style="display: flex; gap: 10px;">
                    <a href="?ajax_action=export_kpis" class="btn-primary" style="background: white; color: var(--text-dark); border: 1px solid rgba(0,0,0,0.1); text-decoration: none;"><i class="fas fa-file-export"></i> Export KPIs</a>
                    <a href="admin_reports.php" class="btn-primary" style="background: white; color: var(--text-dark); border: 1px solid rgba(0,0,0,0.1); text-decoration: none;"><i class="fas fa-file-download"></i> Generate Reports</a>
                    <a href="admin_campaign_create.php" class="btn-primary" style="text-decoration: none;"><i class="fas fa-plus"></i> Create Campaign</a>
                </div>
            </div>

            <!-- KPI Cards Section -->
            <div class="kpi-grid">
                <?php 
                render_kpi_card('Total NGOs', $kpis['total_ngos'], 'fas fa-building', 'trend-up', 'Registered Organizations', 'admin_users.php?role=2');
                render_kpi_card('Total Users', $kpis['total_users'], 'fas fa-users', 'trend-up', 'Active Accounts', 'admin_users.php');
                render_kpi_card('Total Donations', $kpis['total_donations'], 'fas fa-hand-holding-heart', 'trend-up', 'Successful Transactions', 'admin_donations.php');
                render_kpi_card('Amount Raised', formatIndianCurrency($kpis['total_amount']), 'fas fa-rupee-sign', 'trend-up', 'Total Funds', 'admin_reports.php');
                
                render_kpi_card('Active Campaigns', $kpis['active_campaigns'], 'fas fa-bullhorn', 'trend-neutral', 'Currently Running', 'admin_campaigns.php');
                render_kpi_card('Volunteers', $kpis['total_volunteers'], 'fas fa-hands-helping', 'trend-up', 'Ready to help', 'admin_volunteers.php');
                render_kpi_card('Events', $kpis['total_events'], 'far fa-calendar-check', 'trend-neutral', 'Upcoming/Ongoing', 'admin_events.php');
                render_kpi_card('Pending Approvals', $kpis['pending_approvals'], 'fas fa-clipboard-check', 'trend-down', 'Action Required', 'admin_approvals.php');
                render_kpi_card('System Activities', $totalActivities, 'fas fa-history', 'trend-up', 'Logged Actions', 'admin_activity_logs.php');
                ?>
            </div>

            <!-- Charts Section -->
            <div class="dashboard-flex-row">
                <div class="glass-card dashboard-col-2">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 class="card-title">Donation Trends</h3>
                        <select id="chartRangeFilter" class="chart-filter-select">
                            <option value="30d">Last 30 Days</option>
                            <option value="6m" selected>Last 6 Months</option>
                            <option value="12m">Last 12 Months</option>
                        </select>
                    </div>
                    <div style="height: 300px; width: 100%;">
                        <canvas id="donationsChart"></canvas>
                    </div>
                </div>
                <div class="glass-card dashboard-col-1">
                    <div class="card-header">
                        <h3 class="card-title">Recent Activity</h3>
                    </div>
                    <div class="activity-feed" style="max-height: 300px; overflow-y: auto; padding-right: 10px;">
                        <ul id="activityFeedList" style="list-style: none; padding: 0;">
                            <?php if (empty($recentActivity)): ?>
                                <p style="text-align:center; color: var(--text-muted); margin-top: 2rem;">No recent activity.</p>
                            <?php else: ?>
                                <?php foreach($recentActivity as $log): ?>
                                    <li style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid rgba(0,0,0,0.03);">
                                        <a href="admin_user_activity.php" style="text-decoration: none; color: inherit; display: block;">
                                            <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 4px;">
                                                <i class="far fa-clock"></i> <?php echo date('M d, g:i A', strtotime($log['created_at'])); ?>
                                            </div>
                                            <div style="font-size: 0.95rem; color: var(--text-dark);">
                                                <strong><?php echo htmlspecialchars($log['full_name'] ?? 'System'); ?></strong>: 
                                                <?php echo htmlspecialchars(ucfirst($log['module']) . ' - ' . ucfirst($log['action'])); ?>
                                            </div>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Complex Data Tables Row -->
            <!-- Complex Data Tables Row -->
            <div class="dashboard-flex-row">
                
                <!-- Recent Donations -->
                <div class="glass-card dashboard-col-2">
                    <div class="card-header">
                        <h3 class="card-title">Recent Donations</h3>
                        <a href="admin_donations.php" class="btn-primary" style="padding: 6px 15px; font-size: 0.8rem; background: rgba(124,154,134,0.1); color: var(--primary); text-decoration: none;">View All</a>
                    </div>
                    
                    <?php if (empty($recentDonations)): ?>
                        <?php render_empty_state('No Donations Yet', 'There are no completed donations in the system.', 'fas fa-receipt'); ?>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Donor</th>
                                        <th>Campaign</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th style="width: 80px; text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recentDonations as $donation): ?>
                                    <tr>
                                        <td><strong style="color: var(--text-dark);"><?php echo htmlspecialchars($donation['donor_name'] ?? 'Anonymous'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($donation['campaign_name'] ?? 'General Fund'); ?></td>
                                        <td style="font-family: var(--font-stats); font-weight: 700; color: var(--primary);"><?php echo formatIndianCurrency($donation['amount']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($donation['donation_date'])); ?></td>
                                        <td>
                                            <?php 
                                            $statusClass = 'status-inactive';
                                            if ($donation['payment_status'] == 'completed') $statusClass = 'status-active';
                                            if ($donation['payment_status'] == 'pending') $statusClass = 'status-pending';
                                            ?>
                                            <span class="status-badge <?php echo $statusClass; ?>"><?php echo ucfirst($donation['payment_status']); ?></span>
                                        </td>
                                        <td style="text-align: center;">
                                            <?php if ($donation['payment_status'] == 'completed'): ?>
                                                <a href="donor_receipts.php?id=<?php echo htmlspecialchars($donation['id'] ?? ''); ?>" class="quick-action-btn" title="View Receipt" target="_blank">
                                                    <i class="fas fa-file-invoice-dollar"></i>
                                                </a>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted); font-size: 0.8rem;">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Users -->
                <div class="glass-card dashboard-col-1">
                    <div class="card-header">
                        <h3 class="card-title">Recent Users</h3>
                    </div>
                    
                    <?php if (empty($recentUsers)): ?>
                        <?php render_empty_state('No Users', 'No users found.', 'fas fa-users'); ?>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th style="width: 80px; text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recentUsers as $user): ?>
                                    <tr>
                                        <td>
                                            <a href="admin_users.php" style="text-decoration: none;">
                                                <strong style="color: var(--text-dark); display:block;"><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                                <span style="font-size: 0.75rem; color: var(--text-muted);"><?php echo htmlspecialchars($user['email']); ?></span>
                                            </a>
                                        </td>
                                        <td><span style="font-size: 0.8rem; font-weight: 600; color: var(--primary-dark);"><?php echo htmlspecialchars($user['role_name'] ?? 'User'); ?></span></td>
                                        <td>
                                            <span class="status-badge <?php echo $user['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>" id="status-badge-<?php echo md5($user['email']); ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span>
                                        </td>
                                        <td style="text-align: center;">
                                            <?php if ($user['email'] !== ($_SESSION['email'] ?? '')): ?>
                                                <button class="quick-action-btn toggle-status-btn <?php echo $user['status'] == 'active' ? 'btn-danger-hover' : 'btn-success-hover'; ?>" 
                                                        data-email="<?php echo htmlspecialchars($user['email']); ?>" 
                                                        data-md5="<?php echo md5($user['email']); ?>" 
                                                        title="<?php echo $user['status'] == 'active' ? 'Suspend User' : 'Activate User'; ?>">
                                                    <i class="fas <?php echo $user['status'] == 'active' ? 'fa-user-slash' : 'fa-user-check'; ?>"></i>
                                                </button>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted); font-size: 0.8rem;">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Third Row: Campaigns and Events -->
            <div class="dashboard-flex-row">
                <!-- Latest Campaigns -->
                <div class="glass-card dashboard-col-1" style="min-width: 500px;">
                    <div class="card-header">
                        <h3 class="card-title">Latest Campaigns</h3>
                    </div>
                    <?php if (empty($latestCampaigns)): ?>
                        <?php render_empty_state('No Campaigns', 'Create your first campaign to start fundraising.', 'fas fa-bullhorn', 'Create Campaign'); ?>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Campaign Name</th>
                                        <th>Progress</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($latestCampaigns as $camp): ?>
                                    <tr>
                                        <td><a href="admin_campaigns.php" style="text-decoration: none;"><strong style="color: var(--text-dark);"><?php echo htmlspecialchars($camp['name']); ?></strong></a></td>
                                        <td style="width: 40%;">
                                            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 5px; font-weight: 600;">
                                                <span style="color: var(--primary);"><?php echo formatIndianCurrency($camp['collected_amount']); ?></span>
                                                <span style="color: var(--text-muted);"><?php echo formatIndianCurrency($camp['target_amount']); ?></span>
                                            </div>
                                            <div style="height: 6px; width: 100%; background: rgba(0,0,0,0.05); border-radius: 3px; overflow: hidden;">
                                                <div style="height: 100%; background: var(--primary); width: <?php echo htmlspecialchars($camp['goal_completed_percentage']); ?>%;"></div>
                                            </div>
                                        </td>
                                        <td><span class="status-badge <?php echo $camp['status'] == 'active' ? 'status-active' : 'status-pending'; ?>"><?php echo ucfirst($camp['status']); ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Upcoming Events -->
                <div class="glass-card dashboard-col-1" style="min-width: 400px;">
                    <div class="card-header">
                        <h3 class="card-title">Upcoming Events</h3>
                    </div>
                    <?php if (empty($upcomingEvents)): ?>
                        <?php render_empty_state('No Events', 'There are no upcoming events scheduled.', 'far fa-calendar-times'); ?>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Event Name</th>
                                        <th>Date & Time</th>
                                        <th>Venue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($upcomingEvents as $evt): ?>
                                    <tr>
                                        <td><a href="admin_events.php" style="text-decoration: none;"><strong style="color: var(--text-dark);"><?php echo htmlspecialchars($evt['title']); ?></strong></a></td>
                                        <td>
                                            <div style="font-size: 0.85rem; color: var(--text-dark); font-weight: 600;"><?php echo date('M d, Y', strtotime($evt['event_date'])); ?></div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo date('g:i A', strtotime($evt['event_time'])); ?></div>
                                        </td>
                                        <td><span style="font-size: 0.85rem; color: var(--text-muted);"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($evt['venue']); ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Fourth Row: Communications -->
            <div class="dashboard-flex-row">
                
                <!-- Unread Notifications -->
                <div class="glass-card dashboard-col-1" style="min-width: 400px;">
                    <div class="card-header">
                        <h3 class="card-title">Unread Notifications</h3>
                    </div>
                    <?php 
                    $notifications = get_unread_notifications($pdo, 5);
                    if (empty($notifications)): 
                        render_empty_state('All caught up!', 'You have no unread notifications.', 'far fa-bell-slash');
                    else: 
                    ?>
                        <ul style="list-style: none; padding: 0;">
                            <?php foreach($notifications as $notif): ?>
                                <li style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid rgba(0,0,0,0.03);">
                                    <a href="admin_notifications.php" style="text-decoration: none; color: inherit; display: flex; gap: 15px;">
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(124,154,134,0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="fas fa-bell"></i>
                                        </div>
                                        <div>
                                            <strong style="color: var(--text-dark); display: block; font-size: 0.95rem;"><?php echo htmlspecialchars($notif['title']); ?></strong>
                                            <p style="font-size: 0.85rem; color: var(--text-body); margin: 4px 0;"><?php echo htmlspecialchars($notif['message']); ?></p>
                                            <span style="font-size: 0.75rem; color: var(--text-muted);"><i class="far fa-clock"></i> <?php echo date('M d, g:i A', strtotime($notif['created_at'])); ?></span>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <!-- Contact Requests -->
                <div class="glass-card dashboard-col-1" style="min-width: 400px;">
                    <div class="card-header">
                        <h3 class="card-title">Latest Contact Requests</h3>
                    </div>
                    <?php 
                    $messages = get_contact_messages($pdo, 5);
                    if (empty($messages)): 
                        render_empty_state('Inbox Empty', 'No pending contact requests at this time.', 'far fa-envelope');
                    else: 
                    ?>
                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Subject</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($messages as $msg): ?>
                                    <tr>
                                        <td>
                                            <a href="admin_inquiry_detail.php?id=<?php echo $msg['id']; ?>" style="text-decoration: none;">
                                                <strong style="color: var(--text-dark); display:block;"><?php echo htmlspecialchars($msg['name']); ?></strong>
                                                <span style="font-size: 0.75rem; color: var(--text-muted);"><?php echo htmlspecialchars($msg['email']); ?></span>
                                            </a>
                                        </td>
                                        <td style="font-size: 0.9rem;"><?php echo htmlspecialchars($msg['subject']); ?></td>
                                        <td style="font-size: 0.85rem; color: var(--text-muted);"><?php echo date('M d, Y', strtotime($msg['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- Dashboard Interactive Logic -->
<script src="assets/js/dashboard.js"></script>

<!-- Toast Notification Container -->
<div id="dashboardToast" class="dashboard-toast"></div>

<!-- Chart.js & AJAX Dashboard Interactive Logic -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ----------------------------------------------------
    // 1. Chart.js Management & Timeframe Filter
    // ----------------------------------------------------
    let donationsChartInstance = null;

    function renderDonationsChart(labels, amounts) {
        const ctx = document.getElementById('donationsChart');
        if (!ctx) return;
        
        if (donationsChartInstance) {
            donationsChartInstance.destroy();
        }
        
        if (labels.length === 0) {
            labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
            amounts = [0, 0, 0, 0, 0, 0];
        }

        donationsChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Donations (₹)',
                    data: amounts,
                    borderColor: '#7C9A86',
                    backgroundColor: 'rgba(124, 154, 134, 0.1)',
                    borderWidth: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#7C9A86',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#1F2937',
                        bodyColor: '#4B5563',
                        borderColor: 'rgba(0,0,0,0.05)',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return '₹' + context.parsed.y.toLocaleString('en-IN');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: "'Manrope', sans-serif" }, color: '#9CA3AF' }
                    },
                    y: {
                        grid: { color: 'rgba(0,0,0,0.03)', drawBorder: false },
                        ticks: { 
                            font: { family: "'Space Grotesk', sans-serif" }, 
                            color: '#9CA3AF',
                            callback: function(value) { return '₹' + value.toLocaleString('en-IN'); }
                        },
                        beginAtZero: true
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
            }
        });
    }

    // Initial render
    const initialLabels = <?php echo $chartLabelsJSON; ?>;
    const initialAmounts = <?php echo $chartAmountsJSON; ?>;
    renderDonationsChart(initialLabels, initialAmounts);

    // Filter Change Event
    const filterSelect = document.getElementById('chartRangeFilter');
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            fetch(`?ajax_action=get_chart_data&range=${this.value}`)
                .then(res => res.json())
                .then(data => {
                    renderDonationsChart(data.labels, data.amounts);
                    showToast('Chart updated successfully', 'success');
                })
                .catch(err => {
                    console.error('Chart Filter Error:', err);
                    showToast('Failed to retrieve chart data', 'error');
                });
        });
    }

    // ----------------------------------------------------
    // 2. Toast Notifications
    // ----------------------------------------------------
    function showToast(message, type = 'success') {
        const toast = document.getElementById('dashboardToast');
        if (!toast) return;
        
        toast.className = `dashboard-toast show toast-${type}`;
        toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> <span>${message}</span>`;
        
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    // ----------------------------------------------------
    // 3. User Status Dynamic Toggle
    // ----------------------------------------------------
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.toggle-status-btn');
        if (btn) {
            e.preventDefault();
            const email = btn.getAttribute('data-email');
            const md5 = btn.getAttribute('data-md5');
            
            btn.disabled = true;
            
            const formData = new FormData();
            formData.append('email', email);
            
            fetch('?ajax_action=toggle_user_status', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                if (data.success) {
                    const badge = document.getElementById(`status-badge-${md5}`);
                    if (badge) {
                        badge.textContent = data.new_status.charAt(0).toUpperCase() + data.new_status.slice(1);
                        if (data.new_status === 'active') {
                            badge.className = 'status-badge status-active';
                            btn.className = 'quick-action-btn toggle-status-btn btn-danger-hover';
                            btn.innerHTML = '<i class="fas fa-user-slash"></i>';
                            btn.setAttribute('title', 'Suspend User');
                        } else {
                            badge.className = 'status-badge status-inactive';
                            btn.className = 'quick-action-btn toggle-status-btn btn-success-hover';
                            btn.innerHTML = '<i class="fas fa-user-check"></i>';
                            btn.setAttribute('title', 'Activate User');
                        }
                    }
                    showToast(`User status updated to ${data.new_status}.`, 'success');
                } else {
                    showToast(data.message || 'Failed to update user status.', 'error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                console.error('Status Toggle Error:', err);
                showToast('Server connection error occurred.', 'error');
            });
        }
    });

    // ----------------------------------------------------
    // 4. Real-time Activity Feed Polling
    // ----------------------------------------------------
    function pollActivityLogs() {
        fetch('?ajax_action=get_recent_activity')
            .then(res => res.json())
            .then(data => {
                const feed = document.getElementById('activityFeedList');
                if (!feed) return;
                
                if (data.length === 0) {
                    feed.innerHTML = '<p style="text-align:center; color: var(--text-muted); margin-top: 2rem;">No recent activity.</p>';
                    return;
                }
                
                let html = '';
                data.forEach(log => {
                    html += `
                        <li style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid rgba(0,0,0,0.03);">
                            <a href="admin_user_activity.php" style="text-decoration: none; color: inherit; display: block;">
                                <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 4px;">
                                    <i class="far fa-clock"></i> ${log.formatted_time}
                                </div>
                                <div style="font-size: 0.95rem; color: var(--text-dark);">
                                    <strong>${log.full_name}</strong>: 
                                    ${log.module} - ${log.action}
                                </div>
                            </a>
                        </li>
                    `;
                });
                feed.innerHTML = html;
            })
            .catch(err => console.error('Activity logs polling error:', err));
    }

    // Poll every 30 seconds
    setInterval(pollActivityLogs, 30000);
});
</script>

</body>
</html>
