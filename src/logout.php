<?php
 // perform logout
    session_start();
    // reset username
    $_SESSION['username'] = null;
    // redirect the user to the index page
    header('Location: index.php');
    exit();
    ?>
