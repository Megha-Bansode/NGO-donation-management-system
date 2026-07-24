<?php
// coordinator_events.php
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Middleware.php';
require_once __DIR__ . '/includes/dashboard/components.php';

// Protect this dashboard: Only Event Coordinator (Role ID 5) can access
Middleware::role([5]);

$pdo = getDatabase();

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success_msg = '';
$error_msg = '';

// Controller Logic: Handle POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_msg = "Invalid security token.";
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'edit_event') {
            $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
            $title = htmlspecialchars(trim($_POST['title']));
            $event_type = htmlspecialchars(trim($_POST['event_type']));
            $venue = htmlspecialchars(trim($_POST['venue']));
            $event_date = $_POST['event_date'];
            $event_time = $_POST['event_time'];
            $registration_deadline = $_POST['registration_deadline'] ?: null;
            $max_volunteers = filter_var($_POST['max_volunteers'], FILTER_VALIDATE_INT) ?: 0;
            $expected_budget = filter_var($_POST['expected_budget'], FILTER_VALIDATE_FLOAT) ?: 0;
            $status = $_POST['status'];
            $description = htmlspecialchars(trim($_POST['description']));

            if ($title && $venue && $event_date && $event_time) {
                try {
                    // Check Ownership
                    $check = $pdo->prepare("SELECT coordinator_id FROM events WHERE id = ?");
                    $check->execute([$id]);
                    $owner_id = $check->fetchColumn();

                    if ($owner_id != $_SESSION['user_id']) {
                        $error_msg = "You do not have permission to edit this event.";
                    } else {
                        $stmt = $pdo->prepare("UPDATE events SET title=?, description=?, event_type=?, venue=?, event_date=?, event_time=?, registration_deadline=?, max_volunteers=?, expected_budget=?, status=? WHERE id=?");
                        $stmt->execute([$title, $description, $event_type, $venue, $event_date, $event_time, $registration_deadline, $max_volunteers, $expected_budget, $status, $id]);
                        $success_msg = "Event updated successfully.";
                    }
                } catch (PDOException $e) {
                    $error_msg = "Database error: " . $e->getMessage();
                }
            } else {
                $error_msg = "Please fill in all required fields.";
            }
        } elseif ($action === 'create_event') {
            $title = htmlspecialchars(trim($_POST['title']));
            $event_type = htmlspecialchars(trim($_POST['event_type']));
            $venue = htmlspecialchars(trim($_POST['venue']));
            $event_date = $_POST['event_date'];
            $event_time = $_POST['event_time'];
            $registration_deadline = $_POST['registration_deadline'] ?: null;
            $max_volunteers = filter_var($_POST['max_volunteers'], FILTER_VALIDATE_INT) ?: 0;
            $expected_budget = filter_var($_POST['expected_budget'], FILTER_VALIDATE_FLOAT) ?: 0;
            $status = $_POST['status'];
            $description = htmlspecialchars(trim($_POST['description']));
            $coordinator_id = $_SESSION['user_id'];

            if ($title && $venue && $event_date && $event_time) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO events (title, description, event_type, venue, event_date, event_time, registration_deadline, max_volunteers, expected_budget, status, coordinator_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $description, $event_type, $venue, $event_date, $event_time, $registration_deadline, $max_volunteers, $expected_budget, $status, $coordinator_id]);
                    $success_msg = "Event created successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Database error: " . $e->getMessage();
                }
            } else {
                $error_msg = "Please fill in all required fields.";
            }
        }
    }
}

// Fetch Filter Parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$page = isset($_GET['page']) ? max(1, filter_var($_GET['page'], FILTER_VALIDATE_INT)) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build Query
$whereClauses = ["e.coordinator_id = :coord_id"];
$params = [':coord_id' => $_SESSION['user_id']];

if ($search !== '') {
    $whereClauses[] = "(e.title LIKE :search OR e.venue LIKE :search)";
    $params[':search'] = "%{$search}%";
}
if ($status_filter) {
    $whereClauses[] = "e.status = :status";
    $params[':status'] = $status_filter;
}

$whereSQL = implode(" AND ", $whereClauses);

try {
    // Event Statistics
    $statsStmt = $pdo->prepare("SELECT 
        COUNT(*) as total_events,
        SUM(CASE WHEN status = 'upcoming' THEN 1 ELSE 0 END) as upcoming,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
        FROM events WHERE coordinator_id = ?");
    $statsStmt->execute([$_SESSION['user_id']]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    // Total Count for Pagination
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM events e WHERE $whereSQL");
    foreach ($params as $key => $val) {
        $countStmt->bindValue($key, $val);
    }
    $countStmt->execute();
    $totalEvents = $countStmt->fetchColumn();
    $totalPages = ceil($totalEvents / $limit);

    // Fetch Events with Registrations Count
    $query = "SELECT e.*, u.full_name as coordinator_name,
              (SELECT COUNT(*) FROM volunteer_registrations vr WHERE vr.event_id = e.id) as total_registrations
              FROM events e 
              LEFT JOIN users u ON e.coordinator_id = u.id 
              WHERE $whereSQL 
              ORDER BY e.event_date ASC 
              LIMIT :limit OFFSET :offset";
              
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Coordinators (Not needed for Coordinator role, but kept array empty to prevent errors)
    $coordinators = [];

} catch (PDOException $e) {
    $error_msg = "Database error: " . $e->getMessage();
    $events = [];
    $coordinators = [];
    $stats = ['total_events'=>0, 'upcoming'=>0, 'completed'=>0];
    $totalPages = 1;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management | <?php echo htmlspecialchars(APP_NAME); ?></title>
    
    <!-- Premium Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Dashboard Core CSS -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
    
    <style>
        .filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-bar input, .filter-bar select {
            padding: 10px 15px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 8px;
            font-family: var(--font-body);
            background: white;
            min-width: 200px;
        }
        .filter-bar input:focus, .filter-bar select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124, 154, 134, 0.2);
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }
        .page-btn {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid rgba(0,0,0,0.1);
            background: white;
            color: var(--text-dark);
            text-decoration: none;
            transition: all 0.2s;
        }
        .page-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        /* Summary Cards */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .summary-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        /* Modal Styles */
        .modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(6px);
            z-index: 1050;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        .modal.active {
            opacity: 1;
            visibility: visible;
        }
        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 16px;
            width: 100%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            transform: scale(0.95);
            transition: all 0.3s ease;
        }
        .modal.active .modal-content {
            transform: scale(1);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .modal-header h3 {
            margin: 0;
            font-size: 1.2rem;
            color: var(--text-dark);
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--text-muted);
            cursor: pointer;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group.full-width {
            grid-column: span 2;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-dark);
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 8px;
            background: white;
            font-family: var(--font-body);
        }
    </style>
</head>
<body>

<div class="dashboard-layout">
    <!-- Sidebar -->
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>

    <main class="main-content">
        <!-- Topbar -->
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>

        <div class="page-content">
            <!-- Header Section -->
            <div class="page-header">
                <div class="page-title">
                    <h1>Event Management</h1>
                    <div class="breadcrumb">
                        <span>Dashboard</span>
                        <span style="margin: 0 8px;">/</span>
                        <span style="color: var(--primary);">Events</span>
                    </div>
                </div>
                <div class="header-actions">
                    <button class="btn-primary" onclick="openEventModal()">
                        <i class="fas fa-plus"></i> Create Event
                    </button>
                </div>
            </div>

            <!-- Event Statistics -->
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-icon" style="background: rgba(124, 154, 134, 0.1); color: var(--primary);">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Total Events</div>
                        <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-dark);"><?php echo $stats['total_events'] ?? 0; ?></div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Upcoming Events</div>
                        <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-dark);"><?php echo $stats['upcoming'] ?? 0; ?></div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Completed</div>
                        <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-dark);"><?php echo $stats['completed'] ?? 0; ?></div>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <?php if ($success_msg): ?>
                <div style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(16, 185, 129, 0.2);">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_msg); ?>
                </div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div style="background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(239, 68, 68, 0.2);">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>

            <!-- Filter Bar -->
            <div class="glass-card" style="margin-bottom: 20px;">
                <form method="GET" action="coordinator_events.php" class="filter-bar">
                    <input type="text" name="search" placeholder="Search events..." value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="status">
                        <option value="">All Statuses</option>
                        <option value="upcoming" <?php echo $status_filter === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                        <option value="ongoing" <?php echo $status_filter === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>

                    <button type="submit" class="btn-primary" style="padding: 10px 20px;"><i class="fas fa-filter"></i> Filter</button>
                    <a href="coordinator_events.php" class="btn-primary" style="padding: 10px 20px; background: rgba(0,0,0,0.05); color: var(--text-dark); text-decoration: none;"><i class="fas fa-undo"></i> Reset</a>
                </form>
            </div>

            <!-- Events Table -->
            <div class="glass-card">
                <?php if (empty($events)): ?>
                    <?php render_empty_state('No Events Found', 'Create your first community event.', 'far fa-calendar-alt'); ?>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Event Details</th>
                                    <th>Date & Venue</th>
                                    <th>Coordinator</th>
                                    <th>Volunteers</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($events as $evt): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-dark);"><?php echo htmlspecialchars($evt['title']); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($evt['event_type']); ?></div>
                                    </td>
                                    <td>
                                        <div style="color: var(--text-dark);"><i class="far fa-calendar"></i> <?php echo date('M d, Y', strtotime($evt['event_date'])); ?> (<?php echo date('h:i A', strtotime($evt['event_time'])); ?>)</div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($evt['venue']); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($evt['coordinator_name']); ?></td>
                                    <td>
                                        <span style="font-weight: 600; color: var(--primary);"><?php echo $evt['total_registrations']; ?></span> 
                                        <span style="color: var(--text-muted); font-size: 0.8rem;">/ <?php echo $evt['max_volunteers'] ?: '∞'; ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                            $statusColors = [
                                                'upcoming' => 'rgba(245,158,11,0.1)', 'ongoing' => 'rgba(59,130,246,0.1)',
                                                'completed' => 'rgba(16,185,129,0.1)', 'cancelled' => 'rgba(239,68,68,0.1)'
                                            ];
                                            $textColors = [
                                                'upcoming' => 'var(--warning)', 'ongoing' => '#3b82f6',
                                                'completed' => 'var(--success)', 'cancelled' => 'var(--danger)'
                                            ];
                                        ?>
                                        <span class="badge" style="background: <?php echo $statusColors[$evt['status']]; ?>; color: <?php echo $textColors[$evt['status']]; ?>;">
                                            <?php echo ucfirst(htmlspecialchars($evt['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px;">
                                            <button onclick='openEventModal(<?php echo json_encode($evt); ?>)' class="action-btn" style="width: 32px; height: 32px;" title="Edit Event">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php for($i=1; $i<=$totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>" class="page-btn <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
            
        </div>
    </main>
</div>

<!-- Add/Edit Event Modal -->
<div class="modal" id="eventModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Create Event</h3>
            <button class="modal-close" onclick="closeEventModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="coordinator_events.php">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" id="formAction" value="create_event">
            <input type="hidden" name="id" id="evt_id" value="">
            
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Event Title *</label>
                    <input type="text" name="title" id="evt_title" required placeholder="e.g., Annual Beach Cleanup">
                </div>
                
                <div class="form-group">
                    <label>Event Type</label>
                    <input type="text" name="event_type" id="evt_type" placeholder="e.g., Cleanup, Fundraiser">
                </div>
                
                <div class="form-group">
                    <label>Venue *</label>
                    <input type="text" name="venue" id="evt_venue" required placeholder="Event location">
                </div>
                
                <div class="form-group">
                    <label>Date *</label>
                    <input type="date" name="event_date" id="evt_date" required>
                </div>
                
                <div class="form-group">
                    <label>Time *</label>
                    <input type="time" name="event_time" id="evt_time" required>
                </div>
                
                <div class="form-group">
                    <label>Max Volunteers</label>
                    <input type="number" name="max_volunteers" id="evt_max" placeholder="Leave empty for unlimited">
                </div>
                
                <div class="form-group" style="display: none;">
                    <label>Coordinator *</label>
                    <input type="hidden" name="coordinator_id" id="evt_coordinator" value="<?php echo $_SESSION['user_id']; ?>">
                </div>
                
                <div class="form-group full-width">
                    <label>Description *</label>
                    <textarea name="description" id="evt_desc" required placeholder="Event details and instructions..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Registration Deadline</label>
                    <input type="datetime-local" name="registration_deadline" id="evt_deadline">
                </div>

                <div class="form-group">
                    <label>Expected Budget (₹)</label>
                    <input type="number" step="0.01" name="expected_budget" id="evt_budget" placeholder="0.00">
                </div>

                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" id="evt_status" required>
                        <option value="upcoming">Upcoming</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn-primary" style="background: rgba(0,0,0,0.05); color: var(--text-dark);" onclick="closeEventModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Event</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/dashboard.js"></script>
<script>
    function openEventModal(event = null) {
        if (!event) {
            document.getElementById('modalTitle').textContent = 'Create Event';
            document.getElementById('formAction').value = 'create_event';
            document.getElementById('evt_id').value = '';
            document.getElementById('evt_title').value = '';
            document.getElementById('evt_type').value = '';
            document.getElementById('evt_venue').value = '';
            document.getElementById('evt_date').value = '';
            document.getElementById('evt_time').value = '';
            document.getElementById('evt_max').value = '';
            document.getElementById('evt_desc').value = '';
            document.getElementById('evt_deadline').value = '';
            document.getElementById('evt_budget').value = '';
            document.getElementById('evt_status').value = 'upcoming';
            document.getElementById('eventModal').classList.add('active');
            return;
        }
        
        document.getElementById('modalTitle').textContent = 'Edit Event';
        document.getElementById('formAction').value = 'edit_event';
        document.getElementById('evt_id').value = event.id;
        document.getElementById('evt_title').value = event.title;
        document.getElementById('evt_type').value = event.event_type;
        document.getElementById('evt_venue').value = event.venue;
        document.getElementById('evt_date').value = event.event_date;
        document.getElementById('evt_time').value = event.event_time;
        document.getElementById('evt_max').value = event.max_volunteers == 0 ? '' : event.max_volunteers;
        document.getElementById('evt_desc').value = event.description;
        
        if (event.registration_deadline) {
            let dt = event.registration_deadline.replace(' ', 'T');
            document.getElementById('evt_deadline').value = dt.substring(0, 16);
        } else {
            document.getElementById('evt_deadline').value = '';
        }
        
        document.getElementById('evt_budget').value = event.expected_budget;
        document.getElementById('evt_status').value = event.status;
        
        document.getElementById('eventModal').classList.add('active');
    }
    
    function closeEventModal() {
        document.getElementById('eventModal').classList.remove('active');
    }
</script>
</body>
</html>
