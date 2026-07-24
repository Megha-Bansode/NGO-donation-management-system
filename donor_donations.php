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
$donations = getDonationHistory($pdo, $donor_id, 100);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Donations | <?php echo APP_NAME; ?></title>
    <meta name="description" content="View and track all your donation history, filter by status or date, and download receipts.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/donor.css">
</head>
<body data-donor-page="donations">
<div class="dashboard-layout">
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>
        <div class="page-content">

            <div class="page-header">
                <div class="page-title">
                    <h1>My Donations</h1>
                    <p style="color:var(--text-muted); margin-top:6px;">Track your contributions and access your receipts.</p>
                </div>
            </div>

            <?php if (empty($donations)): ?>
                <div class="glass-card">
                    <div class="donor-empty-state">
                        <div class="donor-empty-icon"><i class="fas fa-hand-holding-heart"></i></div>
                        <div class="donor-empty-title">No Donations Yet</div>
                        <div class="donor-empty-text">You haven't made any donations yet. Browse our active campaigns and start making a difference today!</div>
                        <a href="donor_campaigns.php" class="btn-primary" style="text-decoration:none; display:inline-block; margin-top:4px;">Browse Campaigns</a>
                    </div>
                </div>
            <?php else: ?>

                <!-- Filter Toolbar -->
                <div class="donor-filter-bar">
                    <div class="donor-search-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="donationSearch" class="donor-search-input" placeholder="Search by transaction ID or campaign…" aria-label="Search donations">
                    </div>

                    <select id="donationStatusFilter" class="donor-filter-select" aria-label="Filter by status">
                        <option value="">All Statuses</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>

                    <select id="donationDateFilter" class="donor-filter-select" aria-label="Filter by date range">
                        <option value="">All Time</option>
                        <option value="30">Last 30 Days</option>
                        <option value="90">Last 90 Days</option>
                        <option value="365">Last Year</option>
                    </select>

                    <span class="filter-result-count" id="donationResultCount"></span>
                </div>

                <div class="glass-card" style="padding:0; overflow:hidden;">
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Transaction</th>
                                    <th>Campaign</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="donationsTableBody">
                                <?php foreach($donations as $don):
                                    $badgeClass = 'status-pending';
                                    if ($don['payment_status'] === 'completed') $badgeClass = 'status-active';
                                    if ($don['payment_status'] === 'failed')    $badgeClass = 'status-inactive';
                                ?>
                                <tr data-row="1"
                                    data-txn="<?php echo strtolower(htmlspecialchars($don['transaction_id'])); ?>"
                                    data-campaign="<?php echo strtolower(htmlspecialchars($don['campaign_name'] ?? 'general fund')); ?>"
                                    data-status="<?php echo strtolower(htmlspecialchars($don['payment_status'])); ?>"
                                    data-date="<?php echo htmlspecialchars($don['donation_date']); ?>">

                                    <td>
                                        <strong style="color:var(--text-dark); display:block; font-size:0.88rem; font-family:var(--font-stats);">
                                            <?php echo htmlspecialchars($don['transaction_id']); ?>
                                        </strong>
                                        <span style="font-size:0.75rem; color:var(--text-muted);">
                                            <i class="fas fa-credit-card"></i> <?php echo htmlspecialchars($don['payment_method']); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php if($don['campaign_id']): ?>
                                            <a href="donor_campaign_details.php?id=<?php echo $don['campaign_id']; ?>"
                                               style="text-decoration:none; color:var(--text-dark); font-weight:600; font-size:0.9rem;">
                                                <?php echo htmlspecialchars($don['campaign_name']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span style="color:var(--text-muted); font-style:italic; font-size:0.9rem;">General Fund</span>
                                        <?php endif; ?>
                                        <?php if($don['is_anonymous']): ?>
                                            <div style="font-size:0.72rem; color:var(--warning); margin-top:3px; display:flex; align-items:center; gap:3px;">
                                                <i class="fas fa-user-secret"></i> Anonymous
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <span style="font-size:0.85rem; color:var(--text-dark); display:block;">
                                            <?php echo date('M d, Y', strtotime($don['donation_date'])); ?>
                                        </span>
                                        <span style="font-size:0.75rem; color:var(--text-muted);">
                                            <?php echo date('g:i A', strtotime($don['donation_date'])); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <strong style="color:var(--success); font-size:1rem; font-family:var(--font-stats);">
                                            <?php echo formatIndianCurrency($don['amount']); ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <span class="status-badge <?php echo $badgeClass; ?>">
                                            <?php echo ucfirst($don['payment_status']); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php if ($don['payment_status'] === 'completed' && !empty($don['receipt_number'])): ?>
                                            <a href="donor_receipts.php?receipt=<?php echo htmlspecialchars($don['receipt_number']); ?>"
                                               class="btn-secondary"
                                               style="padding:5px 12px; font-size:0.78rem; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                                                <i class="fas fa-file-invoice"></i> Receipt
                                            </a>
                                        <?php else: ?>
                                            <span style="font-size:0.78rem; color:var(--text-muted);">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- No results message -->
                    <div id="donationsNoResults" class="donor-no-results" style="display:none;">
                        <i class="fas fa-filter" style="font-size:2rem; opacity:0.25; display:block; margin-bottom:10px;"></i>
                        No donations match your filters. Try adjusting the search or date range.
                    </div>

                    <!-- Page info -->
                    <div class="donor-page-info" id="donationPageInfo"></div>

                    <!-- Pagination -->
                    <div class="donor-pagination" id="donationPagination"></div>
                </div>

            <?php endif; ?>

        </div>
    </main>
</div>
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/donor.js"></script>
</body>
</html>
