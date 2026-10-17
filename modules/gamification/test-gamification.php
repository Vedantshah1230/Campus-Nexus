<?php

session_start();

require_once "../../config/database.php";
require_once "helpers.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| Test awarding 10 points
|--------------------------------------------------------------------------
*/

if (awardPoints($conn, $userId, 10)) {
    echo "10 points awarded successfully.<br>";
} else {
    echo "Failed to award points.<br>";
}