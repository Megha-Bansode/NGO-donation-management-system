<?php
// admin_volunteers.php
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Middleware.php';
require_once __DIR__ . '/includes/dashboard/components.php';

// Protect this dashboard: Only NGO Admin (Role ID 2) can access
Middleware::role([2]);

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
        
        if ($action === 'update_approval') {
            $reg_id = filter_var($_POST['reg_id'], FILTER_VALIDATE_INT);
            $status = htmlspecialchars($_POST['approval_status']);
            
            if ($reg_id && in_array($status, ['pending', 'approved', 'rejected'])) {
                try {
                    $stmt = $pdo->prepare("UPDATE volunteer_registrations SET approval_status = ? WHERE id = ?");
                    $stmt->execute([$status, $reg_id]);
                    $success_msg = "Registration status updated successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Database error: " . $e->getMessage();
                }
            }
        } elseif ($action === 'update_attendance') {
            $reg_id = filter_var($_POST['reg_id'], FILTER_VALIDATE_INT);
            $vol_id = filter_var($_POST['vol_id'], FILTER_VALIDATE_INT);
            $evt_id = filter_var($_POST['evt_id'], FILTER_VALIDATE_INT);
            $status = htmlspecialchars($_POST['attendance_status']);
            $check_in = $_POST['check_in'] ?: null;
            $check_out = $_POST['check_out'] ?: null;
            
            if ($vol_id && $evt_id && in_array($status, ['present', 'absent', 'late'])) {
                try {
                    // Update registration attendance status text
                    $regStatus = $status == 'present' || $status == 'late' ? 'attended' : 'absent';
                    $pdo->prepare("UPDATE volunteer_registrations SET attendance_status = ? WHERE id = ?")->execute([$regStatus, $reg_id]);
                    
                    // Insert or Update actual attendance record
                    $stmt = $pdo->prepare("INSERT INTO attendance (volunteer_id, event_id, check_in, check_out, attendance_status) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE check_in=VALUES(check_in), check_out=VALUES(check_out), attendance_status=VALUES(attendance_status)");
                    $stmt->execute([$vol_id, $evt_id, $check_in, $check_out, $status]);
                    
                    $success_msg = "Attendance updated successfully.";
                } catch (PDOException $e) {
                    $error_msg = "Database error: " . $e->getMessage();
                }
            }
        }
    }
}

// Fetch Filter Parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$event_filter = isset($_GET['event_id']) ? filter_var($_GET['event_id'], FILTER_VALIDATE_INT) : '';
$page = isset($_GET['page']) ? max(1, filter_var($_GET['page'], FILTER_VALIDATE_INT)) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Build Query
$whereClauses = ["1=1"];
$params = [];

if ($search !== '') {
    $whereClauses[] = "(u.full_name LIKE :search OR u.email LIKE :search)";
    $params[':search'] = "%{$search}%";
}
if ($status_filter) {
    $whereClauses[] = "vr.approval_status = :status";
    $params[':status'] = $status_filter;
}
if ($event_filter) {
    $whereClauses[] = "vr.event_id = :event_id";
    $params[':event_id'] = $event_filter;
}

$whereSQL = implode(" AND ", $whereClauses);

try {
    // Fetch all events for the filter dropdown
    $eventsStmt = $pdo->query("SELECT id, title FROM events ORDER BY event_date DESC");
    $allEvents = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Total Count for Pagination
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM volunteer_registrations vr LEFT JOIN users u ON vr.volunteer_id = u.id WHERE $whereSQL");
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
                     a.check_in, a.check_out, a.attendance_status as specific_attendance,
                     (SELECT GROUP_CONCAT(skill_name SEPARATOR ', ') FROM volunteer_skills vs WHERE vs.volunteer_id = vr.volunteer_id) as skills,
                     (SELECT COUNT(*) FROM tasks t WHERE t.volunteer_id = vr.volunteer_id AND t.event_id = vr.event_id) as tasks_assigned,
                     (SELECT COUNT(*) FROM tasks t WHERE t.volunteer_id = vr.volunteer_id AND t.event_id = vr.event_id AND t.completion_status = 'completed') as tasks_completed
              FROM volunteer_registrations vr 
              LEFT JOIN users u ON vr.volunteer_id = u.id 
              LEFT JOIN events e ON vr.event_id = e.id 
              LEFT JOIN attendance a ON vr.volunteer_id = a.volunteer_id AND vr.event_id = a.event_id
              WHERE $whereSQL 
              ORDER BY vr.registration_date DESC 
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

// Helper to calculate hours
function calculateHours($checkIn, $checkOut) {
    if (!$checkIn || !$checkOut) return 0;
    $in = strtotime($checkIn);
    $out = strtotime($checkOut);
    if ($out > $in) {
        return round(($out - $in) / 3600, 1);
    }
    return 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Management | <?php echo htmlspecialchars(APP_NAME); ?></title>
    
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
            max-width: 500px;
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
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-dark);
        }
        .form-group input, .form-group select {
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
                    <h1>Volunteer Management</h1>
                    <div class="breadcrumb">
                        <span>Dashboard</span>
                        <span style="margin: 0 8px;">/</span>
                        <span style="color: var(--primary);">Volunteers</span>
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
                <form method="GET" action="ngo_volunteers.php" class="filter-bar">
                    <input type="text" name="search" placeholder="Search volunteer..." value="<?php echo htmlspecialchars($search); ?>">
                    
                    <select name="event_id">
                        <option value="">All Events</option>
                        <?php foreach($allEvents as $evt): ?>
                            <option value="<?php echo $evt['id']; ?>" <?php echo $event_filter == $evt['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($evt['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>

                    <button type="submit" class="btn-primary" style="padding: 10px 20px;"><i class="fas fa-filter"></i> Filter</button>
                    <a href="ngo_volunteers.php" class="btn-primary" style="padding: 10px 20px; background: rgba(0,0,0,0.05); color: var(--text-dark); text-decoration: none;"><i class="fas fa-undo"></i> Reset</a>
                </form>
            </div>

            <!-- Registrations Table -->
            <div class="glass-card">
                <?php if (empty($registrations)): ?>
                    <?php render_empty_state('No Volunteers Found', 'No registrations match your search criteria.', 'fas fa-users'); ?>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Volunteer Info</th>
                                    <th>Event</th>
                                    <th>Skills</th>
                                    <th>Tasks</th>
                                    <th>Hours</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($registrations as $reg): ?>
                                <?php $hours = calculateHours($reg['check_in'], $reg['check_out']); ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-dark);"><?php echo htmlspecialchars($reg['full_name']); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($reg['email']); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($reg['phone']); ?></div>
                                    </td>
                                    <td>
                                        <div style="color: var(--text-dark);"><?php echo htmlspecialchars($reg['event_title']); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo date('M d, Y', strtotime($reg['event_date'])); ?></div>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars($reg['skills'] ?: 'None listed'); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: rgba(0,0,0,0.05); color: var(--text-dark);">
                                            <?php echo $reg['tasks_completed']; ?> / <?php echo $reg['tasks_assigned']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-weight: 700; color: var(--primary);"><?php echo $hours; ?>h</span>
                                    </td>
                                    <td>
                                        <?php 
                                            $statusColors = [
                                                'approved' => 'rgba(16,185,129,0.1)', 'pending' => 'rgba(245,158,11,0.1)',
                                                'rejected' => 'rgba(239,68,68,0.1)'
                                            ];
                                            $textColors = [
                                                'approved' => 'var(--success)', 'pending' => 'var(--warning)',
                                                'rejected' => 'var(--danger)'
                                            ];
                                        ?>
                                        <span class="badge" style="background: <?php echo $statusColors[$reg['approval_status']]; ?>; color: <?php echo $textColors[$reg['approval_status']]; ?>; margin-bottom: 5px; display: inline-block;">
                                            <?php echo ucfirst(htmlspecialchars($reg['approval_status'])); ?>
                                        </span>
                                        <br>
                                        <?php if($reg['specific_attendance']): ?>
                                            <span class="badge" style="background: rgba(0,0,0,0.05); color: var(--text-muted);">
                                                <i class="fas fa-clock"></i> <?php echo ucfirst(htmlspecialchars($reg['specific_attendance'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px;">
                                            <button onclick='openApprovalModal(<?php echo json_encode($reg); ?>)' class="action-btn" style="width: 32px; height: 32px;" title="Update Approval Status">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                            <button onclick='openAttendanceModal(<?php echo json_encode($reg); ?>)' class="action-btn" style="width: 32px; height: 32px; color: var(--primary);" title="Update Attendance & Hours">
                                                <i class="fas fa-user-clock"></i>
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
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&event_id=<?php echo $event_filter; ?>&status=<?php echo $status_filter; ?>" class="page-btn <?php echo $i == $page ? 'active' : ''; ?>">
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

<!-- Approval Modal -->
<div class="modal" id="approvalModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Registration Status</h3>
            <button class="modal-close" onclick="closeApprovalModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="ngo_volunteers.php">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" value="update_approval">
            <input type="hidden" name="reg_id" id="appr_reg_id" value="">
            
            <div class="form-group">
                <label>Volunteer</label>
                <input type="text" id="appr_name" readonly style="background: #f9f9f9;">
            </div>
            
            <div class="form-group">
                <label>Approval Status</label>
                <select name="approval_status" id="appr_status" required>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn-primary" style="background: rgba(0,0,0,0.05); color: var(--text-dark);" onclick="closeApprovalModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Attendance Modal -->
<div class="modal" id="attendanceModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Attendance & Hours</h3>
            <button class="modal-close" onclick="closeAttendanceModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="ngo_volunteers.php">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" value="update_attendance">
            <input type="hidden" name="reg_id" id="att_reg_id" value="">
            <input type="hidden" name="vol_id" id="att_vol_id" value="">
            <input type="hidden" name="evt_id" id="att_evt_id" value="">
            
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
    function formatDT(mysqlDT) {
        if (!mysqlDT) return '';
        return mysqlDT.replace(' ', 'T').substring(0, 16);
    }

    function openApprovalModal(reg) {
        document.getElementById('appr_reg_id').value = reg.id;
        document.getElementById('appr_name').value = reg.full_name + ' - ' + reg.event_title;
        document.getElementById('appr_status').value = reg.approval_status;
        
        document.getElementById('approvalModal').classList.add('active');
    }
    
    function closeApprovalModal() {
        document.getElementById('approvalModal').classList.remove('active');
    }
    
    function openAttendanceModal(reg) {
        document.getElementById('att_reg_id').value = reg.id;
        document.getElementById('att_vol_id').value = reg.volunteer_id;
        document.getElementById('att_evt_id').value = reg.event_id;
        
        document.getElementById('att_status').value = reg.specific_attendance || 'absent';
        document.getElementById('att_checkin').value = formatDT(reg.check_in);
        document.getElementById('att_checkout').value = formatDT(reg.check_out);
        
        document.getElementById('attendanceModal').classList.add('active');
    }
    
    function closeAttendanceModal() {
        document.getElementById('attendanceModal').classList.remove('active');
    }
</script>
</body>
</html>
