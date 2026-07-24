<?php
// coordinator_attendance.php
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
        
        if ($action === 'update_attendance') {
            $reg_id = filter_var($_POST['reg_id'], FILTER_VALIDATE_INT);
            $vol_id = filter_var($_POST['vol_id'], FILTER_VALIDATE_INT);
            $evt_id = filter_var($_POST['evt_id'], FILTER_VALIDATE_INT);
            $status = htmlspecialchars($_POST['attendance_status']);
            $check_in = $_POST['check_in'] ?: null;
            $check_out = $_POST['check_out'] ?: null;
            
            if ($vol_id && $evt_id && in_array($status, ['present', 'absent', 'late'])) {
                try {
                    // Check ownership
                    $check = $pdo->prepare("SELECT coordinator_id FROM events WHERE id = ?");
                    $check->execute([$evt_id]);
                    $owner_id = $check->fetchColumn();

                    if ($owner_id != $_SESSION['user_id']) {
                        $error_msg = "You do not have permission to manage attendance for this event.";
                    } else {
                        // Update registration attendance status text
                        $regStatus = $status == 'present' || $status == 'late' ? 'attended' : 'absent';
                        $pdo->prepare("UPDATE volunteer_registrations SET attendance_status = ? WHERE id = ?")->execute([$regStatus, $reg_id]);
                        
                        // Insert or Update actual attendance record
                        $stmt = $pdo->prepare("INSERT INTO attendance (volunteer_id, event_id, check_in, check_out, attendance_status) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE check_in=VALUES(check_in), check_out=VALUES(check_out), attendance_status=VALUES(attendance_status)");
                        $stmt->execute([$vol_id, $evt_id, $check_in, $check_out, $status]);
                        
                        $success_msg = "Attendance updated successfully.";
                    }
                } catch (PDOException $e) {
                    $error_msg = "Database error: " . $e->getMessage();
                }
            }
        }
    }
}

// Fetch Filter Parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$event_filter = isset($_GET['event_id']) ? filter_var($_GET['event_id'], FILTER_VALIDATE_INT) : '';
$page = isset($_GET['page']) ? max(1, filter_var($_GET['page'], FILTER_VALIDATE_INT)) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Build Query
$whereClauses = ["e.coordinator_id = :coord_id", "vr.approval_status = 'approved'"];
$params = [':coord_id' => $_SESSION['user_id']];

if ($search !== '') {
    $whereClauses[] = "(u.full_name LIKE :search OR u.email LIKE :search)";
    $params[':search'] = "%{$search}%";
}
if ($event_filter) {
    $whereClauses[] = "vr.event_id = :event_id";
    $params[':event_id'] = $event_filter;
}

$whereSQL = implode(" AND ", $whereClauses);

try {
    // Fetch all events for the filter dropdown
    $eventsStmt = $pdo->prepare("SELECT id, title FROM events WHERE coordinator_id = ? ORDER BY event_date DESC");
    $eventsStmt->execute([$_SESSION['user_id']]);
    $allEvents = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Total Count for Pagination
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM volunteer_registrations vr LEFT JOIN users u ON vr.volunteer_id = u.id LEFT JOIN events e ON vr.event_id = e.id WHERE $whereSQL");
    foreach ($params as $key => $val) {
        $countStmt->bindValue($key, $val);
    }
    $countStmt->execute();
    $totalRegistrations = $countStmt->fetchColumn();
    $totalPages = ceil($totalRegistrations / $limit);

    // Fetch Registrations with related data
    $query = "SELECT vr.*, 
                     u.full_name, u.email, u.phone,
                     e.title as event_title, e.event_date,
                     a.check_in, a.check_out, a.attendance_status as specific_attendance
              FROM volunteer_registrations vr 
              LEFT JOIN users u ON vr.volunteer_id = u.id 
              LEFT JOIN events e ON vr.event_id = e.id 
              LEFT JOIN attendance a ON vr.volunteer_id = a.volunteer_id AND vr.event_id = a.event_id
              WHERE $whereSQL 
              ORDER BY e.event_date DESC, u.full_name ASC 
              LIMIT :limit OFFSET :offset";
              
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_msg = "Database error: " . $e->getMessage();
    $registrations = [];
    $allEvents = [];
    $totalPages = 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Tracking | <?php echo htmlspecialchars(APP_NAME); ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<div class="dashboard-layout">
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>

    <main class="main-content">
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>

        <div class="page-content">
            <div class="page-header">
                <div class="page-title">
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 5px;">Manage Participants</p>
                    <h1>Attendance Tracking</h1>
                </div>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success" style="padding: 15px; background: rgba(39, 174, 96, 0.1); color: #27ae60; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_msg): ?>
                <div class="alert alert-danger" style="padding: 15px; background: rgba(231, 76, 60, 0.1); color: #e74c3c; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <!-- Filter Bar -->
            <div class="glass-card" style="margin-bottom: 20px;">
                <form method="GET" action="coordinator_attendance.php" class="filter-bar">
                    <input type="text" name="search" placeholder="Search volunteer..." value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="event_id">
                        <option value="">All My Events</option>
                        <?php foreach($allEvents as $ev): ?>
                            <option value="<?php echo $ev['id']; ?>" <?php echo $event_filter == $ev['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ev['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" class="btn-primary" style="padding: 10px 20px;"><i class="fas fa-filter"></i> Filter</button>
                    <a href="coordinator_attendance.php" class="btn-primary" style="padding: 10px 20px; background: rgba(0,0,0,0.05); color: var(--text-dark); text-decoration: none;"><i class="fas fa-undo"></i> Reset</a>
                </form>
            </div>

            <!-- Table -->
            <div class="glass-card">
                <?php if (empty($registrations)): ?>
                    <?php render_empty_state('No Volunteers Found', 'No approved volunteers match your filters.', 'fas fa-clipboard-check'); ?>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Volunteer</th>
                                    <th>Event</th>
                                    <th>Attendance Status</th>
                                    <th>Check In / Out</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registrations as $reg): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-dark);"><?php echo htmlspecialchars($reg['full_name']); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($reg['email']); ?></div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 500; color: var(--text-dark);"><?php echo htmlspecialchars($reg['event_title']); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);"><i class="far fa-calendar"></i> <?php echo date('M d, Y', strtotime($reg['event_date'])); ?></div>
                                    </td>
                                    <td>
                                        <?php 
                                            $att_status = $reg['specific_attendance'] ?: 'absent';
                                            $badgeClass = 'badge-primary';
                                            if ($att_status == 'present') $badgeClass = 'badge-success';
                                            elseif ($att_status == 'late') $badgeClass = 'badge-warning';
                                            elseif ($att_status == 'absent') $badgeClass = 'badge-danger';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>">
                                            <?php echo htmlspecialchars(ucfirst($att_status)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($reg['check_in']): ?>
                                            <div style="font-size: 0.85rem; color: var(--success);"><i class="fas fa-sign-in-alt"></i> <?php echo date('h:i A', strtotime($reg['check_in'])); ?></div>
                                        <?php else: ?>
                                            <div style="font-size: 0.85rem; color: var(--text-muted);">-</div>
                                        <?php endif; ?>
                                        <?php if ($reg['check_out']): ?>
                                            <div style="font-size: 0.85rem; color: var(--danger);"><i class="fas fa-sign-out-alt"></i> <?php echo date('h:i A', strtotime($reg['check_out'])); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button onclick='openAttendanceModal(<?php echo json_encode($reg); ?>)' class="btn-primary" style="padding: 5px 10px; font-size: 0.75rem; background: rgba(124,154,134,0.1); color: var(--primary);">
                                            <i class="fas fa-clock"></i> Mark
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div style="display: flex; justify-content: center; gap: 5px; margin-top: 20px;">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&event_id=<?php echo urlencode($event_filter); ?>" 
                               style="padding: 8px 12px; border-radius: 6px; text-decoration: none; font-weight: 500;
                                      <?php echo $i == $page ? 'background: var(--primary); color: white;' : 'background: rgba(0,0,0,0.05); color: var(--text-dark);'; ?>">
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

<!-- Attendance Modal -->
<div class="modal-backdrop" id="attendanceModal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 id="attModalTitle">Update Attendance</h3>
            <button class="modal-close" onclick="closeAttendanceModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="coordinator_attendance.php">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" value="update_attendance">
            <input type="hidden" name="reg_id" id="att_reg_id" value="">
            <input type="hidden" name="vol_id" id="att_vol_id" value="">
            <input type="hidden" name="evt_id" id="att_evt_id" value="">
            
            <div style="margin-bottom: 15px;">
                <strong id="att_vol_name"></strong><br>
                <span id="att_evt_name" style="color: var(--text-muted); font-size: 0.85rem;"></span>
            </div>
            
            <div class="form-group">
                <label>Attendance Status</label>
                <select name="attendance_status" id="att_status" required>
                    <option value="present">Present</option>
                    <option value="late">Late</option>
                    <option value="absent">Absent</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Check In Time</label>
                <input type="datetime-local" name="check_in" id="att_checkin">
            </div>
            
            <div class="form-group">
                <label>Check Out Time</label>
                <input type="datetime-local" name="check_out" id="att_checkout">
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn-primary" style="background: rgba(0,0,0,0.05); color: var(--text-dark);" onclick="closeAttendanceModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Attendance</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/dashboard.js"></script>
<script>
    function openAttendanceModal(reg) {
        document.getElementById('att_reg_id').value = reg.id;
        document.getElementById('att_vol_id').value = reg.volunteer_id;
        document.getElementById('att_evt_id').value = reg.event_id;
        
        document.getElementById('att_vol_name').textContent = reg.full_name;
        document.getElementById('att_evt_name').textContent = reg.event_title;
        
        document.getElementById('att_status').value = reg.specific_attendance || 'absent';
        
        if (reg.check_in) {
            document.getElementById('att_checkin').value = reg.check_in.replace(' ', 'T').substring(0,16);
        } else {
            document.getElementById('att_checkin').value = '';
        }
        
        if (reg.check_out) {
            document.getElementById('att_checkout').value = reg.check_out.replace(' ', 'T').substring(0,16);
        } else {
            document.getElementById('att_checkout').value = '';
        }
        
        document.getElementById('attendanceModal').classList.add('active');
    }
    
    function closeAttendanceModal() {
        document.getElementById('attendanceModal').classList.remove('active');
    }
</script>
</body>
</html>
