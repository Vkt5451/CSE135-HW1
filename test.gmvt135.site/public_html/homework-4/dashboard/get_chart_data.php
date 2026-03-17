<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "collector_user", "vincent9090", "collector_db");

// Count how many entries exist for each browser type
$sql = "SELECT browser_name, COUNT(*) as count FROM activity_log GROUP BY browser_name";
$result = $conn->query($sql);

$data = [];
while($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>