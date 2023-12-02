<?php
// TODO: extend with mail confirmation
if (isset($_POST['username']) || isset($_POST['password'])) {

    // User's inputted password
    $user_input_password = $_POST['password'];

    // Hash the password using bcrypt
    $hashed_password = password_hash($user_input_password, PASSWORD_BCRYPT);

    // Store the hashed password in the database
    $servername = "localhost";
    $username = "root";
    $password = "rootroot";
    $dbname = "securebooksellingdb";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // use prepared statements to insert into users

    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $_POST['username'], $hashed_password);
    $stmt->execute();
    // check if insertion was successful
    if ($stmt->affected_rows > 0) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $conn->error;
    }
    $conn->close();
}

?>

<DOCTYPE html>
    <html lang="en">
    <head>
        <title>Secure Book selling website</title>
        <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
    </head>
    <body>
    <h1>Register</h1>
    <form method="post" action="register.php">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required="required">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required="required">
        <button type="submit">Register</button>
    </form>
    </body>
</html>

    