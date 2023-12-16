<?php
require_once 'utils/Logger.php';
 // perform logout
    session_start();
    performLog("Info", "User logged out", "with username " . $_SESSION['username'] . " from IP " . $_SERVER['REMOTE_ADDR']);
    // reset username
    $_SESSION['username'] = null;
    // change session id to prevent session fixation
    session_regenerate_id();

    // redirect the user to the index page
    header('Location: index.php');
    exit();
    ?>
