<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Robust Dynamic Base URL Resolution
|--------------------------------------------------------------------------
| Accurately determines root URL path regardless of directory depth
| (e.g., /public, /modules/projects, or local subfolder environments).
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

// Flash messages (support both session flashes and page-level variables)
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

    <title><?= htmlspecialchars($pageTitle) ?> | Campus Nexus</title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription ?? 'Campus Nexus – Student Collaboration Hub connecting ideas, projects, skills, and events.') ?>">

    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/css/style.css">
</head>
<body>

<header class="topbar">
    <div class="topbar-inner">

        <a class="brand" href="<?= htmlspecialchars($baseUrl) ?>/public/index.php" aria-label="Campus Nexus Home">
            <span class="brand-icon" aria-hidden="true">✦</span>
            <span class="brand-text">Campus <span class="brand-accent">Nexus</span></span>
        </a>

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
