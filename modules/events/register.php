<?php

session_start();

require_once "../../config/database.php";
require_once "../gamification/helpers.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$eventId = (int) ($_POST["event_id"] ?? 0);

if ($eventId <= 0) {
    die("Invalid event.");
}

/*
|--------------------------------------------------------------------------
| Get event information
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
            creator_id,
            max_participants,
            status
        FROM events
        WHERE id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $eventId
);

$stmt->execute();

$result = $stmt->get_result();

$event = $result->fetch_assoc();

$stmt->close();

if (!$event) {
    die("Event not found.");
}

if ($event["status"] === "cancelled") {
    die("This event has been cancelled.");
}

/*
|--------------------------------------------------------------------------
| Prevent creator from registering
|--------------------------------------------------------------------------
*/

if ((int) $event["creator_id"] === $userId) {
    die("You created this event.");
}

/*
|--------------------------------------------------------------------------
| Check duplicate registration
|--------------------------------------------------------------------------
*/

$sql = "SELECT event_id
        FROM event_registrations
        WHERE event_id = ?
          AND user_id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ii",
    $eventId,
    $userId
);

$stmt->execute();

$result = $stmt->get_result();

$existingRegistration = $result->fetch_assoc();

$stmt->close();

if ($existingRegistration) {
    die("You are already registered for this event.");
}

/*
|--------------------------------------------------------------------------
| Count current registrations
|--------------------------------------------------------------------------
*/

$sql = "SELECT COUNT(*) AS registered_count
        FROM event_registrations
        WHERE event_id = ?
          AND status = 'registered'";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $eventId
);

$stmt->execute();

$result = $stmt->get_result();

$data = $result->fetch_assoc();

$registeredCount = (int) $data["registered_count"];

$stmt->close();


/*
|--------------------------------------------------------------------------
| Check capacity
|--------------------------------------------------------------------------
*/

if (
    $registeredCount >=
    (int) $event["max_participants"]
) {

    die("Sorry, this event is full.");
}


/*
|--------------------------------------------------------------------------
| Register student
|--------------------------------------------------------------------------
*/

$sql = "INSERT INTO event_registrations
            (event_id, user_id, status)
        VALUES (?, ?, 'registered')";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ii",
    $eventId,
    $userId
);

if (!$stmt->execute()) {

   /*
|--------------------------------------------------------------------------
| Check whether this is the student's first registration
|--------------------------------------------------------------------------
*/

$sql = "SELECT COUNT(*) AS registration_count
        FROM event_registrations
        WHERE user_id = ?
          AND status = 'registered'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$stmt->close();

if ((int) $row["registration_count"] === 1) {

    // Badge ID 5 = Event Explorer
    $badgeAdded = awardBadge($conn, $userId, 5);

    if ($badgeAdded) {
        awardPoints($conn, $userId, 20);
    }
}
}

$stmt->close();

header("Location: index.php");
exit;