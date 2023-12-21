<?php
require_once 'utils/Logger.php';
require_once 'utils/dbUtils.php';
session_start_or_expire();


if (isset($_POST['OldPassword']) && isset($_POST['NewPassword'])&& isset($_SESSION['email'])) {
    // Get username and password from the form submitted by the user
    $OldPassword = $_POST['OldPassword'] ?? '';
    $NewPassword = $_POST['NewPassword'] ?? '';
    if($OldPassword == '' || $NewPassword == ''){
        performLog("Warning", "Empty password field in change", array("email" => $_SESSION['email']));
        $_SESSION["warning"] = "Empty password field";
        header('Location: changepassword.php');
        exit();
    }
    if($OldPassword == $NewPassword){
        performLog("Warning", "Old and new password are the same", array("email" => $_SESSION['email']));
        $_SESSION["warning"] = "Old and new password are the same";
        header('Location: changepassword.php');
        exit();
    }
    $email = $_SESSION['email'];
    // Assume $username and $password are the submitted credentials
    // You need to replace this with your actual login verification logic
    if (verifyLogin($email, $OldPassword)) {
        // Hash the password using bcrypt
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
<?php if (isset($_SESSION['success'])): ?>
    <div class="success">
        <h3>
            <?php
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </h3>
        <a href="index.php">Back to Home</a>
    </div>
<?php else: ?>
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
    <!-- show a form to login -->
    <a href="index.php">Back to Home</a>
    <h1>Change Password</h1>
    <form method="post" action="changepassword.php">
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

<?php endif ?>

</body>