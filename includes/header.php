<?php
/**
 * Reusable Header Component
 * Part of Master Layout System
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(__DIR__)); }
?>
<header class="header">
    <div class="header-container">
        <!-- Sidebar Toggle (Mobile/Desktop) -->
        <button class="btn-icon header-toggle sidebar-toggle-btn" aria-label="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Dynamic Page Title Placeholder -->
        <h1 class="header-title"><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard'; ?></h1>

        <div class="header-actions">
            <!-- Search Placeholder -->
            <div class="header-search search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="form-control" placeholder="Search...">
            </div>

            <!-- Theme Placeholder -->
            <button class="btn-icon theme-toggle" aria-label="Toggle Theme">
                <i class="fas fa-moon"></i>
            </button>

            <!-- Notification Placeholder -->
            <div class="dropdown notification-dropdown-wrapper">
                <button class="btn-icon dropdown-toggle" aria-label="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="badge-notification">3</span>
                </button>
                <div class="dropdown-menu notification-dropdown">
                    <div class="dropdown-header">Notifications</div>
                    <div class="notification-list">
                        <!-- Notification Items Placeholder -->
                    </div>
                </div>
            </div>

            <!-- Profile Placeholder -->
            <div class="dropdown profile-dropdown-wrapper">
                <button class="dropdown-toggle avatar-btn" aria-label="Profile Menu" style="background:none;border:none;cursor:pointer;">
                    <div class="avatar avatar-sm">
                        <img src="/assets/images/placeholders/avatar.png" alt="Profile" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZD0iTTEyIDJDMiAyIDIgMTIgMiAxMnMyIDEwIDEwIDEwIDEwLTEwIDEwLTEwUzIyIDIgMTIgMnpNMTIgMTZjLTIuMjEgMC00LTEuNzktNC00czEuNzktNCA0LTQgNCAxLjc5IDQgNCAxLjc5IDQgNCA0em0wLTZjLTEuMSAwLTIgLjktMiAyczkuOSAyIDIgMiAyLS45IDItMi0uOS0yLTItMnoiLz48L3N2Zz4='">
                        <span class="avatar-status status-online"></span>
                    </div>
                </button>
                <div class="dropdown-menu profile-dropdown">
                    <div class="dropdown-header">User Name</div>
                    <a href="#" class="dropdown-item"><i class="fas fa-user"></i> My Profile</a>
                    <a href="#" class="dropdown-item"><i class="fas fa-cog"></i> Settings</a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item dropdown-item-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>
</header>
