<?php

session_start();

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$ideaId = (int) ($_POST["idea_id"] ?? 0);

if ($ideaId <= 0) {
    die("Invalid idea.");
}

/*
|--------------------------------------------------------------------------
| Check whether idea exists
|--------------------------------------------------------------------------
*/

$sql = "SELECT id, user_id
        FROM ideas
        WHERE id = ?
          AND status != 'archived'";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $ideaId
);

$stmt->execute();

$result = $stmt->get_result();

$idea = $result->fetch_assoc();

$stmt->close();

if (!$idea) {
    die("Idea not found.");
}

/*
|--------------------------------------------------------------------------
| Prevent voting for your own idea
|--------------------------------------------------------------------------
*/

if ((int) $idea["user_id"] === $userId) {
    die("You cannot vote for your own idea.");
}

/*
|--------------------------------------------------------------------------
| Check whether user already voted
|--------------------------------------------------------------------------
*/

$sql = "SELECT idea_id
        FROM idea_votes
        WHERE idea_id = ?
          AND user_id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ii",
    $ideaId,
    $userId
);

$stmt->execute();

$result = $stmt->get_result();

$existingVote = $result->fetch_assoc();

$stmt->close();

if ($existingVote) {
    die("You have already voted for this idea.");
}

/*
|--------------------------------------------------------------------------
| Add vote
|--------------------------------------------------------------------------
*/

$sql = "INSERT INTO idea_votes
            (idea_id, user_id)
        VALUES (?, ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ii",
    $ideaId,
    $userId
);

if (!$stmt->execute()) {

    $stmt->close();

    die("Failed to record vote.");
}

$stmt->close();

header("Location: index.php");
exit;