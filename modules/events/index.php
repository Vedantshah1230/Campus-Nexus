<?php

session_start();

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION["user_id"];
$message = "";

/*
|--------------------------------------------------------------------------
| Create event
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $eventDate = $_POST["event_date"] ?? "";
    $location = trim($_POST["location"] ?? "");
    $maxParticipants = (int) ($_POST["max_participants"] ?? 0);

    if (
        $title === "" ||
        $description === "" ||
        $eventDate === "" ||
        $location === "" ||
        $maxParticipants <= 0
    ) {

        $message = "Please fill in all event fields correctly.";

    } else {

        $sql = "INSERT INTO events
                    (
                        creator_id,
                        title,
                        description,
                        event_date,
                        location,
                        max_participants
                    )
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "issssi",
            $userId,
            $title,
            $description,
            $eventDate,
            $location,
            $maxParticipants
        );

        if ($stmt->execute()) {

            $message = "Event created successfully.";

        } else {

            $message = "Failed to create event.";
        }

        $stmt->close();
    }
}

/*
|--------------------------------------------------------------------------
| Get events
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            events.id,
            events.title,
            events.description,
            events.event_date,
            events.location,
            events.max_participants,
            events.status,
            users.name AS creator,
            COUNT(event_registrations.user_id) AS registered_count
        FROM events
        JOIN users
            ON events.creator_id = users.id
        LEFT JOIN event_registrations
            ON events.id = event_registrations.event_id
        WHERE events.status != 'cancelled'
        GROUP BY
            events.id,
            events.title,
            events.description,
            events.event_date,
            events.location,
            events.max_participants,
            events.status,
            users.name
        ORDER BY events.event_date ASC";

$result = $conn->query($sql);

$events = [];

while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Events - Campus Nexus</title>

</head>

<body>

    <h1>Campus Events</h1>

    <p>
        Discover workshops, meetups and activities happening on campus.
    </p>


    <?php if ($message !== ""): ?>

        <p>
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>


    <hr>


    <h2>Create an Event</h2>

    <form method="POST">

        <label>
            Event Title
        </label>

        <br>

        <input
            type="text"
            name="title"
            maxlength="150"
            required
        >

        <br><br>


        <label>
            Description
        </label>

        <br>

        <textarea
            name="description"
            rows="5"
            cols="50"
            required
        ></textarea>

        <br><br>


        <label>
            Event Date & Time
        </label>

        <br>

        <input
            type="datetime-local"
            name="event_date"
            required
        >

        <br><br>


        <label>
            Location
        </label>

        <br>

        <input
            type="text"
            name="location"
            maxlength="200"
            required
        >

        <br><br>


        <label>
            Maximum Participants
        </label>

        <br>

        <input
            type="number"
            name="max_participants"
            min="1"
            required
        >

        <br><br>


        <button type="submit">
            Create Event
        </button>

    </form>


    <hr>


    <h2>Upcoming Events</h2>

    <?php if (empty($events)): ?>

        <p>
            No events available.
        </p>

    <?php else: ?>

        <?php foreach ($events as $event): ?>

            <article>

                <h3>
                    <?= htmlspecialchars($event["title"]) ?>
                </h3>

                <p>
                    <?= nl2br(
                        htmlspecialchars(
                            $event["description"]
                        )
                    ) ?>
                </p>

                <p>

                    <strong>Date:</strong>

                    <?= htmlspecialchars(
                        $event["event_date"]
                    ) ?>

                </p>

                <p>

                    <strong>Location:</strong>

                    <?= htmlspecialchars(
                        $event["location"]
                    ) ?>

                </p>

                <p>

                    <strong>Created by:</strong>

                    <?= htmlspecialchars(
                        $event["creator"]
                    ) ?>

                </p>

                <p>

                    <strong>Participants:</strong>

                    <?= $event["registered_count"] ?>

                    /

                    <?= $event["max_participants"] ?>

                </p>


                <?php

                $remaining =
                    $event["max_participants"]
                    - $event["registered_count"];

                ?>

                <p>

                    <strong>Seats remaining:</strong>

                    <?= $remaining ?>

                </p>


                <?php if (
                    $remaining > 0 &&
                    $event["creator"]
                    !== $_SESSION["user_name"]
                ): ?>

                    <form
                        method="POST"
                        action="register.php"
                    >

                        <input
                            type="hidden"
                            name="event_id"
                            value="<?= $event["id"] ?>"
                        >

                        <button type="submit">
                            Register
                        </button>

                    </form>

                <?php elseif ($remaining <= 0): ?>

                    <p>
                        Event is full.
                    </p>

                <?php endif; ?>

            </article>

            <hr>

        <?php endforeach; ?>

    <?php endif; ?>


    <a href="../../public/dashboard.php">
        Back to Dashboard
    </a>

</body>

</html>