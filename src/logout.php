<?php
require_once 'utils/Logger.php';
 // perform logout
    require_once 'utils/dbUtils.php';
    session_start_or_expire();
    performLog("Info", "User logged out", array("email" => $_SESSION['email']));
    // reset username
    $_SESSION['email'] = null;
    // remove remember me cookie
    if (isset($_COOKIE['rememberme'])) {
        unset($_COOKIE['rememberme']);
        setcookie('rememberme', '', time() - 3600, '/', '', true, true);
    }

    // change session id to prevent session fixation
    session_regenerate_id();
    // redirect the user to the index page
    header('Location: index.php');
    exit();
    ?>
