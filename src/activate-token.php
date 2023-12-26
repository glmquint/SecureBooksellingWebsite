<?php
require_once 'utils/dbUtils.php';
session_start();
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $userid = getUidFromToken($token);
    if($userid!=-1){
        if(deleteToken($token)) {
            if(activateAccount($userid)){
                $_SESSION['success'] = "Your account was successfully activated";
                $_SESSION['userid'] = $userid;
                header('Location: activate-token.php');
                exit();
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
    if(!isset($_SESSION['success'])) {
        performLog("Error", "Activate token not set", array());
        $_SESSION['errorMsg'] = "Something went wrong with your request!";
    }

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
<?php if (isset($_SESSION['errorMsg'])): ?>
    <div class="error warning">
        <h3>
            <?php
            echo $_SESSION['errorMsg'];
            unset($_SESSION['errorMsg']);
            ?>
        </h3>
    </div>
<?php endif ?>
<?php if (isset($_SESSION['success'])): ?>
    <div class="error success">
        <h3>
            <?php
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </h3>
    </div>

<?php endif ?>
    <a href="index.php">Back to Home</a>
</body>
<?php
