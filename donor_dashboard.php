<?php
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Middleware.php';
require_once __DIR__ . '/includes/dashboard/components.php';
require_once __DIR__ . '/includes/dashboard/donor_queries.php';

// Protect this dashboard: Only Donor (Role ID 3) can access
Middleware::role([3]);

$pdo = getDatabase();
$donor_id = $_SESSION['user_id'];

// Fetch Data utilizing unified data loader and cache
$dashboardData = getDonorDashboardData($pdo, $donor_id);
$kpis = $dashboardData['kpis'];
$activeCampaigns = $dashboardData['activeCampaigns'];
$recentActivity = $dashboardData['recentActivity'];
$notifications = $dashboardData['notifications'];

$firstName = htmlspecialchars(explode(' ', trim($_SESSION['full_name'] ?? 'Donor'))[0]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Dashboard | <?php echo APP_NAME; ?></title>
    <meta name="description" content="Your personal donor dashboard — track donations, campaigns, and impact at <?php echo APP_NAME; ?>.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/donor.css">
</head>
<body data-donor-page="dashboard">

<div class="dashboard-layout">
    <!-- Sidebar -->
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Topbar -->
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>

        <div class="page-content">

            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <h1>Welcome back, <?php echo $firstName; ?>! 👋</h1>
                    <p style="color: var(--text-muted); margin-top: 6px; font-size: 0.95rem;">
                        Your contributions are making a real difference — here's your impact overview.
                    </p>
                </div>
            </div>

            <!-- KPI Grid -->
            <div class="kpi-grid">
                <?php
                render_kpi_card('Total Donations',      $kpis['total_donations'],                            'fas fa-hand-holding-heart', 'primary', 'donor_donations.php');
                render_kpi_card('Total Amount Donated', formatIndianCurrency($kpis['total_amount']),          'fas fa-rupee-sign',          'success', 'donor_donations.php');
                render_kpi_card('Campaigns Supported',  $kpis['campaigns_supported'],                        'fas fa-bullhorn',            'info',    'donor_campaigns.php');
                render_kpi_card('Average Donation',     formatIndianCurrency($kpis['average_donation']),     'fas fa-chart-line',          'warning', 'donor_donations.php');
                render_kpi_card('Favorite Cause',       $kpis['favorite_cause'],                             'fas fa-star',               'primary', 'donor_campaigns.php');
                $last_don = $kpis['last_donation_date'] ? date('M d, Y', strtotime($kpis['last_donation_date'])) : 'N/A';
                render_kpi_card('Last Donation',        $last_don,                                           'fas fa-calendar-check',     'info',    'donor_donations.php');
                ?>
            </div>

            <!-- Second Row: Activity & Active Campaigns -->
            <div class="donor-two-col" style="margin-top: 2rem;">

                <!-- Recent Activity Timeline -->
                <div class="donor-activity-col">
                    <div class="glass-card" style="height: 100%;">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-history" style="color: var(--primary); margin-right: 8px;"></i>Recent Activity</h3>
                        </div>
                        <?php if (empty($recentActivity)): ?>
                            <div class="donor-empty-state">
                                <div class="donor-empty-icon"><i class="fas fa-history"></i></div>
                                <div class="donor-empty-title">No Activity Yet</div>
                                <div class="donor-empty-text">Your donation history and activity will appear here once you start contributing.</div>
                                <a href="donor_campaigns.php" class="btn-primary" style="text-decoration:none; display:inline-block; margin-top:4px;">Browse Campaigns</a>
                            </div>
                        <?php else: ?>
                            <div class="activity-timeline" style="padding: 4px 0;">
                                <?php foreach($recentActivity as $activity): ?>
                                    <div class="timeline-item" style="display:flex; gap:14px; margin-bottom:18px; align-items:flex-start;">
                                        <div style="width:40px; height:40px; border-radius:50%; background:<?php echo $activity['color']; ?>1A; color:<?php echo $activity['color']; ?>; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:1.1rem; box-shadow: 0 2px 8px <?php echo $activity['color']; ?>30;">
                                            <i class="<?php echo htmlspecialchars($activity['icon']); ?>"></i>
                                        </div>
                                        <div class="timeline-content" style="min-width:0;">
                                            <strong style="color:var(--text-dark); display:block; font-size:0.9rem; line-height:1.4;">
                                                <?php echo htmlspecialchars($activity['description']); ?>
                                            </strong>
                                            <span style="font-size:0.82rem; color:var(--text-body);"><?php echo htmlspecialchars($activity['title']); ?></span>
                                            <div style="font-size:0.75rem; color:var(--text-muted); margin-top:3px;">
                                                <i class="far fa-clock"></i>
                                                <?php echo date('M d, g:i A', strtotime($activity['event_date'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Featured / Active Campaigns -->
                <div class="donor-campaigns-col">
                    <div class="glass-card" style="height:100%;">
                        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                            <h3 class="card-title"><i class="fas fa-bullhorn" style="color:var(--primary); margin-right:8px;"></i>Active Campaigns</h3>
                            <a href="donor_campaigns.php" style="font-size:0.85rem; color:var(--primary); text-decoration:none; font-weight:600; display:flex; align-items:center; gap:4px;">
                                View All <i class="fas fa-chevron-right" style="font-size:0.7rem;"></i>
                            </a>
                        </div>
                        <?php if (empty($activeCampaigns)): ?>
                            <div class="donor-empty-state">
                                <div class="donor-empty-icon"><i class="far fa-calendar-times"></i></div>
                                <div class="donor-empty-title">No Active Campaigns</div>
                                <div class="donor-empty-text">There are no active campaigns at the moment. Check back soon!</div>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="modern-table">
                                    <thead>
                                        <tr>
                                            <th>Campaign</th>
                                            <th>Goal</th>
                                            <th>Progress</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $count = 0;
                                        foreach($activeCampaigns as $camp):
                                            if ($count++ >= 4) break;
                                            $percent = $camp['target_amount'] > 0 ? ($camp['collected_amount'] / $camp['target_amount']) * 100 : 0;
                                            $percent = min(100, round($percent, 1));
                                            $remaining = max(0, $camp['target_amount'] - $camp['collected_amount']);
                                        ?>
                                        <tr>
                                            <td>
                                                <a href="donor_campaign_details.php?id=<?php echo $camp['id']; ?>" style="text-decoration:none;">
                                                    <strong style="color:var(--text-dark); display:block; font-size:0.9rem; line-height:1.3;"><?php echo htmlspecialchars($camp['name']); ?></strong>
                                                </a>
                                                <span style="font-size:0.75rem; color:var(--text-muted);">
                                                    <i class="fas <?php echo htmlspecialchars($camp['category_icon']); ?>"></i>
                                                    <?php echo htmlspecialchars($camp['category_name']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong style="color:var(--primary); font-size:0.9rem;"><?php echo formatIndianCurrency($camp['target_amount']); ?></strong>
                                                <div style="font-size:0.72rem; color:var(--text-muted); margin-top:2px;">
                                                    <?php echo formatIndianCurrency($remaining); ?> left
                                                </div>
                                            </td>
                                            <td style="min-width:110px;">
                                                <div class="donor-progress-bar">
                                                    <div class="donor-progress-fill" data-progress="<?php echo $percent; ?>"></div>
                                                </div>
                                                <span style="font-size:0.72rem; color:var(--text-muted);"><?php echo $percent; ?>% funded</span>
                                            </td>
                                            <td>
                                                <a href="donor_donate.php?campaign_id=<?php echo $camp['id']; ?>" class="btn-primary" style="padding:5px 12px; font-size:0.78rem; text-decoration:none; white-space:nowrap;">
                                                    <i class="fas fa-heart"></i> Donate
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- Third Row: Unread Notifications -->
            <div style="margin-top: 2rem;">
                <div class="glass-card">
                    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                        <h3 class="card-title">
                            <i class="fas fa-bell" style="color:var(--primary); margin-right:8px;"></i>Unread Notifications
                            <?php if (!empty($notifications)): ?>
                                <span class="notif-badge"><?php echo count($notifications); ?></span>
                            <?php endif; ?>
                        </h3>
                        <a href="donor_notifications.php" style="font-size:0.85rem; color:var(--primary); text-decoration:none; font-weight:600; display:flex; align-items:center; gap:4px;">
                            View All <i class="fas fa-chevron-right" style="font-size:0.7rem;"></i>
                        </a>
                    </div>
                    <?php if (empty($notifications)): ?>
                        <div class="donor-empty-state">
                            <div class="donor-empty-icon"><i class="far fa-bell-slash"></i></div>
                            <div class="donor-empty-title">All Caught Up!</div>
                            <div class="donor-empty-text">You have no unread notifications. We'll let you know when something important happens.</div>
                        </div>
                    <?php else: ?>
                        <ul style="list-style:none; padding:0; margin:0;">
                            <?php foreach($notifications as $notif): ?>
                                <li style="border-bottom:1px solid #f1f5f9;">
                                    <a href="donor_notifications.php" style="text-decoration:none; color:inherit; display:flex; gap:14px; padding:16px 20px; transition:background 0.2s;" onmouseover="this.style.background='rgba(124,154,134,0.04)'" onmouseout="this.style.background=''">
                                        <div style="width:42px; height:42px; border-radius:50%; background:rgba(124,154,134,0.12); color:var(--primary); display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:1.1rem;">
                                            <i class="fas fa-bell"></i>
                                        </div>
                                        <div style="min-width:0;">
                                            <strong style="color:var(--text-dark); display:block; font-size:0.9rem; line-height:1.4;"><?php echo htmlspecialchars($notif['title']); ?></strong>
                                            <p style="font-size:0.82rem; color:var(--text-body); margin:3px 0; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;"><?php echo htmlspecialchars($notif['message']); ?></p>
                                            <span style="font-size:0.75rem; color:var(--text-muted);">
                                                <i class="far fa-clock"></i>
                                                <?php echo date('M d, g:i A', strtotime($notif['created_at'])); ?>
                                            </span>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>
</div>

<script src="assets/js/dashboard.js"></script>
<script src="assets/js/donor.js"></script>
</body>
</html>
