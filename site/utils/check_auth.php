<?php
require_once(__DIR__ . "/../utils/app_utils.php");

function check_authentication() {
    session_start();
    session_regenerate_id(true);
    return isset($_SESSION[FIELD_USER_ID]) && isset($_SESSION[FIELD_USERNAME]) && isset($_SESSION[FIELD_USER_TYPE]) ? true : false;
}