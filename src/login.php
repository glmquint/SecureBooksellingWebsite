<?php
// TODO: implement session upgrading
// TODO: implement password recovery
require_once 'utils/dbUtils.php';
require_once 'utils/Logger.php';
session_start_or_expire();

if (isset($_POST['email']) || isset($_POST['password'])) {
    // Get username and password from the form submitted by the user
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    // Assume $username and $password are the submitted credentials
    // You need to replace this with your actual login verification logic
    $userValidity = verifyLogin($email, $password);
    if ($userValidity) {
        if ($userValidity == 1) {
            echo "Verify your email first";
        }else {
            // Correct login
            performLog("Info", "User logged in", array("email" => $email));
            $_SESSION['email'] = $email;
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
        }
    } else {
        // Incorrect login
        performLog("Warning", "Incorrect login attempt ", array("email" => $email));
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
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required="required">
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