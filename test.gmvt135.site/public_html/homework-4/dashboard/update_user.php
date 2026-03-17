<?php
session_start();

// 1. Create a log file to see if the script is even being hit
$logData = date('Y-m-d H:i:s') . " - ID: " . ($_POST['id'] ?? 'N/A') . " | Col: " . ($_POST['column'] ?? 'N/A') . " | Role: " . ($_SESSION['role'] ?? 'None') . "\n";
file_put_contents('update_debug.log', $logData, FILE_APPEND);

// 2. Check Permissions
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    die("Error: Not authorized. Your role is: " . ($_SESSION['role'] ?? 'Guest'));
}

$conn = new mysqli("localhost", "collector_user", "vincent9090", "collector_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_POST['id'] ?? '';
$column = $_POST['column'] ?? '';
$value = $_POST['value'] ?? '';

// 3. Security: Only allow these columns
if (!in_array($column, ['role', 'permissions'])) {
    die("Error: Invalid column");
}

$stmt = $conn->prepare("UPDATE users SET $column = ? WHERE id = ?");
$stmt->bind_param("si", $value, $id);

if ($stmt->execute()) {
    echo "Success";
} else {
    echo "SQL Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>