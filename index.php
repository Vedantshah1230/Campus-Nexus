<?php

/**
 * Campus Nexus — Root Entry
 *
 * On Vercel all requests pass through api/index.php (see vercel.json).
 * This file is kept for compatibility with local development servers
 * (e.g. php -S localhost:8080) where the webroot is the project root.
 *
 * It performs an absolute redirect so it works regardless of whether
 * the project is served from / or a sub-path.
 */
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script   = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
$base     = rtrim($script, '/');

header("Location: {$protocol}://{$host}{$base}/public/index.php");
exit;
