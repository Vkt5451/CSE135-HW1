<?php
// submit_feedback.php
$conn = new mysqli("localhost", "collector_user", "vincent9090", "collector_db");

$username = $_POST['username'] ?? 'Guest';
$role     = $_POST['role'] ?? 'viewer'; // This grabs 'super_admin' from JS
$message  = $_POST['message'] ?? '';

if ($message) {
    // Make sure 'role' is included in the columns and the bind_param
    $stmt = $conn->prepare("INSERT INTO dashboard_feedback (username, role, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $role, $message);
    $stmt->execute();
    echo json_encode(['status' => 'success']);
}
?>