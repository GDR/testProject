<?php
require_once(__DIR__ . "/app_utils.php");

// Function adds wallet if it's not exists
function add_wallet($user_id, $db_connection)
{
    $query_add = "INSERT IGNORE INTO wallets(userId, money, blocked, paid, ts) VALUE (?, 0, 0, 0, 0);";
    $add_wallet_statement = mysqli_stmt_init($db_connection);
    if (mysqli_stmt_prepare($add_wallet_statement, $query_add)) {
        mysqli_stmt_bind_param($add_wallet_statement, 'i', $user_id);
        mysqli_stmt_execute($add_wallet_statement);
        if (mysqli_stmt_affected_rows($add_wallet_statement) != 1) {
            show_error_stmt(mysqli_stmt_error($add_wallet_statement), 500, $db_connection, $add_wallet_statement);
        }
        mysqli_stmt_close($add_wallet_statement);
    } else {
        show_error_stmt(mysqli_stmt_error($add_wallet_statement), 500, $db_connection, $add_wallet_statement);
    }
}

function calc_user_wallet($db_connection, $user_id, $user_type)
{
    $get_wallet_statement = mysqli_stmt_init($db_connection);
    $balance = 0;
    $blocked = 0;
    $paid = 0;
    $ts = 0;
    $newTs = 0;

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

        if ($user_type == USER_CUSTOMER) {
            // At first we have to calc adding money to user's balance and deleted tasks

            // Calc added money
            $query = "SELECT price, ts FROM issues WHERE issueType = 'A' AND toUserId = ? AND ts > ?;";
            $get_added_balance_statement = mysqli_stmt_init($db_connection);
            if (mysqli_stmt_prepare($get_added_balance_statement, $query)) {
                mysqli_stmt_bind_param($get_added_balance_statement, 'ii', $user_id, $ts);
                mysqli_stmt_execute($get_added_balance_statement);
                mysqli_stmt_bind_result($get_added_balance_statement, $added, $tempTs);
                while (mysqli_stmt_fetch($get_added_balance_statement)) {
                    $balance += $added;
                    $newTs = max($newTs, $tempTs);
                }
            } else {
                show_error_stmt(mysqli_stmt_error($get_added_balance_statement), 500, $db_connection, $get_added_balance_statement);
            }
            mysqli_stmt_close($get_added_balance_statement);
            // Calc deleted tasks

            $query = "SELECT price, commission, ts, tsEdited FROM issues WHERE issueType = 'D' AND fromUserId = ? AND tsEdited > ?;";

            $get_deleted_tasks_statement = mysqli_stmt_init($db_connection);
            if (mysqli_stmt_prepare($get_deleted_tasks_statement, $query)) {
                mysqli_stmt_bind_param($get_deleted_tasks_statement, 'ii', $user_id, $ts);
                mysqli_stmt_execute($get_deleted_tasks_statement);
                mysqli_stmt_bind_result($get_deleted_tasks_statement, $price, $commission, $db_ts, $db_tsEdited);
                while (mysqli_stmt_fetch($get_deleted_tasks_statement)) {
                    if ($db_ts <= $ts) {
                        $sum = $price + $commission;
                        $balance += $sum;
                        $blocked -= $sum;
                    }
                    $newTs = max($newTs, $db_ts, $db_tsEdited);
                }
            } else {
                show_error_stmt(mysqli_stmt_error($get_deleted_tasks_statement), 500, $db_connection, $get_deleted_tasks_statement);
            }
            mysqli_stmt_close($get_deleted_tasks_statement);

            // Calc opened tasks
            $taskToBlock = array();
            $query = "SELECT id, price, commission, ts FROM issues WHERE (issueType = 'O' OR issueType = 'C') AND fromUserId = ? AND ts > ?;";
            $get_added_tasks_statement = mysqli_stmt_init($db_connection);
            if (mysqli_stmt_prepare($get_added_tasks_statement, $query)) {
                mysqli_stmt_bind_param($get_added_tasks_statement, 'ii', $user_id, $ts);
                mysqli_stmt_execute($get_added_tasks_statement);
                mysqli_stmt_bind_result($get_added_tasks_statement,$id, $price, $commission, $tempTs);
                while (mysqli_stmt_fetch($get_added_tasks_statement)) {
                    $sum = $price + $commission;
                    if ($balance - $sum < 0) {
                        array_push($taskToBlock, $id);
                    } else {
                        $balance -= $sum;
                        $blocked += $sum;
                    }
                    $newTs = max($newTs, $tempTs);
                }
            } else {
                show_error_stmt(mysqli_stmt_error($get_added_tasks_statement), 500, $db_connection, $get_added_tasks_statement);
            }
            mysqli_stmt_close($get_added_tasks_statement);
            // Delete tasks which takes more than our limit

            $query = "UPDATE issues SET issueType = 'D', tsEdited = ? WHERE id = ? AND issueType = 'O';";
            $set_deleted_tasks_statement = mysqli_stmt_init($db_connection);
            if (mysqli_stmt_prepare($set_deleted_tasks_statement, $query)) {
                foreach ($taskToBlock as $id) {
                    mysqli_stmt_bind_param($set_deleted_tasks_statement, 'ii',
                        get_current_time_in_mills(),
                        $id
                        );
                    mysqli_stmt_execute($set_deleted_tasks_statement);
                }
            } else {
                show_error_stmt(mysqli_stmt_error($set_deleted_tasks_statement), 500, $db_connection, $set_deleted_tasks_statement);
            }

            // Calc completed tasks

            $query = "SELECT price, commission, tsEdited FROM issues WHERE issueType='C' AND fromUserId = ? AND tsEdited > ?;";
            $get_completed_tasks_statement = mysqli_stmt_init($db_connection);
            if (mysqli_stmt_prepare($get_completed_tasks_statement, $query)) {
                mysqli_stmt_bind_param($get_completed_tasks_statement, 'ii', $user_id, $ts);
                mysqli_stmt_execute($get_completed_tasks_statement);
                mysqli_stmt_bind_result($get_completed_tasks_statement, $price, $commission, $tempTs);
                while (mysqli_stmt_fetch($get_completed_tasks_statement)) {
                    $sum = $price + $commission;
                    $blocked -= $sum;
                    $paid += $sum;
                    $newTs = max($newTs, $tempTs);
                }
            }
        }
        if ($user_type == USER_PERFORMER) {
            $query = "SELECT price, tsEdited FROM issues WHERE issueType='C' AND toUserId = ? AND tsEdited > ?;";
            $get_completed_tasks_statement = mysqli_stmt_init($db_connection);
            if (mysqli_stmt_prepare($get_completed_tasks_statement, $query)) {
                mysqli_stmt_bind_param($get_completed_tasks_statement, 'ii', $user_id, $ts);
                mysqli_stmt_execute($get_completed_tasks_statement);
                mysqli_stmt_bind_result($get_completed_tasks_statement, $price, $tempTs);
                while (mysqli_stmt_fetch($get_completed_tasks_statement)) {
                    $balance += $price;
                    $newTs = max($newTs, $tempTs);
                }
            }
        }

    } else {
        show_error_stmt(mysqli_stmt_error($get_wallet_statement), 500, $db_connection, $get_wallet_statement);
    }
    $balance = round($balance * 100) / 100;
    $blocked = round($blocked * 100) / 100;
    $paid = round($paid * 100) / 100;

    $query = "UPDATE wallets SET money = ?, blocked = ?, paid = ?, ts = ? WHERE userId = ?";
    $update_wallet_statement = mysqli_stmt_init($db_connection);
    if (mysqli_stmt_prepare($update_wallet_statement, $query)) {
        mysqli_stmt_bind_param($update_wallet_statement, 'iiiii', $balance, $blocked, $paid, $newTs, $user_id);
    } else {
        show_error_stmt(mysqli_stmt_error($update_wallet_statement), 500, $db_connection, $update_wallet_statement);
    }
    mysqli_stmt_close($update_wallet_statement);
    return array(
        'balance' => $balance,
        'blocked' => $blocked,
        'paid' => $paid);
}
