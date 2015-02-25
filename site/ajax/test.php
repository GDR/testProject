<?php
function a() {

    function b() {
        return 'a';
    }
    echo b();
    return 'a';
}
a();