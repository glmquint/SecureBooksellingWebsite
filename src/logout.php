<?php
require_once '../utils/Logger.php';
 // perform logout
    require_once '../utils/dbUtils.php';
    session_start_or_expire();
    performLog("Info", "User logged out", array("email" => $_SESSION['email']));
    // reset username
    unset($_SESSION['email']);
    // remove remember me cookie
    if (isset($_COOKIE['rememberme'])) {
        unset($_COOKIE['rememberme']);
        setcookie('rememberme', '', time() - 3600, '/', '', true, true); // this resets the cookie
    }

    // change session id to prevent session fixation
    session_regenerate_id();

    performLog("Info", "User logged out", array("email" => $_SESSION['email']));
    if(!isset($_SESSION['success'])){
        $_SESSION['success'] = "You are now logged out";
    } else {
        $_SESSION['success'] =  $_SESSION['success'] . ". You are now logged out";
    }
    header('Location: index.php');
    exit();
    ?>
