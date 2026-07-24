<?php
// includes/dashboard/admin_queries.php

/**
 * Super Admin Dashboard Database Queries
 * All functions use PDO prepared statements to ensure security.
 */

/**
 * Fetch all top-level KPIs for the Super Admin Dashboard
 */
function get_dashboard_kpis($pdo) {
    $kpis = [
        'total_ngos' => 0,
        'total_users' => 0,
        'total_donations' => 0,
        'total_amount' => 0,
        'active_campaigns' => 0,
        'total_volunteers' => 0,
        'total_events' => 0,
        'pending_approvals' => 0
    ];

    try {
        // 1. Total NGOs (Users with role 'ngo' or 'organization')
        $stmt = $pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name LIKE '%ngo%' OR r.name LIKE '%organization%' OR r.name = 'Admin'");
        $kpis['total_ngos'] = (int)$stmt->fetchColumn();

        // 2. Total Users
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $kpis['total_users'] = (int)$stmt->fetchColumn();

        // 3. Total Donations Count (Completed)
        $stmt = $pdo->query("SELECT COUNT(*) FROM donations WHERE payment_status = 'completed'");
        $kpis['total_donations'] = (int)$stmt->fetchColumn();

        // 4. Total Amount Raised (Completed)
        $stmt = $pdo->query("SELECT SUM(amount) FROM donations WHERE payment_status = 'completed'");
        $kpis['total_amount'] = (float)$stmt->fetchColumn();

        // 5. Active Campaigns
        $stmt = $pdo->query("SELECT COUNT(*) FROM campaigns WHERE status = 'active'");
        $kpis['active_campaigns'] = (int)$stmt->fetchColumn();

        // 6. Volunteers (Users with role 'volunteer')
        $stmt = $pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name LIKE '%volunteer%'");
        $kpis['total_volunteers'] = (int)$stmt->fetchColumn();

        // 7. Events (Upcoming or Ongoing)
        $stmt = $pdo->query("SELECT COUNT(*) FROM events WHERE status IN ('upcoming', 'ongoing')");
        $kpis['total_events'] = (int)$stmt->fetchColumn();

        // 8. Pending Approvals (Pending Campaigns + Pending Users)
        // Note: Assuming 'draft' or 'pending' exists. Fallback to draft if pending is not enum value.
        $stmt = $pdo->query("SELECT COUNT(*) FROM campaigns WHERE status = 'draft'");
        $pending_campaigns = (int)$stmt->fetchColumn();
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'inactive'");
        $pending_users = (int)$stmt->fetchColumn();
        
        $kpis['pending_approvals'] = $pending_campaigns + $pending_users;

    } catch (PDOException $e) {
        error_log("KPI Query Error: " . $e->getMessage());
    }

    return $kpis;
}

/**
 * Fetch Recent Donations
 */
function get_recent_donations($pdo, $limit = 10) {
    try {
        $stmt = $pdo->prepare("
            SELECT d.amount, d.donation_date, d.payment_status, 
                   u.full_name as donor_name, 
                   c.name as campaign_name
            FROM donations d
            LEFT JOIN users u ON d.donor_id = u.id
            LEFT JOIN campaigns c ON d.campaign_id = c.id
            ORDER BY d.donation_date DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Recent Donations Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Fetch Upcoming Events
 */
function get_upcoming_events($pdo, $limit = 5) {
    try {
        $stmt = $pdo->prepare("
            SELECT title, event_date, event_time, venue, status
            FROM events
            WHERE event_date >= CURDATE()
            ORDER BY event_date ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Upcoming Events Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Fetch Latest Campaigns
 */
function get_latest_campaigns($pdo, $limit = 5) {
    try {
        $stmt = $pdo->prepare("
            SELECT name, target_amount, collected_amount, goal_completed_percentage, status, campaign_image
            FROM campaigns
            ORDER BY created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Latest Campaigns Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Fetch Chart Data (Monthly Donations)
 */
function get_chart_data($pdo) {
    $data = [
        'labels' => [],
        'amounts' => []
    ];
    
    try {
        // Last 6 months of donations
        $stmt = $pdo->query("
            SELECT DATE_FORMAT(donation_date, '%b %Y') as month, 
                   SUM(amount) as total
            FROM donations
            WHERE payment_status = 'completed'
              AND donation_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY YEAR(donation_date), MONTH(donation_date)
            ORDER BY YEAR(donation_date) ASC, MONTH(donation_date) ASC
        ");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data['labels'][] = $row['month'];
            $data['amounts'][] = (float)$row['total'];
        }
    } catch (PDOException $e) {
        error_log("Chart Data Error: " . $e->getMessage());
    }
    
    return $data;
}

/**
 * Fetch Recent Activity Logs
 */
function get_recent_activity($pdo, $limit = 8) {
    try {
        // Check if activity_logs table exists first
        $stmt = $pdo->query("SHOW TABLES LIKE 'activity_logs'");
        if ($stmt->rowCount() == 0) return [];

        $stmt = $pdo->prepare("
            SELECT a.action, a.module, a.timestamp as created_at, u.full_name
            FROM activity_logs a
            LEFT JOIN users u ON a.user_id = u.id
            ORDER BY a.timestamp DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return []; // Suppress if table doesn't exist yet
    }
}

/**
 * Fetch Unread Notifications
 */
function get_unread_notifications($pdo, $limit = 5) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
        if ($stmt->rowCount() == 0) return [];

        $stmt = $pdo->prepare("
            SELECT title, message, created_at, type
            FROM notifications
            WHERE is_read = 0
            ORDER BY created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Fetch Contact Messages
 */
function get_contact_messages($pdo, $limit = 5) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'contact_messages'");
        if ($stmt->rowCount() == 0) return [];

        $stmt = $pdo->prepare("
            SELECT name, email, subject, created_at, status
            FROM contact_messages
            ORDER BY created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}
?>
