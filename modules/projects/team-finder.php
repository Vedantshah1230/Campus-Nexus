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
| Get open projects
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            projects.id,
            projects.title,
            projects.description,
            projects.created_at,
            users.name AS creator
        FROM projects
        JOIN users
            ON projects.creator_id = users.id
        WHERE projects.status = 'open'
        ORDER BY projects.created_at DESC";

$result = $conn->query($sql);

$projects = [];

while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}

/*
|--------------------------------------------------------------------------
| Calculate compatibility
|--------------------------------------------------------------------------
*/

foreach ($projects as &$project) {

    /*
    | Count required skills
    */

    $sql = "SELECT COUNT(*) AS total_required
            FROM project_required_skills
            WHERE project_id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "i",
        $project["id"]
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $requiredData = $result->fetch_assoc();

    $totalRequired = (int) $requiredData["total_required"];

    $stmt->close();


    /*
    | Count matching skills
    */

    $sql = "SELECT COUNT(*) AS matched_skills
            FROM project_required_skills
            JOIN user_skills
                ON project_required_skills.skill_id =
                   user_skills.skill_id
            WHERE project_required_skills.project_id = ?
              AND user_skills.user_id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ii",
        $project["id"],
        $userId
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $matchedData = $result->fetch_assoc();

    $matchedSkills = (int) $matchedData["matched_skills"];

    $stmt->close();


    /*
    | Calculate percentage
    */

    if ($totalRequired > 0) {

        $project["compatibility"] =
            round(
                ($matchedSkills / $totalRequired) * 100,
                2
            );

    } else {

        $project["compatibility"] = 0;
    }

    $project["total_required"] = $totalRequired;
    $project["matched_skills"] = $matchedSkills;
}

unset($project);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Project Team Finder - Campus Nexus</title>

</head>

<body>

    <h1>Project Team Finder</h1>

    <p>
        Find projects that match your skills.
    </p>

    <hr>

    <?php if (empty($projects)): ?>

        <p>
            No open projects available.
        </p>

    <?php else: ?>

        <?php foreach ($projects as $project): ?>

            <div>

                <h2>
                    <?= htmlspecialchars($project["title"]) ?>
                </h2>

                <p>
                    <?= htmlspecialchars($project["description"]) ?>
                </p>

                <p>
                    <strong>Created by:</strong>
                    <?= htmlspecialchars($project["creator"]) ?>
                </p>

                <p>
                    <strong>Skill Compatibility:</strong>
                    <?= $project["compatibility"] ?>%
                </p>

                <p>
                    Matching skills:
                    <?= $project["matched_skills"] ?>
                    /
                    <?= $project["total_required"] ?>
                </p>
<?php if ($project["creator"] !== $_SESSION["user_name"]): ?>

    <form method="POST" action="join.php">

        <input
            type="hidden"
            name="project_id"
            value="<?= $project["id"] ?>"
        >

        <button type="submit">
            Request to Join
        </button>

    </form>

<?php endif; ?>
                <hr>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

    <a href="../../public/dashboard.php">
        Back to Dashboard
    </a>

</body>

</html>