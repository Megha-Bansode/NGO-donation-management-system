<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Event Coordinator Dashboard";
include __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-wrapper">
    <!-- Sidebar Component -->
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

    <!-- Main Content Pane -->
    <main class="dashboard-main">
        <header class="dashboard-header">
            <div class="dashboard-title-area">
                <h1>Coordinator Workspace</h1>
                <p>Track venue setup logistics, assign volunteers to rosters, and check attendees.</p>
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
                        <h3>Coordinated Events</h3>
                        <p>3 Active</p>
                    </div>
                    <div class="metric-icon blue"><i class="fa-solid fa-calendar-check"></i></div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Volunteers Deployed</h3>
                        <p>48</p>
                    </div>
                    <div class="metric-icon green"><i class="fa-solid fa-user-check"></i></div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Assigned Budget</h3>
                        <p>$8,400</p>
                    </div>
                    <div class="metric-icon purple"><i class="fa-solid fa-coins"></i></div>
                </div>

                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Logistics Check</h3>
                        <p>100% OK</p>
                    </div>
                    <div class="metric-icon orange"><i class="fa-solid fa-boxes-packing"></i></div>
                </div>
            </div>

            <!-- Table and Roster Grid -->
            <div class="grid grid-2" style="align-items: flex-start;">
                <!-- Event Coordination List -->
                <div class="card" style="padding: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h3 class="title-small" style="font-size: 1.15rem;">Active Schedules</h3>
                        <button class="btn btn-outline" style="padding: 6px 12px; font-size: 0.8rem;" onclick="alert('Creating event page...')">Create Event</button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Venue</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Gala Night Setup</strong></td>
                                    <td>Grand Plaza Hall</td>
                                    <td>Jul 24, 2026</td>
                                    <td><span class="badge badge-success">Confirmed</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Relief Packing</strong></td>
                                    <td>Warehouse B</td>
                                    <td>Jul 25, 2026</td>
                                    <td><span class="badge badge-warning">On Hold</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Volunteer Seminar</strong></td>
                                    <td>Online Zoom</td>
                                    <td>Jul 27, 2026</td>
                                    <td><span class="badge badge-success">Confirmed</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Event Details Timeline -->
                <div class="card" style="padding: 24px;">
                    <h3 class="title-small" style="font-size: 1.15rem; margin-bottom: 20px;">Coordination Milestones</h3>
                    <div class="timeline">
                        <div class="timeline-event">
                            <div class="timeline-dot"></div>
                            <div class="timeline-time">Today - 03:00 PM</div>
                            <div class="timeline-title">Confirm Grand Plaza Caterers</div>
                            <div class="timeline-desc">Finalize budget logs and menu preferences for 120 dinner seats.</div>
                        </div>

                        <div class="timeline-event">
                            <div class="timeline-dot" style="background-color: var(--secondary);"></div>
                            <div class="timeline-time">Tomorrow - 09:00 AM</div>
                            <div class="timeline-title">Warehouse A Safety Check</div>
                            <div class="timeline-desc">Inspect medical inventory boxes and shipping containers.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
