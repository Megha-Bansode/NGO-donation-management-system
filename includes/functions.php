<?php
/**
 * Core Functions & Layout Utilities
 * Part of Master Layout System
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(__DIR__)); }

/**
 * Render standard layout components
 */
function render_header($title = 'Dashboard') {
    $pageTitle = $title;
    include __DIR__ . '/header.php';
}

function render_sidebar() {
    include __DIR__ . '/sidebar.php';
}

function render_footer() {
    include __DIR__ . '/footer.php';
}

function render_breadcrumbs($title, $module = null) {
    $pageTitle = $title;
    $moduleName = $module;
    include __DIR__ . '/breadcrumbs.php';
}

function render_loader() {
    include __DIR__ . '/loader.php';
}

function render_navbar() {
    include __DIR__ . '/navbar.php';
}
?>
