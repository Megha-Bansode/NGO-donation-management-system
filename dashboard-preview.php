<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/dashboard/components.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Framework Preview | <?php echo APP_NAME; ?></title>
    <!-- Premium Fonts from Auth Phase -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Dashboard Core CSS -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<div class="dashboard-layout">
    
    <!-- Premium Sidebar -->
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>

    <main class="main-content">
        <!-- Sticky Topbar -->
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>

        <div class="page-content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <h1>Dashboard Overview</h1>
                    <div class="breadcrumb">
                        <a href="#">Home</a>
                        <i class="fas fa-chevron-right"></i>
                        <span>Dashboard</span>
                    </div>
                </div>
                <div class="page-actions">
                    <button class="btn-primary">
                        <i class="fas fa-plus"></i> New Campaign
                    </button>
                </div>
            </div>

            <!-- KPI Cards Grid -->
            <div class="kpi-grid">
                <?php 
                render_kpi_card('Total Donations', '$124,500', 'fas fa-hand-holding-dollar', 'trend-up', '+12.5% this month');
                render_kpi_card('Active Campaigns', '42', 'fas fa-bullhorn', 'trend-up', '+4 this week');
                render_kpi_card('Volunteers', '856', 'fas fa-users', 'trend-neutral', 'Stable');
                render_kpi_card('Impact Reach', '12,450', 'fas fa-globe-asia', 'trend-up', '+8% vs last year');
                ?>
            </div>

            <!-- Dashboard Widgets Row -->
            <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                
                <!-- Recent Donations Table (Glass Card) -->
                <div class="glass-card" style="flex: 2; min-width: 600px;">
                    <div class="card-header">
                        <h3 class="card-title">Recent Donations</h3>
                        <a href="#" style="font-size: 0.85rem; color: var(--primary); text-decoration: none; font-weight: 600;">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Donor Name</th>
                                    <th>Campaign</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong style="color: var(--text-dark);">Sarah Jenkins</strong></td>
                                    <td>Education for All</td>
                                    <td style="font-family: var(--font-stats); font-weight: 600;">$500.00</td>
                                    <td>Oct 24, 2026</td>
                                    <td><span class="status-badge status-active">Completed</span></td>
                                </tr>
                                <tr>
                                    <td><strong style="color: var(--text-dark);">Michael Chen</strong></td>
                                    <td>Clean Water Initiative</td>
                                    <td style="font-family: var(--font-stats); font-weight: 600;">$1,250.00</td>
                                    <td>Oct 23, 2026</td>
                                    <td><span class="status-badge status-active">Completed</span></td>
                                </tr>
                                <tr>
                                    <td><strong style="color: var(--text-dark);">Anonymous</strong></td>
                                    <td>Disaster Relief Fund</td>
                                    <td style="font-family: var(--font-stats); font-weight: 600;">$100.00</td>
                                    <td>Oct 23, 2026</td>
                                    <td><span class="status-badge status-pending">Processing</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Empty State Example -->
                <div style="flex: 1; min-width: 300px;">
                    <?php 
                    render_empty_state(
                        'No Active Events', 
                        'There are currently no upcoming events scheduled for this month.', 
                        'far fa-calendar-times',
                        'Create Event'
                    ); 
                    ?>
                </div>

            </div>
            
            <!-- Skeleton Loader Example -->
            <div style="margin-top: 2rem;">
                <h3 style="font-family: var(--font-heading); margin-bottom: 1rem; color: var(--text-dark);">Loading State Example</h3>
                <div class="glass-card">
                    <?php render_skeleton('30px', '40%', '8px'); ?>
                    <?php render_skeleton('15px', '80%', '4px'); ?>
                    <?php render_skeleton('15px', '60%', '4px'); ?>
                    <?php render_skeleton('200px', '100%', '16px'); ?>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- Dashboard Interactive Logic -->
<script src="assets/js/dashboard.js"></script>
</body>
</html>
