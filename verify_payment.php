<?php
/**
 * Phase 4: Secure Payment Verification & Donation Processing
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Logger.php';
require_once __DIR__ . '/modules/payment/RazorpayService.php';
require_once __DIR__ . '/vendor/autoload.php';

use Razorpay\Api\Api;

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['razorpay_payment_id']) || !isset($input['razorpay_order_id']) || !isset($input['razorpay_signature'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid payload.']);
    exit;
}

$paymentId = $input['razorpay_payment_id'];
$orderId = $input['razorpay_order_id'];
$signature = $input['razorpay_signature'];

$razorpayService = new RazorpayService();

// Step 1: Verify Signature
if (!$razorpayService->verifySignature($orderId, $paymentId, $signature)) {
    // Step 2: If signature is invalid
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Payment Failed: Invalid Signature']);
    exit;
}

// Step 3: If signature is valid, fetch order details from Razorpay to ensure amount and campaign are correct
try {
    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
    $order = $api->order->fetch($orderId);
    
    $amount = $order->amount / 100; // Convert paise to INR
    $campaignId = isset($order->notes->campaign_id) ? (int)$order->notes->campaign_id : 0;
    
    if (!$campaignId) {
        throw new Exception("Missing campaign reference in order notes.");
    }
    
    $donorId = $_SESSION['user_id'] ?? 0;
    if (!$donorId) {
        throw new Exception("Session expired or donor not logged in.");
    }

    $pdo = getDatabase();
    
    // Check for duplicate payment (duplicate payment_id/transaction_id)
    $stmtCheckDup = $pdo->prepare("SELECT id FROM donations WHERE transaction_id = ?");
    $stmtCheckDup->execute([$paymentId]);
    if ($stmtCheckDup->fetchColumn()) {
        throw new Exception("Duplicate payment callback.");
    }

    // Begin Database Transaction
    $pdo->beginTransaction();

    // Step 4: Insert Donation
    $receiptNumber = 'REC-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));
    $paymentMethod = 'Razorpay - ' . $order->currency; // Store Gateway and Currency here
    
    $stmtInsert = $pdo->prepare("
        INSERT INTO donations 
        (donor_id, campaign_id, amount, payment_method, transaction_id, payment_status, receipt_number, is_anonymous, donor_message, donation_date) 
        VALUES (?, ?, ?, ?, ?, 'completed', ?, 0, '', NOW())
    ");
    $stmtInsert->execute([$donorId, $campaignId, $amount, $paymentMethod, $paymentId, $receiptNumber]);
    $donationId = $pdo->lastInsertId();

    // Step 5: Update Campaign
    $stmtUpdateCamp = $pdo->prepare("
        UPDATE campaigns 
        SET collected_amount = collected_amount + ?, 
            goal_completed_percentage = LEAST((collected_amount / target_amount) * 100, 100)
        WHERE id = ?
    ");
    $stmtUpdateCamp->execute([$amount, $campaignId]);

    // Step 6: Generate Receipt (DB Entry)
    $pdfPath = "receipts/" . $receiptNumber . ".pdf";
    $stmtReceipt = $pdo->prepare("
        INSERT INTO donation_receipts (receipt_number, donation_id, pdf_path) 
        VALUES (?, ?, ?)
    ");
    $stmtReceipt->execute([$receiptNumber, $donationId, $pdfPath]);

    // Step 7: Notifications (Dashboard Synchronization)
    $stmtNotif = $pdo->prepare("INSERT INTO notifications (recipient_id, role_id, title, message, notification_type) VALUES (?, ?, ?, ?, ?)");
    
    // Donor Notification
    $stmtNotif->execute([$donorId, 3, 'Donation Successful', 'Your donation has been received successfully.', 'Donation']);
    
    // NGO Admin Notification (Campaign Creator)
    $stmtNGO = $pdo->prepare("SELECT created_by FROM campaigns WHERE id = ?");
    $stmtNGO->execute([$campaignId]);
    $ngoAdminId = $stmtNGO->fetchColumn();
    if ($ngoAdminId) {
        $stmtNotif->execute([$ngoAdminId, 2, 'New Donation', 'New donation received.', 'Donation']);
    }

    // Super Admin Notification
    $stmtSA = $pdo->query("SELECT id FROM users WHERE role_id = 1");
    while ($saId = $stmtSA->fetchColumn()) {
        $stmtNotif->execute([$saId, 1, 'Payment Verified', 'Payment successfully verified.', 'System']);
    }

    // Step 8: Activity Log
    Logger::logActivity($pdo, $donorId, 3, 'Donation', 'Successful Payment', 'Donor completed a verified Razorpay payment.');

    // Step 9: Commit Transaction
    $pdo->commit();

    echo json_encode([
        'status' => 'success', 
        'message' => 'Payment verified and donation saved successfully.',
        'receipt' => $receiptNumber
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Processing failed: ' . $e->getMessage()]);
}
