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
    header("Location: team-finder.php");
    exit;
}

$projectId = (int) ($_POST["project_id"] ?? 0);

if ($projectId <= 0) {
    $_SESSION["flash_error"] = "Invalid project specified.";
    header("Location: team-finder.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Check whether project exists and is open
|--------------------------------------------------------------------------
*/
$sql = "SELECT id, title, creator_id FROM projects WHERE id = ? AND status = 'open'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $projectId);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project) {
    $_SESSION["flash_error"] = "This project could not be found or is no longer open for recruitment.";
    header("Location: team-finder.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Prevent creator from joining their own project
|--------------------------------------------------------------------------
*/
if ((int) $project["creator_id"] === $userId) {
    $_SESSION["flash_warning"] = "You are already the owner of this project.";
    header("Location: team-finder.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Check whether user already has a membership record
|--------------------------------------------------------------------------
*/
$sql = "SELECT status FROM project_members WHERE project_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $projectId, $userId);
$stmt->execute();
$existingMembership = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existingMembership) {
    $_SESSION["flash_warning"] = "You already submitted a request for this project (Status: " . ucfirst($existingMembership["status"]) . ").";
    header("Location: my-projects.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Create join request
|--------------------------------------------------------------------------
*/
$sql = "INSERT INTO project_members (project_id, user_id, role, status)
        VALUES (?, ?, 'member', 'pending')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $projectId, $userId);

if ($stmt->execute()) {
    $stmt->close();
    $_SESSION["flash_success"] = "Your request to join '" . htmlspecialchars($project["title"]) . "' has been sent to the team owner.";
    header("Location: my-projects.php");
    exit;
}

$stmt->close();
$_SESSION["flash_error"] = "Unable to submit join request. Please try again.";
header("Location: team-finder.php");
exit;