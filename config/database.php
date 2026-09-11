<?php
$host = '172.31.29.90'; // Your EC2 host IP or container name if using a shared Docker network
$db_name = 'complaint_db';
$username = 'root';
$password = 'rootpassword';

try {
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name . ";port=3306", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    echo "Connection error: " . $exception->getMessage();
}
?>
