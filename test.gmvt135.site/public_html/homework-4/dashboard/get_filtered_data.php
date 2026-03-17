<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "collector_user", "vincent9090", "collector_db");

$start = !empty($_GET['start']) ? $_GET['start'] . " 00:00:00" : "2000-01-01 00:00:00";
$end   = !empty($_GET['end'])   ? $_GET['end']   . " 23:59:59" : "2099-12-31 23:59:59";

$response = [];

// --- 1. BROWSER DATA (From JSON Payload) ---
$sql_browser = "SELECT 
            JSON_UNQUOTE(JSON_EXTRACT(payload, '$.userAgent')) AS raw_agent, 
            COUNT(*) as counts 
        FROM activity_log 
        WHERE created_at BETWEEN ? AND ? 
        AND JSON_EXTRACT(payload, '$.userAgent') IS NOT NULL
        GROUP BY raw_agent";

$stmt = $conn->prepare($sql_browser);
$stmt->bind_param("ss", $start, $end);
$stmt->execute();
$result = $stmt->get_result();

$browser_data = [];
while($row = $result->fetch_assoc()) {
    $agent = $row['raw_agent'];
    if (strpos($agent, 'Firefox') !== false) $name = 'Firefox';
    elseif (strpos($agent, 'Chrome') !== false) $name = 'Chrome';
    elseif (strpos($agent, 'Safari') !== false) $name = 'Safari';
    elseif (strpos($agent, 'Edge') !== false) $name = 'Edge';
    else $name = 'Other';

    if (!isset($browser_data[$name])) $browser_data[$name] = 0;
    $browser_data[$name] += (int)$row['counts'];
}

$response['browser'] = [
    'labels' => array_keys($browser_data),
    'counts' => array_values($browser_data)
];

// --- 2. ENGAGEMENT DATA (From event_name column) ---
$sql_engagement = "SELECT event_name, COUNT(*) as total 
                   FROM activity_log 
                   WHERE created_at BETWEEN ? AND ? 
                   GROUP BY event_name 
                   ORDER BY total DESC";

$stmt2 = $conn->prepare($sql_engagement);
$stmt2->bind_param("ss", $start, $end);
$stmt2->execute();
$result_eng = $stmt2->get_result();

$eng_labels = [];
$eng_counts = [];
while($row = $result_eng->fetch_assoc()) {
    $eng_labels[] = $row['event_name'];
    $eng_counts[] = (int)$row['total'];
}

$response['engagement'] = [
    'labels' => $eng_labels,
    'counts' => $eng_counts
];

// Final Output
echo json_encode($response);

$conn->close();
?>