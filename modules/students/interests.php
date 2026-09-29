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
| Save interests
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $selectedInterests = $_POST["interests"] ?? [];

    if (empty($selectedInterests)) {

        $message = "Please select at least one interest.";

    } else {

        $conn->begin_transaction();

        try {

            /*
            | Remove old interests
            */

            $sql = "DELETE FROM user_interests
                    WHERE user_id = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();

            /*
            | Add selected interests
            */

            $sql = "INSERT INTO user_interests
                        (user_id, interest_id)
                    VALUES (?, ?)";

            $stmt = $conn->prepare($sql);

            foreach ($selectedInterests as $interestId) {

                $interestId = (int) $interestId;

                if ($interestId > 0) {

                    $stmt->bind_param(
                        "ii",
                        $userId,
                        $interestId
                    );

                    $stmt->execute();
                }
            }

            $stmt->close();

            $conn->commit();

            $message = "Interests updated successfully.";

        } catch (Exception $e) {

            $conn->rollback();

            $message = "Failed to update interests.";
        }
    }
}

/*
|--------------------------------------------------------------------------
| Get all available interests
|--------------------------------------------------------------------------
*/

$sql = "SELECT id, name
        FROM interests
        ORDER BY name ASC";

$result = $conn->query($sql);

$interests = [];

while ($row = $result->fetch_assoc()) {
    $interests[] = $row;
}

/*
|--------------------------------------------------------------------------
| Get student's selected interests
|--------------------------------------------------------------------------
*/

$sql = "SELECT interest_id
        FROM user_interests
        WHERE user_id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $userId);

$stmt->execute();

$result = $stmt->get_result();

$userInterestIds = [];

while ($row = $result->fetch_assoc()) {
    $userInterestIds[] = (int) $row["interest_id"];
}

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Interests - Campus Nexus</title>
</head>

<body>

    <h1>My Interests</h1>

    <?php if ($message !== ""): ?>

        <p>
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>


    <h2>Select Your Interests</h2>

    <form method="POST">

        <?php foreach ($interests as $interest): ?>

            <label>

                <input
                    type="checkbox"
                    name="interests[]"
                    value="<?= $interest["id"] ?>"
                    <?= in_array(
                        $interest["id"],
                        $userInterestIds
                    ) ? "checked" : "" ?>
                >

                <?= htmlspecialchars($interest["name"]) ?>

            </label>

            <br>

        <?php endforeach; ?>

        <br>

        <button type="submit">
            Save Interests
        </button>

    </form>


    <hr>


    <h2>My Selected Interests</h2>

    <?php

    if (empty($userInterestIds)):

    ?>

        <p>
            You haven't selected any interests yet.
        </p>

    <?php else: ?>

        <ul>

            <?php foreach ($interests as $interest): ?>

                <?php if (
                    in_array(
                        $interest["id"],
                        $userInterestIds
                    )
                ): ?>

                    <li>
                        <?= htmlspecialchars($interest["name"]) ?>
                    </li>

                <?php endif; ?>

            <?php endforeach; ?>

        </ul>

    <?php endif; ?>


    <br>

    <a href="../../public/dashboard.php">
        Back to Dashboard
    </a>

</body>

</html>