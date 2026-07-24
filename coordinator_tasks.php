<?php
// coordinator_tasks.php
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Middleware.php';
require_once __DIR__ . '/includes/dashboard/components.php';
require_once __DIR__ . '/core/Logger.php';

// Protect this dashboard: Only Event Coordinator (Role ID 5) can access
Middleware::role([5]);

$pdo = getDatabase();

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success_msg = '';
$error_msg = '';

// Fetch all events for this coordinator
$eventsStmt = $pdo->prepare("SELECT id, title FROM events WHERE coordinator_id = ? ORDER BY event_date DESC");
$eventsStmt->execute([$_SESSION['user_id']]);
$allEvents = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all approved volunteers for these events
$volStmt = $pdo->prepare("
    SELECT u.id as volunteer_id, u.full_name, e.id as event_id, e.title as event_title 
    FROM volunteer_registrations vr 
    JOIN users u ON vr.volunteer_id = u.id 
    JOIN events e ON vr.event_id = e.id 
    WHERE e.coordinator_id = ? AND vr.approval_status = 'approved'
    ORDER BY u.full_name ASC
");
$volStmt->execute([$_SESSION['user_id']]);
$allVolunteers = $volStmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function to send notification
function sendNotification($pdo, $recipient_id, $title, $message) {
    $stmt = $pdo->prepare("INSERT INTO notifications (recipient_id, title, message, notification_type) VALUES (?, ?, ?, 'System')");
    $stmt->execute([$recipient_id, $title, $message]);
}

// Controller Logic: Handle POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_msg = "Invalid security token.";
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create_task') {
            $volunteer_event = $_POST['volunteer_event'] ?? ''; // Format: volunteer_id|event_id
            $task_name = htmlspecialchars($_POST['task_name']);
            $description = htmlspecialchars($_POST['description']);
            $priority = $_POST['priority'];
            $deadline = $_POST['deadline'] ?: null;
            
            if ($volunteer_event && $task_name) {
                list($vol_id, $evt_id) = explode('|', $volunteer_event);
                
                try {
                    // Check if event belongs to this coordinator
                    $check = $pdo->prepare("SELECT coordinator_id FROM events WHERE id = ?");
                    $check->execute([$evt_id]);
                    if ($check->fetchColumn() == $_SESSION['user_id']) {
                        $stmt = $pdo->prepare("INSERT INTO tasks (volunteer_id, event_id, task_name, description, priority, deadline, completion_status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
                        $stmt->execute([$vol_id, $evt_id, $task_name, $description, $priority, $deadline]);
                        $taskId = $pdo->lastInsertId();
                        
                        Logger::logActivity($pdo, $_SESSION['user_id'], 5, 'Tasks', 'Create', "Assigned task '{$task_name}' to volunteer ID {$vol_id}");
                        sendNotification($pdo, $vol_id, "New Task Assigned", "You have been assigned a new task: {$task_name}.");
                        
                        $success_msg = "Task assigned successfully.";
                    } else {
                        $error_msg = "Unauthorized event.";
                    }
                } catch (PDOException $e) {
                    $error_msg = "Database error: " . $e->getMessage();
                }
            } else {
                $error_msg = "Please select a volunteer and enter a task name.";
            }
        } elseif ($action === 'update_task') {
            $task_id = filter_var($_POST['task_id'], FILTER_VALIDATE_INT);
            $task_name = htmlspecialchars($_POST['task_name']);
            $description = htmlspecialchars($_POST['description']);
            $priority = $_POST['priority'];
            $deadline = $_POST['deadline'] ?: null;
            $status = $_POST['completion_status'];
            
            if ($task_id && $task_name) {
                try {
                    // Verify Ownership
                    $check = $pdo->prepare("SELECT e.coordinator_id, t.volunteer_id FROM tasks t JOIN events e ON t.event_id = e.id WHERE t.id = ?");
                    $check->execute([$task_id]);
                    $taskData = $check->fetch(PDO::FETCH_ASSOC);
                    
                    if ($taskData && $taskData['coordinator_id'] == $_SESSION['user_id']) {
                        $stmt = $pdo->prepare("UPDATE tasks SET task_name = ?, description = ?, priority = ?, deadline = ?, completion_status = ? WHERE id = ?");
                        $stmt->execute([$task_name, $description, $priority, $deadline, $status, $task_id]);
                        
                        Logger::logActivity($pdo, $_SESSION['user_id'], 5, 'Tasks', 'Update', "Updated task ID {$task_id} status to {$status}");
                        if ($taskData['volunteer_id']) {
                            sendNotification($pdo, $taskData['volunteer_id'], "Task Updated", "Your task '{$task_name}' has been updated by the coordinator.");
                        }
                        $success_msg = "Task updated successfully.";
                    } else {
                        $error_msg = "Unauthorized to update this task.";
                    }
                } catch (PDOException $e) {
                    $error_msg = "Database error: " . $e->getMessage();
                }
            }
        } elseif ($action === 'delete_task') {
            $task_id = filter_var($_POST['task_id'], FILTER_VALIDATE_INT);
            if ($task_id) {
                try {
                    $check = $pdo->prepare("SELECT e.coordinator_id, t.task_name, t.volunteer_id FROM tasks t JOIN events e ON t.event_id = e.id WHERE t.id = ?");
                    $check->execute([$task_id]);
                    $taskData = $check->fetch(PDO::FETCH_ASSOC);
                    
                    if ($taskData && $taskData['coordinator_id'] == $_SESSION['user_id']) {
                        $pdo->prepare("DELETE FROM tasks WHERE id = ?")->execute([$task_id]);
                        Logger::logActivity($pdo, $_SESSION['user_id'], 5, 'Tasks', 'Delete', "Deleted task ID {$task_id}");
                        if ($taskData['volunteer_id']) {
                            sendNotification($pdo, $taskData['volunteer_id'], "Task Removed", "The task '{$taskData['task_name']}' has been removed from your list.");
                        }
                        $success_msg = "Task deleted successfully.";
                    } else {
                        $error_msg = "Unauthorized to delete this task.";
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
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$event_filter = isset($_GET['event_id']) ? filter_var($_GET['event_id'], FILTER_VALIDATE_INT) : '';
$priority_filter = isset($_GET['priority']) ? trim($_GET['priority']) : '';
$page = isset($_GET['page']) ? max(1, filter_var($_GET['page'], FILTER_VALIDATE_INT)) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Build Query
$whereClauses = ["e.coordinator_id = :coord_id"];
$params = [':coord_id' => $_SESSION['user_id']];

if ($search !== '') {
    $whereClauses[] = "(t.task_name LIKE :search OR u.full_name LIKE :search)";
    $params[':search'] = "%{$search}%";
}
if ($status_filter) {
    $whereClauses[] = "t.completion_status = :status";
    $params[':status'] = $status_filter;
}
if ($event_filter) {
    $whereClauses[] = "t.event_id = :event_id";
    $params[':event_id'] = $event_filter;
}
if ($priority_filter) {
    $whereClauses[] = "t.priority = :priority";
    $params[':priority'] = $priority_filter;
}

$whereSQL = implode(" AND ", $whereClauses);

try {
    // Total Count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM tasks t JOIN events e ON t.event_id = e.id LEFT JOIN users u ON t.volunteer_id = u.id WHERE $whereSQL");
    foreach ($params as $key => $val) {
        $countStmt->bindValue($key, $val);
    }
    $countStmt->execute();
    $totalTasks = $countStmt->fetchColumn();
    $totalPages = ceil($totalTasks / $limit);

    // Fetch Tasks
    $query = "SELECT t.*, u.full_name as volunteer_name, e.title as event_title 
              FROM tasks t 
              JOIN events e ON t.event_id = e.id 
              LEFT JOIN users u ON t.volunteer_id = u.id 
              WHERE $whereSQL 
              ORDER BY t.created_at DESC 
              LIMIT :limit OFFSET :offset";
              
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_msg = "Database error: " . $e->getMessage();
    $tasks = [];
    $totalPages = 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management | <?php echo htmlspecialchars(APP_NAME); ?></title>
    
    <!-- Premium Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Dashboard Core CSS -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
    
    <style>
        .filter-bar { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
        .filter-bar input, .filter-bar select { padding: 10px 15px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; font-family: var(--font-body); background: white; min-width: 150px; }
        .filter-bar input:focus, .filter-bar select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(124, 154, 134, 0.2); }
        
        .modal { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(6px); z-index: 1050; display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: all 0.3s ease; }
        .modal.active { opacity: 1; visibility: visible; }
        .modal-content { background: white; padding: 25px; border-radius: 16px; width: 100%; max-width: 500px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); transform: scale(0.95); transition: all 0.3s ease; max-height: 90vh; overflow-y: auto; }
        .modal.active .modal-content { transform: scale(1); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid rgba(0,0,0,0.05); }
        .modal-header h3 { margin: 0; font-size: 1.2rem; color: var(--text-dark); }
        .modal-close { background: none; border: none; font-size: 1.2rem; color: var(--text-muted); cursor: pointer; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem; color: var(--text-dark); }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px 15px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; background: white; font-family: var(--font-body); }
        .pagination { display: flex; justify-content: center; gap: 5px; margin-top: 20px; }
        .page-btn { padding: 8px 12px; border-radius: 6px; border: 1px solid rgba(0,0,0,0.1); background: white; color: var(--text-dark); text-decoration: none; transition: all 0.2s; }
        .page-btn.active { background: var(--primary); color: white; border-color: var(--primary); }
    </style>
</head>
<body>

<div class="dashboard-layout">
    <?php include __DIR__ . '/includes/dashboard/sidebar.php'; ?>

    <main class="main-content">
        <?php include __DIR__ . '/includes/dashboard/topbar.php'; ?>

        <div class="page-content">
            <!-- Header Section -->
            <div class="page-header">
                <div class="page-title">
                    <h1>Task Management</h1>
                    <div class="breadcrumb">
                        <span>Dashboard</span>
                        <span style="margin: 0 8px;">/</span>
                        <span style="color: var(--primary);">Tasks</span>
                    </div>
                </div>
                <div class="header-actions">
                    <button class="btn-primary" onclick="openCreateModal()">
                        <i class="fas fa-plus"></i> Assign Task
                    </button>
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
                <form method="GET" action="coordinator_tasks.php" class="filter-bar">
                    <input type="text" name="search" placeholder="Search task or volunteer..." value="<?php echo htmlspecialchars($search); ?>">
                    
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
                        <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>

                    <select name="priority">
                        <option value="">All Priorities</option>
                        <option value="high" <?php echo $priority_filter === 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="medium" <?php echo $priority_filter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="low" <?php echo $priority_filter === 'low' ? 'selected' : ''; ?>>Low</option>
                    </select>

                    <button type="submit" class="btn-primary" style="padding: 10px 20px;"><i class="fas fa-filter"></i> Filter</button>
                    <a href="coordinator_tasks.php" class="btn-primary" style="padding: 10px 20px; background: rgba(0,0,0,0.05); color: var(--text-dark); text-decoration: none;"><i class="fas fa-undo"></i> Reset</a>
                </form>
            </div>

            <!-- Tasks Table -->
            <div class="glass-card">
                <?php if (empty($tasks)): ?>
                    <?php render_empty_state('No Tasks Found', 'No tasks match your search criteria.', 'fas fa-tasks'); ?>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Task & Description</th>
                                    <th>Assignee</th>
                                    <th>Event</th>
                                    <th>Priority</th>
                                    <th>Status & Deadline</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($tasks as $task): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-dark);"><?php echo htmlspecialchars($task['task_name']); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted); max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($task['description']); ?></div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 500; color: var(--text-dark);"><i class="far fa-user"></i> <?php echo htmlspecialchars($task['volunteer_name'] ?? 'Unassigned'); ?></div>
                                    </td>
                                    <td>
                                        <div style="color: var(--text-dark); font-size: 0.9rem;"><?php echo htmlspecialchars($task['event_title']); ?></div>
                                    </td>
                                    <td>
                                        <?php 
                                            $priClass = 'badge-primary';
                                            if ($task['priority'] == 'high') $priClass = 'badge-danger';
                                            elseif ($task['priority'] == 'medium') $priClass = 'badge-warning';
                                            elseif ($task['priority'] == 'low') $priClass = 'badge-success';
                                        ?>
                                        <span class="badge <?php echo $priClass; ?>"><?php echo ucfirst($task['priority']); ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                            $statClass = 'badge-primary';
                                            if ($task['completion_status'] == 'completed') $statClass = 'badge-success';
                                            elseif ($task['completion_status'] == 'in_progress') $statClass = 'badge-warning';
                                            elseif ($task['completion_status'] == 'pending') $statClass = 'badge-danger';
                                        ?>
                                        <span class="badge <?php echo $statClass; ?>" style="margin-bottom: 5px; display:inline-block;"><?php echo str_replace('_', ' ', ucfirst($task['completion_status'])); ?></span>
                                        <br>
                                        <span style="font-size: 0.8rem; color: var(--text-muted);">
                                            <i class="far fa-calendar-alt"></i> <?php echo $task['deadline'] ? date('M d, Y h:i A', strtotime($task['deadline'])) : 'No Deadline'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px;">
                                            <button onclick='openEditModal(<?php echo json_encode($task); ?>)' class="action-btn" style="width: 32px; height: 32px; color: var(--primary);" title="Edit Task">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" action="coordinator_tasks.php" onsubmit="return confirm('Are you sure you want to delete this task?');" style="display:inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="action" value="delete_task">
                                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                <button type="submit" class="action-btn" style="width: 32px; height: 32px; color: var(--danger);" title="Delete Task">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
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
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&event_id=<?php echo $event_filter; ?>&status=<?php echo $status_filter; ?>&priority=<?php echo $priority_filter; ?>" class="page-btn <?php echo $i == $page ? 'active' : ''; ?>">
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

<!-- Create Modal -->
<div class="modal" id="createModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Assign New Task</h3>
            <button class="modal-close" onclick="closeCreateModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="coordinator_tasks.php">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" value="create_task">
            
            <div class="form-group">
                <label>Select Volunteer (Approved for Event)</label>
                <select name="volunteer_event" required>
                    <option value="">-- Choose Volunteer & Event --</option>
                    <?php foreach($allVolunteers as $v): ?>
                        <option value="<?php echo $v['volunteer_id'] . '|' . $v['event_id']; ?>">
                            <?php echo htmlspecialchars($v['full_name']) . " -> " . htmlspecialchars($v['event_title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Task Name</label>
                <input type="text" name="task_name" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"></textarea>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label>Priority</label>
                    <select name="priority" required>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="low">Low</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Deadline (Optional)</label>
                    <input type="datetime-local" name="deadline">
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn-primary" style="background: rgba(0,0,0,0.05); color: var(--text-dark);" onclick="closeCreateModal()">Cancel</button>
                <button type="submit" class="btn-primary">Assign Task</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Task</h3>
            <button class="modal-close" onclick="closeEditModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="coordinator_tasks.php">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="action" value="update_task">
            <input type="hidden" name="task_id" id="edit_task_id" value="">
            
            <div class="form-group">
                <label>Task Name</label>
                <input type="text" name="task_name" id="edit_task_name" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="edit_description" rows="3"></textarea>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label>Status</label>
                    <select name="completion_status" id="edit_status" required>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Priority</label>
                    <select name="priority" id="edit_priority" required>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Deadline (Optional)</label>
                <input type="datetime-local" name="deadline" id="edit_deadline">
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn-primary" style="background: rgba(0,0,0,0.05); color: var(--text-dark);" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-primary">Update Task</button>
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

    function openCreateModal() {
        document.getElementById('createModal').classList.add('active');
    }
    
    function closeCreateModal() {
        document.getElementById('createModal').classList.remove('active');
    }

    function openEditModal(task) {
        document.getElementById('edit_task_id').value = task.id;
        document.getElementById('edit_task_name').value = task.task_name;
        document.getElementById('edit_description').value = task.description;
        document.getElementById('edit_status').value = task.completion_status;
        document.getElementById('edit_priority').value = task.priority;
        document.getElementById('edit_deadline').value = formatDT(task.deadline);
        
        document.getElementById('editModal').classList.add('active');
    }
    
    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }
</script>
</body>
</html>
