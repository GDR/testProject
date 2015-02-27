<?php
require_once (__DIR__."/../utils/database_util.php");
require_once (__DIR__."/../utils/app_utils.php");
require_once (__DIR__."/../utils/wallet_utils.php");

print_r(calc_user_wallet($db_connection,$_GET['userid'], $_GET['usertype']));