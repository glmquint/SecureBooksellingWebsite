<?php
require_once 'utils/dbUtils.php';
require_once 'utils/Logger.php';
// Code used to get the domain to create the link in the email
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
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
        $_SESSION['message'] = "Something went wrong with your request, please try again later with different email or password";
        header("Location: register.php");
        exit();
    }
    // Register the user
    // If [] is returned, something went wrong
    $userArray=registerUser($_POST['email'], $_POST['password']);
    if(count($userArray) > 0){
        // Generate a 16 random bytes token
        $token = random_bytes(16);
        $email = $_POST['email'];
        $headers = "From: noreply@localhost.com";
        // If the user already exists and is active, send a password reset email (limit of active token)
        if($userArray['exists'] && $userArray['active'] && saveToken($token, $userArray['id'], 5)){
            performLog("Warning", "Invalid register", array("mail" => $_POST['email']));
            $subject = "Invalid register attempt";
            $message = "Someone tried to register with your email address. If it was you, click on the link to reset your password\n"
                    . $DOMAIN . "/resetpassword-token.php?token=" . bin2hex($token). "\n"
                    . "If it wasn't you, ignore this email";
            // Send email
            try{
                $mailSuccess = mail($email, $subject, $message, $headers);

                if ($mailSuccess) {
                    $_SESSION['message'] = "Account registered, a confirmation mail was send to your email address";
                    performLog("Info", "Password reset email sent", array("mail" => $_POST['email']));
                } else {
                    $_SESSION['message'] = "Failed to send email";
                    performLog("Error", "Failed to send email", array("mail" => $_POST['email']));
                    throw new Exception("Email not existing");
                }
            }
            catch (Exception $e){
                performLog("Error", "Failed to send email", array("mail" => $_POST['email'], "error" => $e->getCode(), "message" => $e->getMessage()));
                session_unset();
                session_destroy();
                header('Location: 500.html');
                exit();
            }
        }
        // Otherwise, send an activation email
        else if(saveToken($token,  $userArray['id'], 60)) {
            $subject = "Activation account";
            $message = "This is a activation email. Click on the link to activate your account\n"
                    . $DOMAIN . "/activate-token.php?token=" . bin2hex($token);

            try{
                // Send email
                $mailSuccess = mail($email, $subject, $message, $headers);

                if ($mailSuccess) {
                    $_SESSION['message'] = "Account registered, a confirmation mail was send to your email address";
                    performLog("Info", "New user registered, confirmation mail sent", array("mail" => $_POST['email']));
                } else {
                    $_SESSION['message'] = "Failed to send email";
                    performLog("Error", "Failed to send email", array("mail" => $_POST['email']));
                    throw new Exception("Email not existing");
                }
            } catch (Exception $e) {
                performLog("Error", "Failed to send email", array("mail" => $_POST['email'], "error" => $e->getCode(), "message" => $e->getMessage()));
                session_unset();
                session_destroy();
                header('Location: 500.html');
                exit();
            }

        }
        else{
            // This is a fake message to avoid account enumeration (too many register on the same account)
            $_SESSION['message'] = "Account registered, a confirmation mail was send to your email address";
            performLog("Error", "Failed to generate registration token", array( "mail" => $_POST['email'], "token" => bin2hex($token)));
        }

    }
    else{
        performLog("Warning", "Invalid credentials during registration", array( "mail" => $_POST['email']));
        $_SESSION['message'] = "Something went wrong with your request";
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
    <?php if (isset($_SESSION['message'])): ?>
        <header>
            <h3>
                <?php
                echo htmlspecialchars($_SESSION['message']);
                unset($_SESSION['message']);
                ?>
            </h3>
            <nav>
                <a href="index.php">Back to Home</a>
            </nav>
        </header>

    <?php else: ?>
    <header>
        <h1>Register</h1>
            <nav>
                <a href="index.php">Back to Home</a>
            </nav>
    </header>
    <hr>
        <form method="post" action="register.php">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required="required">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required="required" oninput=checkPasswordStrength(document.getElementById('password').value)>
            <button id="btn" type="submit">Register</button>
        </form>
        <label for="strength">password strength: </label>
        <progress id="strength" value="0" max="4"> password strength </progress>
        <p id="warning"></p>
        <p id="suggestions"></p>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    <?php endif ?>
    </body>
</html>

    