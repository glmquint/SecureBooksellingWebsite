<?php
require_once '../utils/dbUtils.php';
require_once '../utils/Logger.php';
// Code used to get the domain to create the link in the email
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
if(str_contains($_SERVER['SERVER_NAME'], "PhpStorm")){
    $DOMAIN = $_ENV['DEV_DOMAIN'];
} else {
    $DOMAIN = $_ENV['DOMAIN'];
}

if (isset($_POST['email']) || isset($_POST['password'])) {
    // Check if the email and password are strings for type juggling
    if (!is_string($_POST['email'])|| !is_string($_POST['password'])) {
        performLog("Error", "Invalid email or password, not a string", array("mail" => $_POST['email']));
        $_SESSION['errorMsg'] = "Something went wrong with your request, please try again later with different email or password";
        header("Location: register.php");
        exit();
    }

    /*
     *  if registerUser(mail, pwd): # insert with unique mail
     *     mail(activate account)
     *  elif userExists(mail):
     *     if userActive(mail):
     *        mail(possible hijack or reset password)
     *     else:
     *        mail(activate account)
     *  else:
     *     error
     */


    // Register the user
    // If [] is returned, something went wrong
    $email = $_POST['email'];
    // Generate a 16 random bytes token
    $token = random_bytes(16);
    $headers = "From: noreply@localhost.com";
    $userId=registerUser($email, $_POST['password']);
    if($userId){
        if (!saveToken($token, $userId, 60)){
            performLog("Error", "Failed to generate registration token", array( "mail" => $_POST['email']));
            // This is a fake message to avoid account enumeration (too many register on the same account)
            $_SESSION['success'] = "Account registered, a confirmation mail was sent to your email address";
        }
        else {
            $subject = "Activation account";
            $message = "This is a activation email. Click on the link to activate your account\n"
                . $DOMAIN . "/activate-token.php?token=" . bin2hex($token);

            // Send email
            $mailSuccess = mail($email, $subject, $message, $headers);

            if ($mailSuccess) {
                performLog("Info", "New user registered, confirmation mail sent", array("mail" => $_POST['email']));
                $_SESSION['success'] = "Account registered, a confirmation mail was sent to your email address";
            } else {
                performLog("Error", "Failed to send email", array("mail" => $_POST['email']));
                $_SESSION['errorMsg'] = "Failed to send email";
                session_unset();
                session_destroy();
                header('Location: 500.html');
                exit();
            }
        }
    }
    else if (($userArray = getUser($email)) && count($userArray) > 0){ // User already exists
        if($userArray['active']){
            if(saveToken($token,  $userArray['id'], 5)){
                performLog("Warning", "Invalid register", array("mail" => $_POST['email']));
                $subject = "Invalid register attempt";
                $message = "Someone tried to register with your email address. If it was you, click on the link to reset your password\n"
                    . $DOMAIN . "/resetpassword-token.php?token=" . bin2hex($token). "\n"
                    . "If it wasn't you, ignore this email";
                // Send email
                $mailSuccess = mail($email, $subject, $message, $headers);

                if ($mailSuccess) {
                    performLog("Info", "Password reset email sent", array("mail" => $_POST['email']));
                    $_SESSION['success'] = "Account registered, a confirmation mail was sent to your email address";
                } else {
                    performLog("Error", "Failed to send email", array("mail" => $_POST['email']));
                    $_SESSION['errorMsg'] = "Failed to send email";
                    session_unset();
                    session_destroy();
                    header('Location: 500.html');
                    exit();
                }
            }
            else{
                performLog("Warning", "User active but with too many tokens", array( "mail" => $_POST['email']));
                // This is a fake message to avoid account enumeration (too many register on the same account)
                $_SESSION['success'] = "Account registered, a confirmation mail was sent to your email address";
            }

        }
        else{
            if(saveToken($token,  $userArray['id'], 60)){
                $subject = "Activation account";
                $message = "This is a activation email. Click on the link to activate your account\n"
                    . $DOMAIN . "/activate-token.php?token=" . bin2hex($token);

                // Send email
                $mailSuccess = mail($email, $subject, $message, $headers);

                if ($mailSuccess) {
                    performLog("Info", "New user registered, confirmation mail sent", array("mail" => $_POST['email']));
                    $_SESSION['success'] = "Account registered, a confirmation mail was sent to your email address";
                } else {
                    performLog("Error", "Failed to send email", array("mail" => $_POST['email']));
                    $_SESSION['errorMsg'] = "Failed to send email";
                    session_unset();
                    session_destroy();
                    header('Location: 500.html');
                    exit();
                }
            }
            else{
                performLog("Warning", "User not active with too many tokens", array( "mail" => $_POST['email']));
                // This is a fake message to avoid account enumeration (too many register on the same account)
                $_SESSION['success'] = "Account registered, a confirmation mail was sent to your email address";
            }

        }

    }
    else{
        performLog("Warning", "Invalid credentials during registration (how did we end up here?)", array( "mail" => $_POST['email']));
        // This is a fake message to avoid account enumeration (too many register on the same account)
        $_SESSION['success'] = "Account registered, a confirmation mail was sent to your email address";
    }


}



?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Secure Book selling website</title>
        <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
        <script src="js/checkPasswordStrength.js"></script>
    </head>
    <body>
    <?php if(!isset($_SESSION['success']) && !isset($_SESSION['errorMsg'])): ?>
        <header>
            <h1>Register</h1>
                <nav>
                    <a href="index.php">Back to Home</a>
                </nav>
        </header>
        <br>
            <form method="post" action="register.php">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required="required">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required="required" oninput="checkPasswordStrength(document.getElementById('password').value, document.getElementById('email').value)">
                <button id="btn" type="submit">Register</button>
            </form>
            <label for="strength">password strength: </label>
            <progress id="strength" value="0" max="4"> password strength </progress>
            <p id="warning"></p>
            <p id="suggestions"></p>
            <p>Already have an account? <a href="login.php">Login here</a></p>
    <?php else: ?>
        <?php include '../utils/messages.php' ?>
    <?php endif ?>
    </body>
</html>

    