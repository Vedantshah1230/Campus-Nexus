<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Robust Dynamic Base URL Resolution
|--------------------------------------------------------------------------
*/
if (!isset($baseUrl)) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $detectedBase = preg_replace('~/(public|modules)(/.*)?$~i', '', $scriptDir);
    $baseUrl = rtrim($detectedBase, '/');
}

$pageTitle = $pageTitle ?? "Campus Nexus";
$isLoggedIn = isset($_SESSION["user_id"]);
$userName = $_SESSION["user_name"] ?? "Student";

// Active link detector
$currentScript = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$isNavActive = function (string $pattern) use ($currentScript): bool {
    return (bool) preg_match('~' . preg_quote($pattern, '~') . '~i', $currentScript);
};

// Flash messages
$alertSuccess = $_SESSION['flash_success'] ?? $successMessage ?? null;
$alertError = $_SESSION['flash_error'] ?? $errorMessage ?? null;
$alertWarning = $_SESSION['flash_warning'] ?? $warningMessage ?? null;
$alertInfo = $_SESSION['flash_info'] ?? $infoMessage ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error'], $_SESSION['flash_warning'], $_SESSION['flash_info']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($pageTitle) ?> | Campus Nexus — Innovation & Collaboration Hub</title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription ?? 'Campus Nexus – Student Collaboration Hub connecting ideas, projects, skills, and campus workshops.') ?>">

    <!-- Official Favicon -->
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($baseUrl) ?>/images/favicon.png">

    <!-- Design System Stylesheet -->
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/css/style.css">
</head>
<body>

<header class="topbar">
    <div class="topbar-inner">

        <!-- Brand Identity with Official Logo -->
        <a class="brand" href="<?= htmlspecialchars($baseUrl) ?>/public/index.php" aria-label="Campus Nexus Home">
            <div class="brand-logo-wrap">
                <img src="<?= htmlspecialchars($baseUrl) ?>/images/cn-logo.png" alt="Campus Nexus Emblem" class="brand-logo-img">
            </div>
            <div class="brand-info">
                <span class="brand-text">Campus <span class="brand-accent">Nexus</span></span>
                <span class="brand-subtext">Innovation & Collaboration Hub</span>
            </div>
        </a>

        <!-- Global Quick Search Trigger (Ctrl + K) -->
        <button class="quick-search-trigger" id="openCommandPalette" type="button" aria-label="Open Quick Search" title="Quick Search (Ctrl + K)">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <span class="quick-search-label">Quick Search...</span>
            <kbd class="quick-search-kbd">Ctrl K</kbd>
        </button>

        <!-- Responsive Hamburger Toggle -->
        <button class="nav-toggle" id="navToggle" type="button" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="primaryNav">
            <span class="nav-toggle-line"></span>
            <span class="nav-toggle-line"></span>
            <span class="nav-toggle-line"></span>
        </button>

        <nav class="nav-links" id="primaryNav" aria-label="Primary Navigation">

            <?php if ($isLoggedIn): ?>

                <a class="nav-link <?= $isNavActive('/public/dashboard.php') ? 'active' : '' ?>"
                   href="<?= htmlspecialchars($baseUrl) ?>/public/dashboard.php">
                    Dashboard
                </a>

                <a class="nav-link <?= $isNavActive('/modules/projects/') ? 'active' : '' ?>"
                   href="<?= htmlspecialchars($baseUrl) ?>/modules/projects/team-finder.php">
                    Projects
                </a>

                <a class="nav-link <?= $isNavActive('/modules/skills/') ? 'active' : '' ?>"
                   href="<?= htmlspecialchars($baseUrl) ?>/modules/skills/exchange.php">
                    Skill Exchange
                </a>

                <a class="nav-link <?= $isNavActive('/modules/ideas/') ? 'active' : '' ?>"
                   href="<?= htmlspecialchars($baseUrl) ?>/modules/ideas/index.php">
                    Ideas
                </a>

                <a class="nav-link <?= $isNavActive('/modules/events/') ? 'active' : '' ?>"
                   href="<?= htmlspecialchars($baseUrl) ?>/modules/events/index.php">
                    Events
                </a>

                <a class="nav-link <?= $isNavActive('/modules/gamification/leaderboard.php') ? 'active' : '' ?>"
                   href="<?= htmlspecialchars($baseUrl) ?>/modules/gamification/leaderboard.php">
                    Leaderboard
                </a>

                <a class="nav-link <?= $isNavActive('/modules/gamification/badges.php') ? 'active' : '' ?>"
                   href="<?= htmlspecialchars($baseUrl) ?>/modules/gamification/badges.php">
                    Badges
                </a>

                <a class="nav-link <?= $isNavActive('/modules/students/') ? 'active' : '' ?>"
                   href="<?= htmlspecialchars($baseUrl) ?>/modules/students/profile.php">
                    Profile
                </a>

                <div class="nav-divider" aria-hidden="true"></div>

                <a class="nav-link nav-link-logout"
                   href="<?= htmlspecialchars($baseUrl) ?>/modules/auth/logout.php">
                    Logout
                </a>

            <?php else: ?>

                <a class="nav-link <?= $isNavActive('/modules/projects/') ? 'active' : '' ?>"
                   href="<?= htmlspecialchars($baseUrl) ?>/public/index.php#features">
                    Explore
                </a>

                <a class="nav-link <?= $isNavActive('/modules/auth/login.php') ? 'active' : '' ?>"
                   href="<?= htmlspecialchars($baseUrl) ?>/modules/auth/login.php">
                    Sign In
                </a>

                <a class="btn btn-glow"
                   href="<?= htmlspecialchars($baseUrl) ?>/modules/auth/register.php">
                    Get Started
                </a>

            <?php endif; ?>

        </nav>

    </div>
</header>

<!-- Global Command Palette Modal (Ctrl + K) -->
<div class="cmd-palette-backdrop" id="cmdPalette" role="dialog" aria-modal="true" aria-label="Global Quick Search" style="display: none;">
    <div class="cmd-palette-box">
        <div class="cmd-palette-input-wrap">
            <svg class="cmd-palette-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" id="cmdPaletteInput" class="cmd-palette-input" placeholder="Type a command or search (e.g. projects, python, idea, event)..." autocomplete="off" spellcheck="false">
            <kbd class="cmd-palette-kbd">ESC</kbd>
        </div>
        <div class="cmd-palette-results" id="cmdPaletteResults">
            <div class="cmd-group-label">Quick Navigation</div>
            <a href="<?= htmlspecialchars($baseUrl) ?>/modules/projects/team-finder.php" class="cmd-item">
                <span class="cmd-item-icon">🚀</span>
                <div class="cmd-item-text">
                    <strong>Project Team Finder</strong>
                    <small>Discover open student projects and check skill match</small>
                </div>
                <span class="cmd-item-badge">Projects</span>
            </a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/modules/projects/create.php" class="cmd-item">
                <span class="cmd-item-icon">➕</span>
                <div class="cmd-item-text">
                    <strong>Create New Project</strong>
                    <small>Assemble a team and define required skills</small>
                </div>
                <span class="cmd-item-badge">Launch</span>
            </a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/modules/skills/exchange.php" class="cmd-item">
                <span class="cmd-item-icon">🤝</span>
                <div class="cmd-item-text">
                    <strong>Peer Skill Exchange</strong>
                    <small>Offer mentorship or request help from student peers</small>
                </div>
                <span class="cmd-item-badge">Skills</span>
            </a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/modules/ideas/index.php" class="cmd-item">
                <span class="cmd-item-icon">💡</span>
                <div class="cmd-item-text">
                    <strong>Campus Idea Board</strong>
                    <small>Vote on student innovation proposals and campus pitches</small>
                </div>
                <span class="cmd-item-badge">Ideas</span>
            </a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/modules/events/index.php" class="cmd-item">
                <span class="cmd-item-icon">📅</span>
                <div class="cmd-item-text">
                    <strong>Campus Events & Workshops</strong>
                    <small>View upcoming workshops and reserve remaining seats</small>
                </div>
                <span class="cmd-item-badge">Events</span>
            </a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/modules/gamification/leaderboard.php" class="cmd-item">
                <span class="cmd-item-icon">🏆</span>
                <div class="cmd-item-text">
                    <strong>Campus Leaderboard & Podium</strong>
                    <small>See top student contributors ranked by points</small>
                </div>
                <span class="cmd-item-badge">Rankings</span>
            </a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/modules/gamification/badges.php" class="cmd-item">
                <span class="cmd-item-icon">🏅</span>
                <div class="cmd-item-text">
                    <strong>Badges & Achievements</strong>
                    <small>Track your unlocked and locked collaboration tokens</small>
                </div>
                <span class="cmd-item-badge">Badges</span>
            </a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/modules/students/skills.php" class="cmd-item">
                <span class="cmd-item-icon">⚡</span>
                <div class="cmd-item-text">
                    <strong>My Technical Skills</strong>
                    <small>Update your proficiencies in Web, AI, Databases & Design</small>
                </div>
                <span class="cmd-item-badge">Profile</span>
            </a>
        </div>
        <div class="cmd-palette-footer">
            <span>Navigation: <kbd>↑</kbd> <kbd>↓</kbd> to navigate, <kbd>↵</kbd> to select, <kbd>ESC</kbd> to close</span>
        </div>
    </div>
</div>

<?php if (!empty($breadcrumbs) && is_array($breadcrumbs)): ?>
    <nav class="breadcrumbs-bar" aria-label="Breadcrumb">
        <div class="container breadcrumbs-inner">
            <ol class="breadcrumbs-list">
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <?php $isLast = ($index === count($breadcrumbs) - 1); ?>
                    <li class="breadcrumb-item">
                        <?php if (!$isLast && !empty($crumb['url'])): ?>
                            <a class="breadcrumb-link" href="<?= htmlspecialchars($crumb['url']) ?>">
                                <?= htmlspecialchars($crumb['label']) ?>
                            </a>
                            <span class="breadcrumb-separator" aria-hidden="true">/</span>
                        <?php else: ?>
                            <span class="breadcrumb-current" aria-current="page">
                                <?= htmlspecialchars($crumb['label']) ?>
                            </span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </nav>
<?php endif; ?>

<?php if ($alertSuccess || $alertError || $alertWarning || $alertInfo): ?>
    <div class="container flash-messages-container">
        <?php if ($alertSuccess): ?>
            <div class="alert alert-success" role="alert">
                <span class="alert-icon" aria-hidden="true">✓</span>
                <div class="alert-content"><?= htmlspecialchars($alertSuccess) ?></div>
                <button type="button" class="alert-close" aria-label="Close message">&times;</button>
            </div>
        <?php endif; ?>

        <?php if ($alertWarning): ?>
            <div class="alert alert-warning" role="alert">
                <span class="alert-icon" aria-hidden="true">⚠</span>
                <div class="alert-content"><?= htmlspecialchars($alertWarning) ?></div>
                <button type="button" class="alert-close" aria-label="Close message">&times;</button>
            </div>
        <?php endif; ?>

        <?php if ($alertError): ?>
            <div class="alert alert-error" role="alert">
                <span class="alert-icon" aria-hidden="true">✕</span>
                <div class="alert-content"><?= htmlspecialchars($alertError) ?></div>
                <button type="button" class="alert-close" aria-label="Close message">&times;</button>
            </div>
        <?php endif; ?>

        <?php if ($alertInfo): ?>
            <div class="alert alert-info" role="alert">
                <span class="alert-icon" aria-hidden="true">ℹ</span>
                <div class="alert-content"><?= htmlspecialchars($alertInfo) ?></div>
                <button type="button" class="alert-close" aria-label="Close message">&times;</button>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
