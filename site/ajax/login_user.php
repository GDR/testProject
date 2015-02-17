<?php
/*
 * Sign in user with username and password pair
 *
 * Gets from POST request username and password
 */

require_once(__DIR__ . "/../utils/database_util.php");
header('Content-Type: application/json');
$result = array('status' => 'ok');

$username = null;
$password = null;
error_log($_POST['username']." ".$_POST['password']);
// Check username
if (isset($_POST['username'])) {
    $username = $_POST['username'];
} else {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}
// Check password
if (isset($_POST['password'])) {
    $password = $_POST['password'];
} else {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

$query = "SELECT userId, password FROM `users` WHERE username = ? LIMIT 1;";
$select_user_statement = mysqli_stmt_init($db_connection);
if (mysqli_stmt_prepare($select_user_statement, $query)) {
    if (!mysqli_stmt_bind_param($select_user_statement, 's', $username)) {
        header("HTTP/1.1 401 Unauthorized");
        exit();
    }
    if (!mysqli_stmt_execute($select_user_statement)) {
        header("HTTP/1.1 401 Unauthorized");
        exit();
    }

    mysqli_stmt_store_result($select_user_statement);

    if (mysqli_stmt_num_rows($select_user_statement) != 1) {
        header("HTTP/1.1 401 Unauthorized");
        exit();
    }

    mysqli_stmt_bind_result($select_user_statement, $userId_database, $password_database);
    mysqli_stmt_fetch($select_user_statement);

    if (!password_verify($password, $password_database)) {
        header("HTTP/1.1 401 Unauthorized");
        exit();
    } else {
        session_start();
        session_regenerate_id(true);
        $_SESSION['userId'] = $userId_database;
    }
}

mysqli_stmt_close($select_user_statement);
mysqli_close($db_connection);