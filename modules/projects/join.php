<?php

session_start();

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: team-finder.php");
    exit;
}

$projectId = (int) ($_POST["project_id"] ?? 0);

if ($projectId <= 0) {
    header("Location: team-finder.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Check whether project exists and is open
|--------------------------------------------------------------------------
*/

$sql = "SELECT id, creator_id
        FROM projects
        WHERE id = ?
          AND status = 'open'";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $projectId
);

$stmt->execute();

$result = $stmt->get_result();

$project = $result->fetch_assoc();

$stmt->close();

if (!$project) {
    die("Project not found or no longer open.");
}

/*
|--------------------------------------------------------------------------
| Prevent creator from joining their own project
|--------------------------------------------------------------------------
*/

if ((int) $project["creator_id"] === $userId) {
    die("You are already the creator of this project.");
}

/*
|--------------------------------------------------------------------------
| Check whether user already has a membership record
|--------------------------------------------------------------------------
*/

$sql = "SELECT status
        FROM project_members
        WHERE project_id = ?
          AND user_id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ii",
    $projectId,
    $userId
);

$stmt->execute();

$result = $stmt->get_result();

$existingMembership = $result->fetch_assoc();

$stmt->close();

if ($existingMembership) {

    die(
        "You already have a request for this project. " .
        "Current status: " .
        htmlspecialchars($existingMembership["status"])
    );
}

/*
|--------------------------------------------------------------------------
| Create join request
|--------------------------------------------------------------------------
*/

$sql = "INSERT INTO project_members
            (project_id, user_id, role, status)
        VALUES (?, ?, 'member', 'pending')";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ii",
    $projectId,
    $userId
);

if ($stmt->execute()) {

    $stmt->close();

    header("Location: my-projects.php");
    exit;

}

$stmt->close();

die("Failed to send join request.");