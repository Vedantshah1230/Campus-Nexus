<?php

session_start();

require_once __DIR__ . "/../config/database.php";

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

$stats["total_points"] = $stats["total_points"] ?? 0;

// Fetch student institutional metadata
$profileSql = "SELECT sp.department, sp.semester, sp.roll_number, u.last_login_at 
               FROM users u 
               LEFT JOIN student_profiles sp ON u.id = sp.user_id 
               WHERE u.id = ? LIMIT 1";
$profStmt = $conn->prepare($profileSql);
$profStmt->bind_param("i", $userId);
$profStmt->execute();
$studentMeta = $profStmt->get_result()->fetch_assoc();
$profStmt->close();

$pageTitle = "Dashboard";
$pageDescription = "Monitor your campus collaboration metrics, active teams, skills, and achievements.";
$breadcrumbs = [
    ["label" => "Dashboard"]
];

require_once __DIR__ . "/../includes/header.php";

?>

<main class="container">

    <div class="page-header">
        <div>
            <h1 class="page-header-title">
                Welcome, <?= htmlspecialchars($_SESSION["user_name"] ?? "Student") ?>!
            </h1>
            <p class="page-header-subtitle" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <?php if (!empty($studentMeta["department"])): ?>
                    <span class="status status-peach" style="font-size: 0.76rem; padding: 3px 10px;"><?= htmlspecialchars($studentMeta["department"]) ?></span>
                <?php endif; ?>
                <?php if (!empty($studentMeta["roll_number"])): ?>
                    <span class="status" style="background: rgba(99, 102, 241, 0.1); color: var(--primary); font-size: 0.76rem; padding: 3px 10px; font-weight: 700;">ID: <?= htmlspecialchars($studentMeta["roll_number"]) ?></span>
                <?php endif; ?>
                <span>Your campus activities, collaborations, and achievements in one place.</span>
            </p>
        </div>
        <div class="page-header-actions">
            <a class="btn btn-glow" href="<?= htmlspecialchars($baseUrl) ?>/modules/projects/create.php">
                + New Project
            </a>
            <a class="btn btn-peach" href="<?= htmlspecialchars($baseUrl) ?>/modules/ideas/index.php">
                Share Idea
            </a>
        </div>
    </div>

    <h2 class="font-display" style="font-size: 1.45rem; margin-bottom: 16px;">Your Activity Snapshot</h2>

    <div class="stats-grid">

        <div class="stat-card">
            <span class="stat-label">Projects Created</span>
            <span class="stat-value">
                <?= (int) ($stats["projects_created"] ?? 0) ?>
            </span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Projects Joined</span>
            <span class="stat-value">
                <?= (int) ($stats["projects_joined"] ?? 0) ?>
            </span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Skills Listed</span>
            <span class="stat-value">
                <?= (int) ($stats["skills_count"] ?? 0) ?>
            </span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Interests</span>
            <span class="stat-value">
                <?= (int) ($stats["interests_count"] ?? 0) ?>
            </span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Ideas Posted</span>
            <span class="stat-value">
                <?= (int) ($stats["ideas_count"] ?? 0) ?>
            </span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Events Registered</span>
            <span class="stat-value">
                <?= (int) ($stats["events_registered"] ?? 0) ?>
            </span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Total Points</span>
            <span class="stat-value text-peach">
                <?= (int) ($stats["total_points"] ?? 0) ?>
            </span>
        </div>

        <div class="stat-card">
            <span class="stat-label">Badges Earned</span>
            <span class="stat-value">
                <?= (int) ($stats["badges_earned"] ?? 0) ?>
            </span>
        </div>

    </div>

    <h2 class="font-display" style="font-size: 1.45rem; margin-top: 36px; margin-bottom: 16px;">
        Explore Campus Nexus
    </h2>

    <div class="card-grid">

        <article class="card card-interactive">
            <h3 style="margin-top: 0; font-family: var(--font-display);">My Profile</h3>
            <p style="color: var(--muted); margin-bottom: 20px;">Manage your student information, major, and personal bio.</p>
            <a class="btn btn-secondary" href="<?= htmlspecialchars($baseUrl) ?>/modules/students/profile.php">
                View Profile
            </a>
        </article>

        <article class="card card-interactive">
            <h3 style="margin-top: 0; font-family: var(--font-display);">Skills & Interests</h3>
            <p style="color: var(--muted); margin-bottom: 20px;">Show others what you excel at and what you want to learn next.</p>
            <a class="btn btn-secondary" href="<?= htmlspecialchars($baseUrl) ?>/modules/students/skills.php">
                My Skills
            </a>
            &nbsp;
            <a class="btn btn-secondary" href="<?= htmlspecialchars($baseUrl) ?>/modules/students/interests.php">
                My Interests
            </a>
        </article>

        <article class="card card-interactive">
            <h3 style="margin-top: 0; font-family: var(--font-display);">Project Team Finder</h3>
            <p style="color: var(--muted); margin-bottom: 20px;">Discover student projects and see compatibility match scores.</p>
            <a class="btn btn-glow" href="<?= htmlspecialchars($baseUrl) ?>/modules/projects/team-finder.php">
                Find Projects
            </a>
            &nbsp;
            <a class="btn btn-secondary" href="<?= htmlspecialchars($baseUrl) ?>/modules/projects/create.php">
                + Create
            </a>
        </article>

        <article class="card card-interactive">
            <h3 style="margin-top: 0; font-family: var(--font-display);">My Projects</h3>
            <p style="color: var(--muted); margin-bottom: 20px;">Review your created projects, active memberships, and team requests.</p>
            <a class="btn btn-secondary" href="<?= htmlspecialchars($baseUrl) ?>/modules/projects/my-projects.php">
                View Projects
            </a>
            &nbsp;
            <a class="btn btn-secondary" href="<?= htmlspecialchars($baseUrl) ?>/modules/projects/requests.php">
                Requests
            </a>
        </article>

        <article class="card card-interactive">
            <h3 style="margin-top: 0; font-family: var(--font-display);">Skill Exchange</h3>
            <p style="color: var(--muted); margin-bottom: 20px;">Connect with fellow students for peer-to-peer knowledge sharing.</p>
            <a class="btn btn-secondary" href="<?= htmlspecialchars($baseUrl) ?>/modules/skills/exchange.php">
                Explore Skills
            </a>
            &nbsp;
            <a class="btn btn-secondary" href="<?= htmlspecialchars($baseUrl) ?>/modules/skills/requests.php">
                Requests
            </a>
        </article>

        <article class="card card-interactive">
            <h3 style="margin-top: 0; font-family: var(--font-display);">Idea Board</h3>
            <p style="color: var(--muted); margin-bottom: 20px;">Post project ideas, discuss proposals, and upvote student initiatives.</p>
            <a class="btn btn-secondary" href="<?= htmlspecialchars($baseUrl) ?>/modules/ideas/index.php">
                Explore Ideas
            </a>
        </article>

        <article class="card card-interactive">
            <h3 style="margin-top: 0; font-family: var(--font-display);">Campus Events</h3>
            <p style="color: var(--muted); margin-bottom: 20px;">Register for upcoming workshops, hackathons, and guest lectures.</p>
            <a class="btn btn-secondary" href="<?= htmlspecialchars($baseUrl) ?>/modules/events/index.php">
                View Events
            </a>
        </article>

        <article class="card card-interactive">
            <h3 style="margin-top: 0; font-family: var(--font-display);">Achievements</h3>
            <p style="color: var(--muted); margin-bottom: 20px;">Track your points and badges earned through community collaboration.</p>
            <a class="btn btn-secondary" href="<?= htmlspecialchars($baseUrl) ?>/modules/gamification/leaderboard.php">
                Leaderboard
            </a>
            &nbsp;
            <a class="btn btn-secondary" href="<?= htmlspecialchars($baseUrl) ?>/modules/gamification/badges.php">
                My Badges
            </a>
        </article>

    </div>

</main>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>