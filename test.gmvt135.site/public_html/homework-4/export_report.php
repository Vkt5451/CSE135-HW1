<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] !== true) {
    header("Location: /homework-4/login/login.html");
    exit;
}

$view = $_POST['view'] ?? $_GET['view'] ?? 'performance';
$chartImage = $_POST['chart_image'] ?? '';

$allowedViews = ['performance', 'behavior', 'engagement'];
if (!in_array($view, $allowedViews, true)) {
    http_response_code(400);
    die('Invalid report view');
}

$role  = $_SESSION['role'] ?? 'viewer';
$perms = $_SESSION['permissions'] ?? [];

/*
|--------------------------------------------------------------------------
| Export authorization
|--------------------------------------------------------------------------
*/
$canExport = false;

if ($role === 'super_admin') {
    $canExport = true;
} elseif ($role === 'analyst' && in_array($view, $perms)) {
    $canExport = true;
} elseif ($role === 'viewer' && in_array($view, $perms)) {
    $canExport = true;
}

if (!$canExport) {
    http_response_code(403);
    die('403 - You do not have permission to export this report.');
}

$exportsDir = __DIR__ . '/exports';
if (!is_dir($exportsDir)) {
    die('exports folder does not exist');
}
if (!is_writable($exportsDir)) {
    die('exports folder is not writable');
}

/*
|--------------------------------------------------------------------------
| Render dashboard HTML in export mode
|--------------------------------------------------------------------------
*/
define('EXPORT_MODE', true);
$_GET['view'] = $view;

ob_start();
include __DIR__ . '/dashboard/dashboard.php';
$html = ob_get_clean();

if (!$html) {
    die('Could not render report HTML');
}

/*
|--------------------------------------------------------------------------
| Replace chart canvas with submitted image
|--------------------------------------------------------------------------
*/
if (!empty($chartImage)) {
    $imgTag = '<div style="margin: 20px auto; text-align: center; width: 100%;">
              <img src="' . $chartImage . '" style="width: 500px; max-width: 90%; height: auto; display: block; margin: 0 auto;">
           </div>';

    if ($view === 'performance') {
        $html = str_replace('<canvas id="myBrowserChart"></canvas>', $imgTag, $html);
    } elseif ($view === 'behavior') {
        $html = str_replace('<canvas id="lineChart"></canvas>', $imgTag, $html);
    } elseif ($view === 'engagement') {
        $html = str_replace('<canvas id="engagementBarChart"></canvas>', $imgTag, $html);
    }
}

/*
|--------------------------------------------------------------------------
| Generate PDF
|--------------------------------------------------------------------------
*/
$options = new Options();
$options->set('isRemoteEnabled', false);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$pdfOutput = $dompdf->output();

$filename = $view . '_report_' . date('Ymd_His') . '.pdf';
$filePath = $exportsDir . '/' . $filename;

file_put_contents($filePath, $pdfOutput);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo $pdfOutput;
exit();