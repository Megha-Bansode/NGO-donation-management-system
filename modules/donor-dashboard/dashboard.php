<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Donor Dashboard";
include __DIR__ . '/../../components/header.php';
?>

<div class="dashboard-wrapper">
    <!-- Sidebar Component -->
    <?php include __DIR__ . '/../../components/sidebar.php'; ?>

    <!-- Main Content Pane -->
    <main class="dashboard-main">
        <header class="dashboard-header">
            <div class="dashboard-title-area">
                <h1>Donor Workspace</h1>
                <p>Monitor your donations, download tax invoices, and explore campaign impacts.</p>
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
                        <h3>Total Contributed</h3>
                        <p>$1,250</p>
                    </div>
                    <div class="metric-icon blue"><i class="fa-solid fa-wallet"></i></div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Monthly Subscription</h3>
                        <p>$50/mo</p>
                    </div>
                    <div class="metric-icon green"><i class="fa-solid fa-arrows-spin"></i></div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Campaigns Backed</h3>
                        <p>4</p>
                    </div>
                    <div class="metric-icon purple"><i class="fa-solid fa-heart-pulse"></i></div>
                </div>

                <div class="metric-card">
                    <div class="metric-info">
                        <h3>Tax Certificates</h3>
                        <p>2 Available</p>
                    </div>
                    <div class="metric-icon orange"><i class="fa-solid fa-receipt"></i></div>
                </div>
            </div>

            <!-- Table and Roster Grid -->
            <div class="grid grid-2" style="align-items: flex-start;">
                <!-- Donation History -->
                <div class="card" style="padding: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h3 class="title-small" style="font-size: 1.15rem;">Transaction History</h3>
                        <button class="btn btn-outline" style="padding: 6px 12px; font-size: 0.8rem;">Export CSV</button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Campaign</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Tax Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Cambodia Schools</strong></td>
                                    <td>$250.00</td>
                                    <td>Jun 12, 2026</td>
                                    <td><a href="#" onclick="alert('Downloading Receipt...')" class="btn-link"><i class="fa-solid fa-file-pdf"></i> Download</a></td>
                                </tr>
                                <tr>
                                    <td><strong>Malawi Wells</strong></td>
                                    <td>$500.00</td>
                                    <td>May 02, 2026</td>
                                    <td><a href="#" onclick="alert('Downloading Receipt...')" class="btn-link"><i class="fa-solid fa-file-pdf"></i> Download</a></td>
                                </tr>
                                <tr>
                                    <td><strong>Disaster Relief</strong></td>
                                    <td>$500.00</td>
                                    <td>Mar 15, 2026</td>
                                    <td><a href="#" onclick="alert('Downloading Receipt...')" class="btn-link"><i class="fa-solid fa-file-pdf"></i> Download</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Active Campaigns Highlight Callout -->
                <div class="card" style="padding: 32px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                    <div>
                        <span class="section-tag" style="background-color: rgba(5,150,105,0.2); color: #10b981; font-size: 0.75rem;">Make an Impact</span>
                        <h3 class="title-small" style="color: white; margin-top: 12px; margin-bottom: 12px;">Support Our Emergency Disaster Relief Drive</h3>
                        <p style="color: #94a3b8; font-size: 0.9rem; margin-bottom: 24px;">
                            Monsoon floods have damaged community water rigs and structures. Clean water and food provisions are urgently required.
                        </p>
                    </div>
                    <a href="<?php echo $basePath; ?>#campaigns" class="btn btn-secondary" style="align-self: flex-start;"><i class="fa-solid fa-heart"></i> Donate Again</a>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../../components/footer.php'; ?>
