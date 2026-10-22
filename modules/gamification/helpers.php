<?php

/*
|--------------------------------------------------------------------------
| Award points to a user
|--------------------------------------------------------------------------
*/

function awardPoints($conn, $userId, $points)
{
    $sql = "INSERT INTO user_points (user_id, points)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE
                points = points + VALUES(points)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ii",
        $userId,
        $points
    );

    $success = $stmt->execute();

    $stmt->close();

    return $success;
}


/*
|--------------------------------------------------------------------------
| Award a badge to a user
|--------------------------------------------------------------------------
*/

function awardBadge($conn, $userId, $badgeId)
{
    /*
    | Insert the badge only if the user does not already have it.
    */

    $sql = "INSERT IGNORE INTO user_badges
                (user_id, badge_id)
            VALUES (?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ii",
        $userId,
        $badgeId
    );

    $success = $stmt->execute();

    /*
    | affected_rows tells us whether a new badge was actually inserted.
    */

    $badgeAdded = ($stmt->affected_rows === 1);

    $stmt->close();

    return $badgeAdded;
}