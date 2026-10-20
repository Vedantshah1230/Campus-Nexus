<?php

/**
 * Campus Nexus - Institutional Authentication & Access Audit Logger
 * Records student identity access events for institutional security & compliance.
 */

if (!function_exists('logAuthActivity')) {
    function logAuthActivity(mysqli $conn, ?int $userId, string $email, string $action): void
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        // If forwarded for contains multiple IPs, take the first one
        if (strpos($ip, ',') !== false) {
            $ipParts = explode(',', $ip);
            $ip = trim($ipParts[0]);
        }
        $ip = substr($ip, 0, 45);
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Device / Browser', 0, 255);

        $sql = "INSERT INTO auth_logs (user_id, email, action, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("issss", $userId, $email, $action, $ip, $userAgent);
            $stmt->execute();
            $stmt->close();
        }
    }
}
