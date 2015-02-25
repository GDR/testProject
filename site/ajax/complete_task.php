<?php

require_once(__DIR__ . "/../utils/app_utils.php");
header('Content-Type: application/json');


if (!check_authentication()) {
    show_error('You must be logged in', 401);
}
$taskId = null;
$username = get_username();
$userId = get_user_id();

if ($userId == -1 || $username == null) {
    show_error('You must be logged in', 401);
}

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

$query = "UPDATE tasks SET toUserId = ?, toUsername = ?, taskType = 'C', ts = ? WHERE taskId = ? AND taskType = 'O' AND sysblock = 'F';";
$complete_tasks_statement = mysqli_stmt_init($db_connection);
if (mysqli_stmt_prepare($complete_tasks_statement, $query)) {
    mysqli_stmt_bind_param($complete_tasks_statement, 'isii', $userId, $username, get_current_time_in_mills(), $taskId);
    mysqli_stmt_execute($complete_tasks_statement);
    if (mysqli_stmt_affected_rows($complete_tasks_statement) != 1) {
        show_error_stmt('', 403, $db_connection, $complete_tasks_statement);
    }
} else {
    show_error_stmt('', 500, $db_connection, $complete_tasks_statement);
}


mysqli_stmt_close($complete_tasks_statement);
mysqli_close($db_connection);