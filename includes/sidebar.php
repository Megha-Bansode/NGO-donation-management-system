<?php
/**
 * Reusable Sidebar Component (Dashboard Layout)
 * Part of Master Layout System
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(__DIR__)); }
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-hands-helping text-primary"></i>
        <span class="sidebar-text">NGO Admin</span>
    </div>

    <ul class="sidebar-menu">
        <li class="sidebar-menu-title">Main</li>
        <li class="sidebar-item">
            <a href="#" class="sidebar-link active">
                <i class="fas fa-th-large sidebar-icon"></i>
                <span class="sidebar-text">Dashboard</span>
            </a>
        </li>
        <!-- Menu Groups Placeholder -->
        <li class="sidebar-menu-title">Modules</li>
        <li class="sidebar-item accordion-item">
            <button class="sidebar-link accordion-header w-100 border-0 bg-transparent">
                <div style="display:flex;align-items:center;gap:1rem;">
                    <i class="fas fa-hand-holding-heart sidebar-icon"></i>
                    <span class="sidebar-text">Donations</span>
                </div>
                <i class="fas fa-chevron-down accordion-icon sidebar-text" style="font-size:0.8rem;"></i>
            </button>
            <div class="accordion-body sidebar-submenu" style="padding:0;background:var(--surface-hover);">
                <a href="#" class="sidebar-sublink">All Donations</a>
                <a href="#" class="sidebar-sublink">Add New</a>
            </div>
        </li>
        <li class="sidebar-item">
            <a href="#" class="sidebar-link">
                <i class="fas fa-users sidebar-icon"></i>
                <span class="sidebar-text">Volunteers</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="#" class="sidebar-link">
                <i class="fas fa-bullhorn sidebar-icon"></i>
                <span class="sidebar-text">Campaigns</span>
            </a>
        </li>
    </ul>
</aside>
