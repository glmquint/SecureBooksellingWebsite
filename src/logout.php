<?php
 // perform logout
    require_once 'utils/dbUtils.php';
    session_start_or_expire();
    // reset username
    $_SESSION['username'] = null;
    // change session id to prevent session fixation
    session_regenerate_id();
    // redirect the user to the index page
    header('Location: index.php');
    exit();
    ?>
