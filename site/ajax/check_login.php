<?php
require_once(__DIR__ . "/../utils/app_utils.php");
session_start();
session_regenerate_id(true);
if (isset($_SESSION[FIELD_USER_ID])) {
    echo 'Logged in as '.$_SESSION[FIELD_USER_ID];
} else {
    echo 'Need to be logged in';
}