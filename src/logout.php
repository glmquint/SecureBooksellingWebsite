<?php
 // perform logout
    session_start();
    // reset username
    $_SESSION['username'] = null;
    // change session id to prevent session fixation
    session_regenerate_id();
    // redirect the user to the index page
    header('Location: index.php');
    exit();
    ?>
