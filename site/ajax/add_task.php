<?php

require_once(__DIR__ . "/../utils/app_utils.php");
header('Content-Type: application/json');

$userId = -1;
$username = '';
$taskPrice = -1;
$taskTitle = '';

if (isset($_POST[FIELD_TITLE])) {
    $taskTitle = $_POST[FIELD_TITLE];
} else {
    show_error('Task title should be not empty', 403);
}

if (isset($_POST[FIELD_PRICE])) {
    if (is_numeric($_POST[FIELD_PRICE])) {
        $taskPrice = floatval($_POST[FIELD_PRICE]);
    } else {
        show_error('Price must be float', 403);
    }
} else {
    show_error('Task price should be not empty', 403);
}

$userId = get_user_id();
$username = get_username();
$userType = get_user_type();

if ($userId == -1 || $username == null || $userType == -1) {
    show_error('You are not logged in', 403);
}

if ($userType != USER_CUSTOMER) {
    show_error('You are not customer', 403);
}

$taskPrice = floor($taskPrice * 100);
$commission = ceil($taskPrice * COMMISSION);
$taskPrice -= $commission;
$taskPrice /= 100;
$commission /= 100;

require_once(__DIR__ . "/../utils/database_util.php");

$add_task_statement = mysqli_stmt_init($db_connection);
$query = "INSERT INTO issues (title, fromUserId, fromUsername, price, commission) VALUE (?, ?, ?, ?, ?);";

if (mysqli_stmt_prepare($add_task_statement, $query)) {
    mysqli_stmt_bind_param($add_task_statement, 'sisdd',
        $taskTitle,
        $userId,
        $username,
        $taskPrice,
        $commission);

    mysqli_stmt_execute($add_task_statement);

    if (mysqli_stmt_affected_rows($add_task_statement) != 1) {
        show_error_stmt(mysqli_stmt_error($add_task_statement), 500, $db_connection, $add_task_statement);
    }
    $issueId = mysqli_stmt_insert_id($add_task_statement);

    echo json_encode(array(
        FIELD_TASK_ID => $issueId,
        FIELD_TITLE => $taskTitle,
        FIELD_USER_ID => $userId,
        FIELD_USERNAME => $username,
        FIELD_PRICE => $taskPrice
    ));
} else {
    show_error_stmt(mysqli_stmt_error($add_task_statement), 500, $db_connection, $add_task_statement);
}

mysqli_stmt_close($add_task_statement);
mysqli_close($db_connection);