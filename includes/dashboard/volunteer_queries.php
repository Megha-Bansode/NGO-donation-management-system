<?php
/**
 * Shared queries for the Volunteer Dashboard (Role ID = 4)
 * These queries enforce isolation: e.g. volunteer_id = $_SESSION['user_id']
 * Includes request-level caching and conditional aggregation for performance.
 */

// Request-level cache
$_VOLUNTEER_CACHE = [];

function getVolunteerDashboardData(PDO $pdo, int $userId) {
    global $_VOLUNTEER_CACHE;
    $cacheKey = "dashboard_data_{$userId}";
    
    if (isset($_VOLUNTEER_CACHE[$cacheKey])) {
        return $_VOLUNTEER_CACHE[$cacheKey];
    }

    $data = [
        'kpis' => [
            'assigned_events' => 0,
            'upcoming_events' => 0,
            'assigned_tasks' => 0,
            'completed_tasks' => 0,
            'total_hours' => 0,
            'attendance_percentage' => 0
        ],
        'upcomingEvents' => [],
        'assignedTasks' => [],
        'recentActivity' => [],
        'notifications' => []
    ];

    try {
        // 1. KPI Aggregation for Tasks
        $stmtTasks = $pdo->prepare("
            SELECT 
                SUM(CASE WHEN completion_status != 'completed' THEN 1 ELSE 0 END) as assigned_tasks,
                SUM(CASE WHEN completion_status = 'completed' THEN 1 ELSE 0 END) as completed_tasks
            FROM tasks 
            WHERE volunteer_id = ?
        ");
        $stmtTasks->execute([$userId]);
        $taskCounts = $stmtTasks->fetch(PDO::FETCH_ASSOC);
        $data['kpis']['assigned_tasks'] = (int)($taskCounts['assigned_tasks'] ?? 0);
        $data['kpis']['completed_tasks'] = (int)($taskCounts['completed_tasks'] ?? 0);

        // 2. KPI Aggregation for Events & Attendance
        $stmtEvents = $pdo->prepare("
            SELECT 
                COUNT(vr.id) as assigned_events,
                SUM(CASE WHEN e.event_date > CURDATE() AND e.status != 'completed' THEN 1 ELSE 0 END) as upcoming_events,
                SUM(CASE WHEN e.event_date < CURDATE() THEN 1 ELSE 0 END) as past_assigned_events
            FROM volunteer_registrations vr
            JOIN events e ON vr.event_id = e.id
            WHERE vr.volunteer_id = ? AND vr.approval_status = 'approved'
        ");
        $stmtEvents->execute([$userId]);
        $eventCounts = $stmtEvents->fetch(PDO::FETCH_ASSOC);
        
        $data['kpis']['assigned_events'] = (int)($eventCounts['assigned_events'] ?? 0);
        $data['kpis']['upcoming_events'] = (int)($eventCounts['upcoming_events'] ?? 0);
        $past_assigned_events = (int)($eventCounts['past_assigned_events'] ?? 0);

        // 3. KPI Aggregation for Attendance Hours & Rate
        $stmtAttendance = $pdo->prepare("
            SELECT 
                SUM(TIMESTAMPDIFF(MINUTE, check_in, check_out)/60) as total_hours,
                COUNT(id) as attended_events
            FROM attendance 
            WHERE volunteer_id = ? AND attendance_status = 'present'
        ");
        $stmtAttendance->execute([$userId]);
        $attCounts = $stmtAttendance->fetch(PDO::FETCH_ASSOC);
        
        $data['kpis']['total_hours'] = (float)($attCounts['total_hours'] ?? 0);
        
        if ($past_assigned_events > 0) {
            $attended = (int)($attCounts['attended_events'] ?? 0);
            $data['kpis']['attendance_percentage'] = round(($attended / $past_assigned_events) * 100, 1);
        }

        // 4. Load Lists Data (using modular functions)
        $data['upcomingEvents'] = get_volunteer_upcoming_events($pdo, $userId, 3);
        $data['assignedTasks'] = get_volunteer_assigned_tasks($pdo, $userId, 3);
        $data['recentActivity'] = get_volunteer_recent_activity($pdo, $userId, 5);
        $data['notifications'] = get_volunteer_notifications($pdo, $userId, 4);

    } catch (PDOException $e) {
        error_log("Dashboard Data Fetch Error: " . $e->getMessage());
    }

    $_VOLUNTEER_CACHE[$cacheKey] = $data;
    return $data;
}

function get_volunteer_upcoming_events($pdo, $volunteer_id, $limit = 5) {
    global $_VOLUNTEER_CACHE;
    $cacheKey = "upcoming_events_{$volunteer_id}_{$limit}";
    if (isset($_VOLUNTEER_CACHE[$cacheKey])) return $_VOLUNTEER_CACHE[$cacheKey];

    $stmt = $pdo->prepare("
        SELECT e.*, u.full_name as coordinator_name 
        FROM events e
        JOIN volunteer_registrations vr ON e.id = vr.event_id
        JOIN users u ON e.coordinator_id = u.id
        WHERE vr.volunteer_id = ? AND vr.approval_status = 'approved' AND e.event_date >= CURDATE() AND e.status != 'completed'
        ORDER BY e.event_date ASC 
        LIMIT " . (int)$limit
    );
    $stmt->execute([$volunteer_id]);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_VOLUNTEER_CACHE[$cacheKey] = $res;
    return $res;
}

function get_volunteer_assigned_tasks($pdo, $volunteer_id, $limit = 5) {
    global $_VOLUNTEER_CACHE;
    $cacheKey = "assigned_tasks_{$volunteer_id}_{$limit}";
    if (isset($_VOLUNTEER_CACHE[$cacheKey])) return $_VOLUNTEER_CACHE[$cacheKey];

    $stmt = $pdo->prepare("
        SELECT t.*, e.title as event_title 
        FROM tasks t
        JOIN events e ON t.event_id = e.id
        WHERE t.volunteer_id = ? AND t.completion_status != 'completed'
        ORDER BY t.deadline ASC, FIELD(t.priority, 'high', 'medium', 'low')
        LIMIT " . (int)$limit
    );
    $stmt->execute([$volunteer_id]);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_VOLUNTEER_CACHE[$cacheKey] = $res;
    return $res;
}

function get_volunteer_attendance_history($pdo, $volunteer_id, $limit = 50) {
    global $_VOLUNTEER_CACHE;
    $cacheKey = "attendance_{$volunteer_id}_{$limit}";
    if (isset($_VOLUNTEER_CACHE[$cacheKey])) return $_VOLUNTEER_CACHE[$cacheKey];

    $stmt = $pdo->prepare("
        SELECT a.*, e.title as event_title, e.event_date,
               TIMESTAMPDIFF(MINUTE, a.check_in, a.check_out)/60 as hours
        FROM attendance a
        JOIN events e ON a.event_id = e.id
        WHERE a.volunteer_id = ?
        ORDER BY e.event_date DESC
        LIMIT " . (int)$limit
    );
    $stmt->execute([$volunteer_id]);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_VOLUNTEER_CACHE[$cacheKey] = $res;
    return $res;
}

function get_volunteer_recent_activity($pdo, $volunteer_id, $limit = 5) {
    global $_VOLUNTEER_CACHE;
    $cacheKey = "activity_{$volunteer_id}_{$limit}";
    if (isset($_VOLUNTEER_CACHE[$cacheKey])) return $_VOLUNTEER_CACHE[$cacheKey];

    // Generic Event Stream using UNION ALL
    $stmt = $pdo->prepare("
        (
            SELECT task_name as title, 'Completed Task' as description, updated_at as event_date, 'fas fa-check-circle' as icon, 'var(--success)' as color 
            FROM tasks WHERE volunteer_id = ? AND completion_status = 'completed'
        )
        UNION ALL
        (
            SELECT e.title as title, 'Registered for Event' as description, vr.registration_date as event_date, 'fas fa-calendar-plus' as icon, 'var(--primary)' as color 
            FROM volunteer_registrations vr JOIN events e ON vr.event_id = e.id WHERE vr.volunteer_id = ?
        )
        UNION ALL
        (
            SELECT e.title as title, 'Attendance Marked' as description, a.updated_at as event_date, 'fas fa-clipboard-check' as icon, 'var(--info)' as color 
            FROM attendance a JOIN events e ON a.event_id = e.id WHERE a.volunteer_id = ? AND a.attendance_status = 'present'
        )
        UNION ALL
        (
            SELECT title as title, 'Notification Received' as description, created_at as event_date, 'fas fa-bell' as icon, 'var(--warning)' as color 
            FROM notifications WHERE recipient_id = ? AND role_id = 4
        )
        ORDER BY event_date DESC
        LIMIT " . (int)$limit
    );
    $stmt->execute([$volunteer_id, $volunteer_id, $volunteer_id, $volunteer_id]);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_VOLUNTEER_CACHE[$cacheKey] = $res;
    return $res;
}

function get_volunteer_notifications($pdo, $volunteer_id, $limit = 50) {
    global $_VOLUNTEER_CACHE;
    $cacheKey = "notifications_{$volunteer_id}_{$limit}";
    if (isset($_VOLUNTEER_CACHE[$cacheKey])) return $_VOLUNTEER_CACHE[$cacheKey];

    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE recipient_id = ? AND role_id = 4 AND read_status = 0 
        ORDER BY created_at DESC 
        LIMIT " . (int)$limit
    );
    $stmt->execute([$volunteer_id]);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_VOLUNTEER_CACHE[$cacheKey] = $res;
    return $res;
}

function get_volunteer_all_events($pdo, $volunteer_id) {
    global $_VOLUNTEER_CACHE;
    $cacheKey = "all_events_{$volunteer_id}";
    if (isset($_VOLUNTEER_CACHE[$cacheKey])) return $_VOLUNTEER_CACHE[$cacheKey];

    $stmt = $pdo->prepare("
        SELECT e.*, u.full_name as coordinator_name, vr.approval_status, vr.attendance_status 
        FROM events e
        JOIN volunteer_registrations vr ON e.id = vr.event_id
        JOIN users u ON e.coordinator_id = u.id
        WHERE vr.volunteer_id = ? 
        ORDER BY e.event_date DESC
    ");
    $stmt->execute([$volunteer_id]);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_VOLUNTEER_CACHE[$cacheKey] = $res;
    return $res;
}

function get_volunteer_all_tasks($pdo, $volunteer_id) {
    global $_VOLUNTEER_CACHE;
    $cacheKey = "all_tasks_{$volunteer_id}";
    if (isset($_VOLUNTEER_CACHE[$cacheKey])) return $_VOLUNTEER_CACHE[$cacheKey];

    $stmt = $pdo->prepare("
        SELECT t.*, e.title as event_title 
        FROM tasks t
        JOIN events e ON t.event_id = e.id
        WHERE t.volunteer_id = ?
        ORDER BY FIELD(t.completion_status, 'pending', 'in_progress', 'completed'), t.deadline ASC, FIELD(t.priority, 'high', 'medium', 'low')
    ");
    $stmt->execute([$volunteer_id]);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_VOLUNTEER_CACHE[$cacheKey] = $res;
    return $res;
}

function get_volunteer_all_notifications($pdo, $volunteer_id) {
    global $_VOLUNTEER_CACHE;
    $cacheKey = "all_notifications_{$volunteer_id}";
    if (isset($_VOLUNTEER_CACHE[$cacheKey])) return $_VOLUNTEER_CACHE[$cacheKey];

    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE recipient_id = ? AND role_id = 4 
        ORDER BY created_at DESC 
        LIMIT 50
    ");
    $stmt->execute([$volunteer_id]);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_VOLUNTEER_CACHE[$cacheKey] = $res;
    return $res;
}
?>
