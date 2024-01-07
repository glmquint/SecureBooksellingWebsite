<?php
require_once 'utils/dbUtils.php';
session_start();
// Check if the token is set and is a string for type juggling
if (isset($_GET['token']) && is_string($_GET['token'])) {
    // Check if the token is valid
    $token = hex2bin($_GET['token']);
    if(!$token || !is_string($token)){
        performLog("Error", "Invalid token", array("token" => $_GET['token']));
        $_SESSION['errorMsg'] = "Something went wrong with your request!";
        header("Location: activate-token.php");
        exit();
    }
    // Get the user id from the token, if it exists and is valid it is deleted and the account is activated
    $userid = getUidFromToken($token);
    if($userid){
        if(deleteToken($token)) {
            if(activateAccount($userid)){
                $_SESSION['success'] = "Your account was successfully activated";
                $_SESSION['userid'] = $userid;
                // Can log the token because it is deleted and randomBytes is cryptographically secure
                performLog("Info", "Account activated", array("userid" => $userid, "token" => $_GET['token']));
            }
            else{
                // Can log the token because it is deleted and randomBytes is cryptographically secure
                performLog("Error", "Failed to activate account", array("userid" => $userid, "token" => $_GET['token']));
                $_SESSION['errorMsg'] = "Something went wrong with your request!";
            }
        }
        else{
            // Do not log the token since it is not deleted, but it is in the database
            performLog("Error", "Failed to delete activation token", array("userid" => $userid));
            $_SESSION['errorMsg'] = "Something went wrong with your request!";
        }
    }
    else{
        // Can log the token since it is not a valid one (not in the DB)
        // If the attacker exfiltrate the log to see the token not present in the DB, good luck finding
        // a valid one by removing all the token that are not present (16 bytes)
        performLog("Error", "missing user id in activate token", array("userid" => $userid, "token" => $_GET['token']));
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
