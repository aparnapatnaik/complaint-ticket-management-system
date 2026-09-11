<?php
$host = '172.31.29.90'; // or your MySQL host container IP / host
$db_name = 'complaint_db';
$username = 'root';
$password = 'your_password';

try {
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    echo "Connection error: " . $exception->getMessage();
}
?>
