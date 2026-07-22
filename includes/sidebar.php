<?php
if (!isset($basePath)) {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $baseDir = dirname(dirname($scriptName));
    $baseDir = str_replace('\\', '/', $baseDir);
    $basePath = rtrim($baseDir, '/') . '/';
}

$user = isset($_SESSION['user']) ? $_SESSION['user'] : [
    'name' => 'Demo User',
    'email' => 'demo@ngo.org',
    'role' => 'donor'
];

$role = $user['role'];
$roleName = '';
$menuItems = [];

// Determine display name and menu items based on role
switch ($role) {
    case 'super_admin':
        $roleName = 'Super Admin';
        $menuItems = [
            ['title' => 'Dashboard Overview', 'icon' => 'fa-gauge-high', 'url' => $basePath . 'dashboard/super-admin'],
            ['title' => 'Manage NGO Accounts', 'icon' => 'fa-building-columns', 'url' => '#'],
            ['title' => 'Global Analytics', 'icon' => 'fa-chart-pie', 'url' => '#'],
            ['title' => 'System Settings', 'icon' => 'fa-gears', 'url' => '#'],
            ['title' => 'Audit Security Logs', 'icon' => 'fa-shield-halved', 'url' => '#']
        ];
        break;
        
    case 'ngo_admin':
        $roleName = 'NGO Admin';
        $menuItems = [
            ['title' => 'Dashboard Overview', 'icon' => 'fa-gauge-high', 'url' => $basePath . 'dashboard/ngo-admin'],
            ['title' => 'Fundraising Campaigns', 'icon' => 'fa-hand-holding-dollar', 'url' => '#'],
            ['title' => 'Volunteer Management', 'icon' => 'fa-users', 'url' => '#'],
            ['title' => 'Donation Reports', 'icon' => 'fa-file-invoice-dollar', 'url' => '#'],
            ['title' => 'Event Schedules', 'icon' => 'fa-calendar-days', 'url' => '#'],
            ['title' => 'Document Approvals', 'icon' => 'fa-file-signature', 'url' => '#']
        ];
        break;

    case 'volunteer':
        $roleName = 'Volunteer';
        $menuItems = [
            ['title' => 'Volunteer Dashboard', 'icon' => 'fa-gauge-high', 'url' => $basePath . 'dashboard/volunteer'],
            ['title' => 'Available Tasks', 'icon' => 'fa-list-check', 'url' => '#'],
            ['title' => 'Assigned Events', 'icon' => 'fa-calendar-check', 'url' => '#'],
            ['title' => 'Participation History', 'icon' => 'fa-clock-rotate-left', 'url' => '#'],
            ['title' => 'Training & Resources', 'icon' => 'fa-book-open', 'url' => '#']
        ];
        break;

    case 'donor':
        $roleName = 'Donor';
        $menuItems = [
            ['title' => 'Donor Dashboard', 'icon' => 'fa-gauge-high', 'url' => $basePath . 'dashboard/donor'],
            ['title' => 'Donation History', 'icon' => 'fa-receipt', 'url' => '#'],
            ['title' => 'Active Campaigns', 'icon' => 'fa-heart', 'url' => $basePath . '#campaigns'],
            ['title' => 'Tax Receipts & Certificates', 'icon' => 'fa-award', 'url' => '#'],
            ['title' => 'Profile Settings', 'icon' => 'fa-user-gear', 'url' => '#']
        ];
        break;

    case 'event_coordinator':
        $roleName = 'Event Coordinator';
        $menuItems = [
            ['title' => 'Events Dashboard', 'icon' => 'fa-gauge-high', 'url' => $basePath . 'dashboard/event-coordinator'],
            ['title' => 'Create New Event', 'icon' => 'fa-calendar-plus', 'url' => '#'],
            ['title' => 'Volunteer Assignments', 'icon' => 'fa-user-check', 'url' => '#'],
            ['title' => 'Resource Management', 'icon' => 'fa-boxes-stacked', 'url' => '#'],
            ['title' => 'Attendee Check-In', 'icon' => 'fa-clipboard-user', 'url' => '#']
        ];
        break;
}

// Get first letter for avatar
$avatarLetter = strtoupper(substr($user['name'], 0, 1));
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <a href="<?php echo $basePath; ?>" class="logo-container sidebar-logo">
            <img src="<?php echo $basePath; ?>assets/images/logo.png" alt="Arohan Foundation Logo" style="height: 36px; width: auto; object-fit: contain;" class="me-2" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
            <div class="logo-icon d-none" style="background: white; color: var(--primary);">
                <i class="fa-solid fa-hand-holding-heart"></i>
            </div>
            <span>Arohan Foundation</span>
        </a>
    </div>
    
    <div class="sidebar-profile">
        <div class="sidebar-avatar">
            <?php echo $avatarLetter; ?>
        </div>
        <div class="sidebar-profile-info">
            <span class="sidebar-profile-name"><?php echo htmlspecialchars($user['name']); ?></span>
            <span class="sidebar-profile-role"><?php echo $roleName; ?></span>
        </div>
    </div>
    
    <div class="sidebar-menu">
        <h4 class="sidebar-menu-title">Main Navigation</h4>
        <ul class="sidebar-menu-list">
            <?php foreach ($menuItems as $item): 
                $isActive = ($_SERVER['REQUEST_URI'] === $item['url']);
            ?>
                <li class="sidebar-menu-item <?php echo $isActive ? 'active' : ''; ?>">
                    <a href="<?php echo $item['url']; ?>" <?php echo $item['url'] === '#' ? 'onclick="showDummyAlert(\'' . $item['title'] . '\')"' : ''; ?>>
                        <i class="fa-solid <?php echo $item['icon']; ?>"></i>
                        <span><?php echo $item['title']; ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <div class="sidebar-footer">
        <a href="<?php echo $basePath; ?>logout" class="sidebar-logout">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Log Out</span>
        </a>
    </div>
</aside>

<script>
function showDummyAlert(featureName) {
    alert(`The "${featureName}" feature is ready for backend integration and database queries.`);
}
</script>
