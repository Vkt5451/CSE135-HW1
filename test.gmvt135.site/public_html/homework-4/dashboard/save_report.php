<?php
session_start();

// Security: Only Sam (Analyst) or Super Admin can save
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'analyst' && $_SESSION['role'] !== 'super_admin')) {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized access']));
}

$conn = new mysqli("localhost", "collector_user", "vincent9090", "collector_db");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['report_type']; 
    $content = $_POST['content'];

    $stmt = $conn->prepare("UPDATE report_definitions SET content = ? WHERE report_type = ?");
    $stmt->bind_param("ss", $content, $type);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    $stmt->close();
}
$conn->close();
?>