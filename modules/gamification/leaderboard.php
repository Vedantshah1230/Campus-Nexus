<?php

session_start();

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get current user's points
|--------------------------------------------------------------------------
*/

$userId = $_SESSION["user_id"];

$sql = "SELECT
            users.name,
            COALESCE(user_points.points, 0) AS points
        FROM users
        LEFT JOIN user_points
            ON users.id = user_points.user_id
        WHERE users.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$currentUser = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| Get leaderboard
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            users.id,
            users.name,
            COALESCE(user_points.points, 0) AS points
        FROM users
        LEFT JOIN user_points
            ON users.id = user_points.user_id
        WHERE users.role = 'student'
        ORDER BY points DESC, users.name ASC";

$result = $conn->query($sql);

if (!$result) {
    die("Leaderboard query failed: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Campus Nexus - Leaderboard</title>

</head>

<body>

    <h1>Campus Nexus Leaderboard</h1>

    <h2>
        My Points:
        <?= htmlspecialchars($currentUser["points"]) ?>
    </h2>

    <hr>

    <h2>Student Rankings</h2>

    <table border="1" cellpadding="10">

        <tr>
            <th>Rank</th>
            <th>Student</th>
            <th>Points</th>
        </tr>

        <?php

        $rank = 1;

        while ($student = $result->fetch_assoc()):

        ?>

            <tr>

                <td>
                    <?= $rank ?>
                </td>

                <td>
                    <?= htmlspecialchars($student["name"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($student["points"]) ?>
                </td>

            </tr>

        <?php

            $rank++;

        endwhile;

        ?>

    </table>

    <br>

    <a href="../../public/dashboard.php">
        Back to Dashboard
    </a>

</body>

</html>
