<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "collector_user", "vincent9090", "collector_db");

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed"]));
}

// Counts each type of event (click, scroll, resize, etc.)
$sql = "SELECT event_name, COUNT(*) as total FROM activity_log GROUP BY event_name ORDER BY total DESC";
$result = $conn->query($sql);

$data = [];
while($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
$conn->close();
?>