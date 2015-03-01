<?php

require_once(__DIR__ . "/../utils/app_utils.php");
header('Content-Type: application/json');

$userId = -1;
$moneyAmount = -1;

if (isset($_POST[FIELD_AMOUNT])) {
    if (is_numeric($_POST[FIELD_AMOUNT])) {
        $moneyAmount = floatval($_POST[FIELD_AMOUNT]);
        if ($moneyAmount <= 0) {
            show_error('It has to be more than $0', 403);
        }
    } else {
        show_error('It must be float', 403);
    }
} else {
    show_error('Amount of money should be not empty', 403);
}

$moneyAmount = (floor($moneyAmount * 100)) / 100;
echo $moneyAmount;
$userId = get_user_id();

if ($userId == -1) {
    show_error('You are not logged in', 401);
}

require_once(__DIR__ . "/../utils/database_util.php");

$add_money_statement = mysqli_stmt_init($db_connection);
$query = "INSERT INTO issues(title, fromUserId, fromUsername, toUserId, price, issueType, ts) VALUE ('', 0, 'Sys', ?, ?, 'A', ?)";

if (mysqli_stmt_prepare($add_money_statement, $query)) {
    mysqli_stmt_bind_param($add_money_statement, 'idi',
        $userId,
        $moneyAmount,
        get_current_time_in_mills()
        );
    mysqli_stmt_execute($add_money_statement);
    if (mysqli_stmt_affected_rows($add_money_statement) != 1) {
        show_error_stmt(mysqli_stmt_error($add_money_statement), 501, $db_connection, $add_money_statement);
    }
} else {
    show_error_stmt(mysqli_stmt_errno($add_money_statement), 500, $db_connection, $add_money_statement);
}

mysqli_stmt_close($add_money_statement);
mysqli_close($db_connection);