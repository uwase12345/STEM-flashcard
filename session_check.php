<?php
// session_check.php
// Include this file at the top of any protected page

session_start();

// Function to check if user is logged in
function checkLogin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
        header("Location: index.php");
        exit();
    }
}

// Function to check if user is admin
function checkAdmin() {
    checkLogin();
    if ($_SESSION['user_type'] !== 'admin') {
        header("Location: index.php");
        exit();
    }
}

// Function to check if user is student
function checkStudent() {
    checkLogin();
    if ($_SESSION['user_type'] !== 'student') {
        header("Location: index.php");
        exit();
    }
}

// Function to logout user
function logout() {
    session_start();
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

// Function to get current user info
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'firstname' => $_SESSION['firstname'],
        'lastname' => $_SESSION['lastname'],
        'type' => $_SESSION['user_type']
    ];
}

// Auto-logout after inactivity (optional - 30 minutes)
function checkInactivity($timeout = 1800) { // 30 minutes = 1800 seconds
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > $timeout) {
            logout();
        }
    }
    $_SESSION['last_activity'] = time();
}

// Call inactivity check if session exists
if (isset($_SESSION['user_id'])) {
    checkInactivity();
}
?>