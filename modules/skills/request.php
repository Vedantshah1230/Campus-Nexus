<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: exchange.php");
    exit;
}

$exchangeId = (int) ($_POST["exchange_id"] ?? 0);

if ($exchangeId <= 0) {
    $_SESSION["flash_error"] = "Invalid skill exchange specified.";
    header("Location: exchange.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get exchange details & verify open status
|--------------------------------------------------------------------------
*/
$sql = "SELECT skill_exchanges.id, skill_exchanges.user_id, skills.name AS skill_name
        FROM skill_exchanges
        JOIN skills ON skill_exchanges.skill_id = skills.id
        WHERE skill_exchanges.id = ?
          AND skill_exchanges.status = 'open'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $exchangeId);
$stmt->execute();
$exchange = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exchange) {
    $_SESSION["flash_error"] = "Skill exchange offer not found or is currently closed.";
    header("Location: exchange.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Prevent requesting your own exchange
|--------------------------------------------------------------------------
*/
if ((int) $exchange["user_id"] === $userId) {
    $_SESSION["flash_warning"] = "You cannot request assistance from your own skill exchange listing.";
    header("Location: exchange.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Check duplicate request
|--------------------------------------------------------------------------
*/
$sql = "SELECT id, status FROM skill_exchange_requests WHERE exchange_id = ? AND requester_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $exchangeId, $userId);
$stmt->execute();
$existingRequest = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existingRequest) {
    $_SESSION["flash_warning"] = "You have already sent a request for this skill (Status: " . ucfirst($existingRequest["status"]) . ").";
    header("Location: requests.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Create request
|--------------------------------------------------------------------------
*/
$requestMessage = trim($_POST["message"] ?? "I would like to connect and learn this skill from you on Campus Nexus.");

$sql = "INSERT INTO skill_exchange_requests (exchange_id, requester_id, message, status)
        VALUES (?, ?, ?, 'pending')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $exchangeId, $userId, $requestMessage);

if ($stmt->execute()) {
    $stmt->close();
    $_SESSION["flash_success"] = "Your request to learn " . htmlspecialchars($exchange["skill_name"]) . " has been sent to the student mentor.";
    header("Location: requests.php");
    exit;
}

$stmt->close();
$_SESSION["flash_error"] = "Unable to send exchange request. Please try again.";
header("Location: exchange.php");
exit;