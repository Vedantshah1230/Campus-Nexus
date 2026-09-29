<?php

session_start();

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: exchange.php");
    exit;
}

$exchangeId = (int) ($_POST["exchange_id"] ?? 0);

if ($exchangeId <= 0) {
    die("Invalid skill exchange.");
}

/*
|--------------------------------------------------------------------------
| Get exchange
|--------------------------------------------------------------------------
*/

$sql = "SELECT id, user_id
        FROM skill_exchanges
        WHERE id = ?
          AND status = 'open'";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $exchangeId
);

$stmt->execute();

$result = $stmt->get_result();

$exchange = $result->fetch_assoc();

$stmt->close();

if (!$exchange) {
    die("Skill exchange not found or closed.");
}

/*
|--------------------------------------------------------------------------
| Prevent requesting your own exchange
|--------------------------------------------------------------------------
*/

if ((int) $exchange["user_id"] === $userId) {
    die("You cannot request your own skill exchange.");
}

/*
|--------------------------------------------------------------------------
| Check duplicate request
|--------------------------------------------------------------------------
*/

$sql = "SELECT id
        FROM skill_exchange_requests
        WHERE exchange_id = ?
          AND requester_id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ii",
    $exchangeId,
    $userId
);

$stmt->execute();

$result = $stmt->get_result();

$existingRequest = $result->fetch_assoc();

$stmt->close();

if ($existingRequest) {
    die("You have already requested this skill exchange.");
}

/*
|--------------------------------------------------------------------------
| Create request
|--------------------------------------------------------------------------
*/

$message = "";

$requestMessage =
    "I would like to learn this skill from you.";

$sql = "INSERT INTO skill_exchange_requests
            (exchange_id, requester_id, message)
        VALUES (?, ?, ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "iis",
    $exchangeId,
    $userId,
    $requestMessage
);

if ($stmt->execute()) {

    $message = "Request sent successfully.";

} else {

    $message = "Failed to send request.";
}

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Skill Exchange Request</title>

</head>

<body>

    <h1>
        <?= htmlspecialchars($message) ?>
    </h1>

    <a href="exchange.php">
        Back to Skill Exchange
    </a>

</body>

</html>