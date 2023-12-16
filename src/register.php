<?php
require_once 'utils/dbUtils.php';
require_once 'utils/Logger.php';

// TODO: extend with mail confirmation
if (isset($_POST['username']) || isset($_POST['password'])) {

    // User's inputted password
    $user_input_password = $_POST['password'];

    // Hash the password using bcrypt
    $hashed_password = password_hash($user_input_password, PASSWORD_BCRYPT);

    $db = new DBConnection();

    // Store the hashed password in the database
    // use prepared statements to insert into users

    $stmt = $db->conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $_POST['username'], $hashed_password, $_POST['email']);
    $stmt->execute();
    // check if insertion was successful
    if ($stmt->affected_rows > 0) {
        performLog("Info", "New user registered", array("username" => $_POST['username'],"IP" => $_SERVER['REMOTE_ADDR']));
        echo "New user created successfully";

    } else {
        performLog("Warning", "New user registration failed", array("username" => $_POST['username'],"IP" => $_SERVER['REMOTE_ADDR']));
        echo "Error: " . $db->conn->error;
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
    <h1>Register</h1>
    <p>Back to <a href="index.php">Home</a></p>
    <form method="post" action="register.php">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required="required">
        <label for="email">Email</label>
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
    </body>
</html>

    