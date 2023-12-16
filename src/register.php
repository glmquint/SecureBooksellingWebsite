<?php
// TODO: extend with mail confirmation
require_once 'utils/dbUtils.php';
if (isset($_POST['username']) || isset($_POST['password'])) {

    // User's inputted password
    $user_input_password = $_POST['password'];
    $userId=registerUser($_POST['username'], $user_input_password, $_POST['email']);
    if($userId!=-1){
        $token = random_int(100000, 999999999);
        if(saveToken($token, $userId, 60)) {
            $email = $_POST['email'];
            $subject = "Activation account";
            $message = "This is a activation email. Click on the link to activate your account\n"
                . "http://localhost:63342/snh-securebooksellingwebsite/src/activate-token.php?token=" . strval($token);

            // Additional headers
            $headers = "From: noreply@localhost.com";

            // Send email
            $mailSuccess = mail($email, $subject, $message, $headers);

            if ($mailSuccess) {
                $_SESSION['success'] = "Account registered, a confirmation mail was send to your email address";
                header('Location: register.php');
                exit();
            } else {
                echo "Failed to send email.";
            }
        }
        else{
            echo "something went wrong";
        }

    }
    else{
        echo "Invalid login credentials";
    }

}

?>

<DOCTYPE html>
    <html lang="en">
    <head>
        <title>Secure Book selling website</title>
        <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
        <script src="utils/checkPasswordStrength.js"></script>
    </head>
    <body>
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

    <?php else: ?>
        <h1>Register</h1>
        <p>Back to <a href="index.php">Home</a></p>
        <form method="post" action="register.php">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" required="required">
            <label for="Email">Email</label>
            <input type="email" name="email" id="email" required="required">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required="required" oninput=checkPasswordStrength(document.getElementById('password').value)>
            <button id="registerbtn" type="submit">Register</button>
        </form>
        <label for="strength">password strength: </label>
        <progress id="strength" value="0" max="4"> password strength </progress>
        <p id="warning"></p>
        <p id="suggestions"></p>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    <?php endif ?>
    </body>
</html>

    