<?php

function getConnection(): mysqli
{
    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = "rootroot";
    $dbname = "securebooksellingdb";

    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
function verifyLogin($username, $password): bool
{
// Retrieve the hashed password from the database based on the username
// Replace the following lines with your database connection and query

    $conn = getConnection();

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
    $hashed_password = password_hash($newPassword, PASSWORD_BCRYPT);
    $conn = getConnection();

    // use prepared statements to change the password of a user
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE username=?");
    $stmt->bind_param("ss", $hashed_password, $username);
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

function changePasswordId($userId, $newPassword): bool
{
    $hashed_password = password_hash($newPassword, PASSWORD_BCRYPT);
    $conn = getConnection();

    // use prepared statements to change the password of a user
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param("si", $hashed_password, $userId);
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
function checkUser($username): array
{
    $conn = getConnection();

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=? ");
    $stmt->bind_param("s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    // check if insertion was successful
    if ($stmt->affected_rows > 0) {
        $row = mysqli_fetch_array($result);
        $conn->close();
        return array($row["id"], $row["email"]);
    } else {
        $conn->close();
        return [];
    }
}


function saveToken($token, $userId): bool
{
    date_default_timezone_set('Europe/Rome');
    //generate a time to live for the token express in datetime
    $ttl = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    $conn = getConnection();

    //create a prepared statemt to insert the token, the user and ttl into the database
    $stmt = $conn->prepare("INSERT INTO reset_token (token, user_id, expiration_date) VALUES (?, ?, ?)");
    //bind the parameters where token is an integer, username is a strings and ttl is datetime
    $stmt->bind_param("iss", $token, $userId, $ttl);
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

//create a function to check if a token exists in the database
function checkToken($token): int
{
    date_default_timezone_set('Europe/Rome');
    //get the current date and time
    $currentDate = date('Y-m-d H:i:s');

    $conn = getConnection();

    //create a prepare statement to get the token and the expiration_date
    $stmt = $conn->prepare("SELECT token, expiration_date, user_id FROM reset_token WHERE token=?");
    //bind the token parameter
    $stmt->bind_param("i", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    // check if insertion was successful
    if ($stmt->affected_rows > 0) {
        $row = mysqli_fetch_array($result);
        $conn->close();
        //check if the token is expired
        if ($row["expiration_date"] > $currentDate) {
            return $row["user_id"];
        } else {
            //delete the token from the database
            deleteToken($token);
            return -1;
        }
    } else {
        $conn->close();
        return false;
    }

}

//create a function to delete a token from the database
function deleteToken($token): bool
{
    $conn = getConnection();

    //create a prepare statement to delete the token from the database
    $stmt = $conn->prepare("DELETE FROM reset_token WHERE token=?");
    //bind the token parameter
    $stmt->bind_param("i", $token);
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


// TODO: maybe refactor at login time -> set uid in session
function getUserID($conn,$username): int
{
    if(!$conn) {
        $servername = "localhost";
        $dbusername = "root";
        $dbpassword = "rootroot";
        $dbname = "securebooksellingdb";

        $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    }
    $stmt = $conn->prepare("SELECT id FROM users WHERE username=?");

    $stmt->bind_param("s", $username);

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row['id'];
}

?>
