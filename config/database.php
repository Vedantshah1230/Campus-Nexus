<?php

/**
 * Campus Nexus — Database Connection
 *
 * LOCAL (Laragon):  no env vars needed — uses localhost/root/(empty password)
 * PRODUCTION (Vercel): set these in Vercel → Settings → Environment Variables:
 *
 *   DB_HOST   — remote MySQL hostname    (required)
 *   DB_NAME   — database name            (required)
 *   DB_USER   — MySQL username           (required)
 *   DB_PASS   — MySQL password           (required, can be empty string)
 *   DB_PORT   — MySQL port               (optional, default 3306)
 *   DB_SSL_CA — path to CA cert file     (optional, for PlanetScale / Aiven)
 */

$host     = getenv('DB_HOST') ?: 'localhost';
$dbname   = getenv('DB_NAME') ?: 'campus_nexus';
$username = getenv('DB_USER') ?: 'root';
$password = (getenv('DB_PASS') !== false) ? getenv('DB_PASS') : '';
$port     = getenv('DB_PORT')  ? (int) getenv('DB_PORT')  : 3306;
$sslCa    = getenv('DB_SSL_CA') ?: null;

$conn = new mysqli();

// Determine if SSL/TLS is required (Cloud providers like TiDB Cloud, Aiven, PlanetScale enforce SSL)
$isLocalhost = in_array(strtolower($host), ['localhost', '127.0.0.1', 'db', 'host.docker.internal'], true);
$requireSsl  = !empty($sslCa)
               || getenv('DB_SSL') === 'true'
               || getenv('DB_SSL') === '1'
               || $port === 4000 // TiDB Cloud standard port
               || !$isLocalhost; // Any remote cloud database

$flags = 0;

if ($requireSsl) {
    // Determine the CA certificate path (use custom CA, local bundle, or system bundle)
    $caPath = $sslCa;
    if (!$caPath) {
        $candidatePaths = [
            __DIR__ . '/isrgrootx1.pem',          // Project-bundled TiDB / Let's Encrypt CA root
            '/etc/ssl/certs/ca-certificates.crt', // Debian / Ubuntu / Render Linux
            '/etc/pki/tls/certs/ca-bundle.crt',   // RHEL / CentOS
            '/etc/ssl/ca-bundle.pem',             // OpenSUSE / Alpine
        ];
        foreach ($candidatePaths as $bundle) {
            if (file_exists($bundle)) {
                $caPath = $bundle;
                break;
            }
        }
    }

    if ($caPath) {
        $conn->ssl_set(null, null, $caPath, null, null);
    } else {
        $conn->ssl_set(null, null, null, null, null);
    }

    // Disable strict cert verification to ensure intermediate cert chains never reject valid TLS connections
    if (defined('MYSQLI_OPT_SSL_VERIFY_SERVER_CERT')) {
        $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
    }

    $flags |= MYSQLI_CLIENT_SSL;
}

try {
    $conn->real_connect($host, $username, $password, $dbname, $port, null, $flags);
} catch (mysqli_sql_exception $e) {
    http_response_code(503);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
       . '<title>Database Connection Error — Campus Nexus</title>'
       . '<style>body{font-family:system-ui,-apple-system,sans-serif;background:#0b1120;color:#f8fafc;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;padding:20px;}'
       . '.card{background:#1e293b;border:1px solid #334155;border-radius:12px;padding:32px;max-width:540px;box-shadow:0 10px 25px rgba(0,0,0,0.5);}'
       . 'h1{font-size:20px;color:#f87171;margin-top:0;}'
       . 'p{color:#94a3b8;font-size:14px;line-height:1.6;}'
       . 'code{background:#0f172a;padding:3px 6px;border-radius:4px;color:#38bdf8;font-size:13px;word-break:break-all;}'
       . '</style></head><body>'
       . '<div class="card">'
       . '<h1>⚠️ Database Connection Error</h1>'
       . '<p>Campus Nexus could not connect to the database server at <code>' . htmlspecialchars($host) . '</code>.</p>'
       . '<p><strong>Details:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>'
       . '<p>Please check your database credentials and verify that your cluster is accepting connections.</p>'
       . '</div></body></html>';
    exit;
}

if ($conn->connect_error) {
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