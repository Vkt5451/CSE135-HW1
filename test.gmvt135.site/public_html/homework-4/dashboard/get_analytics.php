<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "collector_user", "vincent9090", "collector_db");
$sql = "SELECT payload FROM activity_log WHERE data_type = 'static'";
$result = $conn->query($sql);

$browserCounts = [
    'Chrome' => 0,
    'Safari' => 0,
    'Firefox' => 0,
    'Edge' => 0,
    'Other' => 0
];

while($row = $result->fetch_assoc()) {
    $details = json_decode($row['payload'], true);
    $ua = $details['userAgent'] ?? '';
    
    // Using simple detection logic
    if (strpos($ua, 'Edge')) $browserCounts['Edge']++;
    elseif (strpos($ua, 'Chrome')) $browserCounts['Chrome']++;
    elseif (strpos($ua, 'Safari')) $browserCounts['Safari']++;
    elseif (strpos($ua, 'Firefox')) $browserCounts['Firefox']++;
    else $browserCounts['Other']++;
}

// Format for Chart.js
echo json_encode([
    'labels' => array_keys($browserCounts),
    'counts' => array_values($browserCounts)
]);
?>