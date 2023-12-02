<?php
// TODO: implement session upgrading
// TODO: implement password recovery
session_start();
// verifyLogin() is a function that verifies the user's login credentials with the database
function verifyLogin($username, $password): bool
{
// Retrieve the hashed password from the database based on the username
// Replace the following lines with your database connection and query
    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = "rootroot";
    $dbname = "securebooksellingdb";

    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }


    $stmt = $conn->prepare("SELECT password FROM users WHERE username=? ");
    $stmt->bind_param("s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    // check if insertion was successful
    if ($stmt->affected_rows > 0) {
        // Password hash found in the database
        $row = mysqli_fetch_array($result);
        $conn->close();
        $stored_hashed_password = $row["password"];

        // Verify the entered password against the stored hash
        if (password_verify($password, $stored_hashed_password)) {
            return true;
        } else {
            return false;
        }
    } else {
        $conn->close();
        return false;
    }

}

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
        </div>
        <div class="input-group">
            <button type="submit" name="login_btn">Login</button>
        </div>
    </form>
</body>