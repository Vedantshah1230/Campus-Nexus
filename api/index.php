<?php

/**
 * Campus Nexus — Vercel PHP Serverless Entry Point
 *
 * All HTTP requests are routed here by vercel.json.
 * This router maps the request URI to the correct PHP file
 * within the project, preserving all existing application logic,
 * sessions, auth, and relative asset paths.
 *
 * The project's __DIR__-based and $baseUrl-based path resolution
 * continues to work because we chdir() into the repo root before
 * including the target file.
 */

// ─── 1. Resolve the project root ─────────────────────────────────────────────
// __DIR__ is /var/task/api on Vercel; the repo root is one level up.
$projectRoot = dirname(__DIR__);

// ─── 2. Parse the incoming request path ──────────────────────────────────────
$requestUri  = $_SERVER['REQUEST_URI'] ?? '/';
$parsedPath  = parse_url($requestUri, PHP_URL_PATH);
$requestPath = rtrim($parsedPath ?? '/', '/');

// ─── 3. Route table: URL path prefix → PHP file (relative to project root) ──
//
// Static assets (css/, js/, images/) are served directly by Vercel before this
// script is invoked, so they are NOT listed here.
//
$routes = [
    // Root / landing page
    ''                                          => 'public/index.php',
    '/public'                                   => 'public/index.php',
    '/public/index.php'                         => 'public/index.php',

    // Dashboard
    '/public/dashboard.php'                     => 'public/dashboard.php',

    // Auth
    '/modules/auth/login.php'                   => 'modules/auth/login.php',
    '/modules/auth/register.php'                => 'modules/auth/register.php',
    '/modules/auth/logout.php'                  => 'modules/auth/logout.php',

    // Projects
    '/modules/projects/team-finder.php'         => 'modules/projects/team-finder.php',
    '/modules/projects/create.php'              => 'modules/projects/create.php',
    '/modules/projects/my-projects.php'         => 'modules/projects/my-projects.php',
    '/modules/projects/join.php'                => 'modules/projects/join.php',
    '/modules/projects/requests.php'            => 'modules/projects/requests.php',

    // Skills
    '/modules/skills/exchange.php'              => 'modules/skills/exchange.php',
    '/modules/skills/request.php'               => 'modules/skills/request.php',
    '/modules/skills/requests.php'              => 'modules/skills/requests.php',

    // Ideas
    '/modules/ideas/index.php'                  => 'modules/ideas/index.php',
    '/modules/ideas/vote.php'                   => 'modules/ideas/vote.php',

    // Events
    '/modules/events/index.php'                 => 'modules/events/index.php',
    '/modules/events/register.php'              => 'modules/events/register.php',

    // Gamification
    '/modules/gamification/leaderboard.php'     => 'modules/gamification/leaderboard.php',
    '/modules/gamification/badges.php'          => 'modules/gamification/badges.php',
    '/modules/gamification/helpers.php'         => 'modules/gamification/helpers.php',

    // Students / Profile
    '/modules/students/profile.php'             => 'modules/students/profile.php',
    '/modules/students/edit-profile.php'        => 'modules/students/edit-profile.php',
    '/modules/students/skills.php'              => 'modules/students/skills.php',
    '/modules/students/interests.php'           => 'modules/students/interests.php',
];

// ─── 4. Look up the target file ───────────────────────────────────────────────
$relativeFile = $routes[$requestPath] ?? null;

// Strip query string from path for exact match (already parsed above, but
// also handle bare paths without extensions sent by some browsers).
if ($relativeFile === null) {
    // Attempt prefix match for any unrecognised paths (e.g. /public/test-db.php)
    foreach ($routes as $pattern => $file) {
        if ($pattern !== '' && strpos($requestPath, $pattern) === 0) {
            $relativeFile = $file;
            break;
        }
    }
}

// Default: serve the landing page for unknown routes
if ($relativeFile === null) {
    $relativeFile = 'public/index.php';
}

$targetFile = $projectRoot . '/' . $relativeFile;

// ─── 5. Safety: only serve PHP files that actually exist ─────────────────────
// Never expose source files outside the project root or config credentials.
$realTarget  = realpath($targetFile);
$realRoot    = realpath($projectRoot);

$blockedPaths = [
    $realRoot . DIRECTORY_SEPARATOR . 'config',
    $realRoot . DIRECTORY_SEPARATOR . 'database',
];

$isBlocked = false;
foreach ($blockedPaths as $blocked) {
    if ($realTarget !== false && strpos($realTarget, $blocked) === 0) {
        $isBlocked = true;
        break;
    }
}

if (
    $realTarget === false
    || !is_file($realTarget)
    || !str_ends_with(strtolower($realTarget), '.php')
    || strpos($realTarget, $realRoot) !== 0
    || $isBlocked
) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head>'
       . '<body><h1>404 — Page Not Found</h1>'
       . '<p><a href="/">Return to Campus Nexus</a></p></body></html>';
    exit;
}

// ─── 6. Set working directory to the project root so that all relative ────────
//        require_once / include paths inside the existing PHP files resolve
//        correctly (e.g. "../../config/database.php" from modules/auth/).
chdir($projectRoot);

// ─── 7. Inject the target script's directory into include_path ───────────────
set_include_path(get_include_path() . PATH_SEPARATOR . dirname($realTarget));

// ─── 8. Expose the target file to the application (some files read this) ─────
$_SERVER['SCRIPT_FILENAME'] = $realTarget;
$_SERVER['SCRIPT_NAME']     = '/' . $relativeFile;
$_SERVER['PHP_SELF']        = '/' . $relativeFile;

// ─── 9. Execute the resolved PHP file in the same scope ──────────────────────
//        (include preserves the current variable scope, which is intentional
//         so that session_start() in header.php interacts with $_SESSION
//         already populated by login/register files.)
include $realTarget;
