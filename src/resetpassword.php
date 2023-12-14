<?php


session_start();
require_once 'utils/dbUtils.php';



if(isset($_POST['username'])){
    $token = random_int(100000, 999999999);
    $username = $_POST['username'];
    $userArray = checkUser($username);
    if(count($userArray) > 0){
        $userId = $userArray[0];
        $email = $userArray[1];
        if(saveToken($token, $userId)) {
            $subject = "Reset Email";
            $message = "This is a reset email. Click on the link to reset your password\n"
                . "http://localhost:63342/snh-securebooksellingwebsite/src/resetpassword-token.php?token=" . strval($token);

            // Additional headers
            $headers = "From: noreply@localhost.com";

            // Send email
            $mailSuccess = mail($email, $subject, $message, $headers);

            if ($mailSuccess) {
                $_SESSION['success'] = "Password reset link sent to your email " . strval($token);
                header('Location: resetpassword.php');
                exit();
            } else {
            echo "Failed to send email.";
            }

        }
        else{
            echo "failed inserting token";
        }
    }
    else{
        echo "Invalid username";
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
    <!-- else, show a link to the resetpassword page -->
<?php else: ?>

    <!-- show a form to login -->
    <a href="index.php">Back to Home</a>
    <h1>Reset Password</h1>
    <form method="post" action="resetpassword.php">
        <div class="input-group">
            <label>Username</label>
            <label>
                <input type="text" name="username">
            </label>
        </div>
        <div class="input-group">
            <button type="submit" name="reset_btn">Reset</button>
        </div>
    </form>

<?php endif ?>
