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
| Add skill
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $skillId = (int) $_POST["skill_id"];
    $proficiency = $_POST["proficiency"];

    $allowedProficiency = [
        "beginner",
        "intermediate",
        "advanced",
        "expert"
    ];

    if (
        $skillId <= 0 ||
        !in_array($proficiency, $allowedProficiency, true)
    ) {

        $message = "Invalid skill or proficiency.";

    } else {

        $sql = "INSERT INTO user_skills
                    (user_id, skill_id, proficiency)
                VALUES (?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "iis",
            $userId,
            $skillId,
            $proficiency
        );

        if ($stmt->execute()) {

            $message = "Skill added successfully.";

        } elseif ($conn->errno === 1062) {

            $message = "You have already added this skill.";

        } else {

            $message = "Failed to add skill.";
        }

        $stmt->close();
    }
}

/*
|--------------------------------------------------------------------------
| Get available skills
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
| Get student's skills
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            skills.id,
            skills.name,
            skills.category,
            user_skills.proficiency
        FROM user_skills
        JOIN skills
            ON user_skills.skill_id = skills.id
        WHERE user_skills.user_id = ?
        ORDER BY skills.name ASC";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $userId);

$stmt->execute();

$result = $stmt->get_result();

$userSkills = [];

while ($row = $result->fetch_assoc()) {
    $userSkills[] = $row;
}

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Skills - Campus Nexus</title>

</head>

<body>

    <h1>My Skills</h1>

    <?php if ($message !== ""): ?>

        <p>
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>


    <h2>Add a Skill</h2>

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
                    <?= htmlspecialchars($skill["category"] ?? "Other") ?>

                </option>

            <?php endforeach; ?>

        </select>

        <br><br>


        <label>
            Proficiency
        </label>

        <br>

        <select name="proficiency" required>

            <option value="beginner">
                Beginner
            </option>

            <option value="intermediate">
                Intermediate
            </option>

            <option value="advanced">
                Advanced
            </option>

            <option value="expert">
                Expert
            </option>

        </select>

        <br><br>

        <button type="submit">
            Add Skill
        </button>

    </form>


    <hr>


    <h2>My Current Skills</h2>

    <?php if (count($userSkills) === 0): ?>

        <p>
            You haven't added any skills yet.
        </p>

    <?php else: ?>

        <table border="1" cellpadding="8">

            <tr>
                <th>Skill</th>
                <th>Category</th>
                <th>Proficiency</th>
            </tr>

            <?php foreach ($userSkills as $skill): ?>

                <tr>

                    <td>
                        <?= htmlspecialchars($skill["name"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($skill["category"] ?? "Other") ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($skill["proficiency"]) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

    <?php endif; ?>


    <br>

    <a href="../../public/dashboard.php">
        Back to Dashboard
    </a>

</body>

</html>