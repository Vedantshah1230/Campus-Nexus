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

        <!-- Column 1: Brand & Institutional Identity -->
        <div class="footer-brand-col">
            <a class="brand footer-brand" href="<?= htmlspecialchars($baseUrl) ?>/public/index.php" aria-label="Campus Nexus Home">
                <div class="brand-logo-wrap" style="width: 42px; height: 42px;">
                    <img src="<?= htmlspecialchars($baseUrl) ?>/images/cn-logo.png" alt="Campus Nexus Emblem" class="brand-logo-img">
                </div>
                <div class="brand-info">
                    <span class="brand-text" style="color: white;">Campus <span class="brand-accent">Nexus</span></span>
                    <span class="brand-subtext" style="color: var(--cosmic-text-muted);">Innovation & Collaboration Hub</span>
                </div>
            </a>
            <p class="footer-tagline">
                The centralized student collaboration portal engineered for cross-departmental teaming, peer skill exchanges, innovation proposals, and workshop discovery.
            </p>
        </div>

        <!-- Column 2: Collaboration Links -->
        <div class="footer-nav-col">
            <span class="footer-heading">Collaboration</span>
            <ul class="footer-links">
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/modules/projects/team-finder.php">🚀 Project Team Finder</a></li>
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/modules/projects/create.php">➕ Launch New Project</a></li>
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/modules/skills/exchange.php">🤝 Peer Skill Exchange</a></li>
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/modules/ideas/index.php">💡 Campus Idea Board</a></li>
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/modules/events/index.php">📅 Workshops & Events</a></li>
            </ul>
        </div>

        <!-- Column 3: Community & Gamification -->
        <div class="footer-nav-col">
            <span class="footer-heading">Community</span>
            <ul class="footer-links">
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/modules/gamification/leaderboard.php">🏆 Campus Leaderboard</a></li>
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/modules/gamification/badges.php">🏅 Achievement Badges</a></li>
                <?php if (isset($_SESSION["user_id"])): ?>
                    <li><a href="<?= htmlspecialchars($baseUrl) ?>/modules/students/profile.php">👤 Student Profile</a></li>
                    <li><a href="<?= htmlspecialchars($baseUrl) ?>/modules/students/skills.php">⚡ Manage My Skills</a></li>
                    <li><a href="<?= htmlspecialchars($baseUrl) ?>/public/dashboard.php">📊 Analytics Dashboard</a></li>
                <?php else: ?>
                    <li><a href="<?= htmlspecialchars($baseUrl) ?>/modules/auth/login.php">🔐 Student Sign In</a></li>
                    <li><a href="<?= htmlspecialchars($baseUrl) ?>/modules/auth/register.php">✨ Create Student Account</a></li>
                <?php endif; ?>
                <li><a href="<?= htmlspecialchars($baseUrl) ?>/docs/VIVA_AND_DEMO_GUIDE.md">📖 Viva & Architecture Guide</a></li>
            </ul>
        </div>

    </div>

    <!-- Legal & Copyright Section -->
    <div class="footer-bottom">
        <div class="container footer-bottom-inner">
            <div style="font-size: 0.8rem; color: var(--cosmic-text-muted); line-height: 1.6; max-width: 820px;">
                <p style="margin: 0 0 6px;">
                    &copy; <?= date("Y") ?> <strong>Campus Nexus</strong>. The Next-Generation Student Collaboration & Innovation Platform.
                </p>
                <p style="margin: 0; font-size: 0.74rem; opacity: 0.8;">
                    Empowering students to connect, build cross-disciplinary projects, trade skills, and lead campus initiatives. Distributed under the Open Source MIT License.
                </p>
            </div>
            <div class="footer-badges">
                <span class="footer-status-pill">● System Online · Campus Hub</span>
                <span class="footer-status-pill" style="background: rgba(99, 102, 241, 0.2); border-color: rgba(99, 102, 241, 0.4); color: #c7d2fe;">MIT License</span>
            </div>
        </div>
    </div>
</footer>

<!-- Master Vanilla JavaScript Bundle -->
<script src="<?= htmlspecialchars($baseUrl) ?>/js/main.js" defer></script>
</body>
</html>
