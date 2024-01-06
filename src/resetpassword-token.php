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
        if (!is_string($_POST['newPassword'])|| !is_string($_POST['newPasswordRetype'])) {
            //Don't log the password and token
            performLog("Error", "Invalid email or password, not a string", array("token" => "token"));
            $_SESSION['errorMsg'] = "Something went wrong with your request";
            header("Location: index.php");
            exit();
        }
        $newPassword = $_POST["newPassword"];
        $newPasswordRetype = $_POST["newPasswordRetype"];
        $token = hex2bin($_POST["token"]);

        //hex2bin returns false if the input is not a valid hex string
        if(!$token || !is_string($token)){
            performLog("Error", "Invalid token, cannot convert", array("token" => $_POST["token"]));
            $_SESSION['errorMsg'] = "Something went wrong with your request! Try to reset your password again.";
            header('Location: index.php');
            exit();
        }
        if($newPassword === $newPasswordRetype && $newPassword != "" && $newPasswordRetype != "") {
            $userid = getUidFromToken($token);
            if ($userid) {
                if (deleteToken($token)) {
                    if (changePasswordById($userid, $newPassword)) {
                        $_SESSION['success'] = "Your password was successfully reset";
                        header('Location: login.php');
                        exit();
                    } else {
                        performLog("Error", "Failed to reset password", array("userid" => $userid, "token" => $_POST['token']));
                        $_SESSION['errorMsg'] = "Something went wrong with your request! Try to reset your password again.";
                        header('Location: index.php');
                        exit();

                    }

                } else {
                    performLog("Error", "Failed to delete reset token", array("userid" => $userid, "token" => $_POST['token']));
                    $_SESSION['errorMsg'] = "Something went wrong with your request! Try to reset your password again.";
                    header('Location: index.php');
                    exit();
                }
            } else {
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

    <h1>Password reset</h1>
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
    <a href="index.php">Back to Home</a>
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
