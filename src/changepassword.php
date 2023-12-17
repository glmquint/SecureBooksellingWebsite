<?php
require_once 'utils/dbUtils.php';
session_start_or_expire();


if (isset($_POST['OldPassword']) && isset($_POST['NewPassword'])&& isset($_SESSION['username'])) {
    // Get username and password from the form submitted by the user
    $OldPassword = $_POST['OldPassword'] ?? '';
    $NewPassword = $_POST['NewPassword'] ?? '';
    $username = $_SESSION['username'];
    // Assume $username and $password are the submitted credentials
    // You need to replace this with your actual login verification logic
    if (verifyLogin($username, $OldPassword)) {
        // Hash the password using bcrypt
        $hashed_password = password_hash($NewPassword, PASSWORD_BCRYPT);
        if(changePassword($username, $hashed_password)){
            $_SESSION['success'] = "Password changed successfully";
            header('Location: changepassword.php');
            exit();
        }
        else{
            echo "Invalid login credentials";
        }

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

<!-- if the user is logged in, show a message -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="error success">
        <h3>
            <?php
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </h3>
        <a href="index.php">Back to Home</a>
    </div>
<!-- else, show a link to the changepassword page -->
<?php else: ?>

    <!-- show a form to login -->
    <a href="index.php">Back to Home</a>
    <h1>Change Password</h1>
    <form method="post" action="changepassword.php">
        <div class="input-group">
            <label>Old password</label>
            <label>
                <input type="password" name="OldPassword">
            </label>
        </div>
        <div class="input-group">
            <label>New Password</label>
            <label>
                <input type="password" name="NewPassword">
            </label>
        </div>
        <div class="input-group">
            <button type="submit" name="change_btn">Change</button>
        </div>
    </form>

<?php endif ?>

</body>