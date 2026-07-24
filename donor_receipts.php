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

$receiptNum  = isset($_GET['receipt']) ? trim($_GET['receipt']) : null;
$viewReceipt = null;
$receiptError = false;

if ($receiptNum) {
    // Validate receipt belongs to this donor
    $stmt = $pdo->prepare("
        SELECT r.*, d.amount, d.transaction_id, d.payment_method, d.donation_date, 
               c.name as campaign_name, u.full_name as donor_name, u.email as donor_email
        FROM donation_receipts r
        JOIN donations d ON r.donation_id = d.id
        LEFT JOIN campaigns c ON d.campaign_id = c.id
        JOIN users u ON d.donor_id = u.id
        WHERE r.receipt_number = ? AND d.donor_id = ?
    ");
    $stmt->execute([$receiptNum, $donor_id]);
    $viewReceipt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$viewReceipt) {
        $receiptError = true;
    }
} else {
    // List all receipts
    $stmt = $pdo->prepare("
        SELECT r.*, d.amount, d.donation_date, c.name as campaign_name
        FROM donation_receipts r
        JOIN donations d ON r.donation_id = d.id
        LEFT JOIN campaigns c ON d.campaign_id = c.id
        WHERE d.donor_id = ?
        ORDER BY r.generated_date DESC
    ");
    $stmt->execute([$donor_id]);
    $receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $viewReceipt ? 'Receipt ' . htmlspecialchars($receiptNum) : 'My Receipts'; ?> | <?php echo APP_NAME; ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/donor.css">
    <style>
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 44px 48px;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            border: 1px solid #e2e8f0;
        }
        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 24px;
            margin-bottom: 32px;
        }
        .receipt-brand {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: var(--font-heading), sans-serif;
        }
        .receipt-meta {
            text-align: right;
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.7;
        }
        .receipt-body { margin-bottom: 28px; }
        .receipt-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px dashed #e2e8f0;
            gap: 16px;
        }
        .receipt-row:last-child { border-bottom: none; }
        .receipt-label {
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.88rem;
            flex-shrink: 0;
        }
        .receipt-val {
            color: var(--text-dark);
            font-weight: 700;
            font-size: 0.9rem;
            text-align: right;
        }
        .receipt-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            font-size: 1.35rem;
            font-weight: 800;
            background: linear-gradient(135deg, rgba(16,185,129,0.08) 0%, rgba(16,185,129,0.04) 100%);
            border-radius: 12px;
            margin-top: 24px;
            border: 1px solid rgba(16,185,129,0.2);
            font-family: var(--font-stats);
        }
        .receipt-footer {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.8rem;
            padding-top: 24px;
            border-top: 2px solid #f1f5f9;
            line-height: 1.7;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 0.88rem;
            font-weight: 600;
            transition: color 0.2s;
        }
        .back-link:hover { color: var(--primary); }
        @media print {
            .sidebar, .topbar, .page-header, .btn-primary, .btn-secondary, .print-hide, .main-content > *:not(.page-content) {
                display: none !important;
            }
            .dashboard-layout { display: block; }
            .main-content, .page-content { margin: 0; padding: 0; width: 100%; }
            .receipt-container { box-shadow: none; border: none; padding: 0; }
        }
    </style>
</head>
<body data-donor-page="receipts">
<div class="dashboard-layout">
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>
        <div class="page-content">

            <?php if ($receiptError): ?>
                <!-- Receipt Not Found -->
                <div class="page-header print-hide">
                    <div class="page-title">
                        <h1>Receipt Not Found</h1>
                    </div>
                </div>
                <div class="glass-card" style="max-width:600px; text-align:center; padding:48px;">
                    <i class="fas fa-file-excel fa-3x" style="color:var(--danger); margin-bottom:20px; display:block; opacity:0.6;"></i>
                    <h3 style="color:var(--text-dark); margin-bottom:12px;">Receipt Not Found</h3>
                    <p style="color:var(--text-muted); margin-bottom:24px;">The receipt <strong><?php echo htmlspecialchars($receiptNum); ?></strong> could not be found or does not belong to your account.</p>
                    <a href="donor_receipts.php" class="btn-primary" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                        <i class="fas fa-arrow-left"></i> Back to Receipts
                    </a>
                </div>

            <?php elseif ($viewReceipt): ?>
                <!-- Single Receipt View -->
                <div class="print-hide" style="margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; max-width:800px; margin-left:auto; margin-right:auto;">
                    <a href="donor_receipts.php" class="back-link">
                        <i class="fas fa-arrow-left"></i> Back to Receipts
                    </a>
                    <div style="display:flex; gap:10px;">
                        <button onclick="window.print()" class="btn-primary" style="padding:8px 16px; display:inline-flex; align-items:center; gap:8px;">
                            <i class="fas fa-print"></i> Print Receipt
                        </button>
                    </div>
                </div>

                <div class="receipt-container">
                    <div class="receipt-header">
                        <div class="receipt-brand">
                            <i class="fas fa-hands-helping"></i> <?php echo APP_NAME; ?>
                        </div>
                        <div class="receipt-meta">
                            <strong style="color:var(--text-dark); display:block; font-size:1rem;">DONATION RECEIPT</strong>
                            <span>No: <?php echo htmlspecialchars($viewReceipt['receipt_number']); ?></span><br>
                            <span>Date: <?php echo date('F d, Y', strtotime($viewReceipt['generated_date'])); ?></span>
                        </div>
                    </div>

                    <div class="receipt-body">
                        <div style="margin-bottom:28px; padding:16px; background:#f8fafc; border-radius:10px;">
                            <strong style="color:var(--text-muted); font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px; display:block; margin-bottom:6px;">Received From</strong>
                            <div style="color:var(--text-dark); font-weight:700; font-size:1rem;"><?php echo htmlspecialchars($viewReceipt['donor_name']); ?></div>
                            <div style="color:var(--text-muted); font-size:0.88rem;"><?php echo htmlspecialchars($viewReceipt['donor_email']); ?></div>
                        </div>

                        <div class="receipt-body-wrapper">
                            <!-- Paid watermark -->
                            <div class="receipt-paid-stamp">PAID</div>

                            <div class="receipt-row">
                                <span class="receipt-label"><i class="fas fa-bullhorn" style="margin-right:6px; color:var(--primary);"></i>Campaign</span>
                                <span class="receipt-val"><?php echo htmlspecialchars($viewReceipt['campaign_name'] ?? 'General Fund'); ?></span>
                            </div>
                            <div class="receipt-row">
                                <span class="receipt-label"><i class="fas fa-receipt" style="margin-right:6px; color:var(--primary);"></i>Transaction ID</span>
                                <span class="receipt-val" style="font-family:var(--font-stats); font-size:0.85rem;"><?php echo htmlspecialchars($viewReceipt['transaction_id']); ?></span>
                            </div>
                            <div class="receipt-row">
                                <span class="receipt-label"><i class="fas fa-calendar-alt" style="margin-right:6px; color:var(--primary);"></i>Donation Date</span>
                                <span class="receipt-val"><?php echo date('F d, Y g:i A', strtotime($viewReceipt['donation_date'])); ?></span>
                            </div>
                            <div class="receipt-row">
                                <span class="receipt-label"><i class="fas fa-credit-card" style="margin-right:6px; color:var(--primary);"></i>Payment Method</span>
                                <span class="receipt-val"><?php echo htmlspecialchars($viewReceipt['payment_method']); ?></span>
                            </div>
                            <div class="receipt-row">
                                <span class="receipt-label"><i class="fas fa-check-circle" style="margin-right:6px; color:var(--success);"></i>Status</span>
                                <span class="receipt-val" style="color:var(--success);">
                                    <i class="fas fa-check-circle"></i> Completed
                                </span>
                            </div>
                        </div>

                        <div class="receipt-total">
                            <span>Total Donation Amount</span>
                            <span style="color:var(--success);"><?php echo formatIndianCurrency($viewReceipt['amount']); ?></span>
                        </div>
                    </div>

                    <div class="receipt-footer">
                        <p>Thank you for your generous contribution to <strong><?php echo APP_NAME; ?></strong>.</p>
                        <p style="margin-top:4px;">This receipt is computer-generated and does not require a signature.</p>
                        <p style="margin-top:4px; font-weight:700; color:var(--text-dark);">All donations are tax-deductible to the extent permitted by law.</p>
                    </div>
                </div>

            <?php else: ?>
                <!-- Receipts List -->
                <div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
                    <div class="page-title">
                        <h1>My Receipts</h1>
                        <p style="color:var(--text-muted); margin-top:6px;">View and print your official donation receipts.</p>
                    </div>
                </div>

                <div class="glass-card" style="padding:0; overflow:hidden;">
                    <?php if (empty($receipts)): ?>
                        <div class="donor-empty-state" style="padding:64px 24px;">
                            <div class="donor-empty-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                            <div class="donor-empty-title">No Receipts Available</div>
                            <div class="donor-empty-text">Receipts are generated automatically after each completed donation. Make your first donation to see receipts here.</div>
                            <a href="donor_campaigns.php" class="btn-primary" style="text-decoration:none; display:inline-block; margin-top:4px;">Browse Campaigns</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Receipt Number</th>
                                        <th>Date Generated</th>
                                        <th>Donation Date</th>
                                        <th>Campaign</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($receipts as $r): ?>
                                    <tr>
                                        <td>
                                            <a href="donor_receipts.php?receipt=<?php echo urlencode($r['receipt_number']); ?>"
                                               style="text-decoration:none; color:var(--primary); font-weight:700; font-family:var(--font-stats); font-size:0.88rem; display:flex; align-items:center; gap:6px;">
                                                <i class="fas fa-file-pdf"></i>
                                                <?php echo htmlspecialchars($r['receipt_number']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span style="font-size:0.85rem; color:var(--text-muted);">
                                                <?php echo date('M d, Y', strtotime($r['generated_date'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span style="font-size:0.85rem; color:var(--text-muted);">
                                                <?php echo !empty($r['donation_date']) ? date('M d, Y', strtotime($r['donation_date'])) : '—'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span style="color:var(--text-dark); font-size:0.9rem;">
                                                <?php echo htmlspecialchars($r['campaign_name'] ?? 'General Fund'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong style="color:var(--success); font-family:var(--font-stats);">
                                                <?php echo formatIndianCurrency($r['amount']); ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <a href="donor_receipts.php?receipt=<?php echo urlencode($r['receipt_number']); ?>"
                                               class="btn-secondary"
                                               style="padding:5px 12px; font-size:0.78rem; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </main>
</div>
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/donor.js"></script>
</body>
</html>
