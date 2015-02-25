<?php
require_once(__DIR__ . "/app_utils.php");

function calc_user_wallet($db_connection, $user_id, $user_type)
{

    function add_wallet($user_id, $db_connection) {
        $query_add = "INSERT IGNORE INTO wallets(userId, money, blocked, paid, ts) VALUE (?, 0, 0, 0, 0);";
        $add_wallet_statement = mysqli_stmt_init($db_connection);
        if (mysqli_stmt_prepare($add_wallet_statement, $query_add)) {
            mysqli_stmt_bind_param($add_wallet_statement, 'i', $user_id);
            mysqli_stmt_execute($add_wallet_statement);
            if (mysqli_stmt_affected_rows($add_wallet_statement) != 1) {
                show_error_stmt('', 500, $db_connection, $add_wallet_statement);
            }
            mysqli_stmt_close($add_wallet_statement);
        } else {
            show_error_stmt('', 500, $db_connection, $add_wallet_statement);
        }
    }

    function block_task($task_id, $db_connection) {
        $query_block = "UPDATE wa"
    }

    $get_wallet_statement = mysqli_stmt_init($db_connection);
    $balance = 0;
    $blocked = 0;
    $paid = 0;
    $ts = 0;
    // Try to get wallet from DB
    $query = "SELECT money, blocked, paid, ts FROM wallets WHERE userId = ?;";
    if (mysqli_stmt_prepare($get_wallet_statement, $query)) {
        mysqli_stmt_bind_param($get_wallet_statement, 'i', $user_id);
        mysqli_stmt_execute($get_wallet_statement);
        mysqli_stmt_store_result($get_wallet_statement);
        if (mysqli_stmt_num_rows($get_wallet_statement) != 1) {
            mysqli_stmt_close($get_wallet_statement);
            // If there is no user's wallet in wallets db yet than lets try to create one
            add_wallet($user_id, $db_connection);
        } else {
            // Otherwise lets sync info from wallet
            mysqli_stmt_bind_result($get_wallet_statement, $balance, $paid, $blocked, $ts);
            mysqli_stmt_fetch($get_wallet_statement);
        }

        mysqli_stmt_close($get_wallet_statement);

        // So right now we have information about
        // user's account and so let's try to update this data

        $get_tasks_statement = mysqli_stmt_init($db_connection);
        $query = "SELECT taskId, fromUserId, toUserId, price, comission, taskType, sysblock, ts FROM tasks WHERE (fromUserId = ? OR toUserId = ?) AND (ts > ?) ORDER BY ts ASC;";
        if (mysqli_stmt_prepare($get_tasks_statement, $query)) {
            mysqli_stmt_bind_param($get_tasks_statement, 'iii', $user_id, $user_id, $ts);
            mysqli_stmt_execute($get_tasks_statement);
            mysqli_stmt_bind_result($get_tasks_statement,
                $task_id,
                $task_from_user_id,
                $task_to_user_id,
                $task_price,
                $task_commission,
                $task_type,
                $task_sys_block);
            if ($user_type == USER_CUSTOMER) {
                while (mysqli_stmt_fetch($get_tasks_statement)) {
                    $sum = $task_commission + $task_price;
                    if ($task_sys_block == SYSBLOCK_FALSE) {
                        if ($task_type == TASK_OPENED) {
                            // The only bad thing which could happen if current amount of money
                            // is more than user has
                            $blocked += $sum;
                            if ($balance < $blocked) {
                                // If it happened than we have to remove this task from list of opened
                                $blocked -= $sum;

                            }
                        }
                        if ($task_type == TASK_COMPLETED) {
                            $balance -= $sum;
                            $blocked -= $sum;
                            $paid += $sum;
                        }
                        if ($task_type == TASK_DELETED) {
                            $blocked -= $sum;
                        }
                    }

                    if ($task_sys_block == SYSBLOCK_TRUE) {

                    }
                }
            }
            if ($user_type == USER_PERFORMER) {

            }
        } else {
            show_error_stmt('', 500, $db_connection, $get_tasks_statement);
        }
    } else {
        show_error_stmt('', 500, $db_connection, $get_wallet_statement);
    }
    return array($balance, $blocked, $paid);
}
