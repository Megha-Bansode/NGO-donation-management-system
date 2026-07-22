<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Super Admin Dashboard";
include __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-wrapper">
    <!-- Sidebar Component -->
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

    <!-- Main Content Pane -->
    <main class="dashboard-main">
        <header class="dashboard-header">
            <div class="dashboard-title-area">
                <h1>Super Admin Console</h1>
                <p>Global oversight, system analytics, and account verification.</p>
            </div>
            
            <div class="dashboard-user-widget">
                <div class="text-muted" style="font-size: 0.9rem; text-align: right;">
                    <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                    <div><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
            </div>
        </header>

        <div class="dashboard-content">
            <!-- Alert Banner -->
            <div class="alert alert-success" style="display: block; margin-bottom: 24px;">
                <i class="fa-solid fa-circle-check"></i> System is operating normally. All security logs are verified.
            </div>

            <!-- Global Metrics Cards -->
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Registered NGOs</h3>
                        <p>12</p>
                    </div>
                    <div class="metric-icon blue"><i class="fa-solid fa-building-columns"></i></div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Active Users</h3>
                        <p>1,482</p>
                    </div>
                    <div class="metric-icon green"><i class="fa-solid fa-users"></i></div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-info">
                        <h3>System Health</h3>
                        <p>99.8%</p>
                    </div>
                    <div class="metric-icon purple"><i class="fa-solid fa-shield-halved"></i></div>
                </div>

                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Global Contributions</h3>
                        <p>$2.4M</p>
                    </div>
                    <div class="metric-icon orange"><i class="fa-solid fa-earth-americas"></i></div>
                </div>
            </div>

            <!-- Table and Roster Grid -->
            <div class="grid grid-2" style="align-items: flex-start;">
                <!-- NGO Registration Roster -->
                <div class="card" style="padding: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h3 class="title-small" style="font-size: 1.15rem;">NGO Approvals Queue</h3>
                        <button class="btn btn-outline" style="padding: 6px 12px; font-size: 0.8rem;">View All</button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>NGO Name</th>
                                    <th>Region</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>EcoFuture Earth</strong></td>
                                    <td>South America</td>
                                    <td><span class="badge badge-warning">Pending Review</span></td>
                                    <td><button class="btn btn-primary" style="padding: 4px 8px; font-size: 0.75rem;" onclick="alert('NGO approved')">Verify</button></td>
                                </tr>
                                <tr>
                                    <td><strong>EduReach Kids</strong></td>
                                    <td>Southeast Asia</td>
                                    <td><span class="badge badge-success">Approved</span></td>
                                    <td><span class="text-muted" style="font-size:0.8rem;">Verified</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Aquapure Rigs</strong></td>
                                    <td>East Africa</td>
                                    <td><span class="badge badge-success">Approved</span></td>
                                    <td><span class="text-muted" style="font-size:0.8rem;">Verified</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent System Events -->
                <div class="card" style="padding: 24px;">
                    <h3 class="title-small" style="font-size: 1.15rem; margin-bottom: 20px;">Recent System Security Logs</h3>
                    <div class="timeline">
                        <div class="timeline-event">
                            <div class="timeline-dot"></div>
                            <div class="timeline-time">10 minutes ago</div>
                            <div class="timeline-title">Database Backup Completed</div>
                            <div class="timeline-desc">Automated nightly storage backup written successfully.</div>
                        </div>

                        <div class="timeline-event">
                            <div class="timeline-dot" style="background-color: var(--secondary);"></div>
                            <div class="timeline-time">2 hours ago</div>
                            <div class="timeline-title">New NGO Application: EcoFuture Earth</div>
                            <div class="timeline-desc">Registration documents uploaded for coordinator review.</div>
                        </div>

                        <div class="timeline-event">
                            <div class="timeline-dot"></div>
                            <div class="timeline-time">5 hours ago</div>
                            <div class="timeline-title">SSL Certificate Renewed</div>
                            <div class="timeline-desc">Security protocols updated for global subdomains.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
