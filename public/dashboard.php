<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../modules/auth/login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| Fetch student activity statistics
|--------------------------------------------------------------------------
*/

$sql = "SELECT
    (SELECT COUNT(*)
     FROM projects
     WHERE creator_id = ?) AS projects_created,

    (SELECT COUNT(*)
     FROM project_members
     WHERE user_id = ?
       AND status = 'accepted') AS projects_joined,

    (SELECT COUNT(*)
     FROM user_skills
     WHERE user_id = ?) AS skills_count,

    (SELECT COUNT(*)
     FROM user_interests
     WHERE user_id = ?) AS interests_count,

    (SELECT COUNT(*)
     FROM ideas
     WHERE user_id = ?
       AND status != 'archived') AS ideas_count,

    (SELECT COUNT(*)
     FROM event_registrations
     WHERE user_id = ?
       AND status = 'registered') AS events_registered,

    (SELECT COALESCE(points, 0)
     FROM user_points
     WHERE user_id = ?) AS total_points,

    (SELECT COUNT(*)
     FROM user_badges
     WHERE user_id = ?) AS badges_earned";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "iiiiiiii",
    $userId,
    $userId,
    $userId,
    $userId,
    $userId,
    $userId,
    $userId,
    $userId
);

$stmt->execute();

$result = $stmt->get_result();
$stats = $result->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| Handle students who have no points record yet
|--------------------------------------------------------------------------
*/

$stats["total_points"] = $stats["total_points"] ?? 0;

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Campus Nexus Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">

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


<h2>Your Campus Activity</h2>

<table border="1" cellpadding="12">
    <tr>
        <th>Activity</th>
        <th>Total</th>
    </tr>

    <tr>
        <td>Projects Created</td>
        <td><?= (int) $stats["projects_created"] ?></td>
    </tr>

    <tr>
        <td>Projects Joined</td>
        <td><?= (int) $stats["projects_joined"] ?></td>
    </tr>

    <tr>
        <td>Skills</td>
        <td><?= (int) $stats["skills_count"] ?></td>
    </tr>

    <tr>
        <td>Interests</td>
        <td><?= (int) $stats["interests_count"] ?></td>
    </tr>

    <tr>
        <td>Ideas Posted</td>
        <td><?= (int) $stats["ideas_count"] ?></td>
    </tr>

    <tr>
        <td>Events Registered</td>
        <td><?= (int) $stats["events_registered"] ?></td>
    </tr>

    <tr>
        <td>Total Points</td>
        <td><?= (int) $stats["total_points"] ?></td>
    </tr>

    <tr>
        <td>Badges Earned</td>
        <td><?= (int) $stats["badges_earned"] ?></td>
    </tr>
</table>

<hr>


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
<li>
    <a href="../modules/events/index.php">
        Events
    </a>
</li>
<li>
    <a href="../modules/gamification/badges.php">
        My Badges
    </a>
</li>

</body>

</html>

    <a href="../modules/auth/logout.php">
        Logout
    </a>

</body>

<body>

    <header class="topbar">
        <div class="topbar-inner">
            <a class="brand" href="dashboard.php">
                Campus Nexus
            </a>

            <nav class="nav-links">
                <a href="../modules/students/profile.php">Profile</a>
                <a href="../modules/gamification/leaderboard.php">Leaderboard</a>
                <a href="../modules/gamification/badges.php">My Badges</a>
                <a href="../modules/auth/logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container">

        <h1>
            Welcome,
            <?= htmlspecialchars($_SESSION["user_name"]) ?>!
        </h1>

        <p>
            Your campus activities, collaborations, and achievements
            in one place.
        </p>

        <h2>Your Activity</h2>

        <div class="stats-grid">

            <div class="stat-card">
                <span class="stat-label">Projects Created</span>
                <span class="stat-value">
                    <?= (int) $stats["projects_created"] ?>
                </span>
            </div>

            <div class="stat-card">
                <span class="stat-label">Projects Joined</span>
                <span class="stat-value">
                    <?= (int) $stats["projects_joined"] ?>
                </span>
            </div>

            <div class="stat-card">
                <span class="stat-label">Skills</span>
                <span class="stat-value">
                    <?= (int) $stats["skills_count"] ?>
                </span>
            </div>

            <div class="stat-card">
                <span class="stat-label">Interests</span>
                <span class="stat-value">
                    <?= (int) $stats["interests_count"] ?>
                </span>
            </div>

            <div class="stat-card">
                <span class="stat-label">Ideas Posted</span>
                <span class="stat-value">
                    <?= (int) $stats["ideas_count"] ?>
                </span>
            </div>

            <div class="stat-card">
                <span class="stat-label">Events Registered</span>
                <span class="stat-value">
                    <?= (int) $stats["events_registered"] ?>
                </span>
            </div>

            <div class="stat-card">
                <span class="stat-label">Total Points</span>
                <span class="stat-value">
                    <?= (int) $stats["total_points"] ?>
                </span>
            </div>

            <div class="stat-card">
                <span class="stat-label">Badges Earned</span>
                <span class="stat-value">
                    <?= (int) $stats["badges_earned"] ?>
                </span>
            </div>

        </div>

        <h2>Explore Campus Nexus</h2>

        <div class="card-grid">

            <article class="card">
                <h3>My Profile</h3>
                <p>Manage your student information.</p>
                <a class="btn" href="../modules/students/profile.php">
                    View Profile
                </a>
            </article>

            <article class="card">
                <h3>Skills & Interests</h3>
                <p>Show others what you know and what you enjoy.</p>
                <a class="btn" href="../modules/students/skills.php">
                    My Skills
                </a>
                <a href="../modules/students/interests.php">
                    My Interests
                </a>
            </article>

            <article class="card">
                <h3>Project Team Finder</h3>
                <p>Discover projects that match your skills.</p>
                <a class="btn" href="../modules/projects/team-finder.php">
                    Find Projects
                </a>
            </article>

            <article class="card">
                <h3>My Projects</h3>
                <p>Review your projects and memberships.</p>
                <a class="btn" href="../modules/projects/my-projects.php">
                    View Projects
                </a>
                <a href="../modules/projects/requests.php">
                    Manage Requests
                </a>
            </article>

            <article class="card">
                <h3>Skill Exchange</h3>
                <p>Share skills and learn from fellow students.</p>
                <a class="btn" href="../modules/skills/exchange.php">
                    Explore Skills
                </a>
                <a href="../modules/skills/requests.php">
                    Exchange Requests
                </a>
            </article>

            <article class="card">
                <h3>Idea Board</h3>
                <p>Share ideas and vote for student proposals.</p>
                <a class="btn" href="../modules/ideas/index.php">
                    Explore Ideas
                </a>
            </article>

            <article class="card">
                <h3>Campus Events</h3>
                <p>Discover events and register to participate.</p>
                <a class="btn" href="../modules/events/index.php">
                    View Events
                </a>
            </article>

            <article class="card">
                <h3>Achievements</h3>
                <p>Track your points and earned badges.</p>
                <a class="btn" href="../modules/gamification/leaderboard.php">
                    Leaderboard
                </a>
                <a href="../modules/gamification/badges.php">
                    My Badges
                </a>
            </article>

        </div>

    </main>

</body>
</html>


</html>