<?php
    require_once 'utils/dbUtils.php';
    session_start_or_expire();
    require_once 'utils/Logger.php';
    if (isset($_GET['token'])) {
    $token = $_GET['token'];
        $userid = getUidFromToken($token);
        if($userid!=-1){
            if(deleteToken($token)) {
                $_SESSION['message'] = "You can now reset your password";
                $_SESSION['$userid'] = $userid;
                performLog("Info", "Request to reset password accepted", array("userid" => $userid));
                header('Location: resetpassword-token.php');
                exit();
            }
            else{
                performLog("Error", "Failed to delete token", array("userid" => $userid));
                $_SESSION['message'] = "Failed to reset password";
            }
        }
        else{
            // If 'token' is not wrong, return a 404 error
            $_SESSION['message'] = "Failed to reset password";
            performLog("Warning", "Invalid token", array("token" => $token));
        }
    }
    else {
            performLog("Warning", "Token not set", array());
            // If 'token' is not set, return a 404 error
            $_SESSION['message'] = "Something went wrong with your request!";

    }

    if(isset($_POST["newPassword"]) && isset($_POST["newPasswordRetype"])){
        $newPassword = $_POST["newPassword"];
        $newPasswordRetype = $_POST["newPasswordRetype"];
        if($newPassword == $newPasswordRetype && $newPassword != "" && $newPasswordRetype != ""){
            $userid = $_SESSION['$userid'];
            if(changePasswordById($userid, $newPassword)){
                session_destroy();
                header('Location: index.php');
                exit();
            }
            else{
                performLog("Error", "Failed to change password", array("userid" => $userid));
                $_SESSION['message'] = "Failed to reset password";
            }
        }
        else{
            $_SESSION['message'] = "Passwords do not match";
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Secure Book selling website</title>
    <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
    <script src="utils/checkPasswordStrength.js"></script>
</head>
<body>

<!-- if the user is logged in, show a message -->

    <!-- show a form to login -->
    <a href="index.php">Back to Home</a>
    <h1>Password reset</h1>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="error success">
            <h3>
                <?php
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                ?>
            </h3>
        </div>

    <?php endif ?>
    <form method="post" action="resetpassword-token.php">
        <div class="input-group">
            <label>New password</label>
            <label>
                <input type="password" required="required" id="newPassword" name="newPassword" oninput=checkPasswordStrength(document.getElementById('newPassword').value)>
            </label>
        </div>
        <div class="input-group">
            <label>Rewrite password</label>
            <label>
                <input type="password" required="required" id="newPasswordRetype" name="newPasswordRetype" oninput=checkPasswordStrength(document.getElementById('newPassword').value)>
            </label>
        </div>
        <div class="input-group">
            <button type="submit" id="btn" name="resetPassword_btn">Login</button>
        </div>
    </form>
    <label for="strength">password strength: </label>
    <progress id="strength" value="0" max="4"> password strength </progress>
    <p id="warning"></p>
    <p id="suggestions"></p>



</body>
