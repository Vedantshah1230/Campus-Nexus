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

// CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "info";

/*
|--------------------------------------------------------------------------
| Create event
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $submittedToken = $_POST["csrf_token"] ?? "";
    if (!hash_equals($_SESSION["csrf_token"], $submittedToken)) {
        $message = "Invalid security token. Please try again.";
        $messageType = "error";
    } else {

        $title = trim($_POST["title"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $eventDate = $_POST["event_date"] ?? "";
        $location = trim($_POST["location"] ?? "");
        $maxParticipants = (int) ($_POST["max_participants"] ?? 0);

        if ($title === "" || $description === "" || $eventDate === "" || $location === "" || $maxParticipants <= 0) {
            $message = "Please fill in all event details and specify a positive seat count.";
            $messageType = "error";
        } elseif (strtotime($eventDate) < time()) {
            $message = "Event date must be in the future.";
            $messageType = "error";
        } else {

            $sql = "INSERT INTO events (creator_id, title, description, event_date, location, max_participants, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'active')";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssi", $userId, $title, $description, $eventDate, $location, $maxParticipants);

            if ($stmt->execute()) {
                $message = "Event '" . htmlspecialchars($title) . "' created and published to campus calendar!";
                $messageType = "success";
            } else {
                $message = "Failed to create event. Please try again.";
                $messageType = "error";
            }

            $stmt->close();
        }
    }
}

/*
|--------------------------------------------------------------------------
| Search filter
|--------------------------------------------------------------------------
*/
$search = trim($_GET["search"] ?? "");

$whereClause = "WHERE events.status != 'cancelled'";
$params = [$userId];
$types = "i";

if ($search !== "") {
    $whereClause .= " AND (events.title LIKE ? OR events.location LIKE ? OR events.description LIKE ?)";
    $like = "%" . $search . "%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sss";
}

/*
|--------------------------------------------------------------------------
| Get events with live registrations and user registration state
|--------------------------------------------------------------------------
*/
$sql = "SELECT
            events.id,
            events.creator_id,
            events.title,
            events.description,
            events.event_date,
            events.location,
            events.max_participants,
            events.status,
            users.name AS creator,
            COUNT(DISTINCT event_registrations.user_id) AS registered_count,
            MAX(CASE WHEN my_reg.user_id IS NOT NULL THEN 1 ELSE 0 END) AS is_registered
        FROM events
        JOIN users ON events.creator_id = users.id
        LEFT JOIN event_registrations ON events.id = event_registrations.event_id AND event_registrations.status = 'registered'
        LEFT JOIN event_registrations AS my_reg ON events.id = my_reg.event_id AND my_reg.user_id = ? AND my_reg.status = 'registered'
        $whereClause
        GROUP BY
            events.id,
            events.creator_id,
            events.title,
            events.description,
            events.event_date,
            events.location,
            events.max_participants,
            events.status,
            users.name
        ORDER BY events.event_date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}
$stmt->close();

$pageTitle = "Campus Events & Workshops";
$breadcrumbs = [
    ["label" => "Dashboard", "url" => "../../public/dashboard.php"],
    ["label" => "Events"]
];

require_once __DIR__ . "/../../includes/header.php";

?>

<main class="container">

    <div class="page-header">
        <div>
            <h1 class="page-header-title">Campus Events & Workshops</h1>
            <p class="page-header-subtitle">Discover tech summits, coding workshops, and guest lectures hosted across campus.</p>
        </div>
        <div class="page-header-actions">
            <a class="btn btn-glow" href="#new-event">+ Host an Event</a>
            <a class="btn btn-secondary" href="../gamification/badges.php">My Badges</a>
        </div>
    </div>

    <?php if ($message !== ""): ?>
        <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'error' ?>" role="alert" style="margin-bottom: 24px;">
            <span class="alert-icon" aria-hidden="true"><?= $messageType === 'success' ? '✓' : '✕' ?></span>
            <div class="alert-content"><?= htmlspecialchars($message) ?></div>
        </div>
    <?php endif; ?>

    <!-- Filter Card -->
    <div class="card card-interactive" style="margin-bottom: 28px; padding: 18px 24px; background: white;">
        <form method="GET" action="index.php" style="margin: 0; display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 250px;">
                <input
                    type="text"
                    name="search"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Search events by title, topic, or campus location..."
                    style="margin: 0;"
                >
            </div>
            <button type="submit" class="btn btn-glow" style="padding: 10px 18px;">
                Filter Events
            </button>
            <?php if ($search !== ""): ?>
                <a class="btn btn-secondary" href="index.php" style="padding: 10px 14px;">
                    Reset
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1.8fr; gap: 28px; align-items: start;">

        <!-- Left Column: Create Event -->
        <div id="new-event" class="card card-interactive card-peach" style="padding: 28px;">
            <h2 class="font-display" style="margin-top: 0; font-size: 1.35rem; color: var(--text-primary); margin-bottom: 16px;">
                ✦ Host an Event
            </h2>

            <form method="POST" action="index.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">

                <div style="margin-bottom: 16px;">
                    <label for="title" style="display: block; font-weight: 600; margin-bottom: 6px;">Event Title</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        maxlength="150"
                        placeholder="e.g. Intro to Cloud Architecture"
                        required
                    >
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="event_date" style="display: block; font-weight: 600; margin-bottom: 6px;">Date & Time</label>
                    <input
                        type="datetime-local"
                        id="event_date"
                        name="event_date"
                        required
                    >
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="location" style="display: block; font-weight: 600; margin-bottom: 6px;">Location / Room / Link</label>
                    <input
                        type="text"
                        id="location"
                        name="location"
                        maxlength="200"
                        placeholder="e.g. Auditorium Hall 2 / Google Meet"
                        required
                    >
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="max_participants" style="display: block; font-weight: 600; margin-bottom: 6px;">Available Seats</label>
                    <input
                        type="number"
                        id="max_participants"
                        name="max_participants"
                        min="1"
                        max="500"
                        value="30"
                        required
                    >
                </div>

                <div style="margin-bottom: 22px;">
                    <label for="description" style="display: block; font-weight: 600; margin-bottom: 6px;">Description & Prerequisites</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        placeholder="Topics covered, speaker bio, laptop requirements..."
                        required
                    ></textarea>
                </div>

                <button type="submit" class="btn btn-glow" style="width: 100%;">
                    Publish Event
                </button>
            </form>
        </div>

        <!-- Right Column: Event Listings -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
                <h2 class="font-display" style="margin: 0; font-size: 1.35rem; color: var(--text-primary);">
                    Upcoming Campus Schedule (<?= count($events) ?>)
                </h2>
                <span class="status status-peach">Verified Events</span>
            </div>

            <?php if (empty($events)): ?>
                <div class="card card-interactive" style="text-align: center; padding: 48px 20px; color: var(--muted); background: white;">
                    <div style="font-size: 2.5rem; margin-bottom: 10px;">📅</div>
                    <h3 style="font-family: var(--font-display); color: var(--text-primary); margin: 0 0 6px;">No Upcoming Events Found</h3>
                    <p style="margin: 0;">Be the first student or club lead to publish an event using the form on the left.</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 18px;">
                    <?php foreach ($events as $event): ?>
                        <?php
                        $remaining = (int) $event["max_participants"] - (int) $event["registered_count"];
                        $isFull = ($remaining <= 0);
                        $isRegistered = ((int) $event["is_registered"] === 1);
                        $isOwner = ((int) $event["creator_id"] === $userId);
                        ?>
                        <article class="card card-interactive" style="background: white;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; margin-bottom: 10px;">
                                <h3 style="margin: 0; font-family: var(--font-display); font-size: 1.25rem; color: var(--text-primary);">
                                    <?= htmlspecialchars($event["title"]) ?>
                                </h3>

                                <span class="status <?= $isRegistered ? 'status-success' : ($isFull ? 'status-peach' : 'status-warning') ?>" style="white-space: nowrap; font-weight: 700;">
                                    <?= $isRegistered ? '✓ Registered' : ($isFull ? 'Full Capacity' : ($remaining . ' seats left')) ?>
                                </span>
                            </div>

                            <p style="color: var(--text-secondary); line-height: 1.6; font-size: 0.95rem; margin-bottom: 14px;">
                                <?= nl2br(htmlspecialchars($event["description"])) ?>
                            </p>

                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 8px; font-size: 0.88rem; color: var(--muted); background: var(--surface-muted); padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 16px;">
                                <div>📅 <strong>Date:</strong> <?= date("F j, Y · g:i A", strtotime($event["event_date"])) ?></div>
                                <div>📍 <strong>Location:</strong> <?= htmlspecialchars($event["location"]) ?></div>
                                <div>👤 <strong>Host:</strong> <?= htmlspecialchars($event["creator"]) ?></div>
                                <div>👥 <strong>Seats:</strong> <?= (int) $event["registered_count"] ?> / <?= (int) $event["max_participants"] ?> enrolled</div>
                            </div>

                            <div style="display: flex; justify-content: flex-end; align-items: center;">
                                <?php if ($isOwner): ?>
                                    <span class="status" style="background: var(--surface-muted); color: var(--muted);">
                                        Your Hosted Event
                                    </span>
                                <?php elseif ($isRegistered): ?>
                                    <span class="status status-success" style="font-weight: 700;">
                                        ✓ Seat Confirmed
                                    </span>
                                <?php elseif (!$isFull): ?>
                                    <form method="POST" action="register.php" style="margin: 0;">
                                        <input type="hidden" name="event_id" value="<?= (int) $event["id"] ?>">
                                        <button type="submit" class="btn btn-sm btn-glow">
                                            Register for Seat (+20 pts)
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="status" style="background: #fef2f2; color: #991b1b; font-weight: 600;">
                                        Registration Closed (Full)
                                    </span>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

</main>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>