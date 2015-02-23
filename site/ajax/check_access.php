<?php
require_once(__DIR__ . "/../utils/app_utils.php");
header('Content-Type: application/json');

if (check_authentication()) {
    $result = array();
    $result['username'] = $_SESSION[SESSION_USER][FIELD_USERNAME];
    $result['userType'] = $_SESSION[SESSION_USER][FIELD_USER_TYPE];
    echo json_encode($result);
} else {
    http_response_code(401);
}