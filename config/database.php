<?php

/**
 * Campus Nexus — Database Connection
 *
 * All credentials are read from environment variables.
 * Set these in your Vercel project settings (Settings → Environment Variables):
 *
 *   DB_HOST   — hostname of your remote MySQL server  (required on Vercel)
 *   DB_NAME   — database name                          (required on Vercel)
 *   DB_USER   — MySQL username                         (required on Vercel)
 *   DB_PASS   — MySQL password                         (required on Vercel)
 *   DB_PORT   — MySQL port (default: 3306)             (optional)
 *   DB_SSL_CA — absolute path to CA certificate file   (optional, for SSL)
 *
 * The localhost / root / 123456 fallbacks are intentionally left ONLY for
 * local development; they will never be used in production because the
 * Vercel environment variables take precedence.
 */

$host     = getenv('DB_HOST') ?: 'localhost';
$dbname   = getenv('DB_NAME') ?: 'campus_nexus';
$username = getenv('DB_USER') ?: 'root';
$password = (getenv('DB_PASS') !== false) ? getenv('DB_PASS') : '123456';
$port     = getenv('DB_PORT')  ? (int) getenv('DB_PORT')  : 3306;
$sslCa    = getenv('DB_SSL_CA') ?: null;

$conn = new mysqli();

// Enable SSL before connecting if a CA certificate path is provided.
// Required by PlanetScale, Aiven, Railway, and similar managed MySQL services.
if ($sslCa) {
    $conn->ssl_set(null, null, $sslCa, null, null);
}

$conn->real_connect($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    // Never expose the raw connection error to the browser — it may contain
    // the hostname, username, or other sensitive details.
    http_response_code(503);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
       . '<title>Service Unavailable — Campus Nexus</title></head><body>'
       . '<h1>Campus Nexus is temporarily unavailable</h1>'
       . '<p>The database could not be reached. Please try again shortly.</p>'
       . '</body></html>';
    exit;
}

$conn->set_charset('utf8mb4');