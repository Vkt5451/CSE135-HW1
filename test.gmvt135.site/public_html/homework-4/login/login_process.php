<?php
session_start();

$conn = new mysqli("localhost", "collector_user", "vincent9090", "collector_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_input = $_POST['username'] ?? '';
$pass_input = $_POST['password'] ?? '';

$stmt = $conn->prepare("SELECT id, username, password, role, permissions FROM users WHERE username = ?");
$stmt->bind_param("s", $user_input);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    // Password check (Match this to your hashing strategy)
    if ($pass_input === $user['password']) {
        
        $_SESSION['isLoggedIn'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // CRITICAL: Convert the JSON string into a PHP array
        // This is why your Guest was seeing nothing!
        $decoded_perms = json_decode($user['permissions'], true);
        $_SESSION['permissions'] = is_array($decoded_perms) ? $decoded_perms : [];

        echo "success";
    } else {
        echo "fail";
    }
} else {
    echo "fail";
}

$stmt->close();
$conn->close();
?>