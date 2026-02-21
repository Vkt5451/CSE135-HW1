<?php
$pdo = new PDO("mysql:host=localhost;dbname=collector_db", "collector_user", "vincent9090");
$rows = $pdo->query("SELECT * FROM activity_log ORDER BY id DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
?>
<html>
<body style="font-family:sans-serif; padding:40px; background:#f4f4f9;">
    <h2>📊 Collector Data Feed</h2>
    <table border="1" style="width:100%; border-collapse:collapse; background:white;">
        <tr style="background:#333; color:white;">
            <th>ID</th><th>Event</th><th>Timestamp</th><th>Payload Details</th>
        </tr>
        <?php foreach($rows as $row): ?>
        <tr>
            <td style="padding:10px;"><?= $row['id'] ?></td>
            <td style="padding:10px;"><strong><?= strtoupper($row['event_name']) ?></strong></td>
            <td style="padding:10px;"><?= $row['created_at'] ?></td>
            <td style="padding:10px;"><small><?= htmlspecialchars($row['payload']) ?></small></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
