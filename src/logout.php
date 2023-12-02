<?php
 // perform logout
    session_start();
    // remove all session variables
    session_unset();
    // destroy the session
    session_destroy();
    // redirect the user to the index page
    header('Location: index.php');
    exit();
    ?>
