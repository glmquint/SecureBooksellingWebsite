<?php
session_start();
// verifyLogin() is a function that verifies the user's login credentials with the database
function verifyLogin($username, $password): bool
{
    $db = mysqli_connect('localhost', 'root', 'rootroot', 'securebooksellingdb');
    // user prepared statements to prevent SQL injection
    $stmt = mysqli_prepare($db, "SELECT * FROM users WHERE username=? AND password=?");
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    // if the user is found, return true
    if (mysqli_num_rows($result) == 1) {
        return true;
    } else {
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

<!-- if the user is logged in, show a message -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="error success">
        <h3>
            <?php
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </h3>
    </div>
<!-- else, show a link to the login page -->
<?php else: ?>

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

<?php endif ?>

</body>