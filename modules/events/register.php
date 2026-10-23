<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../gamification/helpers.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$eventId = (int) ($_POST["event_id"] ?? 0);

if ($eventId <= 0) {
    $_SESSION["flash_error"] = "Invalid event specified.";
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get event information
|--------------------------------------------------------------------------
*/
$sql = "SELECT id, title, creator_id, max_participants, status, event_date FROM events WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    $_SESSION["flash_error"] = "This event could not be found.";
    header("Location: index.php");
    exit;
}

if ($event["status"] === "cancelled") {
    $_SESSION["flash_error"] = "This event has been cancelled by the organizer.";
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Prevent creator from registering
|--------------------------------------------------------------------------
*/
if ((int) $event["creator_id"] === $userId) {
    $_SESSION["flash_warning"] = "You are the organizer of this event.";
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Check duplicate registration
|--------------------------------------------------------------------------
*/
$sql = "SELECT event_id, status FROM event_registrations WHERE event_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $eventId, $userId);
$stmt->execute();
$existingRegistration = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existingRegistration && $existingRegistration["status"] === "registered") {
    $_SESSION["flash_warning"] = "You are already registered for '" . htmlspecialchars($event["title"]) . "'.";
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Count current active registrations
|--------------------------------------------------------------------------
*/
$sql = "SELECT COUNT(*) AS registered_count FROM event_registrations WHERE event_id = ? AND status = 'registered'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$registeredCount = (int) ($data["registered_count"] ?? 0);
$stmt->close();

/*
|--------------------------------------------------------------------------
| Check capacity limit
|--------------------------------------------------------------------------
*/
if ($registeredCount >= (int) $event["max_participants"]) {
    $_SESSION["flash_warning"] = "Sorry, '" . htmlspecialchars($event["title"]) . "' has reached its maximum seat capacity.";
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Register student (Using ON DUPLICATE KEY UPDATE in case previously cancelled)
|--------------------------------------------------------------------------
*/
$timeCol = null;
$colCheck = $conn->query("SHOW COLUMNS FROM event_registrations LIKE 'created_at'");
if ($colCheck && $colCheck->num_rows > 0) {
    $timeCol = 'created_at';
} else {
    $colCheck2 = $conn->query("SHOW COLUMNS FROM event_registrations LIKE 'registered_at'");
    if ($colCheck2 && $colCheck2->num_rows > 0) {
        $timeCol = 'registered_at';
    }
}

$updateClause = "status = 'registered'";
if ($timeCol) {
    $updateClause .= ", {$timeCol} = CURRENT_TIMESTAMP";
}

$sql = "INSERT INTO event_registrations (event_id, user_id, status)
        VALUES (?, ?, 'registered')
        ON DUPLICATE KEY UPDATE {$updateClause}";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $eventId, $userId);

if ($stmt->execute()) {
    $stmt->close();

    // Check whether this is the student's first event registration
    $countSql = "SELECT COUNT(*) AS reg_count FROM event_registrations WHERE user_id = ? AND status = 'registered'";
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param("i", $userId);
    $countStmt->execute();
    $countRow = $countStmt->get_result()->fetch_assoc();
    $countStmt->close();

    if ((int) $countRow["reg_count"] === 1) {
        // Badge ID 5 = Event Explorer (20 points)
        $badgeAdded = awardBadge($conn, $userId, 5);
        if ($badgeAdded) {
            awardPoints($conn, $userId, 20);
        }
    }

    $_SESSION["flash_success"] = "Registration confirmed for '" . htmlspecialchars($event["title"]) . "'! We look forward to seeing you there.";
    header("Location: index.php");
    exit;

} else {
    $stmt->close();
    $_SESSION["flash_error"] = "Failed to complete event registration. Please try again.";
    header("Location: index.php");
    exit;
}