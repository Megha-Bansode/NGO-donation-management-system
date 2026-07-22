<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "NGO Admin Dashboard";
include __DIR__ . '/../../components/header.php';
?>

<div class="dashboard-wrapper">
    <!-- Sidebar Component -->
    <?php include __DIR__ . '/../../components/sidebar.php'; ?>

    <!-- Main Content Pane -->
    <main class="dashboard-main">
        <header class="dashboard-header">
            <div class="dashboard-title-area">
                <h1>NGO Workspace</h1>
                <p>Manage fundraising campaigns, assign volunteer teams, and track local programs.</p>
            </div>
            
            <div class="dashboard-user-widget">
                <div class="text-muted" style="font-size: 0.9rem; text-align: right;">
                    <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                    <div><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
            </div>
        </header>

        <div class="dashboard-content">
            <!-- Global Metrics Cards -->
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Active Campaigns</h3>
                        <p>3</p>
                    </div>
                    <div class="metric-icon blue"><i class="fa-solid fa-hand-holding-heart"></i></div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Registered Volunteers</h3>
                        <p>154</p>
                    </div>
                    <div class="metric-icon green"><i class="fa-solid fa-hands-helping"></i></div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Total Funds Raised</h3>
                        <p>$35,650</p>
                    </div>
                    <div class="metric-icon purple"><i class="fa-solid fa-wallet"></i></div>
                </div>

                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Pending Approvals</h3>
                        <p>5</p>
                    </div>
                    <div class="metric-icon orange"><i class="fa-solid fa-hourglass-half"></i></div>
                </div>
            </div>

            <!-- Table and Roster Grid -->
            <div class="grid grid-2" style="align-items: flex-start;">
                <!-- Volunteer Application Queue -->
                <div class="card" style="padding: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h3 class="title-small" style="font-size: 1.15rem;">Pending Volunteer Approvals</h3>
                        <button class="btn btn-outline" style="padding: 6px 12px; font-size: 0.8rem;">Review Queue</button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Volunteer Name</th>
                                    <th>Interest Focus</th>
                                    <th>Submission</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Harry Potter</strong></td>
                                    <td>Education Help</td>
                                    <td>1 day ago</td>
                                    <td><button class="btn btn-primary" style="padding: 4px 8px; font-size: 0.75rem;" onclick="alert('Application Approved')">Approve</button></td>
                                </tr>
                                <tr>
                                    <td><strong>Diana Prince</strong></td>
                                    <td>Disaster Relief</td>
                                    <td>2 days ago</td>
                                    <td><button class="btn btn-primary" style="padding: 4px 8px; font-size: 0.75rem;" onclick="alert('Application Approved')">Approve</button></td>
                                </tr>
                                <tr>
                                    <td><strong>Tony Stark</strong></td>
                                    <td>Fundraising Events</td>
                                    <td>3 days ago</td>
                                    <td><button class="btn btn-primary" style="padding: 4px 8px; font-size: 0.75rem;" onclick="alert('Application Approved')">Approve</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Event Schedule / Timeline -->
                <div class="card" style="padding: 24px;">
                    <h3 class="title-small" style="font-size: 1.15rem; margin-bottom: 20px;">Active Campaign Progress</h3>
                    
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.9rem; font-weight: 600; margin-bottom: 6px;">
                            <span>Build Cambodia School</span>
                            <span class="text-muted">$12,400 / $15,000 (82%)</span>
                        </div>
                        <div class="progress-bar-bg" style="height: 10px;">
                            <div class="progress-bar-fill" style="width: 82%;"></div>
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.9rem; font-weight: 600; margin-bottom: 6px;">
                            <span>Water Wells in Malawi</span>
                            <span class="text-muted">$4,500 / $10,000 (45%)</span>
                        </div>
                        <div class="progress-bar-bg" style="height: 10px;">
                            <div class="progress-bar-fill" style="width: 45%;"></div>
                        </div>
                    </div>

                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.9rem; font-weight: 600; margin-bottom: 6px;">
                            <span>Disaster Relief Fund</span>
                            <span class="text-muted">$18,750 / $25,000 (75%)</span>
                        </div>
                        <div class="progress-bar-bg" style="height: 10px;">
                            <div class="progress-bar-fill" style="width: 75%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../../components/footer.php'; ?>
