<?php

require_once 'utils/dbUtils.php';
require_once 'utils/Logger.php';
session_start_or_expire();
// Code used to get the domain to create the link in the email
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
if(str_contains($_SERVER['SERVER_NAME'], "PhpStorm")){
    $DOMAIN = $_ENV['DEV_DOMAIN'];
} else {
    $DOMAIN = $_ENV['DOMAIN'];
}
if(isset($_POST['email'])){

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] != $_SESSION['csrf_token']) {
        performLog("Error", "CSRF token mismatch", array("token" => $_POST['csrf_token']));
        $_SESSION['errorMsg'] = "Something went wrong with your request";
        header('Location: index.php');
        exit();
    }
    // Check if the email is a string for type juggling
    if (!is_string($_POST['email'])) {
        performLog("Error", "Invalid email (resetpassword), not a string", array("mail" => $_POST['email']));
        $_SESSION['errorMsg'] = "Something went wrong with your request";
        header("Location: index.php");
        exit();
    }
    // Generate a 16 random bytes token
    $token = random_bytes(16);
    $email = $_POST['email'];
    // Check if the user exists
    $userArray = getUser($email);
    if(count($userArray) > 0){
        // If the user exists, check if it is active
        // If not, we don't send the email
        $userId = $userArray["id"];
        if(!$userArray["active"]){
            performLog("Warning", "Reset password for disabled user", array("email" => $email));
            $_SESSION['errorMsg'] = "Your account is disabled. Please activate your account first.";
        } elseif (saveToken($token, $userId, 5)) { // User is active, we try to save the token and send mail
            $subject = "Reset Email";
            $message = "This is a reset email. Click on the link to reset your password\n"
                . $DOMAIN . "/resetpassword-token.php?token=" . bin2hex($token);

            // Additional headers
            $headers = "From: " . $_ENV['NO_REPLY_EMAIL'] . "\r\n";
            // Send email
            $mailSuccess = mail($email, $subject, $message, $headers);

            if ($mailSuccess) {
                performLog("Info", "Password reset link sent to user", array("email" => $email));
                $_SESSION['success'] = "Password reset link sent to your email";
            } else {
                $_SESSION['errorMsg'] = "Failed to send email";
                performLog("Error", "Failed to send email", array("email" => $email));
                session_unset();
                session_destroy();
                header('Location: 500.html');
                exit();
            }


        } else { // user is active but cannot save token (limit of active token or other error)
            performLog("Error", "Failed to save token", array("email" => $email));
            // This is a fake message to avoid account enumeration
            $_SESSION['success'] = "Password reset link sent to your email";
        }
    } else{ // cannot find user
        //This is a fake message to avoid account enumeration
        performLog("Warning", "Reset password for not existing user", array("email" => $email));
        $_SESSION['success'] = "Password reset link sent to your email";
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
<?php if(!isset($_SESSION['success']) && !isset($_SESSION['errorMsg'])): ?>
    <header>
        <h1>Reset Password</h1>
        <nav>
            <a href="index.php">Back to Home</a>
        </nav>
    </header>
    <br>
    <form method="post" action="resetpassword.php">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?>" readonly="readonly" >
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required="required">
        <button type="submit" name="reset_btn">Reset</button>
    </form>

<?php else: ?>
    <?php include 'utils/messages.php' ?>
<?php endif ?>

</body>
</html>