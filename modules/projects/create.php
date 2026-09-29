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
| Create project
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $selectedSkills = $_POST["skills"] ?? [];

    if ($title === "" || $description === "") {

        $message = "Title and description are required.";

    } elseif (empty($selectedSkills)) {

        $message = "Please select at least one required skill.";

    } else {

        $conn->begin_transaction();

        try {

            /*
            | Insert project
            */

            $sql = "INSERT INTO projects
                        (creator_id, title, description)
                    VALUES (?, ?, ?)";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "iss",
                $userId,
                $title,
                $description
            );

            $stmt->execute();

            $projectId = $conn->insert_id;

            $stmt->close();


            /*
            | Insert required skills
            */

            $sql = "INSERT INTO project_required_skills
                        (project_id, skill_id, importance)
                    VALUES (?, ?, 'required')";

            $stmt = $conn->prepare($sql);

            foreach ($selectedSkills as $skillId) {

                $skillId = (int) $skillId;

                if ($skillId > 0) {

                    $stmt->bind_param(
                        "ii",
                        $projectId,
                        $skillId
                    );

                    $stmt->execute();
                }
            }

            $stmt->close();

            $conn->commit();

            header("Location: team-finder.php");
            exit;

        } catch (Exception $e) {

            $conn->rollback();

            $message = "Failed to create project.";
        }
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

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Create Project - Campus Nexus</title>

</head>

<body>

    <h1>Create a Project</h1>

    <?php if ($message !== ""): ?>

        <p>
            <?= htmlspecialchars($message) ?>
        </p>

    <?php endif; ?>


    <form method="POST">

        <label>
            Project Title
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
            rows="6"
            cols="50"
            required
        ></textarea>

        <br><br>


        <label>
            Required Skills
        </label>

        <br><br>

        <?php foreach ($skills as $skill): ?>

            <label>

                <input
                    type="checkbox"
                    name="skills[]"
                    value="<?= $skill["id"] ?>"
                >

                <?= htmlspecialchars($skill["name"]) ?>

                -
                <?= htmlspecialchars(
                    $skill["category"] ?? "Other"
                ) ?>

            </label>

            <br>

        <?php endforeach; ?>


        <br>

        <button type="submit">
            Create Project
        </button>

    </form>


    <br>

    <a href="team-finder.php">
        Back to Team Finder
    </a>

    <br><br> 
    <p>
    <a href="create.php">
        + Create New Project
    </a>
</p>

    <a href="../../public/dashboard.php">
        Back to Dashboard
    </a>

</body>

</html>