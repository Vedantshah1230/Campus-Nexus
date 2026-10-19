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
    header("Location: index.php");
    exit;
}

$ideaId = (int) ($_POST["idea_id"] ?? 0);

if ($ideaId <= 0) {
    $_SESSION["flash_error"] = "Invalid idea specified.";
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Check whether idea exists and is active
|--------------------------------------------------------------------------
*/
$sql = "SELECT id, title, user_id FROM ideas WHERE id = ? AND status != 'archived'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ideaId);
$stmt->execute();
$idea = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$idea) {
    $_SESSION["flash_error"] = "This idea proposal could not be found or has been archived.";
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Prevent voting for your own idea
|--------------------------------------------------------------------------
*/
if ((int) $idea["user_id"] === $userId) {
    $_SESSION["flash_warning"] = "You cannot vote for your own submitted idea.";
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Check whether user already voted
|--------------------------------------------------------------------------
*/
$sql = "SELECT idea_id FROM idea_votes WHERE idea_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $ideaId, $userId);
$stmt->execute();
$existingVote = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existingVote) {
    $_SESSION["flash_warning"] = "You have already cast your vote for '" . htmlspecialchars($idea["title"]) . "'.";
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Add vote
|--------------------------------------------------------------------------
*/
$sql = "INSERT INTO idea_votes (idea_id, user_id) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $ideaId, $userId);

if ($stmt->execute()) {
    $stmt->close();
    $_SESSION["flash_success"] = "Your upvote for '" . htmlspecialchars($idea["title"]) . "' has been recorded!";
    header("Location: index.php");
    exit;
}

$stmt->close();
$_SESSION["flash_error"] = "Unable to record vote. Please try again.";
header("Location: index.php");
exit;