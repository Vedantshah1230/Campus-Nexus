<?php

require_once "../config/database.php";

$sql = "SELECT id, name, email, role FROM users";

$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

while ($user = $result->fetch_assoc()) {
    echo "ID: " . $user["id"] . "<br>";
    echo "Name: " . $user["name"] . "<br>";
    echo "Email: " . $user["email"] . "<br>";
    echo "Role: " . $user["role"] . "<br>";
    echo "<hr>";
}