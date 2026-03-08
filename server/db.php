<?php
// server/db.php

$host = 'sqlXXX.epizy.com'; // CHANGE THIS to your MySQL Hostname from the control panel
$db   = 'if0_40647296_aesnote';
$user = 'if0_40647296'; // CHANGE THIS to your MySQL Username from the control panel
$pass = 'your_password'; // CHANGE THIS to your MySQL Password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new Exception("Connection failed: " . $e->getMessage());
}
