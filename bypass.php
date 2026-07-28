<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role_id'] = 1;
header("Location: admin_dashboard.php");
exit();
?>
