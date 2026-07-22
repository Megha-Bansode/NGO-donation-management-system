<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Volunteer Dashboard";
include __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-wrapper">
    <!-- Sidebar Component -->
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

    <!-- Main Content Pane -->
    <main class="dashboard-main">
        <header class="dashboard-header">
            <div class="dashboard-title-area">
                <h1>Volunteer Workspace</h1>
                <p>Track your assignments, upcoming schedules, and training resources.</p>
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
                        <h3>Hours Logged</h3>
                        <p>42 hrs</p>
                    </div>
                    <div class="metric-icon blue"><i class="fa-solid fa-clock"></i></div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Assigned Tasks</h3>
                        <p>2</p>
                    </div>
                    <div class="metric-icon green"><i class="fa-solid fa-list-check"></i></div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Completed Events</h3>
                        <p>8</p>
                    </div>
                    <div class="metric-icon purple"><i class="fa-solid fa-calendar-check"></i></div>
                </div>

                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Training Status</h3>
                        <p>Certified</p>
                    </div>
                    <div class="metric-icon orange"><i class="fa-solid fa-graduation-cap"></i></div>
                </div>
            </div>

            <!-- Table and Roster Grid -->
            <div class="grid grid-2" style="align-items: flex-start;">
                <!-- Assigned Task Roster -->
                <div class="card" style="padding: 24px;">
                    <h3 class="title-small" style="font-size: 1.15rem; margin-bottom: 16px;">My Active Tasks</h3>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Campaign</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Food Packaging</strong></td>
                                    <td>Disaster Relief</td>
                                    <td>Jul 25, 2026</td>
                                    <td><span class="badge badge-warning">Assigned</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Book Sorting</strong></td>
                                    <td>Cambodia School</td>
                                    <td>Jul 28, 2026</td>
                                    <td><span class="badge badge-info">In Progress</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Volunteer Activities / Timeline -->
                <div class="card" style="padding: 24px;">
                    <h3 class="title-small" style="font-size: 1.15rem; margin-bottom: 20px;">Upcoming Schedule Events</h3>
                    <div class="timeline">
                        <div class="timeline-event">
                            <div class="timeline-dot"></div>
                            <div class="timeline-time">July 22, 2026 - 10:00 AM</div>
                            <div class="timeline-title">Volunteer Orientation Webinar</div>
                            <div class="timeline-desc">Introductory call with the Regional Coordinator for new deployments.</div>
                        </div>

                        <div class="timeline-event">
                            <div class="timeline-dot" style="background-color: var(--secondary);"></div>
                            <div class="timeline-time">July 25, 2026 - 08:00 AM</div>
                            <div class="timeline-title">Food Distribution Roster</div>
                            <div class="timeline-desc">Fulfilling logistics packing at Warehouse A for disaster zones.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
