<?php

session_start();

require_once "../../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| Get current user's points
|--------------------------------------------------------------------------
*/
$sql = "SELECT
            users.id,
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
$currentUser = $result->fetch_assoc() ?: ["id" => $userId, "name" => "Student", "points" => 0];
$stmt->close();

/*
|--------------------------------------------------------------------------
| Get complete leaderboard (including students with 0 points)
|--------------------------------------------------------------------------
*/
$sql = "SELECT
            users.id,
            users.name,
            COALESCE(user_points.points, 0) AS points,
            student_profiles.department,
            student_profiles.semester,
            (SELECT COUNT(*) FROM user_badges WHERE user_badges.user_id = users.id) AS badge_count
        FROM users
        LEFT JOIN user_points
            ON users.id = user_points.user_id
        LEFT JOIN student_profiles
            ON users.id = student_profiles.user_id
        WHERE users.role = 'student' OR users.role IS NULL
        ORDER BY points DESC, users.name ASC";

$result = $conn->query($sql);

$students = [];
$myRank = null;
$rankIndex = 1;

if ($result) {
    while ($student = $result->fetch_assoc()) {
        $student["rank"] = $rankIndex;
        if ((int) $student["id"] === $userId) {
            $myRank = $rankIndex;
        }
        $students[] = $student;
        $rankIndex++;
    }
}

$pageTitle = "Campus Leaderboard";
$breadcrumbs = [
    ["label" => "Dashboard", "url" => "../../public/dashboard.php"],
    ["label" => "Leaderboard"]
];

require_once __DIR__ . "/../../includes/header.php";

// Helper for initials
function getInitials($name) {
    $parts = explode(" ", trim($name));
    $initials = "";
    foreach ($parts as $p) {
        if (!empty($p)) {
            $initials .= mb_substr($p, 0, 1);
        }
    }
    return mb_substr($initials, 0, 2) ?: "ST";
}
?>

<main class="container">

    <div class="page-header">
        <div>
            <h1 class="page-header-title">Campus Leaderboard</h1>
            <p class="page-header-subtitle">
                Celebrating top student innovators driving peer skill-sharing, project collaboration, and community events.
            </p>
        </div>
        <div class="page-header-actions">
            <div class="card card-interactive" style="padding: 10px 18px; display: flex; align-items: center; gap: 12px; background: white;">
                <div>
                    <span class="stat-label" style="display: block; font-size: 0.7rem;">Your Standing</span>
                    <span style="font-family: var(--font-display); font-weight: 700; font-size: 1.15rem; color: var(--primary);">
                        ⭐ <?= (int) ($currentUser["points"] ?? 0) ?> pts
                        <?= $myRank ? '<span style="color: var(--muted); font-size: 0.9rem; font-weight: 500;"> · Rank #' . $myRank . '</span>' : '' ?>
                    </span>
                </div>
            </div>
            <a class="btn btn-secondary" href="badges.php">
                View My Badges 🏅
            </a>
        </div>
    </div>

    <?php if (!empty($students)): ?>
        <?php
        // Prepare Top 3 podium items if available
        $top3 = array_slice($students, 0, 3);
        ?>
        <?php if (count($top3) >= 1): ?>
            <section aria-label="Top 3 Contributors" style="margin-bottom: 36px;">
                <h2 style="font-family: var(--font-display); font-size: 1.15rem; margin-bottom: 16px; color: var(--text-secondary); display: flex; align-items: center; gap: 8px;">
                    <span>🏆</span> Top Innovators Podium
                </h2>
                <div class="podium-container">
                    <?php if (isset($top3[1])): // Rank 2 (Silver) ?>
                        <?php $s2 = $top3[1]; $isSelf2 = ((int)$s2['id'] === $userId); ?>
                        <div class="podium-card rank-2">
                            <span class="podium-medal" title="Silver Medal">🥈</span>
                            <div class="avatar-circle avatar-silver"><?= htmlspecialchars(getInitials($s2["name"])) ?></div>
                            <h3 style="margin: 0 0 4px; font-size: 1.1rem; color: var(--text-primary);">
                                <?= htmlspecialchars($s2["name"]) ?>
                                <?php if ($isSelf2): ?><span class="status status-peach" style="font-size: 0.7rem; padding: 2px 6px;">You</span><?php endif; ?>
                            </h3>
                            <p style="font-size: 0.82rem; color: var(--muted); margin: 0 0 12px;">
                                <?= htmlspecialchars($s2["department"] ?? "Campus Member") ?>
                                <?= !empty($s2["semester"]) ? " · Sem " . (int)$s2["semester"] : "" ?>
                            </p>
                            <div style="font-family: var(--font-display); font-weight: 700; font-size: 1.25rem; color: #475569;">
                                <?= (int) $s2["points"] ?> <span style="font-size: 0.85rem; font-weight: 500;">pts</span>
                            </div>
                            <span class="status" style="margin-top: 8px; font-size: 0.75rem;">
                                <?= (int)$s2["badge_count"] ?> Badges Earned
                            </span>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($top3[0])): // Rank 1 (Gold - Center) ?>
                        <?php $s1 = $top3[0]; $isSelf1 = ((int)$s1['id'] === $userId); ?>
                        <div class="podium-card rank-1" style="transform: translateY(-8px);">
                            <span class="podium-medal" title="Gold Medal">🥇</span>
                            <div class="avatar-circle avatar-gold" style="width: 56px; height: 56px; font-size: 1.25rem;"><?= htmlspecialchars(getInitials($s1["name"])) ?></div>
                            <h3 style="margin: 0 0 4px; font-size: 1.25rem; color: var(--text-primary);">
                                <?= htmlspecialchars($s1["name"]) ?>
                                <?php if ($isSelf1): ?><span class="status status-peach" style="font-size: 0.7rem; padding: 2px 6px;">You</span><?php endif; ?>
                            </h3>
                            <p style="font-size: 0.85rem; color: var(--muted); margin: 0 0 12px;">
                                <?= htmlspecialchars($s1["department"] ?? "Campus Member") ?>
                                <?= !empty($s1["semester"]) ? " · Sem " . (int)$s1["semester"] : "" ?>
                            </p>
                            <div style="font-family: var(--font-display); font-weight: 800; font-size: 1.6rem; color: #b45309;">
                                <?= (int) $s1["points"] ?> <span style="font-size: 0.95rem; font-weight: 500;">pts</span>
                            </div>
                            <span class="status status-peach" style="margin-top: 8px; font-size: 0.75rem; font-weight: 700;">
                                👑 Campus Champion · <?= (int)$s1["badge_count"] ?> Badges
                            </span>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($top3[2])): // Rank 3 (Bronze) ?>
                        <?php $s3 = $top3[2]; $isSelf3 = ((int)$s3['id'] === $userId); ?>
                        <div class="podium-card rank-3">
                            <span class="podium-medal" title="Bronze Medal">🥉</span>
                            <div class="avatar-circle avatar-bronze"><?= htmlspecialchars(getInitials($s3["name"])) ?></div>
                            <h3 style="margin: 0 0 4px; font-size: 1.1rem; color: var(--text-primary);">
                                <?= htmlspecialchars($s3["name"]) ?>
                                <?php if ($isSelf3): ?><span class="status status-peach" style="font-size: 0.7rem; padding: 2px 6px;">You</span><?php endif; ?>
                            </h3>
                            <p style="font-size: 0.82rem; color: var(--muted); margin: 0 0 12px;">
                                <?= htmlspecialchars($s3["department"] ?? "Campus Member") ?>
                                <?= !empty($s3["semester"]) ? " · Sem " . (int)$s3["semester"] : "" ?>
                            </p>
                            <div style="font-family: var(--font-display); font-weight: 700; font-size: 1.25rem; color: #c2410c;">
                                <?= (int) $s3["points"] ?> <span style="font-size: 0.85rem; font-weight: 500;">pts</span>
                            </div>
                            <span class="status" style="margin-top: 8px; font-size: 0.75rem;">
                                <?= (int)$s3["badge_count"] ?> Badges Earned
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Full Standings Table -->
        <section aria-label="Campus Standings Table" class="card" style="padding: 0; overflow: hidden; border: 1px solid var(--border);">
            <div style="padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #fafbfc;">
                <h2 style="margin: 0; font-size: 1.1rem; font-family: var(--font-display);">Full Campus Standings</h2>
                <span style="font-size: 0.85rem; color: var(--muted); font-weight: 500;">
                    <?= count($students) ?> Active <?= count($students) === 1 ? 'Student' : 'Students' ?>
                </span>
            </div>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 80px; text-align: center;">Rank</th>
                            <th>Student</th>
                            <th>Department & Semester</th>
                            <th style="text-align: center;">Badges</th>
                            <th style="text-align: right; width: 140px;">Total Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <?php
                            $isSelf = ((int)$student["id"] === $userId);
                            $rank = (int)$student["rank"];
                            ?>
                            <tr class="<?= $isSelf ? 'leaderboard-self-row' : '' ?>">
                                <td style="text-align: center;">
                                    <?php if ($rank === 1): ?>
                                        <span class="rank-badge rank-1" title="1st Place">🥇</span>
                                    <?php elseif ($rank === 2): ?>
                                        <span class="rank-badge rank-2" title="2nd Place">🥈</span>
                                    <?php elseif ($rank === 3): ?>
                                        <span class="rank-badge rank-3" title="3rd Place">🥉</span>
                                    <?php else: ?>
                                        <span class="rank-badge">#<?= $rank ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div class="avatar-circle" style="width: 34px; height: 34px; font-size: 0.8rem; margin: 0;">
                                            <?= htmlspecialchars(getInitials($student["name"])) ?>
                                        </div>
                                        <div>
                                            <span style="font-weight: <?= $isSelf ? '700' : '600' ?>; color: var(--text-primary);">
                                                <?= htmlspecialchars($student["name"]) ?>
                                            </span>
                                            <?php if ($isSelf): ?>
                                                <span class="status status-peach" style="font-size: 0.7rem; padding: 2px 6px; margin-left: 6px;">You</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td style="color: var(--muted); font-size: 0.9rem;">
                                    <?= htmlspecialchars($student["department"] ?? "General") ?>
                                    <?= !empty($student["semester"]) ? " · Sem " . (int)$student["semester"] : "" ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ((int)$student["badge_count"] > 0): ?>
                                        <span class="status status-peach" style="font-size: 0.8rem; font-weight: 600;">
                                            🏅 <?= (int)$student["badge_count"] ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--muted); font-size: 0.85rem;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right; font-family: var(--font-display); font-weight: 700; font-size: 1.05rem; color: var(--text-primary);">
                                    <?= number_format((int) $student["points"]) ?> <span style="font-size: 0.8rem; font-weight: 500; color: var(--muted);">pts</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

    <?php else: ?>
        <div class="empty-state card" style="text-align: center; padding: 60px 24px;">
            <div style="font-size: 3rem; margin-bottom: 16px;">🌱</div>
            <h2 style="font-size: 1.3rem; margin-bottom: 8px;">No Student Points Recorded Yet</h2>
            <p style="max-width: 440px; margin: 0 auto 20px; color: var(--muted);">
                Be the first to score points! Join or create a project, share a skill, or submit an idea to earn achievements and climb the rankings.
            </p>
            <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                <a class="btn" href="../projects/team-finder.php">Explore Projects</a>
                <a class="btn btn-secondary" href="../skills/exchange.php">Offer a Skill</a>
            </div>
        </div>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
