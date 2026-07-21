<?php
// Volunteer Update Work Status Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Work Status - Volunteer Dashboard</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Volunteer CSS -->
    <link rel="stylesheet" href="../../assets/css/volunteer.css">
</head>
<body>
    <!-- Header -->
    <?php include '../../includes/header.php'; ?>
    
    <!-- Sidebar -->
    <?php include '../../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right"></i> <a href="tasks.php">Assigned Tasks</a> <i class="fas fa-chevron-right"></i> <span>Update Work Status</span>
        </div>

        <!-- Page Header -->
        <div class="dashboard-header-section">
            <div class="dashboard-titles">
                <h1>Update Work Status</h1>
                <p>Submit progress updates for your assigned volunteer tasks.</p>
            </div>
        </div>

        <!-- Success Alert -->
        <div id="status-success-alert" class="success-alert" style="display: none;">
            <i class="fas fa-check-circle"></i>
            <span>Work status submitted successfully.</span>
            <button class="close-alert"><i class="fas fa-times"></i></button>
        </div>

        <!-- Task Summary -->
        <div class="dashboard-card status-summary-card">
            <div class="card-header">
                <h3><i class="fas fa-clipboard-list text-primary"></i> Task Summary</h3>
                <span class="badge badge-high">High Priority</span>
            </div>
            <div class="summary-grid">
                <div class="summary-item">
                    <span class="summary-label">Task ID</span>
                    <span class="summary-value">#T-102</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Task Name</span>
                    <span class="summary-value">Food Distribution Logistics</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Associated Event</span>
                    <span class="summary-value">Downtown Food Drive</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Assigned By</span>
                    <span class="summary-value">Sarah Jenkins</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Assigned Date</span>
                    <span class="summary-value">Oct 11, 2026</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Deadline</span>
                    <span class="summary-value text-danger">Oct 17, 2026</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Current Status</span>
                    <span class="badge badge-progress">In Progress</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Estimated Hours</span>
                    <span class="summary-value">12 Hours</span>
                </div>
                <div class="summary-item" style="grid-column: 1 / -1;">
                    <span class="summary-label">Location</span>
                    <span class="summary-value">Downtown Main Shelter</span>
                </div>
            </div>
        </div>

        <!-- Main Layout (Two Column) -->
        <div class="status-layout">
            <!-- Left Column: Form -->
            <div class="status-form-container dashboard-card">
                <h3 class="form-section-title"><i class="fas fa-edit text-primary"></i> Work Status Form</h3>
                
                <form id="work-status-form" action="#" method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        
                        <div class="form-group">
                            <label class="form-label">Task Name (Read-only)</label>
                            <input type="text" class="form-control" name="task_name" value="Food Distribution Logistics" readonly>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Current Status (Read-only)</label>
                            <input type="text" class="form-control" name="current_status" value="In Progress" readonly>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Update Status *</label>
                            <select class="form-control" name="update_status" id="update-status" required>
                                <option value="">Select Status</option>
                                <option value="pending">Pending</option>
                                <option value="progress" selected>In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="on-hold">On Hold</option>
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <div class="slider-header">
                                <label class="form-label">Progress Percentage *</label>
                                <span class="progress-display" id="progress-val-display">40%</span>
                            </div>
                            <input type="range" class="status-slider" name="progress_percentage" id="progress-slider" min="0" max="100" value="40" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Hours Worked</label>
                            <input type="number" class="form-control" name="hours_worked" min="0" step="0.5" placeholder="e.g. 2.5">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Work Date</label>
                            <input type="date" class="form-control" name="work_date">
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Remarks *</label>
                            <textarea class="form-control" name="remarks" placeholder="Describe the work completed today..." required></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Challenges Faced</label>
                            <textarea class="form-control" name="challenges" placeholder="Any issues?"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Next Planned Activity</label>
                            <textarea class="form-control" name="next_activity" placeholder="What's next?"></textarea>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Upload Supporting Evidence</label>
                            <div class="file-upload">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click or drag files here</p>
                                <span class="helper-text">Accepts: JPG, PNG, PDF, DOCX (Max 5MB)</span>
                                <input type="file" name="evidence[]" multiple accept=".jpg,.jpeg,.png,.pdf,.docx">
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <h4 style="margin-bottom: 12px; color: var(--text-main); font-size: 1rem;"><i class="fas fa-tasks"></i> Task Checklist</h4>
                            <div class="checklist">
                                <label class="checklist-item"><input type="checkbox" name="checklist[]" value="started" checked> Task Started</label>
                                <label class="checklist-item"><input type="checkbox" name="checklist[]" value="half"> Half Completed</label>
                                <label class="checklist-item"><input type="checkbox" name="checklist[]" value="docs"> Documentation Uploaded</label>
                                <label class="checklist-item"><input type="checkbox" name="checklist[]" value="supervisor"> Supervisor Informed</label>
                                <label class="checklist-item"><input type="checkbox" name="checklist[]" value="completed"> Task Completed</label>
                            </div>
                        </div>

                    </div>
                    
                    <div class="form-actions mt-4">
                        <button type="button" class="action-btn btn-danger" id="cancel-btn"><i class="fas fa-times"></i> Cancel</button>
                        <button type="button" class="action-btn btn-outline"><i class="fas fa-save"></i> Save Draft</button>
                        <button type="submit" class="action-btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Update</button>
                    </div>
                </form>
            </div>

            <!-- Right Column: Visualization & Timeline -->
            <div class="status-sidebar">
                
                <div class="dashboard-card text-center">
                    <h3 class="form-section-title" style="text-align: left;"><i class="fas fa-chart-pie text-primary"></i> Current Progress</h3>
                    <div class="circular-progress">
                        <div class="progress-circle" id="progress-circle">
                            <div class="progress-inner-value" id="progress-inner-val">40%</div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card mt-4">
                    <h3 class="form-section-title"><i class="fas fa-history text-primary"></i> Recent Updates</h3>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h5>Task marked In Progress</h5>
                                <span class="timeline-date">Yesterday, 09:00 AM</span>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h5>Photo uploaded</h5>
                                <span class="timeline-date">Yesterday, 11:30 AM</span>
                                <p class="timeline-desc">Added site_inspection.jpg</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h5>Supervisor reviewed submission</h5>
                                <span class="timeline-date">Yesterday, 02:15 PM</span>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h5>Progress updated to 25%</h5>
                                <span class="timeline-date">Today, 09:30 AM</span>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h5>Progress updated to 40%</h5>
                                <span class="timeline-date">Today, 01:00 PM</span>
                                <p class="timeline-desc">Sorted food packets for route A.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </main>

    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>

    <!-- JS -->
    <script src="../../assets/js/volunteer.js"></script>
</body>
</html>
