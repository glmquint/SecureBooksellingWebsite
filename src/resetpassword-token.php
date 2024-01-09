<?php
    require_once 'utils/dbUtils.php';
    session_start_or_expire();
    require_once 'utils/Logger.php';
    if (!isset($_REQUEST['token']) || $_REQUEST['token'] === "") {
        performLog("Warning", "Token not set", array());
        // If 'token' is not set, return a 404 error
        $_SESSION['errorMsg'] = "Something went wrong with your request! Try to reset your password again.";
        header('Location: index.php');
        exit();
    }

    if(isset($_POST["newPassword"]) && isset($_POST["newPasswordRetype"]) && isset($_POST["token"])){
        // Check if the passwords are strings for type juggling
        if (!is_string($_POST['newPassword'])|| !is_string($_POST['newPasswordRetype'])) {
            //Don't log the password and token
            performLog("Error", "Invalid email or password, not a string", array("token" => "token"));
            $_SESSION['errorMsg'] = "Something went wrong with your request";
            header("Location: index.php");
            exit();
        }
        $newPassword = $_POST["newPassword"];
        $newPasswordRetype = $_POST["newPasswordRetype"];

        //hex2bin returns false if the input is not a valid hex string
        $token = hex2bin($_POST["token"]);
        if(!$token || !is_string($token)){ // Also check for potential type juggling
            performLog("Error", "Invalid token, cannot convert", array("token" => $_POST["token"]));
            $_SESSION['errorMsg'] = "Something went wrong with your request! Try to reset your password again.";
            header('Location: index.php');
            exit();
        }
        // Check if the passwords match and are not empty
        if($newPassword === $newPasswordRetype && $newPassword != "" && $newPasswordRetype != "") {
            // Get the user id from the token, if it exists and is valid it is deleted and the password can be changed
            $userid = getUidFromToken($token);
            if ($userid) {
                if (deleteToken($token)) {
                    if (changePasswordById($userid, $newPassword)) {
                        $_SESSION['success'] = "Your password was successfully reset";
                        header('Location: login.php');
                    } else {
                        // Can log the token because it is deleted and randomBytes is cryptographically secure
                        performLog("Error", "Failed to reset password", array("userid" => $userid, "token" => $token));
                        $_SESSION['errorMsg'] = "Something went wrong with your request! Request a new password reset.";
                        header('Location: index.php');

                    }
                    exit();

                } else {
                    // Do not log the token since it is not deleted, but it is in the database
                    performLog("Error", "Failed to delete reset token", array("userid" => $userid));
                    $_SESSION['errorMsg'] = "Something went wrong with your request! Request a new password reset.";
                    header('Location: index.php');
                    exit();
                }
            } else {
                // Can log the token since it is not a valid one (not in the DB)
                // Even if attackers have access to the log, and can infer which tokens are not present in the DB,
                // they still need to bruteforce ~O(16 bytes) of information
                performLog("Error", "missing user id in reset token", array("userid" => $userid, "token" => $_POST['token']));
                $_SESSION['errorMsg'] = "Something went wrong with your request! Try to reset your password again.";
                header('Location: index.php');
                exit();
            }
        } else{
            $_SESSION['message'] = "Passwords do not match or are empty";
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

<header>
    <h1>Password reset</h1>
    <nav>
    <a href="index.php">Back to Home</a>
    </nav>
</header>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="error message">
            <h3>
                <?php
                echo htmlspecialchars($_SESSION['message']);
                unset($_SESSION['message']);
                ?>
            </h3>
        </div>
    <?php endif ?>
<br>
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
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? '') ; ?>" readonly>
        <div class="input-group">
            <button type="submit" id="btn" name="resetPassword_btn">Reset Password</button>
        </div>
    </form>
    <label for="strength">password strength: </label>
    <progress id="strength" value="0" max="4"> password strength </progress>
    <p id="warning"></p>
    <p id="suggestions"></p>



</body>
