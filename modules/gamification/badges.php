```php
<?php

session_start();

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| Fetch all badges and whether the current student earned each one
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            badges.id,
            badges.name,
            badges.description,
            badges.points,
            user_badges.earned_at
        FROM badges
        LEFT JOIN user_badges
            ON badges.id = user_badges.badge_id
            AND user_badges.user_id = ?
        ORDER BY badges.points DESC, badges.name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

$badges = [];

while ($badge = $result->fetch_assoc()) {
    $badges[] = $badge;
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Count earned badges
|--------------------------------------------------------------------------
*/

$earnedCount = 0;

foreach ($badges as $badge) {
    if ($badge["earned_at"] !== null) {
        $earnedCount++;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Campus Nexus - My Badges</title>
</head>

<body>

    <h1>My Badge Collection</h1>

    <p>
        Badges earned:
        <?= $earnedCount ?> / <?= count($badges) ?>
    </p>

    <hr>

    <?php foreach ($badges as $badge): ?>

        <section>

            <h2>
                <?= htmlspecialchars($badge["name"]) ?>
            </h2>

            <p>
                <?= htmlspecialchars(
                    $badge["description"] ?? ""
                ) ?>
            </p>

            <p>
                Points:
                <?= (int) $badge["points"] ?>
            </p>

            <?php if ($badge["earned_at"] !== null): ?>

                <p>
                    Status: Earned
                </p>

                <p>
                    Earned on:
                    <?= htmlspecialchars($badge["earned_at"]) ?>
                </p>

            <?php else: ?>

                <p>
                    Status: Not earned yet
                </p>

            <?php endif; ?>

            <hr>

        </section>

    <?php endforeach; ?>

    <a href="../../public/dashboard.php">
        Back to Dashboard
    </a>

</body>

</html>
```