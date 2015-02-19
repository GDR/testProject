<?php
require_once(__DIR__ . "/../utils/app_utils.php");
session_start();
session_regenerate_id(true);

header('Content-Type: application/json');
$result = array();
if (isset($_SESSION[FIELD_USER_ID]) && isset($_SESSION[FIELD_USERNAME]) && isset($_SESSION[FIELD_USER_TYPE])  ) {
    $result['username'] = $_SESSION[FIELD_USERNAME];
    $result['userType'] = $_SESSION[FIELD_USER_TYPE];
} else {
    http_response_code(401);
}
echo json_encode($result);