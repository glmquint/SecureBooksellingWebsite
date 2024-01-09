<?php
require_once 'utils/Logger.php';
require_once 'utils/dbUtils.php';
session_start_or_expire();

// Check if the user is logged in and if the form was submitted
// Also check if the email and passwords are strings for type juggling
if (isset($_POST['OldPassword']) && isset($_POST['NewPassword']) && isset($_SESSION['email'])
    && is_string($_POST['OldPassword']) && is_string($_POST['NewPassword']) && is_string($_SESSION['email'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] != $_SESSION['csrf_token']) {
        performLog("Error", "CSRF token mismatch", array("token" => $_POST['csrf_token']));
        $_SESSION['errorMsg'] = "Something went wrong with your request";
        header('Location: index.php');
        exit();
    }
    // Get username and password from the form submitted by the user
    $email = $_SESSION['email'];
    $OldPassword = $_POST['OldPassword'] ?? '';
    $NewPassword = $_POST['NewPassword'] ?? '';
    // use == instead of === to have type juggling in case of missing or invalid input
    if($OldPassword == '' || $NewPassword == ''){
        performLog("Warning", "Empty password field in change", array("email" => $_SESSION['email']));
        $_SESSION["errorMsg"] = "Empty password field";
    } elseif($OldPassword === $NewPassword){
        performLog("Warning", "Old and new password are the same", array("email" => $_SESSION['email']));
        $_SESSION["errorMsg"] = "Old and new password are the same";
    } elseif (verifyLogin($email, $OldPassword)) { // Use the VerifyLogin function to check if the user has input the correct OldPassword
        // Change the password
        if(changePassword($email, $NewPassword)){

            $subject = "Password changed successfully";
            $message = "Your password has been changed successfully. You can now login with your new credentials\n";

            // Additional headers
            $headers = "From: " . $_ENV['NO_REPLY_EMAIL'] . "\r\n";
            // Send email
            $mailSuccess = mail($email, $subject, $message, $headers);

            if ($mailSuccess) {
                performLog("Info", "Password changed correctly, confirmation email sent", array("email" => $email));
                $_SESSION['success'] = "Password changed successfully, a confirmation email has been sent to your email";
            } else {
                $_SESSION['success'] = "Password changed successfully, but there was an error sending the confirmation email";
                performLog("Error", "Password changed correctly, confirmation email not sent", array("mail" => $_POST['email']));

            }
            header('Location: logout.php');
            exit();
        }
        else{
            performLog("Warning", "Password change failed", array("email" => $email));
            $_SESSION['errorMsg']="Something went wrong with your request!";
        }

    } else {
        // Incorrect login
        performLog("Warning", "Verify Login failed during password change", array("email" => $email));
        $_SESSION['errorMsg']="Something went wrong with your request!";
    }
}

if (!isset($_SESSION['email'])) {
    performLog("Warning", "User not logged in", array());
    $_SESSION['errorMsg'] = "You need to login first!";
    header('Location: login.php?redirect=changepassword.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Secure Book selling website</title>
    <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
    <script src="utils/checkPasswordStrength.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
</head>
<body>

<header>
    <h1>Change Password</h1>
    <nav>
        <a href="index.php">Back to Home</a>
    </nav>
</header>
<br>
    <?php include 'utils/messages.php' ?>
    <form method="post" action="changepassword.php">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?>" readonly="readonly" >
        <div class="input-group">
            <label>Old password</label>
            <label>
                <input type="password" required="required" name="OldPassword">
            </label>
        </div>
        <div class="input-group">
            <label>New Password</label>
            <label>
                <input type="password" name="NewPassword" id="NewPassword" required="required" oninput=checkPasswordStrength(document.getElementById('NewPassword').value)>
            </label>
        </div>
        <div class="input-group">
            <button type="submit" id="btn" name="btn">Change</button>
        </div>
    </form>
    <label for="strength">password strength: </label>
    <progress id="strength" value="0" max="4"> password strength </progress>
    <p id="warning"></p>
    <p id="suggestions"></p>


</body>