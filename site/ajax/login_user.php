<?php
/*
 * Sign in user with username and password pair
 *
 * Gets from POST request username and password
 */

require_once(__DIR__ . "/../utils/database_util.php");
require_once(__DIR__ . "/../utils/app_utils.php");
header('Content-Type: application/json');

$username = null;
$password = null;

$response = array();

// Check username
if (isset($_POST['username'])) {
    $username = $_POST['username'];
} else {
    $response['reason'] = 'Username must not be empty';
}

// Check password
if (isset($_POST['password'])) {
    $password = $_POST['password'];
} else {
    $response['reason'] = 'Password must not be empty';
}

if (isset($response['reason'])) {
    http_response_code(403);
    echo json_encode($response);

    mysqli_close($db_connection);
    exit();
}

$query = "SELECT userId, userType, password FROM `users` WHERE username = ? LIMIT 1;";
$select_user_statement = mysqli_stmt_init($db_connection);
if (mysqli_stmt_prepare($select_user_statement, $query)) {
    mysqli_stmt_bind_param($select_user_statement, 's', $username);
    mysqli_stmt_execute($select_user_statement);
    mysqli_stmt_store_result($select_user_statement);
    if (mysqli_stmt_num_rows($select_user_statement) != 1) {
        http_response_code(401);

        mysqli_stmt_close($select_user_statement);
        mysqli_close($db_connection);
        exit();
    }

    mysqli_stmt_bind_result($select_user_statement, $user_id, $user_type, $password_database);
    mysqli_stmt_fetch($select_user_statement);

    if (!password_verify($password, $password_database)) {
        http_response_code(401);
        mysqli_stmt_close($select_user_statement);
        mysqli_close($db_connection);
        exit();
    } else {
        // Everything is good
        session_start();
        session_regenerate_id(true);
        $_SESSION[FIELD_USER_ID] = $user_id;
        $_SESSION[FIELD_USERNAME] = $username;
        $_SESSION[FIELD_USER_TYPE] = $user_type;

        $response[FIELD_USERNAME] = $username;
        $response[FIELD_USER_TYPE] = $user_type;
        echo json_encode($response);
        exit();
    }
} else {
    http_response_code(401);
}

mysqli_stmt_close($select_user_statement);
mysqli_close($db_connection);