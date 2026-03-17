<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "collector_user", "vincent9090", "collector_db");

// This query counts how many rows exist for every 1-minute window
$sql = "SELECT DATE_FORMAT(created_at, '%H:%i') as minute, COUNT(*) as count 
        FROM activity_log 
        GROUP BY minute 
        ORDER BY minute DESC 
        LIMIT 15"; // Shows the last 15 minutes of activity

$result = $conn->query($sql);
$labels = [];
$data = [];

while($row = $result->fetch_assoc()) {
    $labels[] = $row['minute'];
    $data[] = (int)$row['count'];
}

// We reverse them so the time flows left-to-right (past to present)
echo json_encode([
    'labels' => array_reverse($labels),
    'counts' => array_reverse($data)
]);
?>