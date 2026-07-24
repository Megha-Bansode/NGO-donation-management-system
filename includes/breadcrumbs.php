<?php
/**
 * Reusable Breadcrumbs Component
 * Part of Master Layout System
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(__DIR__)); }
?>
<nav aria-label="breadcrumb" class="breadcrumb-container">
    <div class="breadcrumb">
        <a href="/"><i class="fas fa-home"></i> Home</a>
        <span class="separator"><i class="fas fa-chevron-right"></i></span>
        <?php if(isset($moduleName) && $moduleName): ?>
            <a href="#"><?php echo htmlspecialchars($moduleName); ?></a>
            <span class="separator"><i class="fas fa-chevron-right"></i></span>
        <?php endif; ?>
        <span class="active" aria-current="page"><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Current Page'; ?></span>
    </div>
</nav>
