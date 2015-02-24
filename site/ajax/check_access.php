<?php
require_once(__DIR__ . "/../utils/app_utils.php");
header('Content-Type: application/json');

if (check_authentication()) {
    $result = array();
    $result[FIELD_USERNAME] = $_SESSION[SESSION_USER][FIELD_USERNAME];
    $result[FIELD_USER_TYPE] = $_SESSION[SESSION_USER][FIELD_USER_TYPE];
    $result[FIELD_USER_ID] = $_SESSION[SESSION_USER][FIELD_USER_ID];
    echo json_encode($result);
} else {
    http_response_code(401);
}