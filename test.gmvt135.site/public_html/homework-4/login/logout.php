<?php
/**
 * STEP 1: AUTHENTICATION BACKEND - LOGOUT
 * This script destroys the server-side session to prevent "Forceful Browsing."
 */

// 1. Access the existing session
session_start();

// 2. Clear all session variables (e.g., isLoggedIn)
session_unset();

// 3. Destroy the session data on the server
session_destroy();

// 4. Redirect the user back to the login page
// Since this file is in the 'login' folder, we point to 'login.html' in the same directory.
header("Location: login.html");
exit;
?>
