<?php
// TODO: implement session upgrading
// TODO: implement password recovery
session_start();
// verifyLogin() is a function that verifies the user's login credentials with the database
require_once 'utils/dbUtils.php';

if (isset($_POST['username']) || isset($_POST['password'])) {
    // Get username and password from the form submitted by the user
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    // Assume $username and $password are the submitted credentials
    // You need to replace this with your actual login verification logic
    if (verifyLogin($username, $password)) {
        // Correct login
        $_SESSION['username'] = $username;
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
        echo "Invalid login credentials";
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
    <a href="index.php">Back to Home</a>
    <h1>Login</h1>
    <form method="post" action="login.php">
        <div class="input-group">
            <label>Username</label>
            <label>
                <input type="text" name="username">
            </label>
        </div>
        <div class="input-group">
            <label>Password</label>
            <label>
                <input type="password" name="password">
            </label>
            <?php if(isset($_GET['redirect'])) {
                echo "<input type='hidden' value='" . $_GET['redirect'] . "' name='redirect'>";
            }
            ?>
        </div>
        <div class="input-group">
            <button type="submit" name="login_btn">Login</button>
        </div>
    </form>
</body>