<?php

require_once(__DIR__ . "/../utils/app_utils.php");
require_once(__DIR__ . "/../utils/database_util.php");
header('Content-Type: application/json');

$startingFrom = 1000000;

if (isset($_GET[FIELD_OFFSET])) {
    if (is_numeric($_GET[FIELD_OFFSET])) {
        $startingFrom = (int)$_GET[FIELD_OFFSET];
    }
}


$query = "SELECT taskId, title, fromUsername, price FROM tasks WHERE tasks.taskId < ? AND taskType='O' AND sysblock = 'F' ORDER BY taskId DESC LIMIT " . GET_TASK_LIMIT . " ;";

$get_tasks_statement = mysqli_stmt_init($db_connection);

if (mysqli_stmt_prepare($get_tasks_statement, $query)) {
    mysqli_stmt_bind_param($get_tasks_statement, 'i', $startingFrom);
    mysqli_stmt_execute($get_tasks_statement);
    mysqli_stmt_bind_result($get_tasks_statement, $taskId, $title, $fromUsername, $price);
    $response = array();
    while(mysqli_stmt_fetch($get_tasks_statement)) {
        array_push($response, array(
            FIELD_TASK_ID => $taskId,
            FIELD_TITLE => $title,
            FIELD_USERNAME => $fromUsername,
            FIELD_PRICE => $price
        ));
    }
    echo (json_encode($response));
} else {
    show_error_stmt('Error connecting to db: '.mysqli_stmt_error($get_tasks_statement), 500, $db_connection, $get_tasks_statement);
}

mysqli_stmt_close($get_tasks_statement);
mysqli_close($db_connection);