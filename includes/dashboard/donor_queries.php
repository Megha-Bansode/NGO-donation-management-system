<?php
/**
 * Shared queries for the Donor Dashboard (Role ID = 3)
 * Enforces isolation: donor_id = $_SESSION['user_id']
 * Includes request-level caching, conditional aggregation, and PRG transactions.
 */

$_DONOR_CACHE = [];

function getDonorDashboardData(PDO $pdo, int $userId) {
    global $_DONOR_CACHE;
    $cacheKey = "dashboard_data_{$userId}";
    
    if (isset($_DONOR_CACHE[$cacheKey])) {
        return $_DONOR_CACHE[$cacheKey];
    }

    $data = [
        'kpis' => [
            'total_donations' => 0,
            'total_amount' => 0,
            'campaigns_supported' => 0,
            'average_donation' => 0,
            'favorite_cause' => 'None',
            'last_donation_date' => null
        ],
        'activeCampaigns' => [],
        'recentActivity' => [],
        'notifications' => []
    ];

    try {
        // 1. KPI Aggregation for Donations
        $stmtDonations = $pdo->prepare("
            SELECT 
                COUNT(id) as total_donations,
                SUM(amount) as total_amount,
                COUNT(DISTINCT campaign_id) as campaigns_supported,
                AVG(amount) as average_donation,
                MAX(donation_date) as last_donation_date
            FROM donations 
            WHERE donor_id = ? AND payment_status = 'completed'
        ");
        $stmtDonations->execute([$userId]);
        $donKPIs = $stmtDonations->fetch(PDO::FETCH_ASSOC);

        $data['kpis']['total_donations'] = (int)($donKPIs['total_donations'] ?? 0);
        $data['kpis']['total_amount'] = (float)($donKPIs['total_amount'] ?? 0);
        $data['kpis']['campaigns_supported'] = (int)($donKPIs['campaigns_supported'] ?? 0);
        $data['kpis']['average_donation'] = (float)($donKPIs['average_donation'] ?? 0);
        $data['kpis']['last_donation_date'] = $donKPIs['last_donation_date'] ?? null;

        // 2. Favorite Cause (Category)
        $stmtCause = $pdo->prepare("
            SELECT cc.name as favorite_cause
            FROM donations d
            JOIN campaigns c ON d.campaign_id = c.id
            JOIN campaign_categories cc ON c.category_id = cc.id
            WHERE d.donor_id = ? AND d.payment_status = 'completed'
            GROUP BY cc.id
            ORDER BY COUNT(d.id) DESC, SUM(d.amount) DESC
            LIMIT 1
        ");
        $stmtCause->execute([$userId]);
        $cause = $stmtCause->fetchColumn();
        if ($cause) {
            $data['kpis']['favorite_cause'] = $cause;
        }

        // 3. Load Lists Data
        $data['activeCampaigns'] = getDonorCampaigns($pdo, 3);
        $data['recentActivity'] = getDonorRecentActivity($pdo, $userId, 5);
        $data['notifications'] = getDonorNotifications($pdo, $userId, 4);

    } catch (PDOException $e) {
        error_log("Donor Dashboard Data Fetch Error: " . $e->getMessage());
    }

    $_DONOR_CACHE[$cacheKey] = $data;
    return $data;
}

function getDonorCampaigns(PDO $pdo, $limit = 50) {
    global $_DONOR_CACHE;
    $cacheKey = "active_campaigns_{$limit}";
    if (isset($_DONOR_CACHE[$cacheKey])) return $_DONOR_CACHE[$cacheKey];

    $stmt = $pdo->prepare("
        SELECT c.*, cc.name as category_name, cc.icon as category_icon, u.full_name as organization_name
        FROM campaigns c
        JOIN campaign_categories cc ON c.category_id = cc.id
        JOIN users u ON c.created_by = u.id
        WHERE c.status = 'active' AND c.start_date <= CURDATE() AND c.end_date >= CURDATE()
        ORDER BY c.featured DESC, c.start_date DESC
        LIMIT " . (int)$limit
    );
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_DONOR_CACHE[$cacheKey] = $res;
    return $res;
}

function getDonationHistory(PDO $pdo, int $donor_id, $limit = 50) {
    global $_DONOR_CACHE;
    $cacheKey = "donation_history_{$donor_id}_{$limit}";
    if (isset($_DONOR_CACHE[$cacheKey])) return $_DONOR_CACHE[$cacheKey];

    $stmt = $pdo->prepare("
        SELECT d.*, c.name as campaign_name, r.pdf_path
        FROM donations d
        LEFT JOIN campaigns c ON d.campaign_id = c.id
        LEFT JOIN donation_receipts r ON d.id = r.donation_id
        WHERE d.donor_id = ?
        ORDER BY d.donation_date DESC
        LIMIT " . (int)$limit
    );
    $stmt->execute([$donor_id]);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_DONOR_CACHE[$cacheKey] = $res;
    return $res;
}

function getDonorRecentActivity(PDO $pdo, int $donor_id, $limit = 5) {
    global $_DONOR_CACHE;
    $cacheKey = "activity_{$donor_id}_{$limit}";
    if (isset($_DONOR_CACHE[$cacheKey])) return $_DONOR_CACHE[$cacheKey];

    // Generic Event Stream for Donors
    $stmt = $pdo->prepare("
        (
            SELECT CONCAT('Donated $', amount) as title, 'Donation Made' as description, donation_date as event_date, 'fas fa-hand-holding-heart' as icon, 'var(--success)' as color 
            FROM donations WHERE donor_id = ? AND payment_status = 'completed'
        )
        UNION ALL
        (
            SELECT r.receipt_number as title, 'Receipt Generated' as description, r.generated_date as event_date, 'fas fa-file-invoice-dollar' as icon, 'var(--info)' as color 
            FROM donation_receipts r JOIN donations d ON r.donation_id = d.id WHERE d.donor_id = ?
        )
        UNION ALL
        (
            SELECT title as title, 'Notification Received' as description, created_at as event_date, 'fas fa-bell' as icon, 'var(--warning)' as color 
            FROM notifications WHERE recipient_id = ?
        )
        UNION ALL
        (
            SELECT 'Profile Details' as title, 'Profile Updated' as description, updated_at as event_date, 'fas fa-user-edit' as icon, 'var(--primary)' as color 
            FROM users WHERE id = ? AND updated_at > created_at
        )
        ORDER BY event_date DESC
        LIMIT " . (int)$limit
    );
    $stmt->execute([$donor_id, $donor_id, $donor_id, $donor_id]);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_DONOR_CACHE[$cacheKey] = $res;
    return $res;
}

function getDonorNotifications(PDO $pdo, int $donor_id, $limit = 50) {
    global $_DONOR_CACHE;
    $cacheKey = "notifications_{$donor_id}_{$limit}";
    if (isset($_DONOR_CACHE[$cacheKey])) return $_DONOR_CACHE[$cacheKey];

    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE recipient_id = ? AND read_status = 0 
        ORDER BY created_at DESC 
        LIMIT " . (int)$limit
    );
    $stmt->execute([$donor_id]);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_DONOR_CACHE[$cacheKey] = $res;
    return $res;
}

function getDonorAllNotifications(PDO $pdo, int $donor_id) {
    global $_DONOR_CACHE;
    $cacheKey = "all_notifications_{$donor_id}";
    if (isset($_DONOR_CACHE[$cacheKey])) return $_DONOR_CACHE[$cacheKey];

    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE recipient_id = ? 
        ORDER BY created_at DESC 
        LIMIT 50
    ");
    $stmt->execute([$donor_id]);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_DONOR_CACHE[$cacheKey] = $res;
    return $res;
}

function getCampaignDetails(PDO $pdo, int $campaign_id) {
    $stmt = $pdo->prepare("
        SELECT c.*, cc.name as category_name, cc.icon as category_icon, u.full_name as organization_name
        FROM campaigns c
        JOIN campaign_categories cc ON c.category_id = cc.id
        JOIN users u ON c.created_by = u.id
        WHERE c.id = ?
    ");
    $stmt->execute([$campaign_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Process a donation using a PDO Transaction.
 * Validates campaign status, inserts donation, generates receipt, updates campaign totals securely.
 */
function process_donation(PDO $pdo, int $donor_id, int $campaign_id, float $amount, string $payment_method, int $is_anonymous, ?string $message) {
    try {
        $pdo->beginTransaction();

        // 1. Validate Campaign Status
        $stmtCheck = $pdo->prepare("SELECT status FROM campaigns WHERE id = ? FOR UPDATE");
        $stmtCheck->execute([$campaign_id]);
        $status = $stmtCheck->fetchColumn();

        if ($status !== 'active') {
            throw new Exception("Campaign is not active.");
        }

        // 2. Insert Donation
        $transaction_id = 'TXN-' . strtoupper(uniqid());
        $receipt_number = 'REC-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));

        $stmtInsert = $pdo->prepare("
            INSERT INTO donations 
            (donor_id, campaign_id, amount, payment_method, transaction_id, payment_status, receipt_number, is_anonymous, donor_message, donation_date) 
            VALUES (?, ?, ?, ?, ?, 'completed', ?, ?, ?, NOW())
        ");
        $stmtInsert->execute([$donor_id, $campaign_id, $amount, $payment_method, $transaction_id, $receipt_number, $is_anonymous, $message]);
        
        $donation_id = $pdo->lastInsertId();

        // 3. Generate Receipt
        $pdf_path = "receipts/" . $receipt_number . ".pdf"; // Placeholder for actual PDF generation
        $stmtReceipt = $pdo->prepare("
            INSERT INTO donation_receipts (receipt_number, donation_id, pdf_path) 
            VALUES (?, ?, ?)
        ");
        $stmtReceipt->execute([$receipt_number, $donation_id, $pdf_path]);

        // 4. Update Campaign Totals (cached field synchronization)
        $stmtUpdateCamp = $pdo->prepare("
            UPDATE campaigns 
            SET collected_amount = collected_amount + ?, 
                goal_completed_percentage = LEAST((collected_amount / target_amount) * 100, 100)
            WHERE id = ?
        ");
        $stmtUpdateCamp->execute([$amount, $campaign_id]);

        // 5. Create Notification for Donor
        $stmtNotif = $pdo->prepare("
            INSERT INTO notifications (recipient_id, title, message, notification_type) 
            VALUES (?, 'Donation Successful', ?, 'Donation')
        ");
        $msg = "Thank you for your generous donation of " . formatIndianCurrency($amount) . ". Your receipt number is " . $receipt_number . ".";
        $stmtNotif->execute([$donor_id, $msg]);

        $pdo->commit();
        return ['success' => true, 'receipt' => $receipt_number, 'transaction_id' => $transaction_id];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
?>
