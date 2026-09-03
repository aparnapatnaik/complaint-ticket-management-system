<?php

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME') ?: 'complaint_system';

$conn = new mysqli(
    $host,
    $user,
    $password,
    $database,
    (int) $port
);

if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    die('Unable to connect to the database.');
}

$conn->set_charset('utf8mb4');
