<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Authentication Check
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] !== true) {
    header("Location: ../login/login.html");
    exit;
}

// 2. Define our Authorization variables from the Session
$section = $_GET['view'] ?? 'overview'; // Gets 'performance' or 'behavior' from URL
$role    = $_SESSION['role'] ?? 'viewer';
$perms   = $_SESSION['permissions'] ?? [];
$valid_sections = ['overview','performance','behavior','engagement','admin'];

if (!in_array($section, $valid_sections)) {
    header("Location: ../404.html");
    exit;
}

if ($section === 'admin' && $role !== 'super_admin') {
    header("Location: ../403.html");
    exit;
}

if ($section === 'performance' && $role !== 'super_admin' && !in_array('performance', $perms)) {
    header("Location: ../403.html");
    exit;
}

if ($section === 'behavior' && $role !== 'super_admin' && !in_array('behavior', $perms)) {
    header("Location: ../403.html");
    exit;
}

if ($section === 'engagement' && $role !== 'super_admin' && !in_array('engagement', $perms)) {
    header("Location: ../403.html");
    exit;
}

// 3. Helper function for roles
function hasAccess($requiredRole) {
    $currentRole = $_SESSION['role'] ?? 'viewer';
    if ($currentRole === 'super_admin') return true;
    if ($requiredRole === 'analyst' && $currentRole === 'analyst') return true;
    if ($requiredRole === 'viewer') return true;
    return false;
}

$conn = new mysqli("localhost", "collector_user", "vincent9090", "collector_db");
$saved_reports = [];
$res = $conn->query("SELECT report_type, content FROM report_definitions");
while($row = $res->fetch_assoc()) {
    $saved_reports[$row['report_type']] = $row['content'];
}
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homework 4</title>
    <link href="dashboard.css" rel="stylesheet">
    <?php if (!defined('EXPORT_MODE')): ?>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel="stylesheet">
    <?php endif; ?>
    <?php if (!defined('EXPORT_MODE')): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>
    <?php if (defined('EXPORT_MODE')): ?>
<style>
    body {
        font-size: 12px;
    }

    .sidebar {
        display: none !important;
    }

    .main-content {
        margin: 0 !important;
        padding: 10px !important;
        width: 100% !important;
    }

    .dashboard-controls {
    display: none !important;
    }

    .save-btn,
    .toggle-btn,
    .report-badge,
    textarea,
    select,
    input {
        display: none !important;
    }
    

    .data-table {
        width: 100% !important;
        table-layout: fixed !important;
        border-collapse: collapse !important;
        font-size: 10px !important;
    }

    .data-table th,
    .data-table td {
        padding: 6px !important;
        vertical-align: top !important;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        white-space: normal !important;
    }

    .data-table th:nth-child(1),
    .data-table td:nth-child(1) {
        width: 12% !important;
    }

    .data-table th:nth-child(2),
    .data-table td:nth-child(2) {
        width: 16% !important;
    }

    .data-table th:nth-child(3),
    .data-table td:nth-child(3) {
        width: 52% !important;
    }

    .data-table th:nth-child(4),
    .data-table td:nth-child(4) {
        width: 20% !important;
    }

    .data-table td div {
        white-space: normal !important;
        overflow: visible !important;
        max-width: 100% !important;
    }

    /* Fix engagement table layout in PDF */
.details .data-table th:nth-child(1),
.details .data-table td:nth-child(1) {
    width: 40% !important;
}

.details .data-table th:nth-child(2),
.details .data-table td:nth-child(2) {
    width: 30% !important;
    text-align: center !important;
}

.details .data-table th:nth-child(3),
.details .data-table td:nth-child(3) {
    width: 30% !important;
    text-align: center !important;
}

</style>
<?php endif; ?>
</head>


<body>
    <?php if (!defined('EXPORT_MODE')): ?>
<noscript>
    <div style="background:#fff3cd; color:#856404; padding:12px; margin:12px; border:1px solid #ffeeba; border-radius:8px;">
        JavaScript is disabled. Charts and some dashboard features may not display correctly.
    </div>
</noscript>
<?php endif; ?>
<nav class="sidebar">
    <div class="logo-menu">
        <h2 class="logo">Hub</h2>
        <i class='bx bx-menu toggle-btn'></i>
    </div>
    <ul class="list">
        <li class="list-item">
            <a href="dashboard.php?view=overview">
                <i class='bx bx-home'></i>
                <span class="link-name">Dashboard Overview</span>
            </a>
        </li>

        <?php if ($role === 'super_admin'): ?>
        <li class="list-item">
            <a href="dashboard.php?view=admin">
                <i class='bx bx-shield-quarter'></i>
                <span class="link-name">Manage Users</span>
            </a>
        </li>
        <?php endif; ?>

        <?php 
        // Logic to determine the label based on role
        $reportSuffix = ($role === 'viewer') ? ' Saved Report' : ' Analytics';
        ?>

        <?php if ($role === 'super_admin' || in_array('performance', $perms)): ?>
        <li class="list-item">
            <a href="dashboard.php?view=performance">
                <i class='bx bx-line-chart'></i>
                <span class="link-name">Platform Insights<?php echo $reportSuffix; ?></span>
            </a>
        </li>
        <?php endif; ?>

        <?php if ($role === 'super_admin' || in_array('behavior', $perms)): ?>
        <li class="list-item">
            <a href="dashboard.php?view=behavior">
                <i class='bx bx-user-voice'></i>
                <span class="link-name">Behavior<?php echo $reportSuffix; ?></span>
            </a>
        </li>
        <?php endif; ?>

        <?php if ($role === 'super_admin' || in_array('engagement', $perms)): ?>
        <li class="list-item">
            <a href="dashboard.php?view=engagement">
                <i class='bx bx-mouse-alt'></i>
                <span class="link-name">Engagement<?php echo $reportSuffix; ?></span>
            </a>
        </li>
        <?php endif; ?>

        <li class="list-item" style="margin-top: auto;">
            <a href="../login/logout.php">
                <i class='bx bx-exit'></i>
                <span class="link-name">Sign Out</span> 
            </a>
        </li>
    </ul>
</nav>
    
    <main class="main-content">
<?php if ($section === 'performance' && (hasAccess('super_admin') || in_array('performance', $perms))): ?>
    <section class="report-section">
        
        <div class="report-header">
            <h2><i class='bx bx-pie-chart-alt-2'></i> Browser Distribution Analysis</h2>
       <div class="analyst-comment">
    <strong>Report Insight:</strong>

    <?php if (defined('EXPORT_MODE')): ?>
        <div class="saved-report-view">
            <p class="static-report-text">
                <?php echo nl2br(htmlspecialchars($saved_reports['performance'] ?? 'No analyst insights available.')); ?>
            </p>
        </div>

    <?php elseif ($role === 'analyst' || $role === 'super_admin'): ?>
        <p style="font-size: 0.8rem; color: #666; margin-bottom: 5px;">(You are defining this report for viewers)</p>
        <textarea id="insight_performance" class="analyst-textarea"><?php echo htmlspecialchars($saved_reports['performance'] ?? 'Default performance text...'); ?></textarea>
        <button class="save-btn" onclick="window.saveReportDefinition('performance', 'insight_performance')">
            <i class='bx bx-save'></i> Save Performance Definition
        </button>

    <?php else: ?>
        <div class="saved-report-view">
            <p id="display_insight_performance" class="static-report-text">
                <?php echo nl2br(htmlspecialchars($saved_reports['performance'] ?? 'No analyst insights available.')); ?>
            </p>
            <span class="report-badge">SAVED VIEW</span>
        </div>
    <?php endif; ?>

    <?php if (!defined('EXPORT_MODE')): ?>
<button class="save-btn"
        style="display:inline-block; margin-left:10px;"
        onclick="exportReportWithChart('performance','myBrowserChart')">
    <i class='bx bx-download'></i> Export Performance PDF
</button>
<?php endif; ?>
</div>
        <div class="cardBox chart-main">
    <div class="chart-container" style="position: relative; height: 40vh; width: 100%; display: flex; justify-content: center;">
        <canvas id="myBrowserChart"></canvas>
    </div>

    <div class="dashboard-controls" style="margin-top: 30px;">
        <p style="font-size: 0.75rem; text-transform: uppercase; color: #a0aec0; letter-spacing: 1px; font-weight: 700; margin-bottom: 10px; text-align: center;">
            Adjust Report Window
        </p>
        <div class="filter-pill">
            <div class="pill-segment">
                <i class='bx bx-calendar'></i>
                <input type="date" id="startDate" class="hidden-date-input">
            </div>
            <div class="pill-divider"></div>
            <div class="pill-segment">
                <input type="date" id="endDate" class="hidden-date-input">
            </div>
            <button onclick="window.applyDateFilter()" class="pill-button">
                <span>Update</span>
                <i class='bx bx-refresh'></i>
            </button>
        </div>
    </div>
</div>
<?php if (!defined('EXPORT_MODE')): ?>
<div class="cardBox" style="margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <div class="card" style="padding: 20px; display: flex; flex-direction: column; gap: 10px;">
        <h3 style="font-size: 1.1rem; color: #2a2185;">Discussion Board</h3>
        <p style="font-size: 0.8rem; color: #666;">See something weird in the data? Ask for a breakdown.</p>
        <textarea id="feedbackText" placeholder="e.g. Why did Chrome traffic spike yesterday?" 
                  style="width: 100%; height: 80px; padding: 10px; border-radius: 8px; border: 1px solid #ddd;"></textarea>
        <button onclick="submitFeedback()" class="pill-button" style="width: fit-content; background: #2a2185; color: white; padding: 10px 20px; border-radius: 20px; border: none; cursor: pointer;">
            Post Comment
        </button>
        
    </div>
    

    <div class="card" style="padding: 20px; background: #f8f9fa;">
        <h3 style="font-size: 1.1rem; color: #2a2185;">Recent Discussions</h3>
        <div id="feedbackThread" style="max-height: 200px; overflow-y: auto; font-size: 0.85rem;">
            <p style="color: #999;">Loading discussions...</p>
        </div>
    </div>
</div>
<?php endif; ?>

    </section>
<?php endif; ?>

    <?php if ($section === 'behavior' && (hasAccess('super_admin') || in_array('behavior', $perms))): ?>
        <section class="report-section">
            <div class="report-header">
                <h2><i class='bx bx-trending-up'></i> User Activity Velocity</h2>
                <div class="analyst-comment">
    <strong>Report Insight:</strong>

    <?php if (defined('EXPORT_MODE')): ?>
        <div class="saved-report-view">
            <p class="static-report-text">
                <?php echo nl2br(htmlspecialchars($saved_reports['behavior'] ?? 'No analyst insights available.')); ?>
            </p>
        </div>

    <?php elseif ($role === 'analyst' || $role === 'super_admin'): ?>
        <p style="font-size: 0.8rem; color: #666;">(You are defining this report for viewers)</p>
        <textarea id="insight_behavior" class="analyst-textarea"><?php echo htmlspecialchars($saved_reports['behavior'] ?? 'Default behavior text...'); ?></textarea>
        <button class="save-btn" onclick="window.saveReportDefinition('behavior', 'insight_behavior')">
            <i class='bx bx-save'></i> Save Behavior Definition
        </button>

    <?php else: ?>
        <div class="saved-report-view">
            <p id="display_insight_behavior" class="static-report-text">
                <?php echo nl2br(htmlspecialchars($saved_reports['behavior'] ?? 'No analyst insights available.')); ?>
            </p>
            <span class="report-badge">SAVED VIEW</span>
        </div>
    <?php endif; ?>

    <?php if (!defined('EXPORT_MODE')): ?>
<button class="save-btn"
        style="display:inline-block; margin-left:10px;"
        onclick="exportReportWithChart('behavior','lineChart')">
    <i class='bx bx-download'></i> Export Behavior PDF
</button>
<?php endif; ?>
</div>
            </div>
            <div class="cardContainer">
                <div class="cardBox full-width">
                    <div class="chart-container">
                        <canvas id="lineChart"></canvas>
                    </div>
                </div>
            </div>

<?php if (!defined('EXPORT_MODE')): ?>
<div class="cardBox" style="margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <div class="card" style="padding: 20px; display: flex; flex-direction: column; gap: 10px;">
        <h3 style="font-size: 1.1rem; color: #2a2185;">Discussion Board</h3>
        <p style="font-size: 0.8rem; color: #666;">See something weird in the data? Ask for a breakdown.</p>
        <textarea id="feedbackText" placeholder="e.g. Why did Chrome traffic spike yesterday?" 
                  style="width: 100%; height: 80px; padding: 10px; border-radius: 8px; border: 1px solid #ddd;"></textarea>
        <button onclick="submitFeedback()" class="pill-button" style="width: fit-content; background: #2a2185; color: white; padding: 10px 20px; border-radius: 20px; border: none; cursor: pointer;">
            Post Comment
        </button>
    </div>

    <div class="card" style="padding: 20px; background: #f8f9fa;">
        <h3 style="font-size: 1.1rem; color: #2a2185;">Recent Discussions</h3>
        <div id="feedbackThread" style="max-height: 200px; overflow-y: auto; font-size: 0.85rem;">
            <p style="color: #999;">Loading discussions...</p>
        </div>
    </div>
</div>
<?php endif; ?>

        </section>
    <?php endif; ?>
        
<?php if ($section === 'engagement' && (hasAccess('super_admin') || in_array('engagement', $perms))): ?>
        <section class="report-section">
            <div class="report-header">
                <h2><i class='bx bx-mouse-alt'></i> User Engagement Analysis</h2>
<div class="analyst-comment">
    <strong>Report Insight:</strong>

    <?php if (defined('EXPORT_MODE')): ?>
        <div class="saved-report-view">
            <p class="static-report-text">
                <?php echo nl2br(htmlspecialchars($saved_reports['engagement'] ?? 'No analyst insights available.')); ?>
            </p>
        </div>

    <?php elseif ($role === 'analyst' || $role === 'super_admin'): ?>
        <p style="font-size: 0.8rem; color: #666;">(You are defining this report for viewers)</p>
        <textarea id="insight_engagement" class="analyst-textarea"><?php echo htmlspecialchars($saved_reports['engagement'] ?? 'Default engagement text...'); ?></textarea>
        <button class="save-btn" onclick="window.saveReportDefinition('engagement', 'insight_engagement')">
            <i class='bx bx-save'></i> Save Engagement Definition
        </button>

    <?php else: ?>
        <div class="saved-report-view">
            <p id="display_insight_engagement" class="static-report-text">
                <?php echo nl2br(htmlspecialchars($saved_reports['engagement'] ?? 'No analyst insights available.')); ?>
            </p>
            <span class="report-badge">SAVED VIEW</span>
        </div>
    <?php endif; ?>

    <?php if (!defined('EXPORT_MODE')): ?>
<button class="save-btn"
        style="display:inline-block; margin-left:10px;"
        onclick="exportReportWithChart('engagement','engagementBarChart')">
    <i class='bx bx-download'></i> Export Engagement PDF
</button>
<?php endif; ?>
</div>
            </div>
            <div class="cardBox chart-main">
    <h3 style="text-align: center; margin-bottom: 20px;">Engagement Volume by Event Type</h3>
    
    <div class="chart-container" style="position: relative; height: 40vh; width: 100%; display: flex; justify-content: center;">
        <canvas id="engagementBarChart"></canvas>
    </div>

    <div class="dashboard-controls" style="margin-top: 30px;">
        <p style="font-size: 0.75rem; text-transform: uppercase; color: #a0aec0; letter-spacing: 1px; font-weight: 700; margin-bottom: 10px; text-align: center;">
            Adjust Report Window
        </p>
        <div class="filter-pill">
            <div class="pill-segment">
                <i class='bx bx-calendar'></i>
                <input type="date" id="startDateEng" class="hidden-date-input">
            </div>
            <div class="pill-divider"></div>
            <div class="pill-segment">
                <input type="date" id="endDateEng" class="hidden-date-input">
            </div>
            <button onclick="window.applyDateFilter('engagement')" class="pill-button">
                <span>Update</span>
                <i class='bx bx-refresh'></i>
            </button>
        </div>
    </div>
</div>

<div class="details">
    <table class="data-table">
        <thead>
            <tr>
                <th>Interaction Type</th>
                <th>Total Occurrences</th>
                <th>Engagement Score</th>
            </tr>
        </thead>
        <tbody id="engagementTableBody">

<?php if (defined('EXPORT_MODE')): ?>

<?php
$conn = new mysqli("localhost", "collector_user", "vincent9090", "collector_db");

$sql = "SELECT event_name, COUNT(*) as total
        FROM activity_log
        GROUP BY event_name
        ORDER BY total DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        $score = $row['total'] > 50 ? "High" : "Medium";
        $color = $row['total'] > 50 ? "green" : "orange";

        echo "<tr>
                <td><strong>" . htmlspecialchars($row['event_name']) . "</strong></td>
                <td>" . htmlspecialchars($row['total']) . "</td>
                <td style='color: {$color}; font-weight:bold;'>{$score}</td>
              </tr>";
    }

} else {

    echo "<tr><td colspan='3' style='text-align:center;'>No engagement data found</td></tr>";

}

$conn->close();
?>

<?php else: ?>

<tr><td colspan="3" style="text-align:center;">Loading metrics...</td></tr>

<?php endif; ?>

</tbody>
    </table>
</div>
<?php if (!defined('EXPORT_MODE')): ?>
<div class="cardBox" style="margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <div class="card" style="padding: 20px; display: flex; flex-direction: column; gap: 10px;">
        <h3 style="font-size: 1.1rem; color: #2a2185;">Discussion Board</h3>
        <p style="font-size: 0.8rem; color: #666;">See something weird in the data? Ask for a breakdown.</p>
        <textarea id="feedbackText" placeholder="e.g. Why did Chrome traffic spike yesterday?" 
                  style="width: 100%; height: 80px; padding: 10px; border-radius: 8px; border: 1px solid #ddd;"></textarea>
        <button onclick="submitFeedback()" class="pill-button" style="width: fit-content; background: #2a2185; color: white; padding: 10px 20px; border-radius: 20px; border: none; cursor: pointer;">
            Post Comment
        </button>
    </div>

    <div class="card" style="padding: 20px; background: #f8f9fa;">
        <h3 style="font-size: 1.1rem; color: #2a2185;">Recent Discussions</h3>
        <div id="feedbackThread" style="max-height: 200px; overflow-y: auto; font-size: 0.85rem;">
            <p style="color: #999;">Loading discussions...</p>
        </div>
    </div>
</div>
<?php endif; ?>

        </section>
    <?php endif; ?>
    <?php if (
    ($section === 'overview' || $section === 'performance' || $section === 'behavior' || $section === 'engagement') 
    && (hasAccess('analyst') || hasAccess('super_admin'))
): ?>
    <section class="report-section">
        <div class="report-header">
            <h2><i class='bx bx-list-ul'></i> System Audit Trail</h2>
            <div class="analyst-comment">
                <strong>Analyst Insight:</strong> Monitoring the latest 30 events for security anomalies.
            </div>
        </div>
        <div class="cardBox log-card">
            <table class="data-table">
                <tbody>
                    <?php                         function getBrowserName($userAgent) {
                                    if (strpos($userAgent, 'Opera') || strpos($userAgent, 'OPR')) return 'Opera';
                                    if (strpos($userAgent, 'Edge')) return 'Edge';
                                    if (strpos($userAgent, 'Chrome')) return 'Chrome';
                                    if (strpos($userAgent, 'Safari')) return 'Safari';
                                    if (strpos($userAgent, 'Firefox')) return 'Firefox';
                                    if (strpos($userAgent, 'MSIE') || strpos($userAgent, 'Trident/7')) return 'Internet Explorer';
                                    return 'Unknown';
                                }

                                $conn = new mysqli("localhost", "collector_user", "vincent9090", "collector_db");

                                if (!$conn->connect_error) {
                                    $sql = "SELECT data_type, event_name, payload, created_at FROM activity_log ORDER BY created_at DESC LIMIT 30";
                                    $result = $conn->query($sql);

                                        while($row = $result->fetch_assoc()) {
                                            $details = json_decode($row['payload'], true);
                                            
                                            // 1. Logic to extract Browser or fallback to Category
                                            $ua = $details['userAgent'] ?? '';
                                            if ($ua != '') {
                                                $displaySource = getBrowserName($ua); // Shows "Chrome", "Safari", etc.
                                            } else {
                                                $displaySource = ucfirst($row['data_type']); // Shows "Activity", "Performance"
                                            }
                                            
                                            // 2. Logic to extract Resolution or fallback to Event Name
                                            $res = $details['screenDim'] ?? $row['event_name'] ?? 'N/A';

    echo "<tr style='border-bottom: 1px solid #333;'>";
        echo "<td style='padding: 12px; font-weight: bold; color: #36a2eb; width: 120px;'>{$displaySource}</td>";
        echo "<td style='padding: 12px;'>{$res}</td>"; 
        echo "<td style='padding: 12px; max-width: 600px;'>
            <div style='width: 100%; overflow-x: auto; white-space: nowrap; font-family: monospace; font-size: 0.85em; background: #ffffff; padding: 5px; border-radius: 4px;'>
                " . htmlspecialchars($row['payload']) . "
            </div>
          </td>"; 
        echo "<td style='padding: 12px; color: #888; white-space: nowrap; width: 180px;'>{$row['created_at']}</td>";
    echo "</tr>";
                                        }
                                    $conn->close();
                                } ?>
                </tbody>
            </table>
        </div>
    </section>

<?php elseif ($section === 'overview'): ?>
    <section class="report-section">
        <div class="cardBox" style="display: block;">
            <div class="card" style="padding: 50px; text-align: center; background: #fff; border-radius: 20px; box-shadow: 0 7px 25px rgba(0,0,0,0.08);">
                <div style="font-size: 4rem; margin-bottom: 20px;">🔍</div>
                <h2 style="color: #2a2185; margin-bottom: 15px; font-size: 1.8rem;">Select a Report to Begin</h2>
                <p style="color: #666; max-width: 600px; margin: 0 auto 30px auto; line-height: 1.8; font-size: 1.1rem;">
                    Welcome, <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></strong>! 
                    The overview dashboard is reserved for administrative audits. Please use the 
                    <strong>sidebar navigation</strong> to access specific analytical reports.
                </p>
                
                <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
                    <div style="padding: 20px; background: #f8f9fa; border-radius: 15px; border-top: 5px solid #2a2185; width: 220px; text-align: left;">
                        <h4 style="margin: 0 0 10px 0; color: #2a2185;"><i class='bx bx-bar-chart-alt-2'></i> Performance</h4>
                        <p style="font-size: 0.85rem; color: #777; margin: 0;">Analyze Browser distributions and data.</p>
                    </div>
                    <div style="padding: 20px; background: #f8f9fa; border-radius: 15px; border-top: 5px solid #2a2185; width: 220px; text-align: left;">
                        <h4 style="margin: 0 0 10px 0; color: #2a2185;"><i class='bx bx-mouse-alt'></i> Engagement</h4>
                        <p style="font-size: 0.85rem; color: #777; margin: 0;">Track user clicks and custom event interaction scores.</p>
                    </div>
                </div>

                <div style="margin-top: 40px; color: #2a2185; font-weight: bold; animation: pulse 2s infinite;">
                    <i class='bx bx-left-arrow-alt'></i> Click a menu item on the left to load data
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>



<?php if ($section === 'admin' && $role === 'super_admin'): ?>
    <section class="report-section admin-section">
        <div class="report-header">
            <h2><i class='bx bx-shield-quarter'></i> User Management</h2>
            <div class="analyst-comment">
                <strong>System Note:</strong> Changes to roles and permissions take effect upon the next user login. 
                Ensure permissions are entered as a valid JSON array (e.g., <code>["performance", "behavior"]</code>).
            </div>
        </div>
        
        <div class="cardBox admin-card" style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #edf2f7;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Current Role</th>
                        <th>Permissions (JSON)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $conn = new mysqli("localhost", "collector_user", "vincent9090", "collector_db");
                    $result = $conn->query("SELECT id, username, role, permissions FROM users");
                    while($u = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td>
                            <select onchange="window.updateUser(<?php echo $u['id']; ?>, 'role', this.value)">
                                <option value="super_admin" <?php if($u['role'] == 'super_admin') echo 'selected'; ?>>Super Admin</option>
                                <option value="analyst" <?php if($u['role'] == 'analyst') echo 'selected'; ?>>Analyst</option>
                                <option value="viewer" <?php if($u['role'] == 'viewer') echo 'selected'; ?>>Viewer</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" 
                                   class="perm-input"
                                   value='<?php echo htmlspecialchars($u['permissions']); ?>' 
                                   onchange="window.updateUser(<?php echo $u['id']; ?>, 'permissions', this.value)">
                        </td>
                    </tr>
                    <?php endwhile; $conn->close(); ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?php if (empty($perms) && $role !== 'super_admin'): ?>
    <div class="error-container" style="text-align: center; margin-top: 50px;">
        <i class='bx bx-lock-alt' style="font-size: 4rem; color: #ccc;"></i>
        <h2>No Reports Assigned</h2>
        <p>Your account does not have permission to view any analytics modules.</p>
        <p>Please contact <strong>Sam</strong> or <strong>Sally</strong> to request access.</p>
    </div>
<?php endif; ?>

</main>

<?php if (!defined('EXPORT_MODE')): ?>
<script>
    const currentView = "<?php echo $section; ?>";
    window.currentView = currentView;

    function exportReportWithChart(view, canvasId) {
        const canvas = document.getElementById(canvasId);

        if (!canvas) {
            alert("Chart not found.");
            return;
        }

        const chartImage = canvas.toDataURL("image/png");

        const form = document.createElement("form");
        form.method = "POST";
        form.action = "../export_report.php";

        const viewInput = document.createElement("input");
        viewInput.type = "hidden";
        viewInput.name = "view";
        viewInput.value = view;

        const chartInput = document.createElement("input");
        chartInput.type = "hidden";
        chartInput.name = "chart_image";
        chartInput.value = chartImage;

        form.appendChild(viewInput);
        form.appendChild(chartInput);

        document.body.appendChild(form);
        form.submit();
    }
</script>


<script>
    // Bridge PHP Session to JS
    window.userSession = {
        // We pull directly from the Session to be 100% sure
        username: "<?php echo $_SESSION['username'] ?? 'Anonymous'; ?>",
        role: "<?php echo $_SESSION['role'] ?? 'viewer'; ?>", 
        currentView: "<?php echo $section; ?>"
    };
    console.log("Logged in as:", window.userSession.username, "with role:", window.userSession.role);

    // Helper to match your PHP hasAccess function
    window.hasAccess = function(requiredRole) {
        const role = window.userSession.role;
        if (role === 'super_admin') return true;
        if (requiredRole === 'analyst' && role === 'analyst') return true;
        if (requiredRole === 'viewer') return true;
        return false;
    };
</script>

<script type="module" src="dashboard.js?v=1.1"></script>
<?php endif; ?>

</body>
</html>