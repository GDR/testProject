<?php
/*
 * Registering user with username and password pair
 *
 * Gets from POST request username and password
 */

require_once(__DIR__."/../utils/database_util.php");
header('Content-Type: application/json');
$result = array('status' => 'ok');

// Check username
if (isset($_POST['username'])) {
    $username = $_POST['username'];
    if (!ctype_alnum($username)) {
        $result['status'] = 'fail';
    }
    if (strlen($username) <= 5) {
        $result['status'] = 'fail';
    }
} else {
    $result['status'] = 'fail';
}
// Check password
if (isset($_POST['password'])) {
    $password = $_POST['password'];
    if (strlen($password) <= 5) {
        $result['status'] = 'fail';
    }
} else {
    $result['status'] = 'fail';
}

if ($result['status'] == 'fail') {
    die (json_decode($result));
}

$username_db = mysqli_real_escape_string($db_connection, $username);
$password_db = password_hash($password, PASSWORD_BCRYPT);

$add_user_statement = mysqli_prepare($db_connection, "INSERT INTO `users` (`username`, `password`) VALUES (?, ?);");

mysqli_stmt_bind_param($add_user_statement, 'ss', $username_db, $password_db);
mysqli_stmt_execute($add_user_statement);
if (mysqli_stmt_affected_rows($add_user_statement) != 1) {
    $result['status'] = 'fail';
}
echo json_encode($result);
mysqli_stmt_close($add_user_statement);
mysqli_close($db_connection);