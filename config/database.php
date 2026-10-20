<?php

/**
 * Campus Nexus - Active Database Connection
 */

$host     = getenv("DB_HOST") ?: "localhost";
$dbname   = getenv("DB_NAME") ?: "campus_nexus";
$username = getenv("DB_USER") ?: "root";
$password = getenv("DB_PASS") !== false ? getenv("DB_PASS") : "123456";
$port     = getenv("DB_PORT") ? (int)getenv("DB_PORT") : 3306;

$conn = new mysqli(
    $host,
    $username,
    $password,
    $dbname,
    $port
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");