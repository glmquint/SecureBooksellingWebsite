<?php

require_once 'utils/dbUtils.php';
require_once 'utils/Logger.php';
session_start_or_expire();



if(isset($_POST['username'])){
    $token = random_int(100000, 999999999);
    $username = $_POST['username'];
    $userArray = getUser($username);
    if(count($userArray) > 0){
        if(!$userArray["active"]){
            performLog("Warning", "Reset password for disabled user", array("username" => $username));
            header('Location: resetpassword.php');
            $_SESSION['success'] = "Your account is disabled. Please activate your account first.";
            exit();
        }
        $userId = $userArray["id"];
        $email = $userArray["email"];
        if(saveToken($token, $userId, 5)) {
            $subject = "Reset Email";
            $message = "This is a reset email. Click on the link to reset your password\n"
                . "http://localhost:63342/snh-securebooksellingwebsite/src/resetpassword-token.php?token=" . strval($token);

            // Additional headers
            $headers = "From: noreply@localhost.com";

            // Send email
            $mailSuccess = mail($email, $subject, $message, $headers);

            if ($mailSuccess) {
                performLog("Info", "Password reset link sent to user", array("username" => $username));
                $_SESSION['success'] = "Password reset link sent to your email";
                header('Location: resetpassword.php');
                exit();
            } else {
                performLog("Warning", "Failed to send email", array("username" => $username, "mail" => $email ));
                echo "Failed to send email.";
            }

        }
        else{
            performLog("Error", "Failed to save token", array("username" => $username, "mail" => $email ));
            echo "something went wrong";
        }
    }
    else{
        //This is a fake success message is to avoid account enumeration
        performLog("Warning", "Reset password for not existing user", array("username" => $username));
        $_SESSION['success'] = "Password reset link sent to your email";
        header('Location: resetpassword.php');
        exit();
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
<?php if (isset($_SESSION['success'])): ?>
    <div class="error success">
        <h3>
            <?php
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </h3>
        <a href="index.php">Back to Home</a>
    </div>
    <!-- else, show a link to the resetpassword page -->
<?php else: ?>

    <!-- show a form to login -->
    <a href="index.php">Back to Home</a>
    <h1>Reset Password</h1>
    <form method="post" action="resetpassword.php">
        <div class="input-group">
            <label>Username</label>
            <label>
                <input type="text" name="username">
            </label>
        </div>
        <div class="input-group">
            <button type="submit" name="reset_btn">Reset</button>
        </div>
    </form>

<?php endif ?>
