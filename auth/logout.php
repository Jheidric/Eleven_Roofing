<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/session.php';

if (isLoggedIn()) {
    $u = currentUser();
    logActivity($u['id'], 'Logout', 'Auth', 'User logged out');
}

// destroy session
session_destroy();

// redirect to public homepage
header('Location: ../public/index.php');
exit;
?>