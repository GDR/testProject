<?php
/*
 * Sign in user with username and password pair
 *
 * Gets from POST request username and password
 */

require_once(__DIR__ . "/../utils/database_util.php");
require_once(__DIR__ . "/../utils/app_utils.php");

header('Content-Type: application/json');

// Initializing values to login user
$username = null;
$password = null;

// Array for response
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
    // Trying to find user in database with username which we got in POST request
    mysqli_stmt_bind_param($select_user_statement, 's', $username);
    mysqli_stmt_execute($select_user_statement);
    mysqli_stmt_store_result($select_user_statement);

    // Check if we found some user
    if (mysqli_stmt_num_rows($select_user_statement) != 1) {
        // If we didn't find than response with 401 Auth fail
        http_response_code(401);

        mysqli_stmt_close($select_user_statement);
        mysqli_close($db_connection);
        exit();
    }
    // In other way if we found user lets check his password
    mysqli_stmt_bind_result($select_user_statement, $user_id, $user_type, $password_database);
    mysqli_stmt_fetch($select_user_statement);

    if (!password_verify($password, $password_database)) {
        // If it's not equal than response with 401 Auth fail
        http_response_code(401);
        mysqli_stmt_close($select_user_statement);
        mysqli_close($db_connection);
        exit();
    } else {
        // Otherwise lets store credentials in session
        session_start();

        $_SESSION[SESSION_USER] = array(
            FIELD_USER_ID => $user_id,
            FIELD_USERNAME => $username,
            FIELD_USER_TYPE => $user_type
        );

        $response[FIELD_USERNAME] = $username;
        $response[FIELD_USER_TYPE] = $user_type;
        die (json_encode($response));
    }
} else {
    http_response_code(401);
}

mysqli_stmt_close($select_user_statement);
mysqli_close($db_connection);