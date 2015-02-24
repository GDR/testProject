<?php

DEFINE("USER_CUSTOMER", '0');
DEFINE("USER_PERFORMER", '1');

DEFINE("USERNAME", "username");
DEFINE("PASSWORD", "password");
DEFINE("USERTYPE", "userType");

DEFINE("FIELD_TITLE", "title");
DEFINE("FIELD_PRICE", "price");
DEFINE("FIELD_PASSWORD", 'password');

DEFINE("FIELD_USERNAME", 'username');
DEFINE("FIELD_USER_ID", 'userId');
DEFINE("FIELD_USER_TYPE", 'userType');
DEFINE("FIELD_TASK_ID", 'taskId');

DEFINE("REASON", 'reason');

DEFINE("SESSION_USER", 'user');

DEFINE("FIELD_OFFSET", 'offset');

DEFINE("GET_TASK_LIMIT", 20);

DEFINE("COMMISSION", 0.20);

function check_authentication()
{
    if (!session_id()) {
        session_start();
    }
    return isset($_SESSION[SESSION_USER]);
}

function get_user_id() {
    if (check_authentication()) {
        return $_SESSION[SESSION_USER][FIELD_USER_ID];
    }
    return -1;
}

function get_username() {
    if (check_authentication()) {
        return $_SESSION[SESSION_USER][FIELD_USERNAME];
    }
    return null;
}

function get_user_type() {
    if (check_authentication()) {
        return $_SESSION[SESSION_USER][FIELD_USER_TYPE];
    }
    return -1;
}

function show_error($error, $http_code) {
    http_response_code($http_code);
    die (json_encode(array(REASON => $error)));
}

function show_error_db($error, $http_code, $db) {
    mysqli_close($db);
    show_error($error, $http_code);
}

function show_error_stmt($error, $http_code, $db, $stmt) {
    mysqli_stmt_close($stmt);
    show_error_db($error, $http_code, $db);
}

function get_current_time_in_mills() {
    return (int) round(microtime(true) * 10000);
}