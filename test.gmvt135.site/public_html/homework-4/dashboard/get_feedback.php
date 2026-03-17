<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "collector_user", "vincent9090", "collector_db");

$result = $conn->query("SELECT * FROM dashboard_feedback ORDER BY created_at DESC LIMIT 10");

$feedback = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        $feedback[] = $row;
    }
}

// ALWAYS return an array, even if empty
echo json_encode($feedback);
?>