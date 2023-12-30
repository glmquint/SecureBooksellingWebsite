<?php
require_once 'utils/dbUtils.php';
session_start();
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $userid = getUidFromToken($token);
    if($userid){
        if(deleteToken($token)) {
            if(activateAccount($userid)){
                $_SESSION['success'] = "Your account was successfully activated";
                $_SESSION['userid'] = $userid;
            }
            else{
                performLog("Error", "Failed to activate account", array("userid" => $userid, "token" => $token));
                $_SESSION['errorMsg'] = "Something went wrong with your request!";
            }
        }
        else{
            performLog("Error", "Failed to delete activation token", array("userid" => $userid, "token" => $token));
            $_SESSION['errorMsg'] = "Something went wrong with your request!";
        }
    }
    else{
        performLog("Error", "missing user id in activate token", array("userid" => $userid, "token" => $token));
        $_SESSION['errorMsg'] = "Something went wrong with your request!";
    }
}
else {
    performLog("Error", "Activate token not set", array());
    $_SESSION['errorMsg'] = "Something went wrong with your request!";
}



?>

    <!DOCTYPE html>
    <html lang="en">
<head>
    <title>Secure Book selling website</title>
    <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
</head>
<body>

<!-- if the user is logged in, show a message -->
<?php include 'utils/messages.php' ?>

    <a href="index.php">Back to Home</a>
</body>
<?php
