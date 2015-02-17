<?php


function generate_new_password() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $len = strlen($characters);
    $random_password = '';
    for ($i = 0; $i < 10; $i++) {
        $random_password .= $characters[rand(0, $len)];
    }
    return $random_password;
}