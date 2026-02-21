<?php
// NO HEADERS HERE - Apache is handling them!

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=collector_db", "collector_user", "vincent9090");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("INSERT INTO activity_log (session_id, data_type, event_name, payload) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $data['sessionId'] ?? 'N/A',
            $data['type'] ?? 'N/A',
            $data['event'] ?? 'N/A',
            $json
        ]);
        echo json_encode(["status" => "success"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}
?>