<?php
require_once 'utils/Logger.php';
require_once 'utils/dbUtils.php';
session_start_or_expire();

// Check if the user is logged in and if the form was submitted
// Also check if the email and passwords are strings for type juggling
if (isset($_POST['OldPassword']) && isset($_POST['NewPassword']) && isset($_SESSION['email'])
    && is_string($_POST['OldPassword']) && is_string($_POST['NewPassword']) && is_string($_SESSION['email'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] != $_SESSION['csrf_token']) {
        $_SESSION['errorMsg'] = "CSRF token mismatch";
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
            performLog("Info", "Password changed correctly", array("email" => $email));
            $_SESSION['success'] = "Password changed successfully";
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

<!-- if the user is logged in, show a message -->
    <?php include 'utils/messages.php' ?>

    <!-- show a form to login -->
<header>
    <h1>Change Password</h1>
    <nav>
        <a href="index.php">Back to Home</a>
    </nav>
</header>
<hr>
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