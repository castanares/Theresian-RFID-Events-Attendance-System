<?php
$username = "root";
$password = "";
$host = "localhost";
$dbname = "stc_event_db";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}