<?php
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

function changePassword($username, $newPassword): bool
{
    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = "rootroot";
    $dbname = "securebooksellingdb";

    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // use prepared statements to change the password of a user
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE username=?");
    $stmt->bind_param("ss", $newPassword, $username);
    mysqli_stmt_execute($stmt);
    // check if insertion was successful
    if ($stmt->affected_rows > 0) {
        $conn->close();
        return true;
    } else {
        $conn->close();
        return false;
    }
}


// create a function to check if a user exists in the database
function checkUser($username): bool
{
    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = "rootroot";
    $dbname = "securebooksellingdb";

    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }


    $stmt = $conn->prepare("SELECT username FROM users WHERE username=? ");
    $stmt->bind_param("s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    // check if insertion was successful
    if ($stmt->affected_rows > 0) {
        $conn->close();
        return true;
    } else {
        $conn->close();
        return false;
    }
}
