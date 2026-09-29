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
| Create skill exchange
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $skillId = (int) ($_POST["skill_id"] ?? 0);
    $description = trim($_POST["description"] ?? "");

    if ($skillId <= 0 || $description === "") {

        $message = "Please select a skill and enter a description.";

    } else {

        $sql = "INSERT INTO skill_exchanges
                    (user_id, skill_id, description)
                VALUES (?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "iis",
            $userId,
            $skillId,
            $description
        );

        if ($stmt->execute()) {

            $message = "Skill exchange created successfully.";

        } else {

            $message = "Failed to create skill exchange.";
        }

        $stmt->close();
    }
}

/*
|--------------------------------------------------------------------------
| Get skills
|--------------------------------------------------------------------------
*/

$sql = "SELECT id, name, category
        FROM skills
        ORDER BY name ASC";

$result = $conn->query($sql);

$skills = [];

while ($row = $result->fetch_assoc()) {
    $skills[] = $row;
}

/*
|--------------------------------------------------------------------------
| Get open skill exchanges
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            skill_exchanges.id,
            skill_exchanges.description,
            skill_exchanges.created_at,
            skills.name AS skill_name,
            skills.category,
            users.name AS owner_name
        FROM skill_exchanges
        JOIN skills
            ON skill_exchanges.skill_id = skills.id
        JOIN users
            ON skill_exchanges.user_id = users.id
        WHERE skill_exchanges.status = 'open'
        ORDER BY skill_exchanges.created_at DESC";

$result = $conn->query($sql);

$exchanges = [];

while ($row = $result->fetch_assoc()) {
    $exchanges[] = $row;
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

    <title>Skill Exchange - Campus Nexus</title>

</head>

<body>

    <h1>Skill Exchange</h1>

    <p>
        Share what you know and help other students learn.
    </p>

    <?php if ($message !== ""): ?>

        <p>
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>


    <hr>


    <h2>Offer a Skill</h2>

    <form method="POST">

        <label>
            Skill
        </label>

        <br>

        <select name="skill_id" required>

            <option value="">
                Select a skill
            </option>

            <?php foreach ($skills as $skill): ?>

                <option value="<?= $skill["id"] ?>">

                    <?= htmlspecialchars($skill["name"]) ?>

                    -
                    <?= htmlspecialchars(
                        $skill["category"] ?? "Other"
                    ) ?>

                </option>

            <?php endforeach; ?>

        </select>

        <br><br>


        <label>
            Description
        </label>

        <br>

        <textarea
            name="description"
            rows="5"
            cols="50"
            placeholder="Describe what you can teach..."
            required
        ></textarea>

        <br><br>

        <button type="submit">
            Offer Skill
        </button>

    </form>


    <hr>


    <h2>Available Skill Exchanges</h2>

    <?php if (empty($exchanges)): ?>

        <p>
            No skill exchanges are currently available.
        </p>

    <?php else: ?>

        <?php foreach ($exchanges as $exchange): ?>

            <div>

                <h3>
                    <?= htmlspecialchars(
                        $exchange["skill_name"]
                    ) ?>
                </h3>

                <p>
                    <?= htmlspecialchars(
                        $exchange["description"]
                    ) ?>
                </p>

                <p>

                    <strong>Offered by:</strong>

                    <?= htmlspecialchars(
                        $exchange["owner_name"]
                    ) ?>

                </p>

                <?php if (
                    $exchange["owner_name"]
                    !== $_SESSION["user_name"]
                ): ?>

                    <form
                        method="POST"
                        action="request.php"
                    >

                        <input
                            type="hidden"
                            name="exchange_id"
                            value="<?= $exchange["id"] ?>"
                        >

                        <button type="submit">
                            Request Help
                        </button>

                    </form>

                <?php endif; ?>

            </div>

            <hr>

        <?php endforeach; ?>

    <?php endif; ?>


    <a href="../../public/dashboard.php">
        Back to Dashboard
    </a>

</body>

</html>