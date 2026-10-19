<?php

/*
|--------------------------------------------------------------------------
| Base URL fallback if footer is referenced independently
|--------------------------------------------------------------------------
*/
if (!isset($baseUrl)) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $detectedBase = preg_replace('~/(public|modules)(/.*)?$~i', '', $scriptDir);
    $baseUrl = rtrim($detectedBase, '/');
}

?>

<footer class="site-footer">
    <div class="container footer-inner">

        <div class="footer-brand-col">
            <a class="brand footer-brand" href="<?= htmlspecialchars($baseUrl) ?>/public/index.php" aria-label="Campus Nexus Home">
                <span class="brand-icon" aria-hidden="true">✦</span>
                <span class="brand-text">Campus <span class="brand-accent">Nexus</span></span>
            </a>
            <p class="footer-tagline">
                The student collaboration hub connecting skills, project teammates, ideas, and campus workshops in one unified ecosystem.
            </p>
        </div>

        <div class="footer-nav-col">
            <span class="footer-heading">Collaboration</span>
            <ul class="footer-links">
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/modules/projects/team-finder.php">Project Finder</a></li>
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/modules/skills/exchange.php">Skill Exchange</a></li>
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/modules/ideas/index.php">Idea Board</a></li>
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/modules/events/index.php">Campus Events</a></li>
            </ul>
        </div>

        <div class="footer-nav-col">
            <span class="footer-heading">Community</span>
            <ul class="footer-links">
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/modules/gamification/leaderboard.php">Leaderboard</a></li>
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/modules/gamification/badges.php">Badges & Ranks</a></li>
                <?php if (isset($_SESSION["user_id"])): ?>
                    <li><a href="<?= htmlspecialchars($baseUrl) ?>/modules/students/profile.php">My Profile</a></li>
                    <li><a href="<?= htmlspecialchars($baseUrl) ?>/public/dashboard.php">Dashboard</a></li>
                <?php else: ?>
                    <li><a href="<?= htmlspecialchars($baseUrl) ?>/modules/auth/login.php">Sign In</a></li>
                    <li><a href="<?= htmlspecialchars($baseUrl) ?>/modules/auth/register.php">Create Account</a></li>
                <?php endif; ?>
            </ul>
        </div>

    </div>

    <div class="footer-bottom">
        <div class="container footer-bottom-inner">
            <p>&copy; <?= date("Y") ?> Campus Nexus. Built for student collaboration.</p>
            <p class="footer-badges">
                <span class="footer-status-pill">● System Online</span>
            </p>
        </div>
    </div>
</footer>

<script src="<?= htmlspecialchars($baseUrl) ?>/js/main.js" defer></script>
</body>
</html>
