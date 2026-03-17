<?php
// reply_feedback.php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "collector_user", "vincent9090", "collector_db");

$id = $_POST['id'] ?? '';
$reply = $_POST['reply'] ?? '';

if ($id && $reply) {
    $stmt = $conn->prepare("UPDATE dashboard_feedback SET analyst_reply = ?, is_resolved = 1 WHERE id = ?");
    $stmt->bind_param("si", $reply, $id);
    $stmt->execute();
    echo json_encode(['status' => 'success']);
}
?>