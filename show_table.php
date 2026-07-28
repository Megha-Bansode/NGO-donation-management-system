<?php
require 'config/database.php';
$pdo = getDatabase();
$stmt = $pdo->query('SHOW CREATE TABLE tasks');
print_r($stmt->fetch());
