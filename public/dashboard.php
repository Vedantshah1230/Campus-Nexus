<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../modules/auth/login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Campus Nexus Dashboard</title>

</head>

<body>

    <h1>Campus Nexus</h1>

    <h2>
        Welcome,
        <?= htmlspecialchars($_SESSION["user_name"]) ?>!
    </h2>

    <p>
        Email:
        <?= htmlspecialchars($_SESSION["user_email"]) ?>
    </p>

    <p>
        Role:
        <?= htmlspecialchars($_SESSION["user_role"]) ?>
    </p>

    <hr>

    <h3>Campus Nexus Modules</h3>

   <ul>
    <li>
        <a href="../modules/students/profile.php">
            My Profile
        </a>
    </li>

    <li>
        <a href="../modules/students/skills.php">
            My Skills
        </a>
    </li>

    <li>Skill Exchange</li>

    <li>Project Team Finder</li>

    <li>Idea Board</li>

    <li>Events</li>

    <li>Leaderboard</li>
</ul>
<ul>
    <li>
        <a href="../modules/students/profile.php">
            My Profile
        </a>
    </li>

    <li>
        <a href="../modules/students/skills.php">
            My Skills
        </a>
    </li>

    <li>
        <a href="../modules/students/interests.php">
            My Interests
        </a>
    </li>

    <li>Skill Exchange</li>
    <li>
    <a href="../modules/projects/team-finder.php">
        Project Team Finder
    </a>
</li>
    <li>Idea Board</li>
    <li>Events</li>
    <li>Leaderboard</li>
</ul>
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
| Get projects created by the user
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
            title,
            description,
            status,
            created_at
        FROM projects
        WHERE creator_id = ?
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $userId
);

$stmt->execute();

$result = $stmt->get_result();

$createdProjects = [];

while ($row = $result->fetch_assoc()) {
    $createdProjects[] = $row;
}

$stmt->close();


/*
|--------------------------------------------------------------------------
| Get projects joined/requested by the user
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            projects.id,
            projects.title,
            projects.description,
            project_members.role,
            project_members.status,
            project_members.joined_at
        FROM project_members
        JOIN projects
            ON project_members.project_id = projects.id
        WHERE project_members.user_id = ?
        ORDER BY project_members.joined_at DESC";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $userId
);

$stmt->execute();

$result = $stmt->get_result();

$joinedProjects = [];

while ($row = $result->fetch_assoc()) {
    $joinedProjects[] = $row;
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

    <title>My Projects - Campus Nexus</title>

</head>

<body>

    <h1>My Projects</h1>


    <h2>Projects I Created</h2>

    <?php if (empty($createdProjects)): ?>

        <p>
            You haven't created any projects yet.
        </p>

    <?php else: ?>

        <?php foreach ($createdProjects as $project): ?>

            <div>

                <h3>
                    <?= htmlspecialchars($project["title"]) ?>
                </h3>

                <p>
                    <?= htmlspecialchars($project["description"]) ?>
                </p>

                <p>
                    Status:
                    <?= htmlspecialchars($project["status"]) ?>
                </p>

            </div>

            <hr>

        <?php endforeach; ?>

    <?php endif; ?>


    <h2>Projects I Requested / Joined</h2>

    <?php if (empty($joinedProjects)): ?>

        <p>
            You haven't requested to join any projects.
        </p>

    <?php else: ?>

        <?php foreach ($joinedProjects as $project): ?>

            <div>

                <h3>
                    <?= htmlspecialchars($project["title"]) ?>
                </h3>

                <p>
                    <?= htmlspecialchars($project["description"]) ?>
                </p>

                <p>
                    Role:
                    <?= htmlspecialchars($project["role"]) ?>
                </p>

                <p>
                    Status:
                    <?= htmlspecialchars($project["status"]) ?>
                </p>

            </div>

            <hr>

        <?php endforeach; ?>

    <?php endif; ?>


    <a href="team-finder.php">
        Find More Projects
    </a>

    <br><br>

    <a href="../../public/dashboard.php">
        Back to Dashboard
    </a>
<li>
    <a href="../modules/skills/exchange.php">
        Skill Exchange
    </a>
</li>
<li>
    <a href="../modules/skills/requests.php">
        Skill Exchange Requests
    </a>
</li>

</body>

</html>

    <a href="../modules/auth/logout.php">
        Logout
    </a>

</body>

</html>