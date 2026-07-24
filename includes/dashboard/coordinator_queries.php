<?php
/**
 * Shared queries for the Coordinator Dashboard
 * These queries enforce isolation: e.coordinator_id = $_SESSION['user_id']
 */

function get_coordinator_kpis($pdo, $coordinator_id) {
    // Total Assigned Events
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE coordinator_id = ?");
    $stmt->execute([$coordinator_id]);
    $total_events = $stmt->fetchColumn();

    // Upcoming Events
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE coordinator_id = ? AND event_date > CURDATE() AND status != 'completed'");
    $stmt->execute([$coordinator_id]);
    $upcoming_events = $stmt->fetchColumn();

    // Total Volunteers Registered for their events
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT vr.volunteer_id) 
        FROM volunteer_registrations vr
        JOIN events e ON vr.event_id = e.id
        WHERE e.coordinator_id = ? AND vr.approval_status = 'approved'
    ");
    $stmt->execute([$coordinator_id]);
    $total_volunteers = $stmt->fetchColumn();

    // Today's Events
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE coordinator_id = ? AND event_date = CURDATE()");
    $stmt->execute([$coordinator_id]);
    $todays_events = $stmt->fetchColumn();

    return [
        'total_events' => $total_events,
        'upcoming_events' => $upcoming_events,
        'total_volunteers' => $total_volunteers,
        'todays_events' => $todays_events
    ];
}

function get_coordinator_upcoming_events($pdo, $coordinator_id, $limit = 5) {
    $stmt = $pdo->prepare("
        SELECT * FROM events 
        WHERE coordinator_id = ? AND event_date >= CURDATE() AND status != 'completed'
        ORDER BY event_date ASC 
        LIMIT " . (int)$limit
    );
    $stmt->execute([$coordinator_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_coordinator_recent_activity($pdo, $coordinator_id, $limit = 5) {
    $stmt = $pdo->prepare("
        SELECT 'volunteer' as type, u.full_name as title, vr.registration_date as date, 'Registered for event' as description
        FROM volunteer_registrations vr
        JOIN events e ON vr.event_id = e.id
        JOIN users u ON vr.volunteer_id = u.id
        WHERE e.coordinator_id = ?
        ORDER BY vr.registration_date DESC
        LIMIT " . (int)$limit
    );
    $stmt->execute([$coordinator_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
