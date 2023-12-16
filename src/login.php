<?php
// TODO: implement session upgrading
// TODO: implement password recovery
require_once '../vendor/autoload.php';
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
session_start();
// verifyLogin() is a function that verifies the user's login credentials with the database
require_once 'utils/dbUtils.php';

date_default_timezone_set('Europe/Rome');
$log = new Logger('LoginAttempt');
$log->pushHandler(new StreamHandler('../logfile.log', Level::Warning));

if (isset($_POST['username']) || isset($_POST['password'])) {
    // Get username and password from the form submitted by the user
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    // Assume $username and $password are the submitted credentials
    // You need to replace this with your actual login verification logic
    if (verifyLogin($username, $password)) {
        // Correct login
        $_SESSION['username'] = $username;
        // change session id to prevent session fixation
        session_regenerate_id();
        // You can set other session variables as needed
        // To Redirect the user to the home page or another secure page
        if (isset($_POST['redirect'])) {
            header('Location: ' . $_POST['redirect']);
            exit();
        }
        header('Location: index.php');
        exit();
    } else {
        // Incorrect login
        $log->warning('Invalid login');
        echo "Invalid login credentials. Try again later!";
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
    <!-- show a form to login -->
    <h1>Login</h1>
    <p>Back to <a href="index.php">Home</a></p>
    <form method="post" action="login.php">
        <label for="username">Username</label>
        <input type="text" name="username" id="username">
        <label for="password">Password</label>
        <input type="password" name="password" id="password">
        <?php if(isset($_GET['redirect'])) {
            echo "<input type='hidden' value='" . $_GET['redirect'] . "' name='redirect'>";
        }
        ?>
        <button type="submit" name="login_btn">Login</button>

    </form>
    <p><a href="resetpassword.php">Forgot password?</a></p>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
</body>