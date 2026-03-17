<?php
session_start();

// 1. Basic Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

/**
 * 2. Authorization Helper Function
 * This checks if the logged-in user has the required role.
 */
function hasAccess($requiredRole) {
    $currentRole = $_SESSION['role'];

    // Super Admin always has access
    if ($currentRole === 'super_admin') return true;

    // Analysts can access Analyst and Viewer areas
    if ($requiredRole === 'analyst' && $currentRole === 'analyst') return true;

    // Viewers can ONLY access Viewer areas
    if ($requiredRole === 'viewer' && ($currentRole === 'viewer' || $currentRole === 'analyst')) return true;

    return false;
}
?>