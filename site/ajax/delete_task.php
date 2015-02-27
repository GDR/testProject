<?php

require_once(__DIR__ . "/../utils/app_utils.php");
header('Content-Type: application/json');


if (!check_authentication()) {
    show_error('You must be logged in', 401);
}


$taskId = null;
$userId = get_user_id();

if (isset($_POST[FIELD_TASK_ID])) {
    if (is_numeric($_POST[FIELD_TASK_ID])) {
        $taskId = intval($_POST[FIELD_TASK_ID]);
    } else {
        show_error('Task id must be int', 403);
    }
} else {
    show_error('Task id shouldn\'t be empty', 403);
}

require_once(__DIR__. "/../utils/database_util.php");

$query = "UPDATE issues SET issueType = 'D', tsEdited = ? WHERE id = ? AND fromUserId = ? AND issueType = 'O' AND blocked = 'F';";
$delete_tasks_statement = mysqli_stmt_init($db_connection);
if (mysqli_stmt_prepare($delete_tasks_statement, $query)) {
    mysqli_stmt_bind_param($delete_tasks_statement, 'iii', get_current_time_in_mills(), $taskId, $userId);
    mysqli_stmt_execute($delete_tasks_statement);
    if (mysqli_stmt_affected_rows($delete_tasks_statement) != 1) {
        show_error_stmt('', 403, $db_connection, $delete_tasks_statement);
    }
} else {
    show_error_stmt(mysqli_stmt_error($delete_tasks_statement), 500, $db_connection, $delete_tasks_statement);
}


mysqli_stmt_close($delete_tasks_statement);
mysqli_close($db_connection);