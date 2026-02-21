<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

$pdo = new PDO("mysql:host=localhost;dbname=collector_db", "collector_user", "vincent9090");
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch ($method) {
    case 'GET':
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM activity_log WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $pdo->query("SELECT * FROM activity_log ORDER BY id DESC LIMIT 10");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        // Read the raw body data
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if ($data) {
            $sql = "INSERT INTO activity_log (session_id, data_type, event_name, payload, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            
            // If payload is an array/object, convert it to string; if it's already a string, keep it.
            $payload = is_array($data['payload']) ? json_encode($data['payload']) : $data['payload'];
            
            $stmt->execute([
                $data['session_id'],
                $data['data_type'],
                $data['event_name'],
                $payload
            ]);
            
            echo json_encode(["status" => "success", "id" => $pdo->lastInsertId()]);
        } else {
            echo json_encode(["status" => "error", "message" => "No data provided"]);
        }
        break;

    case 'PUT':
        if ($id) {
            $stmt = $pdo->prepare("UPDATE activity_log SET event_name = 'REVIEWED' WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(["status" => "success", "message" => "Record $id marked as reviewed"]);
        }
        break;

    case 'DELETE':
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM activity_log WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(["status" => "success", "message" => "Record $id deleted"]);
        }
        break;
}