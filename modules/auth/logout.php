<?php

session_start();

$loggedUserId = isset($_SESSION["user_id"]) ? (int)$_SESSION["user_id"] : null;
$loggedUserEmail = $_SESSION["user_email"] ?? "unknown@campus.edu";

if ($loggedUserId !== null) {
    require_once __DIR__ . "/../../config/database.php";
    require_once __DIR__ . "/../../includes/auth_logger.php";
    logAuthActivity($conn, $loggedUserId, $loggedUserEmail, 'LOGOUT');
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

session_start();
$_SESSION["flash_info"] = "You have been signed out successfully. See you soon!";

header("Location: login.php");
exit;