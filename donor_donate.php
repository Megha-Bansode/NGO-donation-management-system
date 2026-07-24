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
$campaign_id = isset($_GET['campaign_id']) ? (int)$_GET['campaign_id'] : 0;

$campaign = getCampaignDetails($pdo, $campaign_id);

if (!$campaign) {
    header("Location: donor_campaigns.php");
    exit;
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = false;
$receipt = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token. Please try again.";
    } else {
        $amount = (float)($_POST['amount'] ?? 0);
        if (isset($_POST['custom_amount']) && !empty($_POST['custom_amount'])) {
            $amount = (float)$_POST['custom_amount'];
        }

        $payment_method = $_POST['payment_method'] ?? '';
        $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
        $message = trim($_POST['message'] ?? '');

        // Server-side validation
        if ($amount <= 0) {
            $error = "Please enter a valid donation amount greater than zero.";
        } elseif ($amount < 1) {
            $error = "Minimum donation amount is ₹1.";
        } elseif ($amount > 1000000) {
            $error = "Maximum donation amount is ₹10,00,000.";
        } elseif (empty($payment_method)) {
            $error = "Please select a payment method.";
        } else {
            // Process via Transaction
            $result = process_donation($pdo, $donor_id, $campaign_id, $amount, $payment_method, $is_anonymous, $message);

            if ($result['success']) {
                // Post/Redirect/Get to prevent resubmission
                $_SESSION['donation_success'] = true;
                $_SESSION['donation_receipt'] = $result['receipt'];
                header("Location: donor_donate.php?campaign_id={$campaign_id}&status=success");
                exit;
            } else {
                $error = $result['error'] ?? 'An error occurred during payment processing.';
            }
        }
    }
}

// Handle PRG success state
if (isset($_GET['status']) && $_GET['status'] === 'success' && isset($_SESSION['donation_success'])) {
    $success = true;
    $receipt = $_SESSION['donation_receipt'];
    unset($_SESSION['donation_success'], $_SESSION['donation_receipt']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate to <?php echo htmlspecialchars($campaign['name']); ?> | <?php echo APP_NAME; ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/donor.css">
    <style>
        .donation-container { max-width: 800px; margin: 0 auto; }
        .preset-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        @media (max-width: 480px) {
            .preset-grid { grid-template-columns: repeat(2, 1fr); }
        }
        .preset-btn {
            background: white;
            border: 2px solid #e2e8f0;
            padding: 14px 10px;
            border-radius: 12px;
            text-align: center;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-dark);
            cursor: pointer;
            transition: all 0.2s;
            font-family: var(--font-stats);
        }
        .preset-btn:hover {
            border-color: var(--primary-light);
            background: #f8fafc;
            transform: translateY(-2px);
        }
        .preset-btn.active {
            border-color: var(--primary);
            background: rgba(124,154,134,0.08);
            color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124,154,134,0.15);
        }
        .custom-amount-wrapper {
            position: relative;
            margin-bottom: 28px;
        }
        .custom-amount-wrapper .currency-symbol {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-muted);
        }
        .custom-amount-input {
            width: 100%;
            padding: 18px 18px 18px 38px;
            font-size: 1.4rem;
            font-weight: 700;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: var(--font-stats);
            color: var(--text-dark);
        }
        .custom-amount-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124,154,134,0.15);
        }
        .payment-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 28px;
        }
        @media (max-width: 480px) {
            .payment-methods { grid-template-columns: 1fr; }
        }
        .pm-option {
            background: white;
            border: 2px solid #e2e8f0;
            padding: 14px 16px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .pm-option input[type="radio"] { display: none; }
        .pm-option:hover { border-color: var(--primary-light); background: #f8fafc; }
        .pm-option.active {
            border-color: var(--primary);
            background: rgba(124,154,134,0.06);
            box-shadow: 0 0 0 3px rgba(124,154,134,0.12);
        }
        .pm-option .pm-checkmark {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #e2e8f0;
            margin-left: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s;
        }
        .pm-option.active .pm-checkmark {
            background: var(--primary);
            border-color: var(--primary);
        }
        .pm-option.active .pm-checkmark::after {
            content: '';
            display: block;
            width: 6px;
            height: 6px;
            background: white;
            border-radius: 50%;
        }
        .success-box {
            background: linear-gradient(135deg, rgba(16,185,129,0.08) 0%, rgba(16,185,129,0.03) 100%);
            border: 2px solid rgba(16,185,129,0.3);
            padding: 48px 40px;
            border-radius: 20px;
            text-align: center;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 16px;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 0.88rem;
            font-weight: 600;
            transition: color 0.2s;
        }
        .back-link:hover { color: var(--primary); }
    </style>
</head>
<body data-donor-page="donate">
<div class="dashboard-layout">
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>
    <main class="main-content">
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>
        <div class="page-content">

            <a href="donor_campaign_details.php?id=<?php echo $campaign['id']; ?>" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Campaign
            </a>

            <div class="donation-container">
                <?php if ($success): ?>
                    <div class="success-box">
                        <div style="width:80px; height:80px; background:rgba(16,185,129,0.15); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                            <i class="fas fa-check-circle" style="font-size:2.5rem; color:var(--success);"></i>
                        </div>
                        <h2 style="color:var(--text-dark); margin:0 0 12px; font-size:1.6rem;">Thank You for Your Donation!</h2>
                        <p style="color:var(--text-body); margin-bottom:28px; font-size:0.95rem; line-height:1.7;">
                            Your contribution has been securely processed and applied to the
                            <strong><?php echo htmlspecialchars($campaign['name']); ?></strong> campaign.
                            Together we're making a difference!
                        </p>
                        <div style="background:white; display:inline-block; padding:14px 30px; border-radius:12px; margin-bottom:28px; box-shadow:var(--shadow-sm);">
                            <span style="font-size:0.78rem; color:var(--text-muted); display:block; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.5px; font-weight:600;">Receipt Number</span>
                            <strong style="font-size:1.2rem; color:var(--text-dark); font-family:var(--font-stats);"><?php echo htmlspecialchars($receipt); ?></strong>
                        </div>
                        <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                            <a href="donor_receipts.php" class="btn-primary" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                                <i class="fas fa-file-invoice"></i> View Receipt
                            </a>
                            <a href="donor_dashboard.php" class="btn-secondary" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </div>
                    </div>

                <?php else: ?>

                    <?php if ($campaign['status'] !== 'active'): ?>
                        <div class="glass-card" style="text-align:center; padding:48px;">
                            <i class="fas fa-exclamation-triangle fa-3x" style="color:var(--warning); margin-bottom:20px; display:block;"></i>
                            <h2 style="color:var(--text-dark); margin-bottom:12px;">Campaign Inactive</h2>
                            <p style="color:var(--text-muted);">This campaign is currently marked as <strong><?php echo htmlspecialchars($campaign['status']); ?></strong> and is not accepting new donations.</p>
                            <a href="donor_campaigns.php" class="btn-primary" style="text-decoration:none; display:inline-block; margin-top:20px;">Browse Active Campaigns</a>
                        </div>

                    <?php else: ?>

                        <!-- Campaign context banner -->
                        <div style="padding:16px 20px; background:rgba(124,154,134,0.08); border-radius:12px; border-left:4px solid var(--primary); margin-bottom:20px;">
                            <div style="font-size:0.78rem; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">You are donating to</div>
                            <strong style="color:var(--primary); font-size:1.15rem; font-family:var(--font-heading), sans-serif;"><?php echo htmlspecialchars($campaign['name']); ?></strong>
                        </div>

                        <!-- Inline error box (hidden by default, shown by JS) -->
                        <div id="donationFormError" class="donor-inline-error" style="display:none;" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <span class="error-text"></span>
                        </div>

                        <?php if ($error): ?>
                            <div class="donor-alert donor-alert-error" role="alert">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <div class="glass-card">
                            <form action="" method="POST" id="donationForm" novalidate>
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="amount" id="presetAmount" value="">

                                <h3 style="margin:0 0 18px; color:var(--text-dark); font-size:1.05rem; font-weight:700;">
                                    <i class="fas fa-rupee-sign" style="color:var(--primary); margin-right:8px;"></i>Select Amount
                                </h3>

                                <div class="preset-grid">
                                    <div class="preset-btn" data-amount="50">₹50</div>
                                    <div class="preset-btn" data-amount="100">₹100</div>
                                    <div class="preset-btn" data-amount="250">₹250</div>
                                    <div class="preset-btn" data-amount="500">₹500</div>
                                    <div class="preset-btn" data-amount="1000">₹1,000</div>
                                    <div class="preset-btn" data-amount="custom" style="font-size:0.9rem; color:var(--text-muted);">
                                        <i class="fas fa-pen" style="font-size:0.85rem;"></i> Custom
                                    </div>
                                </div>

                                <div class="custom-amount-wrapper" id="customAmountWrapper" style="display:none;">
                                    <span class="currency-symbol">₹</span>
                                    <input type="number" name="custom_amount" class="custom-amount-input" placeholder="0.00"
                                           min="1" max="1000000" step="1" id="customAmountInput"
                                           aria-label="Enter custom donation amount">
                                </div>

                                <h3 style="color:var(--text-dark); margin:4px 0 16px; font-size:1.05rem; font-weight:700;">
                                    <i class="fas fa-credit-card" style="color:var(--primary); margin-right:8px;"></i>Payment Method
                                </h3>
                                <div class="payment-methods" role="radiogroup" aria-label="Select payment method">
                                    <label class="pm-option" for="pm_card">
                                        <input type="radio" name="payment_method" id="pm_card" value="Credit Card" aria-required="true">
                                        <i class="fas fa-credit-card" style="font-size:1.4rem; color:var(--text-dark); flex-shrink:0;"></i>
                                        <div>
                                            <strong style="display:block; font-size:0.9rem;">Credit/Debit Card</strong>
                                            <span style="font-size:0.72rem; color:var(--text-muted);">Secure SSL Processing</span>
                                        </div>
                                        <div class="pm-checkmark"></div>
                                    </label>
                                    <label class="pm-option" for="pm_paypal">
                                        <input type="radio" name="payment_method" id="pm_paypal" value="PayPal">
                                        <i class="fab fa-paypal" style="font-size:1.4rem; color:#003087; flex-shrink:0;"></i>
                                        <div>
                                            <strong style="display:block; font-size:0.9rem;">PayPal</strong>
                                            <span style="font-size:0.72rem; color:var(--text-muted);">Redirects to PayPal</span>
                                        </div>
                                        <div class="pm-checkmark"></div>
                                    </label>
                                    <label class="pm-option" for="pm_bank">
                                        <input type="radio" name="payment_method" id="pm_bank" value="Bank Transfer">
                                        <i class="fas fa-university" style="font-size:1.4rem; color:var(--text-dark); flex-shrink:0;"></i>
                                        <div>
                                            <strong style="display:block; font-size:0.9rem;">Bank Transfer</strong>
                                            <span style="font-size:0.72rem; color:var(--text-muted);">Direct deposit</span>
                                        </div>
                                        <div class="pm-checkmark"></div>
                                    </label>
                                    <label class="pm-option" for="pm_upi">
                                        <input type="radio" name="payment_method" id="pm_upi" value="UPI">
                                        <i class="fas fa-mobile-alt" style="font-size:1.4rem; color:var(--primary); flex-shrink:0;"></i>
                                        <div>
                                            <strong style="display:block; font-size:0.9rem;">UPI</strong>
                                            <span style="font-size:0.72rem; color:var(--text-muted);">GPay, PhonePe, Paytm</span>
                                        </div>
                                        <div class="pm-checkmark"></div>
                                    </label>
                                </div>

                                <h3 style="color:var(--text-dark); margin:4px 0 16px; font-size:1.05rem; font-weight:700;">
                                    <i class="fas fa-sliders-h" style="color:var(--primary); margin-right:8px;"></i>Additional Options
                                </h3>

                                <div class="form-group" style="margin-bottom:18px;">
                                    <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer;">
                                        <input type="checkbox" name="is_anonymous" value="1"
                                               style="width:18px; height:18px; margin-top:2px; accent-color:var(--primary);">
                                        <div>
                                            <span style="font-weight:600; color:var(--text-dark);">Make this donation anonymous</span>
                                            <p style="font-size:0.78rem; color:var(--text-muted); margin:3px 0 0;">Your name will not appear on the public donor wall for this campaign.</p>
                                        </div>
                                    </label>
                                </div>

                                <div class="form-group" style="margin-bottom:28px;">
                                    <label for="message" style="font-weight:600; color:var(--text-dark); margin-bottom:8px; display:block;">
                                        Message of Support <span style="font-weight:400; color:var(--text-muted);">(Optional)</span>
                                    </label>
                                    <textarea name="message" id="message" rows="3" class="form-control"
                                              placeholder="E.g., Keep up the great work! You're making a difference."></textarea>
                                </div>

                                <button type="submit" class="btn-primary" id="submitBtn"
                                        style="width:100%; padding:16px; font-size:1.1rem; border-radius:12px; display:flex; align-items:center; justify-content:center; gap:10px;">
                                    <i class="fas fa-lock"></i> Complete Donation
                                </button>

                                <div style="text-align:center; margin-top:14px; font-size:0.78rem; color:var(--text-muted);">
                                    <i class="fas fa-shield-alt" style="color:var(--success);"></i>
                                    256-bit SSL encrypted &bull; Safe &amp; Secure
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<script>
    // Preset amount button interaction
    document.addEventListener('DOMContentLoaded', () => {
        const presetBtns    = document.querySelectorAll('.preset-btn');
        const customWrapper = document.getElementById('customAmountWrapper');
        const presetInput   = document.getElementById('presetAmount');
        const customInput   = document.getElementById('customAmountInput');

        presetBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                presetBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const amt = btn.dataset.amount;
                if (amt === 'custom') {
                    customWrapper.style.display = 'block';
                    presetInput.value = '';
                    customInput.focus();
                } else {
                    customWrapper.style.display = 'none';
                    presetInput.value = amt;
                    customInput.value = '';
                }
            });
        });

        // Payment method selection visual
        const pmOptions = document.querySelectorAll('.pm-option');
        pmOptions.forEach(opt => {
            const radio = opt.querySelector('input[type="radio"]');
            opt.addEventListener('click', () => {
                pmOptions.forEach(o => o.classList.remove('active'));
                opt.classList.add('active');
                if (radio) radio.checked = true;
            });
        });
    });
</script>
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/donor.js"></script>
</body>
</html>
